=== Superadmin Helper ===
Contributors: zaantar
Tags: superadmin, multisite, permban, spam, log, logging
Donate link: http://zaantar.eu/financni-prispevek
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 2.0.5

Set of utilities for managing multisite Wordpress installations. Logging, simple permban, etc.

== Description ==

This plug-in currently offers following features:

* Logging of basic events in the network (see FAQ for list)
* Logging all e-mails sent by WordPress
* Changing user's primary blog ID on user profile page
* Storing user's last logon time
* Permanently banning IP's trying to log in as selected users (optional)
* Partially working even on single-site.

All logging features make use of the [Wordpress Logging Service](http://wordpress.org/extend/plugins/wordpress-logging-service/).

Requires PHP >= 5.3.

See Usage and FAQ for more information.

Plugin support is not guaranteed due to lack of free time.

**Translations**

- Spanish and Serbian - Ogi Djuraskovic, http://firstsiteguide.com/

== Frequently Asked Questions ==

= What is logged? =

* user profile update
* sending e-mail
* logging in
* password resetting
* deleting an user
* uploading a file
* user logout
* user registered
* theme switch
* activate plugin
* deactivate plugin
* login errors
* deactivate blog
* activate blog
* delete blog
* add user to blog
* remove user from blog
* archive blog
* unarchive blog
* make spam blog
* make ham blog
* mature blog
* unmature blog
* update plugin
* install plugin
* update theme
* install theme
* 404 errors

= Where do I find all the logs? =

See Wordpress Logging Service plugin.

== Changelog ==

= 2.0.5 =
* Added Spanish and Serbian translations (by Ogi Djuraskovic, http://firstsiteguide.com/)

= 2.0.4 =
* Fix: Multiple typos in `add_filter` and `add_action` calls.
* Fix: Use of undefined constant TXD.
* Readme update.

= 2.0.3 =
* Fix: Showing only first 50 banned IPs.

= 2.0.2 =
* Tweak: verbose logging of database errors in add_permban().
* Fix: SQL syntax error in add_permban().

= 2.0.1 =
* Fix: wrong function name (log_email() -> email()).

= 2.0 =
* MAJOR UPDATE: Code almost completely rewritten.
* Cleaner code, using PHP namespaces (so PHP >=5.3 is required).
* Fix some issues with misbehaving permban settings.
* Few database load optimalizations (although there is space for a lot more).
* Please test if plugin works well after upgrading to 2.0 and if not, send me an e-mail.

= 1.3.11 =
* Add option to log attempts to login as banned user and IP banning (so it can be turned off as of now).
* Reduce the number of new log entries when creating a permban.

= 1.3.10 =
* Fix incorrect `$wpdb->prepare()` usage.

= 1.3.9 =
* replace short php tag `<?` with a long one `<?php` to prevent issues on some server configurations

= 1.3.8 =
* minor visual improvements
* bug fixes for single-site use

= 1.3.7 =
* using WP_List_Table on Permban page (ability to search IP, order records, pagination, bulk delete, etc.)
* network admin can manually ban IP on Permban page

= 1.3.6 =
* attempts to access site from banned IPs are no longer logged (unless specified in Options menu), but attempt count and last attempt date is stored for each banned IP
* minor bug fixes

= 1.3.5 =
* (important)fix: failing SUH_DB_VERSION upgrade when permban table is not already present

= 1.3.4 =
* logging following events: add user to blog, remove user from blog, archive blog, unarchive blog, make spam blog, make ham blog, mature blog, unmature blog, update plugin, install plugin, update theme, install theme, 404 errors
* fixed: permban deleting
* fixed: duplicate permban table records
* showing wls log entries containing banned ip address on permban overview page (link works for WLS version >= 1.4.12)
* optional logging of some events
* fixed: stripslashes for permban message
* fixed: various is_multisite checks

= 1.3.3 =
* code cleanup
* correct log severity for suh-mail
* logging ip on user logon
* added logging of events: activate blog, deactivate blog, delete blog
* admin can select multiple usernames which will be banned
* donate link in plugin settings

= 1.3.2 =
* I18zed and translated to English and Czech language.
* published to wordpress.org

= 1.3 =
* permban functionality
* minor changes and fixes

= 1.2 =
* first really usable version

