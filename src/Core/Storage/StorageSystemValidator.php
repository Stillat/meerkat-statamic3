<?php

namespace Stillat\Meerkat\Core\Storage;

use League\Flysystem\FilesystemInterface;

class StorageSystemValidator
{
    private $storageDriver;

    public function __construct($driver)
    {
        $this->storageDriver = $driver;
    }

    public function validate($basePath)
    {
        if ($this->storageDriver == null) {
        }
    }
}
