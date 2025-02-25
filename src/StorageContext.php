<?php

declare(strict_types=1);

namespace Istrelka\Storage;

use Istrelka\Storage\Contract\StorageInterface;

class StorageContext
{
    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * StorageContext constructor.
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }
}