import Environment from '../Config/environment';
import Type from '../Types/type';

/**
 * Attempts to parse the provided content as Markdown.
 *
 * @param {string} content The content to parse.
 * @returns {string|*}
 */
export function parseMarkdown(content : string) : string {
  if (Type.hasValue(Environment.MarkdownHandler)) {
    return Environment.MarkdownHandler(content);
  }

  return content;
}
