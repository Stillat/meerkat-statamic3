<?php

namespace Stillat\Meerkat\Threads;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Entries\Entry;
use Statamic\Fields\Value;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Threads\Context;

/**
 * Class ContextResolver
 *
 * Locates a Statamic Entry context for a Meerkat thread.
 *
 * @since 2.0.0
 */
class ContextResolver implements ContextResolverContract
{
    /**
     * A global context cache.
     *
     * @var array
     */
    public static $resolverCache = [];

    /**
     * The Statamic Entry repository instance.
     *
     * @var EntryRepository
     */
    private $entryRepository = null;

    /**
     * The Meerkat Core thread mutation pipeline instance.
     *
     * @var ThreadMutationPipeline
     */
    private $threadPipeline = null;

    public function __construct(EntryRepository $entries)
    {
        $this->entryRepository = $entries;
        $this->threadPipeline = new ThreadMutationPipeline();
    }

    /**
     * Tests if a thread exists for the provided identifier.
     *
     * @param  string  $contextId The thread identifier.
     * @return bool
     */
    public function contextExistsById($contextId)
    {
        $context = $this->findById($contextId);

        if ($context == null) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to locate a thread context by it's string identifier.
     *
     * @param  string  $contextId
     * @parma array|null $contextCache A cache of previously resolved contexts.
     * @return ThreadContextContract
     */
    public function findById($contextId, $contextCache = null)
    {
        if ($contextId instanceof Value) {
            $contextId = $contextId->value();
        }

        if (self::$resolverCache !== null && is_array(self::$resolverCache)) {
            if (array_key_exists($contextId, self::$resolverCache)) {
                return self::$resolverCache[$contextId];
            }
        }

        /** @var Entry $statamicContext */
        $statamicContext = null;

        if ($contextCache != null && array_key_exists($contextId, $contextCache)) {
            $statamicContext = $contextCache[$contextId];
        } else {
            $statamicContext = $this->entryRepository->find($contextId);
        }

        if ($statamicContext === null) {
            return null;
        }

        $threadContext = new Context();
        $threadContext->contextId = $statamicContext->id();

        $contextCreatedDate = $statamicContext->date();

        if ($contextCreatedDate !== null) {
            $threadContext->createdUtc = $contextCreatedDate;
        }

        $threadContext->contextName = $statamicContext->title;

        $statamicContext->id();

        $this->threadPipeline->resolving($threadContext, function ($resolved) use (&$threadContext) {
            if ($resolved !== null && $resolved instanceof ThreadContextContract) {
                $threadContext->mergeAttributes($resolved->getDataAttributes());
                $threadContext->contextName = $resolved->contextName;
            }
        });

        // Add the context to the resolver cache.
        self::$resolverCache[$contextId] = $threadContext;

        return $threadContext;
    }
}
