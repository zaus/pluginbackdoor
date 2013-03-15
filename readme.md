# Plugin Backdoor #

Contributors: zaus
Donate link: http://drzaus.com
Tags: administration, backdoor, plugins
Requires at least: 3.0.1
Tested up to: 3.5
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows direct access to WP_Options table and the 'active_plugins' setting in particular, to manually disable/enable plugins without going through Wordpress.

## Description ##

Allows direct access to WP_Options table and the 'active_plugins' setting in particular, to manually disable/enable plugins without going through Wordpress.

It does include the `wp-config.php` file to get your database credentials, but otherwise uses "regular" PHP methods for database interaction.

## Installation ##

1. *CHANGE THE DEFAULTS IN `access.php`*
    1. the username and password here are what you enter to access the "backdoor".
1. Upload plugin folder to the `/wp-content/plugins/` directory
1. Manually navigate to `www.yoursite.com/wp-content/plugins/pluginbackdoor/pluginbackdoor.php`
    1. Optionally -- if you're using pgsql instead of mysql, append `?type=pgsql` to url
1. Enter the password and username you updated in `access.php` (password goes in first field)
1. To disable a plugin, clear the text field corresponding to the main plugin file
    1. _ex)_ to disable Akismet, clear the value `akismet/akismet.php`.
1. To enable a plugin, click the "Add" button to create another text field, and add a value corresponding to the main plugin file
    1. _ex)_ to enable Akismet, enter the value `akismet/akismet.php`.

## Frequently Asked Questions ##

### Why would you grant this kind of access? ###

Because sometimes you install plugins that screw up and take down your site.  Then you can't even log in to turn them off (or back on).

And because all the other (older) examples suggest blowing out the entire list, rather than allowing you to selectively manage them.

### How long does the access last? ###

You can only continue to submit the form for 1 hour, after that you must start over at the login.

## Screenshots ##


## Changelog ##

### 0.1 ###
* First version

## Upgrade Notice ##
