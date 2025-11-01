Troubleshooting
===============

This page contains solutions to common issues you may encounter while using MultiFlexi.

Authentication Issues
---------------------

Invalid Security Token Error
~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you encounter an "Invalid security token." error when trying to log in, this typically indicates a CSRF token mismatch. Here are the steps to resolve it:

**Causes:**

* Session timeout or expired session data
* Browser cache issues
* Cookie configuration problems
* Server-side session storage issues

**Solutions:**

1. **Clear your browser cache and cookies**
   
   * Clear all cookies for the MultiFlexi domain
   * Clear browser cache
   * Restart your browser

2. **Check session configuration**
   
   * Verify that your PHP session storage is working correctly
   * Check if the session directory has proper write permissions
   * Review ``php.ini`` session settings (``session.save_path``, ``session.gc_maxlifetime``)

3. **Verify cookie settings**
   
   * Ensure cookies are enabled in your browser
   * Check if you're accessing MultiFlexi via HTTPS (some cookie settings require secure connections)
   * Review cookie domain and path settings in your configuration

4. **Check server configuration**
   
   * Ensure the web server has write access to the session directory
   * Verify that the ``session.save_path`` directory exists and is writable
   * Check server time synchronization (incorrect server time can cause token validation issues)

5. **Try accessing from a different browser or incognito/private mode**
   
   This helps determine if the issue is browser-specific

6. **Restart PHP-FPM or web server**
   
   .. code-block:: bash
   
      sudo systemctl restart php-fpm
      # or
      sudo systemctl restart apache2
      # or
      sudo systemctl restart nginx

**Still having issues?**

If the problem persists after trying these solutions, check the application logs for more detailed error messages:

.. code-block:: bash

   tail -f /var/log/multiflexi/multiflexi.log

See the :doc:`configuration` page for more information about logging configuration.

Or check PHP error logs for session-related errors.
