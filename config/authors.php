<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Control Panel Avatar Driver
    |--------------------------------------------------------------------------
    |
    | This value controls how visitor's are visualized in the Control Panel.
    |
    | Options include:
    |  - initials
    |  - simple
    |  - gravatar
    |  - identicon
    |  - jdenticon
    */
    'cp_avatar_driver' => 'initials',

    /*
    |--------------------------------------------------------------------------
    | Form User Field Mapping
    |--------------------------------------------------------------------------
    |
    | This value controls how fields from your blueprint map to author details.
    |
    | The fields on the left are the Meerkat fields; the right is your blueprint's.
    |*/
    'form_user_fields' => [
        'name'  => 'first_name',
        'email' => 'email',
    ]

];
