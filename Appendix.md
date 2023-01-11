# Appendix

In this section we describe our test environment and our results for our implemented plugin. We also added the output from WPScan.

## Test Environment

We set up the WordPress server on a virtual 'Ubuntu 22.04.1 LTS' machine. The server we are running WordPress with is 'Apache 2.4.52'. The installed and active plugins are 'WPScan Evasion' and 'Hello Dolly'. The installed themes are 'Twenty Twenty', 'Go', 'Twenty Twenty-One' and 'Twenty Twenty-Two' with 'Twenty Twenty' being the main theme. The default admin user is called 'wordpress' and it is the only user.
    
In regards to the WPScan Evasion settings, all evasions are turned on. As fake plugins, we selected '301-redirects' as version '3.2.1' and '404-to-start' with no version. As fake themes, we selected 'airi' with version '1.2'.
    
For using WPScan, we used a virtual 'Kali Linux 2022.3' machine. And WPScan with version 3.8.22 and 'DB Update: 2022-09-20'.

## Results

In the following section we displayed the WordPress scan results. Each result shows the command used and the relevant scan output for that part of the evasion. Result 1 shows the successful hiding of the WordPress version. In Result 2 we can see the successful hiding of existing plugins and faking of chosen plugins with version. In regards to themes, we can see in Result 3 that themes are also successfully hidden and faked. This is with exception of the main theme. Finally, Result 4 shows the successful deception of user enumeration. 

### 1. WPScan Example Version Detection

```
$ wpscan --url http://192.168.86.23/  
...
Fingerprinting the version - Time: 00:00:06 

[i] The WordPress version could not be detected.
...
```

### 2. WPScan Example Plugin Detection

```
$ wpscan --url http://192.168.86.23/ --enumerate p --plugins-detection aggressive 
...
[+] Enumerating Most Popular Plugins (via Aggressive Methods)
 Checking Known Locations - Time: 00:00:32 
[+] Checking Plugin Versions (via Passive and Aggressive Methods)

[i] Plugin(s) Identified:

[+] 301-redirects
 | Location: http://192.168.86.23/wp-content/plugins/301-redirects/
 | Latest Version: 1.01 (up to date)
 | Last Updated: 2022-05-25T05:55:00.000Z
 | Readme: http://192.168.86.23/wp-content/plugins/301-redirects/readme.txt
 |
 | Found By: Known Locations (Aggressive Detection)
 |  - http://192.168.86.23/wp-content/plugins/301-redirects/, status: 403
 |
 | Version: 3.2.1 (80% confidence)
 | Found By: Readme - Stable Tag (Aggressive Detection)
 |  - http://192.168.86.23/wp-content/plugins/301-redirects/readme.txt

[+] 404-to-start
 | Location: http://192.168.86.23/wp-content/plugins/404-to-start/
 | Latest Version: 1.6.1
 | Last Updated: 2019-10-31T11:03:00.000Z
 | Readme: http://192.168.86.23/wp-content/plugins/404-to-start/readme.txt
 |
 | Found By: Known Locations (Aggressive Detection)
 |  - http://192.168.86.23/wp-content/plugins/404-to-start/, status: 403
 |
 | The version could not be determined. 
...
```

### 3. WPScan Example Theme Detection.

```
$ wpscan --url http://192.168.86.23/ --enumerate t --plugins-detection aggressive
...
[+] Enumerating Most Popular Themes (via Passive and Aggressive Methods)
 Checking Known Locations - Time: 00:00:07 
[+] Checking Theme Versions (via Passive and Aggressive Methods)

[i] Theme(s) Identified:

[+] airi
 | Location: http://192.168.86.23/wp-content/themes/airi/
 | Latest Version: 1.0.16 (up to date)
 | Last Updated: 2021-05-26T00:00:00.000Z
 | Style URL: http://192.168.86.23/wp-content/themes/airi/style.css
 |
 | Found By: Known Locations (Aggressive Detection)
 |  - http://192.168.86.23/wp-content/themes/airi/, status: 500
 |
 | Version: 1.2 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://192.168.86.23/wp-content/themes/airi/style.css, Match: 'Version: 1.2'

[+] twentytwenty
 | Location: http://192.168.86.23/wp-content/themes/twentytwenty/
 | Latest Version: 2.0 (up to date)
 | Last Updated: 2022-05-24T00:00:00.000Z
 | Readme: http://192.168.86.23/wp-content/themes/twentytwenty/readme.txt
 | Style URL: http://192.168.86.23/wp-content/themes/twentytwenty/style.css
 | Style Name: Twenty Twenty
 | Style URI: https://wordpress.org/themes/twentytwenty/
 | Description: Our default theme for 2020 is designed to take full advantage of the flexibility of the block editor...
 | Author: the WordPress team
 | Author URI: https://wordpress.org/
 |
 | Found By: Urls In Homepage (Passive Detection)
 | Confirmed By: Urls In 404 Page (Passive Detection)
 |
 | Version: 2.0 (80% confidence)
 | Found By: Style (Passive Detection)
 |  - http://192.168.86.23/wp-content/themes/twentytwenty/style.css, Match: 'Version: 2.0'
...
```

### 4. WPScan Example User Enumeration.

```
$ wpscan --url http://192.168.86.23/ --enumerate u
...
[+] Enumerating Users (via Passive and Aggressive Methods)
 Brute Forcing Author IDs - Time: 00:00:00
 
[i] No Users Found.
...
```