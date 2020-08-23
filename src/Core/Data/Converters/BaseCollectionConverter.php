<?php

namespace Stillat\Meerkat\Core\Data\Converters;

use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;
use Stillat\Meerkat\Core\Exceptions\InconsistentCompositionException;
use Stillat\Meerkat\Core\Parsing\SanitationManagerFactory;
use Stillat\Meerkat\Core\Threads\ContextResolverFactory;

/**
 * Class BaseCollectionConverter
 *
 * Provides helpers for converting a collection of CommentContract into arrays.
 *
 * @package Stillat\Meerkat\Core\Data\Converters
 * @since 2.0.0
 */
class BaseCollectionConverter
{

    /**
     * A shared BaseCollectionConverter instance.
     *
     * @var null|BaseCollectionConverter
     */
    protected static $cachedInstance = null;

    /**
     * The SanitationManagerContract implementation instance.
     *
     * @var SanitationManagerContract
     */
    private $sanitationManager = null;

    /**
     * A cache of the context's array data.
     *
     * @var array
     */
    private $contextArrayCache = [];

    public function __construct(SanitationManagerContract $sanitationManager)
    {
        $this->sanitationManager = $sanitationManager;
    }

    /**
     * Returns access to a shared BaseCollectionConverter instance.
     *
     * @return BaseCollectionConverter
     * @throws InconsistentCompositionException
     */
    public static function make()
    {
        if (BaseCollectionConverter::$cachedInstance === null &&
            SanitationManagerFactory::hasInstance()) {
            BaseCollectionConverter::$cachedInstance = new BaseCollectionConverter(
                SanitationManagerFactory::$instance
            );
        }

        if (BaseCollectionConverter::$cachedInstance === null) {
            throw new InconsistentCompositionException('SanitationManagerFactory value not set');
        }

        return BaseCollectionConverter::$cachedInstance;
    }

    /**
     * Converts a comment to an array.
     *
     * @param CommentContract $comment The comment to convert.
     * @param string $datasetName The inner dataset name.
     * @return array
     */
    public function convertSingle(CommentContract $comment, $datasetName)
    {
        $commentId = $comment->getId();

        /** @var CommentContract[] $singleArray */
        $singleArray = [];
        $singleArray[] = $comment;

        $convertedDataset = $this->convert($singleArray, $datasetName);

        if ($convertedDataset === null || is_array($convertedDataset) === false || count($convertedDataset) === 0) {
            return null;
        }

        // Only return a result if the original identifier
        // can be located within the resulting dataset.
        if (array_key_exists($commentId, $convertedDataset)) {
            return $convertedDataset[$commentId];
        }

        return null;
    }

    /**
     * Converts  a collection of comments into an array collection.
     *
     * @param CommentContract[] $comments The comments to convert.
     * @param string $datasetName The inner dataset name.
     * @return array
     */
    public function convert(array $comments, $datasetName)
    {
        // Do the initial conversion.
        foreach ($comments as $datasetKey => $comment) {
            $commentArray = $comment->toArray();
            $commentArray[$datasetName] = [];

            // TODO: Provide "collecting hook" here?
            // TODO: Augment with Context here.,
            // TODO: Markdown?

            if (ContextResolverFactory::hasInstance()) {
                $threadId = $comment->getThreadId();

                if (array_key_exists($threadId, $this->contextArrayCache) === false) {
                    $context = ContextResolverFactory::$instance->findById($comment->getThreadId());
                    $contextValue = [];

                    if ($context !== null) {
                        $contextValue = $context->toArray();
                    }

                    $this->contextArrayCache[$threadId] = $contextValue;
                }

                $commentArray[CommentContract::INTERNAL_CONTEXT] = $this->contextArrayCache[$threadId];
            }

            $commentAuthor = $comment->getAuthor();

            if ($commentAuthor === null) {
                $commentAuthor = [];
            } else {
                $commentAuthor = $this->sanitationManager->sanitizeArrayValues($commentAuthor->toArray());
            }

            $commentArray[CommentContract::KEY_AUTHOR] = $commentAuthor;

            $comments[$comment->getId()] = $commentArray;

            if ((string)$datasetKey !== $comment->getId()) {
                unset($comments[$datasetKey]);
            }
        }

        $comments = array_filter($comments, function ($value) {
            return $value != null;
        });

        // Update the inner comment properties to use the array form.
        /** @var array $comment */
        foreach ($comments as &$comment) {
            if ($comment === null) {
                continue;
            }

            /** @var CommentContract[] $currentChildren */
            $currentChildren = $comment[CommentContract::KEY_CHILDREN];
            $newChildren = [];

            foreach ($currentChildren as $child) {
                $newChildren[] =& $comments[$child->getId()];
            }

            $comment[$datasetName] = $newChildren;

            if (array_key_exists(CommentContract::KEY_PARENT, $comment)) {
                $commentParent = [];


                $parentId = $comment[CommentContract::KEY_PARENT]->getId();
                $parentProperties = [];

                if (array_key_exists($parentId, $comments) == true) {
                    $parentProperties = $comments[$parentId];
                } else {
                    /** @var CommentContract $objParent */
                    $objParent = $comment[CommentContract::KEY_PARENT];
                    $parentProperties = $objParent->toArray();
                }

                foreach ($parentProperties as $property => $value) {
                    if ($property === $datasetName || $property === CommentContract::KEY_CHILDREN) {
                        continue;
                    }

                    $commentParent[$property] = $value;
                }

                $comment[CommentContract::KEY_PARENT] = $commentParent;
            }
        }

        $commentsToReturn = [];

        foreach ($comments as $key => $val) {
            if ($val !== null) {
                $commentsToReturn[$key] = $val;
            }
        }

        foreach ($commentsToReturn as $commentId => $comment) {
            $commentsToReturn[$commentId] = $this->sanitationManager->sanitizeArrayValues($comment);
        }

        return $commentsToReturn;
    }

}
