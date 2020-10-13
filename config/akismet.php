<?php

/**
 * Note: Akismet is a third party service, and API access does not
 * come with Meerkat (only a custom API integration). Meerkat
 * must share data with the service in order for to use
 * their API to check your comments for spam/ham.
 *
 * Use of this service is completely optional.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Akismet API Key
    |--------------------------------------------------------------------------
    |
    | This value allows Meerkat to make requests to the Akismet services
    | on your behalf. This is required to use the Akismet spam API.
    |
    | https://docs.akismet.com/getting-started/api-key/
    |
    */
    'api_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Akismet Front Page
    |--------------------------------------------------------------------------
    |
    | This value should be the domain name of your site, or your home page.
    |
    */
    'front_page' => '',

    /*
    |--------------------------------------------------------------------------
    | Akismet Filed Mapping
    |--------------------------------------------------------------------------
    |
    | Not all blueprints are the same; if you've customized the names of
    | fields heavily, you will need to remap the following fields so
    | that the appropriate data is sent with Akismet API calls.
    |
    | The fields on the left are the Akismet fields; the fields
    | on the right are your blueprint's field names/handles.
    |
    */
    'fields' => [
        'author'     => 'name',
        'email'      => 'email',
        'content'    => 'comment',

        // These values are handled automatically.
        'user_ip'    => 'user_ip',
        'user_agent' => 'user_agent',
        'referrer'   => 'referer',
        'permalink'  => 'page_url',
    ],

];
