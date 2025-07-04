﻿=== WP Visitor Statistics (Real Time Traffic) ===
Contributors: osamaesh
Tags: analytics, google analytics, insights, stats, visitors
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 8.1
License: GPL3

This plugin will help you to track your visitors & visits, browsers, operating systems, GEO locations and much more, easy to install and working fine.

== Description ==
A comprehensive plugin for your WordPress visitor statistics, Track statistics for your WordPress site without depending on external services. Enable you to display how many users are online on your WordPress blog with detailed statistics, easy to install and working fine (Thousands of WordPress sites are already using it)
When it comes to ease of use, WordPress visitor statistics comes in first, You will have a real counter and statistics plugin for your WordPress website..
This plugin will help you to track your visitors, browsers, operating systems, visits and much more in one dashboard page..
= Basic Features =
&#9989; Real time statistics
&#9989; visits, visitors location.
&#9989; Comprehensive overview page & User-friendly interface
&#9989; GeoIP location by Country
&#9989; Search Engines queries from popular search engines like Google
&#9989; Support for hashing IP addresses (Full GDPR)
&#9989; Automatically prune the databases of old data
&#9989; Automatic updates to the GeoIP database.
&#9989; Fully compliant with the European GDPR guidelines.
&#9989; and more

= Pro features =
&#9989; Online counter
&#9989; Widgets to provide information to your users
&#9989; Shortcodes for many different types of data.
&#9989; E-mail reports of statistics
&#9989; Interactive map of visitors location
&#9989; GEO locations (country and city)
&#9989; Recent visitors by IP
&#9989; and more

= Requirements =
* WordPress 3.8+
* PHP 5.2+ (or 5.5+ if you use the Browscap data file)
* MySQL 5.0.3+
* At least 20 MB of free web space
* At least 5 MB of free DB space
* At least 32 Mb of free PHP memory for the tracker (peak memory usage)
* IE9+ or any browser supporting HTML5, to access the reports

