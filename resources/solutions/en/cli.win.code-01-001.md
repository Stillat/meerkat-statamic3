Error Code: {{ error_code }}  
Environment: {{ environment }}  
OS: {{ os_name }}  

Issue: The Windows user {{ php_user_name }} does not have sufficient privileges for the directory: {{ win_storage_path }}.

This issue can be solved using Explorer. To attempt to solve this issue, please complete the following steps:

1. Navigate to "{{ win_storage_path }}"
2. Right Click and Select Properties
3. Navigate to the "Security" Tab
4. Locate "{{ php_user_name }}" in the "Group or user names:" group
5. Under "Permissions for {{ php_user_name }}" make sure that at least the following permissions are allowed: Modify, Read, Write.

If you continue to have this issue, check that the "Authenticated Users" group has not been denied the same permissions.

If you encounter this issue when running Meerkat from the a web server, repeat for the PHP the steps for user that executes the web server process.
