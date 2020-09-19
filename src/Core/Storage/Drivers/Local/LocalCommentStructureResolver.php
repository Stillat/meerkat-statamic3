<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Storage\Paths;
use Stillat\Meerkat\Core\Threads\ThreadHierarchy;

/**
 * Class LocalCommentStructureResolver
 *
 * Resolves the hierarchy structure of a comment thread from a filesystem.
 *
 * @package Stillat\Meerkat\Core\Storage\Drivers\Local
 * @since 2.0.0
 */
class LocalCommentStructureResolver implements StructureResolverInterface
{

    /**
     * A list of all the paths processed by the resolver.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The absolute path to the thread's storage.
     *
     * @var string
     */
    protected $threadPath = '';

    /**
     * The total length of the thread's storage path.
     *
     * @var int
     */
    protected $threadPathLength = 0;

    /**
     * The path reply value that will be replaced.
     *
     * Typically /replies
     *
     * @var string
     */
    protected $replyReplacement = '';

    /**
     * A mapping between comment identifiers and their storage paths.
     *
     * @var array
     */
    protected $commentIdPathMapping = [];

    /**
     * A mapping between depths and comment identifiers.
     *
     *    [depth][] = 'identifier'
     *
     * @var array
     */
    protected $depthMapping = [];

    /**
     * A mapping between identifiers and depths.
     *
     *     [identifier] = depth
     *
     * @var array
     */
    protected $commentDepthMapping = [];

    /**
     * A mapping between identifiers and direct ancestors.
     *
     *     [identifier][] = 'ancestor-identifier'
     *
     * @var array
     */
    protected $directAncestorMapping = [];

    /**
     * A mapping between identifiers and direct descendents.
     *
     *     [identifier][] = 'descendent-identifier'
     *
     * @var array
     */
    protected $directDescendentMapping = [];

    /**
     * A mapping between identifiers and all ancestors.
     *
     *     [identifier][] = 'any-ancestor-identifier'
     *
     * @var array
     */
    protected $ancestorMapping = [];

    /**
     * A mapping between identifiers and all descendents.
     *
     *     [identifier][] = 'any-descendent-identifier'
     *
     * @var array
     */
    protected $descendentMapping = [];

    /**
     * A mapping between identifiers and their potential replies path.
     *
     *     [identifier] = 'reply-path'
     *
     * @var array
     */
    protected $internalRepliesPathMapping = [];

    /**
     * A list of previously resolve hierarchies.
     *
     * @var array
     */
    private $hierarchyCache = [];

    public function __construct()
    {
        $this->replyReplacement = Paths::SYM_FORWARD_SEPARATOR . LocalCommentStorageManager::PATH_REPLIES_DIRECTORY;
    }

