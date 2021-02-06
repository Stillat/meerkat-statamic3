<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DatabaseThread
 * @package Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models
 * @property int id The storage system's identifier.
 * @property string context_id The thread's context system identifier.
 * @property string virtual_path The thread's virtual path.
 * @property string meta_data The json_encode'd thread attributes.
 * @property Carbon created_at The date/time instance the thread was created.
 * @property Carbon updated_at The date/time instance the thread was last updated.
 * @property Carbon|null deleted_at The date/time instance the thread was deleted, if deleted.
 */
class DatabaseThread extends Model
{
    use HasTimestamps, SoftDeletes;

    protected $table = 'meerkat_threads';

}
