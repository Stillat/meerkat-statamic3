<?php

namespace Stillat\Meerkat\Core\Contracts\Identity;

/**
 * Interface AuthorFactoryContract
 *
 * Author factories create instances of AuthorContract
 *
 * Author factors should be implemented in the host system,
 * and work with their native authentication systems.
 *
 * @since 2.0.0
 */
interface AuthorFactoryContract
{
    /**
     * Constructs a valid AuthorContract instance from the prototype.
     *
     * @param  array  $protoAuthor
     * @return AuthorContract
     */
    public function makeAuthor($protoAuthor);
}