    /**
     * Resolves the comment dependency graph.
     *
     * @param string $threadPath The thread's base path.
     * @param array $commentPaths A collection of comment absolute paths.
     * @return ThreadHierarchy
     */
    public function resolve($threadPath, $commentPaths)
    {
        if (array_key_exists($threadPath, $this->hierarchyCache)) {
            return $this->hierarchyCache[$threadPath];
        }

        $this->reset();

        $hierarchy = new ThreadHierarchy();

        $this->threadPathLength = mb_strlen($threadPath) + 1;
        $this->threadPath = $threadPath;

        usort($commentPaths, [$this, 'compareLength']);

        // Pre-process all the comment paths.
        foreach ($commentPaths as $path) {
            $structurePath = mb_substr($path, $this->threadPathLength);
            $structurePath = mb_substr($structurePath, 0, -11);
            $structurePath = str_replace($this->replyReplacement, '', $structurePath);

            $structureId = mb_substr($structurePath, -10);
            $structureDepth = substr_count($structurePath, Paths::SYM_FORWARD_SEPARATOR);
            $internalReplyPath = mb_substr($path, 0, -10) . LocalCommentStorageManager::PATH_REPLIES_DIRECTORY;

            if (array_key_exists($structureDepth, $this->depthMapping) == false) {
                $this->depthMapping[$structureDepth] = [];
            }

            if (array_key_exists($structureId, $this->internalRepliesPathMapping) == false) {
                $this->internalRepliesPathMapping[$structureId] = $internalReplyPath;
            }

            $internalReplyPath = null;

            $this->depthMapping[$structureDepth][] = $structureId;
            $this->commentDepthMapping[$structureId] = $structureDepth;

            $this->commentIdPathMapping[$structureId] = $path;

            if ($structureId != $structurePath) {
                $ancestorGraph = explode(Paths::SYM_FORWARD_SEPARATOR, $structurePath);

                if (count($ancestorGraph) > 0 && mb_strlen($ancestorGraph[0]) === 36) {
                    array_shift($ancestorGraph);
                }

                $descendentGraph = $ancestorGraph;

                array_pop($ancestorGraph);

                if (count($ancestorGraph) > 0) {
                    $parentCommentId = $ancestorGraph[count($ancestorGraph) - 1];

                    if (array_key_exists($structureId, $this->ancestorMapping) == false) {
                        $this->ancestorMapping[$structureId] = [];
                    }

                    if (array_key_exists($structureId, $this->directAncestorMapping) == false) {
                        $this->directAncestorMapping[$structureId] = $parentCommentId;
                    }

                    if (array_key_exists($parentCommentId, $this->directDescendentMapping) == false) {
                        $this->directDescendentMapping[$parentCommentId] = [];
                    }

                    $this->directDescendentMapping[$parentCommentId][] = $structureId;

                    for ($i = 0; $i < count($ancestorGraph); $i += 1) {
                        $this->ancestorMapping[$structureId][] = $ancestorGraph[$i];
                    }
                }

                if (count($descendentGraph) > 0) {
                    $descendentGraphLength = count($descendentGraph);
                    $descendentGraphLengthComparison = $descendentGraphLength - 1;

                    for ($i = 0; $i < $descendentGraphLength; $i += 1) {
                        if ($i === $descendentGraphLengthComparison) {
                            break;
                        }

                        if ($i === 0) {
                            $subDescendentGraph = $descendentGraph;
                            $graphRoot = array_shift($subDescendentGraph);

                            if (array_key_exists($graphRoot, $this->descendentMapping) == false) {
                                $this->descendentMapping[$graphRoot] = [];
                            }

                            for ($j = 0; $j < count($subDescendentGraph); $j += 1) {
                                $this->descendentMapping[$graphRoot][] = $subDescendentGraph[$j];
                            }
                        } else {
                            $subDescendentGraph = array_slice($descendentGraph, $i);
                            $graphRoot = array_shift($subDescendentGraph);

                            if (array_key_exists($graphRoot, $this->descendentMapping) == false) {
                                $this->descendentMapping[$graphRoot] = [];
                            }

                            for ($j = 0; $j < count($subDescendentGraph); $j += 1) {
                                $this->descendentMapping[$graphRoot][] = $subDescendentGraph[$j];
                            }
                        }
                    }
                }
            }

            $this->paths[] = $path;
        }

        $hierarchy->setCommentIdPathMapping($this->commentIdPathMapping);
        $hierarchy->setIdentifierDepthMapping($this->commentDepthMapping);
        $hierarchy->setCommentDepthMapping($this->depthMapping);
        $hierarchy->setDirectAncestorMapping($this->directAncestorMapping);
        $hierarchy->setDirectDescendentMapping($this->directDescendentMapping);
        $hierarchy->setDescendentMapping($this->descendentMapping);
        $hierarchy->setAncestorMapping($this->ancestorMapping);

        $this->hierarchyCache[$threadPath] = $hierarchy;

        $this->reset();

        return $hierarchy;
    }

    /**
     * Resets the internal state of the resolver.
     *
     * @return void
     */
    public function reset()
    {
        $this->paths = [];
        $this->threadPath = '';
        $this->threadPathLength = 0;
        $this->commentIdPathMapping = [];
        $this->depthMapping = [];
        $this->commentDepthMapping = [];
        $this->directAncestorMapping = [];
        $this->directDescendentMapping = [];
        $this->ancestorMapping = [];
        $this->descendentMapping = [];
        $this->internalRepliesPathMapping = [];
    }

    /**
     * Compares the lengths of the provided values.
     *
     * @param string $a First test value.
     * @param string $b Second test value.
     * @return int
     */
    private function compareLength($a, $b)
    {
        return mb_strlen($b) - mb_strlen($a);
    }

}
