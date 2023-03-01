<?php

namespace Stillat\Meerkat\Core\Contracts\Data;

/**
 * Interface PagedGroupedDataSetContract
 *
 * Provides a standard API for representing paged datasets that contain groups.
 *
 * @since 2.0.0
 */
interface PagedGroupedDataSetContract extends PagedDataSetContract, GroupedDataSetContract
{
}
