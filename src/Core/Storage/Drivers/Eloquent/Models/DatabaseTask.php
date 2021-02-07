<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DatabaseTask
 * @package Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models
 * @property int id The storage system's identifier.
 * @property string system_id The tasks's global identifier.
 * @property string task_code The internal task code.
 * @property int task_status The internal task status.
 * @property string task_name The internal task name.
 * @property bool is_complete Indicates if the task has completed.
 * @property string task_args The json_encode'd task arguments.
 * @property Carbon created_at The date/time instance the thread was created.
 * @property Carbon updated_at The date/time instance the thread was last updated.
 */
class DatabaseTask extends Model
{
    use HasTimestamps;

    protected $table = 'meerkat_tasks';

}
