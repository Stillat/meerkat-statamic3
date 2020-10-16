<?php
/*
|--------------------------------------------------------------------------
| Meerkat Events Helper File
|--------------------------------------------------------------------------
|
| This file was created to provide you with a central, convenient location
| to register any Meerkat event hooks you might need. This file will be
| automatically loaded by Meerkat after the application has launched.
|
*/

use Stillat\Meerkat\Support\Facades\Meerkat;
use Stillat\Meerkat\Core\Contracts\Comments\CommentContract;

/* The following is an example of hooking into a comment being saved:

Meerkat::onCommentCreated(function (CommentContract $comment) {
    // A comment was created!
});

*/
