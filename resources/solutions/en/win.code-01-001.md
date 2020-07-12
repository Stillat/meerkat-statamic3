Error Code: {{ error_code }}  
Environment: {{ environment }}  
OS: {{ os_name }}  

## Problem

The Windows user **{{ php_user_name }}** does not have sufficient privileges for the directory: **{{ win_storage_path }}**.

## Solving Using Explorer

1. Navigate to "{{ win_storage_path }}"
2. Right Click and Select Properties
3. Navigate to the "Security" Tab
4. Locate "{{ php_user_name }}" in the "Group or user names:" group
5. Under "Permissions for {{ php_user_name }}" make sure that at least the following permissions are allowed: Modify, Read, Write.

> Tip: If you continue to have this issue, check that the "Authenticated Users" group has not been denied the same permissions.

If you encounter this issue when running Meerkat from the command line, repeat the steps for the PHP user that executes the `php artisan` process.
