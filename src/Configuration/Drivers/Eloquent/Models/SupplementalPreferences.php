<?php

namespace Stillat\Meerkat\Configuration\Drivers\Eloquent\Models;

use Carbon\Carbon;
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
 * @property int id The storage system's identifier.
 * @property string preferences The json_encoded'd preferences.
 * @property Carbon created_at The date/time instance the thread was created.
 * @property Carbon updated_at The date/time instance the thread was last updated.
 * @property Carbon|null deleted_at The date/time instance the thread was deleted, if deleted.
 */
class SupplementalPreferences extends Model
{
    use HasTimestamps, SoftDeletes;

    protected $table = 'meerkat_supplemental_settings';

}
