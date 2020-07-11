<?php

namespace Stillat\Meerkat\Core\Contracts\Parsing;

/**
 * @since 2.0.0
 */
interface YAMLParserContract
{

  /**
   * Parses the provided string document and returns a value array.
   *
   * @param  string $content
   *
   * @return array
   */
  public function parseDocument($content);

  /**
   * Parses the provided string document and merges the results into the provided data container array.
   *
   * @param string $content
   * @param array  $dataContainer
   *
   * @return void
   */
  public function parseAndMerge($content, array &$dataContainer);

}
