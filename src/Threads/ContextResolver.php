<?php

namespace Stillat\Meerkat\Threads;

use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Entries\Entry;
use Stillat\Meerkat\Core\Contracts\Threads\ContextResolverContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadContextContract;
use Stillat\Meerkat\Core\Threads\Context;

/**
 * Class ContextResolver
 *
 * Locates a Statamic Entry context for a Meerkat thread.
 *
 * @package Stillat\Meerkat\Threads
 * @since 2.0.0
 */
class ContextResolver implements ContextResolverContract
{

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
     * Attempts to locate a thread context by it's string identifier.
     *
     * @param string $contextId
     *
     * @return ThreadContextContract
     */
    public function findById($contextId)
    {
        /** @var Entry $statamicContext */
        $statamicContext = $this->entryRepository->find($contextId);

        if ($statamicContext === null) {
            return null;
        }

        $contextData = $statamicContext->data();

        if ($contextData !== null) {
            $contextData = $contextData->toArray();
        }

        if ($contextData === null || is_array($contextData) === false) {
            $contextData = [];
        }

        $threadContext = new Context();
        $threadContext->contextId = $statamicContext->id();

        if (array_key_exists('title', $contextData)) {
            $threadContext->contextName = $contextData['title'];
        }

        $statamicContext->id();

        foreach ($statamicContext->toCacheableArray() as $arrayKey => $arrayValue) {
            if ($arrayKey === 'data') {
                continue;
            }
            $threadContext->setDataAttribute($arrayKey, $arrayValue);
        }

        foreach ($contextData as $arrayKey => $arrayValue) {
            $threadContext->setDataAttribute($arrayKey, $arrayValue);
        }

        $this->threadPipeline->resolving($threadContext, function ($resolved) use (&$threadContext) {
            if ($resolved !== null && $resolved instanceof ThreadContextContract) {
                $threadContext->mergeAttributes($resolved->getDataAttributes());
                $threadContext->contextName = $resolved->contextName;
            }
        });

        return $threadContext;
    }

}
