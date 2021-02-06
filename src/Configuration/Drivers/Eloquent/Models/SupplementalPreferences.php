<?php

namespace Stillat\Meerkat\Configuration\Drivers\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SupplementalPreferences
 *
 * Represents an entry in the meerkat_supplemental_settings database table.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Eloquent\Models
 * @since 2.3.0
 */
class SupplementalPreferences extends Model
{
    use HasTimestamps, SoftDeletes;

    protected $table = 'meerkat_supplemental_settings';

}
