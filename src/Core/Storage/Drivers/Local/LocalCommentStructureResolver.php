<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local;

use Stillat\Meerkat\Core\Contracts\Storage\StructureResolverInterface;
use Stillat\Meerkat\Core\Storage\Paths;

class LocalCommentStructureResolver implements StructureResolverInterface
{

    protected $paths = [];

    protected $threadPath = '';

    protected $threadPathLength = 0;

    protected $replyReplacement = '';

    protected $commentIdPathMapping = [];

    protected $depthMapping = [];

    protected $commentDepthMapping = [];

    protected $directAncestorMapping = [];

    protected $directDescendentMapping = [];

    protected $ancestorMapping = [];

    protected $descendentMapping = [];

    protected $internalRepliesPathMapping = [];

    public function __construct()
    {
        $this->replyReplacement = Paths::SYM_FORWARD_SEPARATOR . LocalCommentStorageManager::PATH_REPLIES_DIRECTORY;
    }

    public function reset()
    {
        $this->paths = [];
        $this->threadPath = '';
    }

    private function compareLength($a, $b)
    {
        return mb_strlen($b) - mb_strlen($a);
    }

    /**
     * Resolves the comment dependency graph.
     *
     * @param string $threadPath The thread's base path.
     * @param array $commentPaths A collection of comment absolute paths.
     */
    public function resolve($threadPath, $commentPaths)
    {
        $this->reset();

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
                $descendentGraph = $ancestorGraph;
                array_pop($ancestorGraph);

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

            $this->paths[] = $path;
        }

    }

    public function getDepth($commentId)
    {
        if (array_key_exists($commentId, $this->commentDepthMapping)) {
            return $this->commentDepthMapping[$commentId];
        }

        return 0;
    }

    /**
     * Indicates if the comment has a direct ancestor.
     *
     * @param string $commentId The comment ID.
     * @return bool
     */
    public function hasAncestor($commentId)
    {
        return array_key_exists($commentId, $this->directAncestorMapping);
    }

    public function getAllAncestors($commentId)
    {
        if (array_key_exists($commentId, $this->ancestorMapping)) {
            return $this->ancestorMapping[$commentId];
        }

        return [];
    }

    public function getParent($commentId)
    {
        return $this->directAncestorMapping[$commentId];
    }

    public function getAllDescendents($commentId)
    {
        if (array_key_exists($commentId, $this->descendentMapping)) {
            return $this->descendentMapping[$commentId];
        }

        return [];
    }

    public function getDirectDescendents($commentId)
    {
        if (array_key_exists($commentId, $this->directDescendentMapping)) {
            return $this->directDescendentMapping[$commentId];
        }

        return [];
    }

}