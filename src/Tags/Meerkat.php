<?php

namespace Stillat\Meerkat\Tags;

use Statamic\API\Parse;
use Statamic\Facades\Antlers;
use Statamic\Support\Arr;
use Statamic\Tags\Tags;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;
use Stillat\Meerkat\Core\Contracts\Comments\CommentManagerContract;
use Stillat\Meerkat\Core\Contracts\Storage\CommentStorageManagerContract;
use Stillat\Meerkat\Core\Contracts\Threads\ThreadManagerContract;
use Stillat\Meerkat\Tags\Output\RecursiveThreadRenderer;
use Stillat\Meerkat\Tags\Threads\MeerkatResponses;
use Stillat\Meerkat\Addon as MeerkatAddon;
use Stillat\Meerkat\View\Antlers\RecursiveCommentParser;

class Meerkat extends Tags
{

    private $threadManager = null;

    public function __construct(ThreadManagerContract $threadManager)
    {
        $this->threadManager = $threadManager;
    }

    private function getHiddenContext()
    {
        $sharing = data_get($this->context, 'meerkat_share_comments', null);

        if ($sharing !== null && is_array($sharing) && count($sharing) > 0) {
            return $sharing[0];
        }

        return data_get($this->context, 'page.id', null);
    }

    public function responses()
    {
        $contextId = $this->getHiddenContext();

        if ($contextId === null) {
            return '';
        }

        $collectionName = $this->getParam('as', 'comments');
        $flatList = $this->getParam('flat', false);

        $thread = $this->threadManager->findById($contextId);

        $displayComments = [];
        $comments = $thread->getCommentCollection($collectionName);

        if ($flatList === true) {
            $displayComments = $comments;
        } else {
            foreach ($comments as $comment) {
                if ($comment[CommentContract::KEY_DEPTH] === 1) {
                    $displayComments[] = $comment;
                }
            }
        }

        return $this->parseComments([
            $collectionName => $displayComments
        ], [], $collectionName);
    }

    protected function parseComments($data = [], $context = [], $collectionName = 'comments')
    {
        $metaData = ['total_results' => count($data)];
        $data = array_merge($data, $metaData);

        return RecursiveThreadRenderer::renderRecursiveThread($this->content, $data, $context, $collectionName);

        /*
        $nestedTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}.*?\{\{\s*\/' . $collectionName . '\s*\}\}/ms';
        preg_match($nestedTagRegex, $this->content, $match);

        $subKey = 'meerkat_comments_tags_' . md5(time());

        if ($match && count($match) > 0) {
            $nestedCommentsString = $match[0];

            // Remove tag pair from the original template.
            $this->content = preg_replace($nestedTagRegex, $subKey, $this->content);

            // Create some regexes to find the opening and closing comments.
            $openingTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}/ms';
            $closingTagRegex = '/\{\{\s*\/' . $collectionName . '\s*\}\}/ms';

            // We need to remove the opening and closing tag pairs from the template.
            $nestedCommentsString = preg_replace($openingTagRegex, '', $nestedCommentsString);
            $nestedCommentsString = preg_replace($closingTagRegex, '', $nestedCommentsString);

            $commentData = $data[$collectionName];

            $nestedCommentsString = trim($nestedCommentsString);

            $tempContent = \Statamic\Facades\Parse::templateLoop($nestedCommentsString, $commentData, true, $context);
            // At this point, we need to render the template without the Meerkat comments tags.
            $template = \Statamic\Facades\Parse::template($this->content, $data, $context);

            return str_replace($subKey, $tempContent, $template);
        }*/
    }

    public function index()
    {
        return 'asdfasdfasdfasdf';
    }


    public function version()
    {
        return MeerkatAddon::VERSION;
    }

}