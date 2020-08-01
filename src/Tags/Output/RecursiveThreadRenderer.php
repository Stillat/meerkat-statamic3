<?php

namespace Stillat\Meerkat\Tags\Output;

use Illuminate\Support\Str;
use Statamic\Facades\Parse;
use Stillat\Meerkat\Core\Contracts\Parsing\SanitationManagerContract;

/**
 * Class RecursiveThreadRenderer
 *
 * Provides utilities to render recursive comment threads.
 *
 * @package Stillat\Meerkat\Tags\Output
 * @since 2.0.0
 */
class RecursiveThreadRenderer
{

    /**
     * Recursively renders a comment thread.
     *
     * @param SanitationManagerContract $sanitizer The sanitizer instance.
     * @param string $template The template.
     * @param array $data The comment data to render.
     * @param array $context Optional context data.
     * @param string $collectionName The name of the nested collection.
     * @return string|string[]
     */
    public static function renderRecursiveThread(SanitationManagerContract $sanitizer, $template, $data, $context, $collectionName)
    {
        // TODO: Capture memory exhaustion and throw a custom exception
        //       when the template has malformed safety checks.

        $nestedTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}.*?\{\{\s*\/' . $collectionName . '\s*\}\}/ms';
        preg_match($nestedTagRegex, $template, $match);

        $subKey = 'meerkat_comments_tags_' . md5(time());

        if ($match && count($match) > 0) {
            $nestedCommentsString = $match[0];
            // Remove tag pair from the original template.

            // Wraps the recursive call in a `has_replies` check to prevent memory issues.
            if (Str::contains($nestedCommentsString, '{{ if has_replies }}') === false) {
                $templateParts = preg_split("/\r\n|\n|\r/", $nestedCommentsString);
                $newParts = [];
                $recursiveTagToLookFor = '{{ *recursive '.$collectionName.'* }}';

                foreach ($templateParts as $part) {
                    if (Str::contains($part, $recursiveTagToLookFor)) {
                        $newParts[] = '{{ if has_replies }}';
                        $newParts[] = $part;
                        $newParts[] = '{{ /if }}';
                    } else {
                        $newParts[] = $part;
                    }
                }

                $nestedCommentsString = implode("\n", $newParts);
            }

            $template = preg_replace($nestedTagRegex, $subKey, $template);

            // Create some regular expressions to find the opening and closing comments.
            $openingTagRegex = '/\{\{\s*' . $collectionName . '\s*\}\}/ms';
            $closingTagRegex = '/\{\{\s*\/' . $collectionName . '\s*\}\}/ms';

            // We need to remove the opening and closing tag pairs from the template.
            $nestedCommentsString = preg_replace($openingTagRegex, '', $nestedCommentsString);
            $nestedCommentsString = preg_replace($closingTagRegex, '', $nestedCommentsString);


            $commentData = $data[$collectionName];

            $sanitizer->sanitizeArrayValues($commentData);

            $nestedCommentsString = trim($nestedCommentsString);

            $tempContent = Parse::templateLoop($nestedCommentsString, $commentData, true, $context);
            // At this point, we need to render the template without the Meerkat comments tags.
            $subTemplate = Parse::template($template, $data, $context);

            return str_replace($subKey, $tempContent, $subTemplate);
        }
    }

}