<?php

namespace Stillat\Meerkat\Core;

use Stillat\Meerkat\Core\Contracts\UniqueIdentifierGeneratorContract;

/**
 * Class UuidGenerator
 *
 * Generates a UUIDv4 compatible unique identifier string.
 *
 * @package Stillat\Meerkat\Core
 * @since 2.0.0
 */
class UuidGenerator implements UniqueIdentifierGeneratorContract
{

    /**
     * A shared instance of UuidGenerator used by internal Meerkat services.
     *
     * @var UuidGenerator|null
     */
    public static $instance = null;

    /**
     * Returns access to the shared UuidGenerator instance.
     *
     * @return UuidGenerator
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new UuidGenerator();
        }

        return self::$instance;
    }

    /**
     * Requests a new unique identifier.
     *
     * @return string
     */
    public function newId()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