= Premium Add-ons =
Visit [our website](http://plugins-market.com/products/add-ons/) for a list of available extensions.

= Translations =
- English
- French
- Russian
- German (Formal)

= Contributing and Reporting Bugs =
WordPress visitor statistics (Histats) is being developed on GitHub, If you’re interested in contributing to plugin, Please look at [Github page](https://github.com/wp-histats)

= Support =
We're happy to help.  Here are a few things to do before contacting us:
* Have you read the [FAQs] (http://plugins-market.com/documentation/)
* Have you search the [support forum](https://wordpress.org/support/plugin/wp-stats-manager) for a similar issue?
* You can also contact us at support@plugins-market.com if you have any enquiry.

== Installation ==
1. Download the package.
2. Upload the package using your plugins page > add new > upload or Extract the contents of .zip folder to wp-content/plugins/ folder
3. Activate the Plugin via plugins page
4. Clear your site cache (if you have any caching plugin enabled)
Thanks!

== Screenshots ==
1. screenshot-1
2. screenshot-2
3. screenshot-3
4. screenshot-4
5. screenshot-5
6. screenshot-6
7. screenshot-7
8. screenshot-8
9. screenshot-9


== Changelog ==

= 8.1 =
Updated the data type of the id column from TINYINT(2) UNSIGNED to INT UNSIGNED to support more than 255 records per table.

= 7.9 =
Bug Fixing: vulnerable to Cross Site Scripting (XSS)

= 7.8 =
1. Solve error in wsm_db.php file
2. Add timezone notation 
3. Check IPV4/IPV6 Exclusion
4. improvements

= 7.7 =
Nonce check, IP validation, security improvements


= 7.6 =
Exclude both IPv4 and IPv6.

= 7.5 =
Security bug fixing

= 7.4 =
Enhancing plugin security by fixing existing vulnerabilities and implementing additional security measures.

= 7.3 =
Security bug fixing

= 7.2 =
Bug fixing - customizer issue

= 7.1 =
Bug fixing when giving access to the editor users

= 6.9.6 =
1. Styling
2 WordPress 6.5.5 compatibility checking


= 6.9.5 =
1. Bug fixing (log file can be accessed directly via wp-content directory)


= 6.9.4 =
1. Bug fixing PHP (Error trying to access array offset of type bool)


= 6.9.3 =
1. enhance reports performance


= 6.9.2 =
1. escaping data
2. WordPress 6.2 compatibility checking
3. Bug fixing PHP Warning:  Undefined variable $wpdb


= 6.9.1 =

3. Bug fixing PHP Warning:  Undefined variable $wpdb


= 6.9 =
1. escaping data
2. WordPress 6.2 compatibility checking

= 6.8.1 =
1. Bug fixes - Sanitizing issue


= 6.7 =
1. Bug fixes - Sanitizing issue

= 6.6 =
1. Bug fixes - escape data


= 6.5 =
1. Bug fixes in Shortcodes - Sanitizing issue


= 6.4 =
1. Bug fixes in database indexes



= 6.3 =
1. PHP8 Bug fixes



= 6.2 =
1. Bug fixes in database indexes



= 6.1 =
1. Bug fixes (Query enhancement - Resource Usage)


= 5.9 =
1. Bug fixes (notice)


= 5.8 =
1. Bug fixes in settings page
2. Adding new feature in the settings page (Excluding Roles from statistics) 


= 5.7 =
1. Bug fixes in the GEO location vars



= 5.6 =
1. Bug fixes - escape data


= 5.5 =
1. Bug fixes in IP execlusion page


= 5.4 =
1. Bug fixes - escape data


= 5.3 =
1. Bug fixes in IP exclusion ajax request


= 5.2 =
1. Bug fixes in timezone


= 5.1 =
1. Bug fixes in settings


= 4.9 =
1. Security Bug fixes



= 4.8 =
1. Security Bug fixing in ajax requests 


= 4.7 =
1. Bug fixing in multisite (network)


= 4.6 =
1. Bug fixing in IP extension


= 4.5 =
1. Bug fixing in removing plugin (remove all tables and views)

= 4.4 =
1. Bug fixing in dashboard stylesheet (with non admin users)


= 4.3 =
1. Bug fixing in storing URLs (Lowercase issue)


= 4.2 =
1. Security Bug fixing in ajax requests - part 2

= 4.1 =
1. Security Bug fixing in ajax requests reported by Mr. Krzysztof Zając

= 3.18 =
1. Security Bug fixing in ajax requests reported by Mr. Krzysztof Zając

= 3.18 =
1. Bug fixing in the timezone (part 2)

= 3.17 =
1. Bug fixing in the timezone

= 3.16 =
1. bug fixing in stylesheet

= 3.15 =
1. Adding FR translation (Thanks Maxence RICHARD for your help)
2. Bug fixing in the main statistics chart
3. bug fixing in stylesheet

= 3.14 =
1. Bug fixing "PHP Notice:  Undefined variable" in functions



= 3.13 =
1. Bug fixing "PHP Notice:  Undefined variable" happend after the latest changes
2. Reports enhancements


= 3.12 =
1. Bug fixing "PHP Notice:  Undefined variable" happend after the latest changes


= 3.11 =
1. bug fixing (single quote issue) in search engines

= 3.10 =
1. Urgent fixing (Database errors)


= 3.9 =
1. Top bar icon + bug fixing in the plugin init file

= 3.8 =
1. Security bug fixing reported by Mr. "Austin Turecek", Sanitizing ajax request

= 3.7 =
1. Bug fixing in search engines graphs

= 3.6 =
1. Bug fixing in prepare statement in the reports


= 3.5 =
1. Bug fixing in prepare statement


= 3.4 =
1. Bug fixing in search engines query (single quote issue reported by @madmax4ever)

= 3.3 =
1. Bug fixing in enqueue scripts

= 3.2 =
1. Bug fixing in user roles with different site language

= 3.1 =
1. Bug fixing in user roles

= 2.9 =
1. Bug fixing in traffic counter after the latest updates

= 2.8 =
1. Bug fixing - PHP notice

= 2.7 =
1. js compression
 
= 2.6 =
1. Css compression

= 2.5 =
1. adding the ability to give access to statistics page for any user type
2. exclude robots
= 2.4 =
1. adding new subscribe message to get latest updates 
= 2.3 =
1. bug fixing in CSS
2. enhancement in the plugin performance 
= 2.2 =
1. removing alert message
= 2.1 =
1. IP addresses hashing for last 3 digits
2. Bug fixing in default data
= 1.9 =
1. we used the [GEOPLUGIN] web service (http://geoip-db.com) These library give us more help in identifying the city name, here is the privacy page for this service http://geoip-db.com/privacy
2. improve reports
= 1.8 =
1. fixing content reports (Traffic by title and by url)
1. timezone is now support syncronization
2. php 7.2 bug fixing
3. improve reports
= 1.7 =
1. Fixing undefined offset issue
= 1.6 =
1. New feature, user can setup add-ons
2. wordpress network (multisite support)
= 1.5 =
1. Fix Bounce issue
= 1.4 =
1. Fix total page views issue
= 1.3 =
1. Fix timezone issue
= 1.2 =
1. show alert if another stats plugin is enabled
= 1.1 =
1. Fix schedule cron job issue
= 1.0 =
1. Initial version