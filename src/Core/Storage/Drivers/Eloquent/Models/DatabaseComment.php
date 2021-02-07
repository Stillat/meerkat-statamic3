<?php

namespace Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DatabaseComment
 * @package Stillat\Meerkat\Core\Storage\Drivers\Eloquent\Models
 * @property int id The storage system's identifier.
 * @property int|null parent_compatibility_id
 * @property int compatibility_id
 * @property string|null statamic_user_id
 * @property string thread_context_id
 * @property string virtual_path
 * @property string virtual_dir_path
 * @property string root_path
 * @property int depth
 * @property bool is_root
 * @property bool is_published
 * @property bool|null is_spam
 * @property string content
 * @property string comment_attributes
 * @property Carbon created_at The date/time instance the thread was created.
 * @property Carbon updated_at The date/time instance the thread was last updated.
 * @property Carbon|null deleted_at The date/time instance the thread was deleted, if deleted.
 */
class DatabaseComment extends Model
{
    use HasTimestamps, SoftDeletes;

    protected $table = 'meerkat_comments';

}