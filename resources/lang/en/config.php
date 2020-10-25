<?php

return [

    'updated' => 'Your site\'s configuration values were updated.',
    'preferences_updated' => 'Your user preferences were updated.',
    'save' => 'Save',

    'managed_notice' => 'Some items are managed by your site\'s administrator and cannot be changed from the Control Panel.',

    'tab_general' => 'General',

    'preferences_title' => 'Your Preferences',
    'preferences_desc' => 'These settings are yours, and will follow you around on each device you access this Statamic Control Panel on.',

    'avatar' => 'Control Panel Avatar',
    'avatar_desc' => 'Controls how submission authors appear in the Control Panel.',

    'per_page' => 'Submissions per Page',
    'per_page_desc' => 'Controls how many submissions are displayed per page by default.',

    'publishing_title' => 'Comment Publishing',
    'publishing_desc' => 'The publish settings allow you to control various automated aspects of the comment submission process.',

    'only_accept_comments_from_authenticated_users' => 'Only Accept Authenticated Comments',
    'only_accept_comments_from_authenticated_users_desc' => 'Only accept comments from authenticated user sessions; anonymous, or guest, comments will be rejected.',

    'publish_auto' => 'Publish Comments Automatically',
    'publish_auto_desc' => 'All comments from anonymous users will be published automatically when enabled. Disable this to review comments before they are listed on your site.',

    'publish_user_auto' => 'Publish User Comments Automatically',
    'publish_user_auto_desc' => 'Any comment left by an authenticated Statamic user will be published automatically when enabled.',

    'close_threads' => 'When to Close Comment Threads',
    'close_threads_desc' => 'Enter the number of days after which comments will no longer be accepted; entering a value of "0" will disable this feature.',

    'tab_email' => 'Email',
    'email_general_title' => 'Email Settings',
    'email_general_desc' => 'These settings control the automated submission email system.',

    'email_send_mail' => 'Send Mail',
    'email_send_mail_desc' => 'Controls if emails are automatically sent.',
    'email_check_spam_guard' => 'Check Spam',
    'email_check_spam_guard_desc' => 'If enabled, only comments not marked as spam will send an email.',

    'email_addresses' => 'Addresses to Send Emails',
    'email_addresses_desc' => 'The list of email addresses to send emails to.',
    'email_addresses_notice' => 'Your site administrator has configured default email addresses.',
    'email_addresses_view_defaults' => 'Click here to view them.',
    'email_addresses_default_title' => 'Email Address Defaults',
    'email_addresses_default_desc' => 'Your site administrator has configured default email addresses that will be sent to in addition to the ones configured in the Control Panel.',

    'tab_spam' => 'Spam',

    'spam_general_title' => 'General Spam Settings',
    'spam_general_desc' => 'The Meerkat spam service helps to protect your site from spam, and is highly customizable. You may automatically check all incoming submissions for spam, delete spam as soon as its detected, and much more.',

    'auto_check_spam' => 'Automatically Check for Spam',
    'auto_check_spam_desc' => 'Controls whether all submissions are automatically checked for spam.',

    'auto_delete_spam' => 'Automatically Delete all Spam',
    'auto_delete_spam_desc' => 'Controls whether submissions identified as spam are automatically deleted.',

    'check_all_spam_guards' => 'Check All Spam Guards',
    'check_all_spam_guards_desc' => 'When enabled, all spam guards will be checked even if one has already determined a submission was spam.',

    'unpublish_on_guard_failures' => 'Unpublish Comments on Guard Failures',
    'unpublish_on_guard_failures_desc' => 'Controls whether comments are automatically unpublished if an error occurs.',

    'submit_moderator_results' => 'Submit Moderator Results',
    'submit_moderator_results_desc' => 'Controls whether false positive/negatives are sent to third-party providers.',

    'spam_guards_title' => 'Spam Guards',
    'spam_guards_desc' => 'Spam guards improve the built-in spam service by allowing it to utilize additional methods to check for spam. If there are no spam guards enabled, Meerkat will not be able to determine if submissions are spam.',

    'table_spam_guard' => 'Spam Guard',
    'table_enabled' => 'Enabled',

    'akismet_title' => 'Akismet Configuration',
    'akismet_desc' => 'Akismet is a third-party service, and needs a few extra configuration items to work properly.',
    'akismet_link_text' => 'Learn more about Akismet on their website.',

    'akismet_api_key' => 'API Key',
    'akismet_api_key_desc' => 'Your Akismet API key.',

    'akismet_front_page' => 'Front Page',
    'akismet_front_page_desc' => 'The Akismet front page to use.',

    'tab_ip_address_filter' => 'IP Address Filter',

    'ip_filter_title' => 'IP Address Filter',
    'ip_filter_desc' => 'If a submission is sent from a network with any of the following IP addresses, it will be marked as spam.',
    'ip_filter_blocked' => 'Blocked IP Addresses',
    'ip_filter_blocked_desc' => 'The list of IP addresses to check all new submissions against.',
    'ip_filter_managed_notice' => 'Your site administrator has configured default IP addresses.',
    'ip_filter_view_defaults' => 'Click here to view them.',
    'ip_filter_default_title' => 'IP Address Defaults',
    'ip_filter_default_desc' => 'Your site administrator has configured default IP addresses that will be checked in addition to the ones configured in the Control Panel.',

    'tab_word_filter' => 'Word Filter',
    'word_filter_title' => 'Word Filter',
    'word_filter_desc' => 'If a submission contains any of the words in the list below, it will be marked as spam.',
    'word_filter_banned' => 'Banned Words',
    'word_filter_banned_desc' => 'The list of words to check all new submissions against.',
    'word_filter_managed_notice' => ' Your site administrator has configured default banned words.',
    'word_filter_view_defaults' => 'Click here to view them.',
    'word_filter_default_title' => 'Word Filter Defaults',
    'word_filter_default_desc' => 'Your site administrator has configured default words that will be checked in addition to the ones configured in the Control Panel.',


    'tab_permissions' => 'Permissions',
    'permissions_title' => 'User Group Permissions',
    'permissions_desc' => 'User group permissions allow you to control what actions users of different User Groups can take. For example, you can create a User Group specifically for moderators who can only view, approve, or remove comments. If you use a spam service provider that charges per API request, you may also wish to limit who can issue those requests.',
    'table_user_group' => 'User Group',
    'table_all' => 'All',
    'table_view_comments' => 'View Comments',
    'table_approve' => 'Approve',
    'table_unapprove' => 'Unapprove',
    'table_edit' => 'Edit',
    'table_reply' => 'Reply',
    'table_report_ham' => 'Report Ham',
    'table_report_spam' => 'Report Spam',
    'table_delete' => 'Delete',

    'validate_akismet_prompt' => 'Click to validate your Akismet configuration.',
    'validate_akismet_validating' => 'Validating your configuration. One moment please.',
    'validate_akismet_okay' => 'The Akismet API configuration was validated successfully.',
    'validate_akismet_failure' => 'Something went wrong while validating the Akismet API configuration.',
    'validate_akismet_no_params' => 'Required parameters are missing to validate your Akismet configuration.',
    'validate_akismet_api_invalid' => 'The Akismet API has determined your API key configuration is invalid.',

    'server_changes_warning_title' => 'Configuration Changes Detected',
    'server_changes_warning_message' => 'Changes to your site\'s configuration were detected since you last loaded this page; any changes you save will overwrite those changes.',
    'server_changes_warning_reload_prompt' => 'Click here to reload your settings.',

];