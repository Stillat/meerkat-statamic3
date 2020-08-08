<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Local\Indexing;

use Stillat\Meerkat\Core\Configuration;
use Stillat\Meerkat\Core\Storage\Paths;

// TODO: Documentation!
class ShadowIndex
{

    const ALL_THREAD_INDEX = 'all.shadow';
    protected $isThreadIndexEnabled = true;
    protected $isProtoTypeIndexEnabled = true;
    private $storageDirectory = '';
    private $paths = null;

    public function __construct(Configuration $config)
    {
        $this->storageDirectory = $config->indexDirectory;
        $this->paths = new Paths($config);


        if (!file_exists($this->storageDirectory)) {
            mkdir($this->storageDirectory, Paths::DIRECTORY_PERMISSIONS, true);
        }
    }

    public function setIsThreadIndexEnabled($isEnabled)
    {
        $this->isThreadIndexEnabled = $isEnabled;
    }

    public function setIsCommentProtoTypeIndexEnabled($isEnabled)
    {
        $this->isProtoTypeIndexEnabled = $isEnabled;
    }

    public function getThreadIndex($threadId)
    {
        return unserialize(file_get_contents($this->getThreadIndexPath(($threadId))));
    }

    private function getThreadIndexPath($threadId)
    {
        return $this->paths->combine([$this->storageDirectory, $threadId . '.shadow']);
    }

    public function getProtoTypeIndex($threadId)
    {
        return unserialize(file_get_contents($this->getProtoTypeIndexPath(($threadId))));
    }

    private function getProtoTypeIndexPath($threadId)
    {
        return $this->paths->combine([$this->storageDirectory, $threadId . '.proto.shadow']);
    }

    public function buildIndex($threadId, $paths)
    {
        if ($this->isThreadIndexEnabled === false) {
            return;
        }

        $indexPath = $this->getThreadIndexPath($threadId);

        file_put_contents($indexPath, serialize($paths));
    }

    public function buildProtoTypeIndex($threadId, $prototypes)
    {
        if ($this->isProtoTypeIndexEnabled === false) {
            return;
        }

        $indexPath = $this->getPrototypeIndexPath($threadId);

        file_put_contents($indexPath, serialize($prototypes));
    }

    public function hasProtoTypeIndex($threadId)
    {
        if ($this->isProtoTypeIndexEnabled === false) {
            return false;
        }

        return file_exists($this->getPrototypeIndexPath($threadId));
    }

    public function hasIndex($threadId)
    {
        if ($this->isThreadIndexEnabled === false) {
            return false;
        }

        return file_exists($this->getThreadIndexPath($threadId));
    }

}