=== AnsPress - Question & answer system ===
Contributors: nerdaryan
Donate link: https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development
Tags: question, answer, q&a, forum, community
Requires at least: 3.5.1
Tested up to: 3.9.1
Stable tag: 0.1.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A most advance community question and answer system for WordPress (still under development)

== Description ==

A most advance community question and answer system for WordPress (Still under development).

Follow me on twitter to get update of next stable version @nerdaryan.

= Help & Support =
For fast help and support, please post on our forum https://rahularyan.com/support

= How to Setup =

** After updating to 0.1.8 **

Make sure your do `Meta recount check` from `AnsPress Settings -> Maintaince` tab. This will update default meta values.

* After installing plugin create a new page and add this shortcode `[anspress]`
* Now set this newly created page as base page from WP-Admin -> Settings -> AnsPress Options.
* Head to Menu editor (WP-Admin -> Appearance -> Menus ) and assign Anspress menu to your nav.
* if anspress permalink won't work, then save your permalinks once from WP-Admin -> Settings -> permalinks.
 

**New features**

Here are some features which we have recently added to AnsPress:

* Added categories page
* Added tags page
* Added category page
* Added tag page
* Point system
* Theme system.
* Theme override.
* Voting questions/answers.
* Add question to favourite (User profile is still in development).
* Flag question/answer (Auto queue for moderation is still in development).
* Commenting on question and answer.
* Create custom notes for flagging question/answer.
* Edit answers/questions.
* Change question status.
* User roles.
* inline comment edit/delete.
* View Count.

**Page Shortcodes**

Use this shortcode in base to AnsPress work properly
`[anspress]`

**New features coming soon in v1.0**

We are working hard to make this plugin much better, and here are some features which will available in future version 1.0:

* Better voting system
* Better admin interface
* User profile
* Choose Best Answer for Question
* Revision control
* Multiple authors
* Point based user access
* Filter questions
* Sort answers by votes and date
* Quick ajax search system
* Ajax based form submit

== Installation ==

Its very easy to install the plugin.

* Install plugin (WP -> Plugins -> Install new -> Anspress)
* Activate plugin (Plugins -> Installed Plugins -> Activate)
* Create an AnsPress page (mine had done it automatically, but incase it doesnt) Pages -> Add New -> Type [anspress] in the page and publish (the name can be changed to whatever you like, but there must be `[anspress]` shortcode in content.
* Add AnsPress links in your menu, Appearance -> Menus, and then drag AnsPress links in your menu, and save.
* Your page should have the Ask, Categories, Tags, Users etc wherever you placed your menu!

** After updating to 0.1.8 **

Make sure your Meta recount check from AnsPress Settings -> Maintaince tab. This will update default meta values.

= Page Template =

As AnsPress output its content based on shortcode, so you must edit you page template to avoid double title. So create a page template without title and set it for AnsPress base page. or simply create a page template with this name `page-YOUR_BASE_PAGE_ID.php`. Change the YOUR_BASE_PAGE_ID to the id of the base page.

That's all. enjoy :)


== Frequently Asked Questions ==

**Page Shortcodes**

`[anspress]` add anspress shortcode to your base page for anspress to work properly

= Can I override theme ? =

Yes, you can override the theme file easily. Simply follow below steps:

1. Create a `anspress` dir inside your currently active wordpress theme directory.
	
2. Copy file which you want to override from AnsPress theme dir to currently created dir in your WordPress theme folder.

== Screenshots ==

1. List of question in home page, category, or tags page.

2. Single page layout

3. Admin questions 

4. AnsPress Options in wp-admin 


== Changelog ==

= 0.1.8 =

* Added sorting for answers
* Added ask form if no questions found
* Added recount for metas
* Signup form in answer form
* Improved question page, vote and fav button
* Improved layout of list page
* Improved ask form
* Directly login after asking
* Added OOP based JS for site and validation for ask signup form
* Added signup form in ask form 

= 0.1.7 =

* Fixed rewrite rule
* Fixed ap_append_vote_count if post type is answer
* Updated ap_user_display_name_point to ap_user_display_name
* Fixed menu to use pretty URL
* Fixed views count
* Fixed is_anspress()
* Fixed edit button
* Removed points (later it will be included as an addon)
* Fixed vote button
* Flush on activate
* Fixed wp_title on question page 

= 0.1.3 =

* Added categories page
* Added category page
* Added tags page
* Added tags page
* Removed page shortcodes
* Theme system updated

= 0.1.2 =

* View Count.
* FIX: Question status chnage.

= 0.1.1 =

* FIX: Flush rule on option update

= 0.1.0 =

* Theme system updated.
* Added capability for administrators and subscribers
* More capability added to user roles.
* Shortcode based main pages.
* Theme tweaks.
* inline comment edit/delete.
* Removed Bootstrap.


= 0.0.8 =

* Plugin option page fix.

= 0.0.7 =

* Moved all activation code to activate.php
* Theme tweaks
* Added point system
* Added points option to plugin option
* Merge default plugin options

= 0.0.6 =

* Added user roles.
* Fixed flush rewrite rule on plugin activation.
* content filter in question and answer edit form.

= 0.0.5 =

* Theme tweaks
* removed unwanted styles from bootstrap
* Added option for changing question status

= 0.0.4 =

* Added customized bootstrap.js and bootstrap.min.css 
* Added nonce in edit question button and form 
* Removed unused tab code 
* Added answer edit option 

= 0.0.3 =

* Added question status in single question page.
* Removed bootstrap.css and bootstrap.js from admin.
* Single question page style tweaks.
* Added tabs in option, added theme tab in option page.
* Added flag notes default message, also added MISC tab in option panel.

= 0.0.2 =

* Added question edit button.
* Sanitised from submission.
* Added category and tags to question single page.
* Added edit page to default theme.
* Modified CSS of default theme.

= 0.0.1 =

* Initial version commit.










