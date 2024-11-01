=== The sixtyseven song requester plugin ===
Contributors: sixtyseven
Requires at least: 4.9.1
Tested up to: 5.4
Stable tag: 1.0.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The sixtyseven song requester plugin allows management of song requests in the frontend of your Website. Requesters could see how often their song was allready requested and when it was last played.

== Description ==
The sixtyseven song requester plugin was originally created as a tool to manage the song requests on the programmer's wedding. The goal was to give the attending guests a nice way to make a song request to the DJ without even leaving the table. It offers the ability to mark songs as played by the DJ and some basic settings. Everything can be fully achieved in the frontend, so you do not need to give your DJ access to the WordPress admin area, but the plugin has of course the typical admin view of the song requests as well. And there is a nice way to get some demo data from Apple's itunes store topselling tracks from all over the world.

The frontend output is realized by a shortcode to provide maximum flexibility. Simply put the shortcode [ss_song_requester] anywhere on a page to show the song requests list. To match the output styling with your theme, simply copy the file ss_song_requester-public.css from the public/css directory of this plugin to the directory wp-content/themes/yourtheme/css. When the plugin finds this file, it will load it superior to the plugin's public css file.

Translations:
* English (sixtyseven)
* German (sixtyseven)
* German formal (sixtyseven)

Please contact me if you want to add a translation

== Installation ==
* Upload `the-sixtyseven-song-requester` to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Generate some demo data and have fun with the plugin.

== Frequently Asked Questions ==
Q: I can not see any plugin output. What to do?
A: Put the shortcode [ss_song_requester] anywhere on a page!

Q: I do not like the styling. Can I override it?
A: Of course. Copy the file ss_song_requester-public.css from the public/css directory of this plugin to the directory wp-content/themes/yourtheme/css and change everything to your liking.

Q: How can I give my DJ access to the frontend administration?
A: Create him an account and log him in. Don't forget to activate the Subscriber role in the Manage requests setting (or any other role your DJ should belong to).

== Screenshots ==
1. Empty request list
2. Request list with open request form
3. Generate demo data in the admin
4. Request list with admin buttons
5. Edit in place function
6. List view of song requests with opened screen options tab
7. Edit in place function with opened help tab
8. Plugin's settings page
9. Request list public view
10. Request list on mobile device



== Changelog ==

= 1.0.3 =
*Release Date - 29. 11. 2018*

* Bugfix in admin (D`oh!)

= 1.0.2 =
*Release Date - 28. 11. 2018*

* Updated font awesome to v5.5.0
* Added active/inactive setting for the request form

= 1.0.1 =
*Release Date - 20. 04. 2018*

* Updated font awesome to v5.2.0
* Added How to use panel in admin
* tested up to 4.9.8


= 1.0.0 =
*Release Date - 20. 04. 2018*

* Initial release


== Upgrade Notice ==
v1.0.3: Overwrite admin/class-ss_song_requester-admin.php