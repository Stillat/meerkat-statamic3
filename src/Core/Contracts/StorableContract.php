<?php

namespace Stillat\Meerkat\Core\Contracts;

interface StorableContract
{

  /**
   * Sets the storage path.
   *
   * @param  string $path
   *
   * @return void
   */
  public function setPath($path);

  /**
   * Gets the storage path.
   *
   * @return string
   */
  public function getPath();

  /**
   * Attemps to remove the current object instance.
   *
   * @return boolean
   */
  public function delete();

}
