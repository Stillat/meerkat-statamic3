<?php
/*
|--------------------------------------------------------------------------
| Meerkat Filters Helper File
|--------------------------------------------------------------------------
|
| This file was created to provide you with a central, convenient location
| to register any Meerkat filter/groups you might need. This file will be
| automatically loaded by Meerkat after the application has launched.
|
| See https://meerkatcomments.com/docs/advanced-filtering
|
*/

use Stillat\Meerkat\Support\Facades\Comments;

/* The following is an example of creating a filter group:

Comments::filterGroup('noSpam', 'where(is_spam, !==, true)');

 */
