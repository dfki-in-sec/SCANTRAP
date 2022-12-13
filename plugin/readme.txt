=== SCANTRAP ===
Contributors: (this should be a list of wordpress.org userid's)
Tags: wpscan, security, vulnerability, wpscan evasion
Requires at least: 6.0.2
Tested up to: 6.0.2
Stable tag: 1.0.0
Requires PHP: 8.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Wordpress plugin that evades correct Plugin Detection, Theme Detection, Version Detection and User Enumeration.
It redirects WPScan plugin/theme requests and removes version detection and user enumeration.

== Description ==

Wordpress plugin that evades correct Plugin Detection, Theme Detection, Version Detection and User Enumeration.
It redirects WPScan plugin/theme requests and removes version detection and user enumeration. the following sections describe the functionality in more detail:

= Plugin Redirect =
Tricks the WPScan plugin enumeration, by redirecting requests to non existing plugins. Additionally allows for Version detection through simulated 'Stable' tag in 'readme.txt'. 
Also hides existing Plugins, by redirecting directory path to 404.
Logs access to fake plugins in file: 'wp-content/uploads/wpscan_evasion.log'.

*Example Log:*
`2022-09-22 09:48:14 :: IP: 127.0.0.1 - Fake Plugin Accessed (GET): /wp-content/plugins/301-redirects/`

= Theme Redirect =
Tricks the WPScan plugin enumeration, by redirecting requests to non existing plugins. Additionally allows for Version detection through simulated 'Version' tag.
Also hides existing Plugins, by redirecting directory path to 404. This however only works if default main theme is changed. (Main/Active Theme should not be 'twentytwentytwo')
Logs access to fake themes in file: 'wp-content/uploads/wpscan_evasion.log'.

= Prevent Version Detection =
Prevents Version Detection from WPScan.
- removes wordpress head with version
- removes generator in web pages
- removes versions from scripts and styles
- adds space to files ending in '.js', '.css' and '.json' to prevent md5 hash comparison
- remove version from `load_styles.php` file
- removes 'version' query from 'install.php' (changes code)

= Prevent User Enumeration =
Prevents User Enumeration from WPScan.
- closes REST API
- removes author comment class
- disallow url queries with user id
- remove login error
- prevent JSON API User Enumeration
- remove author from RSS tag

== Usage ==

= Plugin Redirect/Theme Redirect =
- path of plugins (that do not exist) that should be recognized additionally to exisitng ones
- one line includes path with space and version

*Example Line:*
`/wp-content/plugins/404-to-homepage/ 1.2.3`
`/wp-content/plugins/404-to-start/` (no version detected)
`/wp-content/themes/archeo/ 1.2`

*Notes:* 
*Only works for default Plugin/Theme directories.*
*After installing a new Plugin/Theme deactivate/activate the Plugin again*
*The Main Theme will always be detected*

= Admin Menu =
- Admin Menu Page called 'WP Plugin'
- can turn on/off the different functions:
  - Redirect Plugins
  - Redirect Themes
  - Prevent Version Detection
  - Prevent User Enumeration
- add path to plugin/themes (default folder) to fake them

== Screenshots ==
Example of SCANTRAP Admin Page Settings:
![SCANTRAP Admin Page](wpscan_evasion_admin_page.PNG)