<?php

return [

    'yes' => 'Yes',
    'no' => 'No',

    'does_exists' => 'Exists',
    'is_readable' => 'Is Readable',
    'is_writeable' => 'Is Writeable',

    'local_path' => 'Your local path:',

    'system_information' => 'System Information',
    'statamic_version' => 'Statamic Version',
    'meerkat_version' => 'Meerkat Version',
    'server_type' => 'Software Name',
    'clear_route_cache' => 'Clear Route Cache',

    'route_clear_artisan_header' => 'Clearing the Route Cache With Artisan',
    'route_clear_artisan_instructions' => 'To clear the route cache using Laravel\'s Artisan command-line utility, issue the following command in the directory: :directory:',

    'route_clear_manual_header' => 'Clearing the Route Cache Manually',
    'route_clear_manual_instructions' => 'To manually clear the route cache, locate and remove the following file on all servers your site runs on:',

    'category_routes' => 'Route Caching',
    'routes_valid' => 'No route caching issues were detected.',
    'routes_invalid' => 'One or more route caching issues were detected; your site may not function correctly until these issues are resolved. These issues may be resolved by clearing your site\'s route cache. If your site runs on multiple servers, the route cache must be cleared on all servers.',
    'route_category_emissions' => 'Control Panel Assets',
    'route_category_general' => 'General Routes',

    'route_table_header_name' => 'Overview',
    'route_table_header_category' => 'Category',
    'route_table_header_description' => 'Description',

    'route_cache_cleared' => 'Route cache cleared!',
    'emissions_cpConfiguration' => 'Control Panel Configuration',
    'emissions_cpConfiguration_desc' => 'This is required to deliver critical information to Meerkat\'s features within the Statamic Control Panel.',

    'route_category_cp_configuration' => 'Control Panel Configurator',
    'route_category_cp_configuration_desc' => 'Provides and manages Meerkat\'s Control Panel configuration interface.',

    'route_category_spam_api' => 'Spam API',
    'route_category_spam_api_desc' => 'Manages spam moderation requests.',

    'route_category_telemetry_api' => 'Telemetry API',
    'route_category_telemetry_api_desc' => 'Manages bug and crash reports.',

    'route_category_moderation_api' => 'Moderation API',
    'route_category_moderation_api_desc' => 'Provides the core moderation features such as editing, replying, and comment visibility.',

    'route_category_submission_api' => 'Site Submission API',
    'route_category_submission_api_desc' => 'Handles comment submission from website visitors.',

    'category_config_dir' => 'Configuration and Storage Directories',
    'config_supplement_name' => 'Supplemental Storage Configuration Directory',
    'config_supplement' => 'The supplemental configuration directory contains configuration modifications made through the Statamic Control Panel.',

    'config_users_name' => 'User Configuration Storage Directory', 'config_users' => 'The user configuration directory contains all user-specific configuration settings for the Statamic Control Panel.',

    'storage_content_name' => 'Comment Storage Directory',
    'storage_content' => 'The Comments storage directory contains all user-submitted content, as well as meta data about your site\'s comments.',

    'storage_meerkat_name' => 'Meerkat System Storage Directory',
    'storage_meerkat' => 'The Meerkat storage directory is where Meerkat will place logs, temporary files, and various other items it needs in order to function properly.',
];
