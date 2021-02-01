<?php

namespace Stillat\Meerkat\Configuration\Drivers\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UserPreferences
 *
 * Represents a configuration entry in the meerkat_user_settings database table.
 *
 * @package Stillat\Meerkat\Configuration\Drivers\Eloquent\Models
 * @since 2.3.0
 */
class UserPreferences extends Model
{
    use SoftDeletes, HasTimestamps;

    protected $table = 'meerkat_user_settings';

}
