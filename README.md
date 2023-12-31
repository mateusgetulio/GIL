### Get in Line - GIL ###
Contributors: Mateus Getulio Vieira
Tags: Queue, Line, Limit access, maximum, capacity, simultaneously, access, accesses, limitation, session
Requires at least: 5.6
Tested up to: 6.2
Stable tag: 1.0.0
License: GPLv3
Requires PHP: 5.6

Get in Line

## Description ##

What happens when you're expecting a great number of visitors, higher than your server can handle at that particular time? 

GIL, Get In Line, will help you with this task, it creates a virtual queue or pool and limit the access to the site to the number that you set up inside the settings. 

**How to use it**

No code knowledge required, you just activate, configure it and it's done, your site's front end is limited to the number you decided and protected against DDoS as well.

The plugin will register a unique ID per device and browser and control the pool for you.

After installation you'll see a new settings page added to your backend, in there you'll be able to configure:

- The maximum access you can support
- The expiration time per session
- Clear the current state of the queue to allow new visitors

Just try it out! You'll love it :)

## Installation ##

# Admin Installer via search #
1. Visit the Add New plugin screen and search for "Get in Line".
2. Click the "Install Now" button.
3. Activate the plugin.
4. The plugin should be shown below settings menu.

# Admin Installer via zip #
1. Visit the Add New plugin screen and click the "Upload Plugin" button.
2. Click the "Browse..." button and select the zip file of our plugin.
3. Click "Install Now" button.
4. Once uploading is done, activate "Get in Line".
5. The plugin should be shown below the settings menu.

## Frequently Asked Questions ##

# Can I customize the waiting lobby page? #
In the upcoming versions there's going to be an editor available in the settings page, please stay tuned. As of now, you can customize it by updating the lobby.php file inside the plugin.

# Does the pool apply for wp-admin pages as well?  #
No, currently, only the front end of the site is limited. But it's limited even if you're logged in.

# How's the waiting time calculated  #
It checks the amount of devices ahead of you in the line and calculate the maximum waiting time based on how the plugin is configured. In the upcoming versions, this is going to be adjust to take in consideration the current allowed devices expiration time to give a most accurate estimated time.

# Where are the sessions stored, can I reset them? #
The sessions are saved in the database, and you can flush it inside the GIL settings page.

# Is there other way to end a session other than waiting for it to expire? #
Currently the only way for a session to end is when it expires based on the expiration time that was set in the settings. A hook will be created to allow you to end a session in specific parts of the site, like when a transaction happens, or when your visitors reach a specific part of your site.

# Is the plugin also available in my language? #
So far we the plugin is only available in English. But all texts are inserted into GIL's domain allowing translations to be made. Upcoming versions will count with more languages by default.

## Changelog ##
# 1.0.0 #
* Initial release
