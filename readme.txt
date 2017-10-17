=== AnsPress - Question and answer ===
Contributors: nerdaryan
Donate link: https://www.paypal.me/nerdaryan
Tags: question, answer, q&a, forum, profile, stackoverflow, quora, buddypress
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 4.0.5
License: GPLv2 or later
Demo: https://anspress.io/demo/?product=anspress
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Free question and answer plugin for WordPress. Made with developers in mind, highly customizable.

== Description ==
AnsPress is an open source, developer friendly, question and answer plugin for WordPress. AnsPress adds a full question and answer system to your existing WordPress site. It can be used to create a q&a network similar to StackOverflow and quora, or be a single page in existing site. The plugin supports multi-languages, shortcodes, reCAPTCHA, email and push notification and more.

AnsPress is not just limited to question and answer. It can be used in many different ways such as BugTracker, Company Internal QA board, support tickets, FAQ etc. Anspress is trusted and used by many popular companies and sites at scale.

**Performance and Optimizations:**

In our latest release we've made big improvements to performance and have tested in many different server environments. It runs smoothly on every type of server with minimal impact on load times. AnsPress (including all of its functionalities) is very fast comparing to other QA plugins. In the latest release we have made major improvements to reduce MySQL queries and to increase the speed of page load for all page templates within plugin.

**Developer Friendly:**

AnsPress is built with developers in mind and can easily be extended to fit your needs. All parts of layout templates can be overridden. Our source code follows WP coding standards and is properly commented. We are working on documentation site which will be available soon.

**Contributors:**

This plugin wouldn't be possible without a huge amount of contribution. Check our contributors at https://anspress.io/contributors/

**Support and Demo:**

We provide support on our site: https://anspress.io/questions
Our support section uses actual version of AnsPress, so you can check out the support site if you want to see full working version of AnsPress.
You can also take the demo site for a test drive https://d.anspress.io/

**AnsPress Key Features:**

* Submit / Filter / Order / Edit / Delete Question
* Answer / Comment
* Vote and Select Best Answer
* Notification Emails and Web Push Notifications
* 10+ languages supported
* Captcha supported
* Shortcodes available
* Private/Public for Question and Answer
* Ability to Follow Questions / Answers
* Featured (Sticky) Question
* Closed questions
* Flag/report questions and answers to moderator
* Basic user profile
* BuddyPress integration
* More to come...

**Free Add-ons:**

* Reputations
* Tags
* Categories
* Bad words filter
* Email notifications
* reCaptcha
* Dynamic text avatar

AnsPress is frequently updated and more features are added based on feedback from our users. This means you are welcome to give us feedback and suggestions as to what you would like to see or need in the plugin. Visit our Github project https://github.com/anspress/anspress  or visit our community https://anspress.io/questions/  to get the updates.


**Page Shortcodes**

Use this shortcode in base to AnsPress work properly
`[anspress]`


== Installation ==

Read full documentation here https://anspress.io/docs/


== Frequently Asked Questions ==

Read full FAQ here https://anspress.io/docs/?topic=faq

== Screenshots ==

1. Question list page.

2. Single question page.

3. User profile

4. User hover card

5. Users directory

6. Notifications



== Changelog ==

= 4.0.5 =
  * Fixed user profile rewrite and reputation query
  * Fixed: dropdown reputation for reputation count
  * Addons not toggling on WpEngine
  * Hide notification and user profile if not logged in
  * Fixed: filters are not persisting
  * Language updated
  * Fixed: restored_question activity string update
  * Fixed: admin JS errors
  * Fixed: notification dropdown
  * Removed CSS minify folder
  * Improved list filters
  * Fixed buddypress "load more" issue, disable AnsPress notification if buddypress enabled
  * Added reputation re-count
  * Added answer and flags count
  * Added vote re-count
  * Fixed theme updater
  * Fixed: load more not working in BP profile

= 4.0.4 =

  * Do not create base pages automatically unless user wants
  * Fixed: Illegal string offset error
  * Added options for user page titles
  * Make user pages slug editable
  * Fixed missing assets
  * Fixed: AnsPress JS is missing from home
  * Fixed translation
  * Fixed wrong text domain in category template
  * Updated po and mo

= 4.0.3 =

 * Improved updater
 * Fixed: fatal error while deleting a question

= 4.0.2 =

 * Minor fixes

= 4.0.1 =

 * Minor fixes

= 4.0.0 =

** Beta 4 **

* Fixed bugs in files
* Fixed buddypress.php bugs
* Fixed avatar.php issues
* Fixed: Author is not able to view their own private posts
* Fixed fatal error
* Allow user page callback without class
* Show WP_Error message for comments in snackbar
* Fixed: unable to submit form when duplicate check is disabled
* Fixed: duplicate post error snackbar message not showing
* Improved user page title
* Fixed php notice
* Fixed menu
* fixed warning
* Added option to disable duplicate check
* Do not insert qameta if post is not question or answer
* Fixed trash question
* Added misisng assets in wp-admin
* Fixed parent_q warning
* Fixed typo
* Added notification table in uninstaller
* Fixed reputation migrator
* Deactivate old AnsPress extensions
* Disable old AnsPress extensions while initializing AnsPress
* Improved migrator
* Add 302 status in shortlink redirect
* Fixed shrotlink in reputation
* Check ap_meta table exists while migrating
* Do not insert reputations, notifications and prevent sending emails while updating
* Added filter to modify tag question query
* Added user answers page
* Load comments by default
* Fixed JS template not loading properly
* Prevent question query to use SQL_CALC_FOUND_ROWS which is relatively slow in big site
* Added support for polylang
* Fixed short link
* Improved notification dropdown toggle
* Improved notification dropdown menu
* Added notification dropdown
* Improved user menu
* Improved anspress pages, menu and slugs
* Improved user menu
* User profile as an addon
* Added load more button in notification
* Improved notification sorting
* Added notification count and mark all notification read button
* Improved notification ref prefetch
* Improved notification query
* Improved best answer notification
* Improved notification flush on post delete
* Delete corresponding notification when a post gets deleted
* Notify user on comment
* Notification for best answer
* Reload reCaptcha after form submission
* Fixed: while editing anonymous user's post author changes
* FIXED: wrong classes for columns in tags and categories
* Fixed tags pagination
* Added recaptcha methods
* Added notifications addon
* Added base query class
* Fixed addons loading
* Improved addons functions
* Added add-on activation hook
* CREATE TABLE IF NOT EXISTS to CREATE TABLE
* Fixed reputation template file location
* Improved category style
* Fixed: strpos(): Offset not contained in string
* Fixed: x-index of snackbar
* Improved category icon
* Store reputation in meta for sorting purpose
* Removed debug code
* Fixed: AP_Roles::remove_roles() should not be called statically
* Added improved version of dynamic avatar addon
* Fixed: private check box not working in form

** Beta 3 **

* Normalize addons path
* Added subscribe button
* Check if user can access post before sending email
* Fixed: PHP Fatal error:  Uncaught Error: Call to undefined function ap_delete_subscriptions
* Fixed: Question border not being highlighted
* Removing codeception tests as PHPUnit test will be written from scratch
* Improved email notification and subscribers
* Added email queues and content table
* Added subscription CRUD
* Fix Categories can’t remove icon or add a new one
* When using BuddyPress Add in the links to answers and reps don’t work.
* Fixed MySql datatype of qameta views and flags to store large numbers
* Fixed: illegal string offset date
* Fixed attachment delete
* Fixed delete uploads
* Renamed author page to user page
* Update term ids
* Improved list layout
* Include missing template files
* Rename base.php and content-list.php to question-list.php and question-list-item.php
* Fixed: non-logged in user seeing moderate answer
* Removed dynamic avatar addon
* Migrate category meta
* Fixed static method warning
* Fixed best answer migrate bug
* Do not show upgrade message to new installs

** Beta 2 **

* Improved upgrader script
* Added post activity migrator
* Restore dates of question/answer after migration
* Added best answers migrator
* Added reputation migrator
* Added answers count migrator
* Added vote migration
* Added migration helper
* Fixed reputation prefetching
* Added reputation page in BuddyPress
* Fixed category addon
* Added reputation load more button
* Fixed: category icon and color setting not saving
* Improved reputations
* Improved DB table creation
* Fixed: headers already sent error on saving options
* Improved authors page
* Added question permalink options
* Fixed: clicking on answer order tab scroll to top
* Fixed: uploaded media are not cleared after form submission
* Removed repetitive words from login signup
* Fixed upload progress bar
* Fixed grunt tasks to add missing files
* Fixed wrong class name in hook
* Fixed comment warnings
* Fixed typo in qameta column name
* Fixed wrong user capability check
* Fixed terms
* Added about action link
* Improve about
* Removed unused images
* Added about page
* BuddyPress notification fixes
* Improved buddyPress question and answer page template
* Improved buddypress answer page
* Improved answer link
* Optimized reputation pre fetch
* Improved reputation
* Award reputation for comments
* Added reputation addon
* Fixed addon dir search
* Improved email addon
* Added email addons
* Added tags extension as addon
* Added category extension as an addon
* Added bad words
* Improved recaptcha
* Unselect best answer when its get deleted
* Removed broken widgets
* Improved breadcrumbs
* Delete votes when corresponding votes get deleted
* Improved login/signup template
* Improved addons
* Improved addons and options
* Added addons
* Improved uninstaller
* Added uninstall data tool
* Show notice when option gets updated
* Improved option form
* Added author page
* Renamed theme folder to templates
* Improved search form and widget
* Improved filter search
* Improved list filter
* Improved list style
* Fixed warning in ask question submission
* Check if user can read comment before fetching via ajax
* Improved comment link
* Before improving comments
* Improved comment edit
* Improved comments
* Improved comment form
* Improved comments listing
* Fixed attachment display
* Fixed permanent delete action
* Improved delete and restore actions
* Improved toggle featured action button
* Improved actions and post staus view
* Improved answer permalink
* Improved edit answer page
* Improved question edit page
* Improved close action and select button
* Improved select answer button and post actions
* Delete temporary media using wp_cron
* Finalizing uploader
* Improved uploader
* Improved ask form and vote bug
* Organized assets
* Remove plupload files after uploading files
* Improved uploader
* Improved Backbone collection and view
* Replaced LESS with SCSS
* Improved answer form
* Improved answer form
* Added snackbar
* Adding backbone.js
* Fixed errors in flag.php
* Fixed votes.php errors
* Removed ap_meta table and functions
* Do not store views to ap_views table by default
* Added views table
* Fixed vote and clear flag button in wp-admin
* Removed buddypress hooks
* Added vue.js and answer list in question edit.
* Improved post table
* Improved admin
* Improved option page
* Removed user profile and mentions
* Removed reputation and follow
* Improved query by status
* Removed subscription feature
* Permission check in close question
* Added close button
* Moved status above question
* Closed is not post_status anymore
* Removed activity and notification
* Remove activities page
* Fixed error in admin
* Removed activities
* Improved question activities
* Improved avatar for anonymous user
* Improved avatar generation
* Dynamically generate user avatar
* Added misisng attach table in qameta
* Fixed db error while creating ap_qameta table
* Fixed activity in list
* Releasing 4.0.0-alpha.1
* Fix wpcs reporting
* Fixed errors in qaquery
* Pre cache users
* Delete qameta row when post get deleted
* Improved caching
* Improved attachment cache
* Pre fetch votes
* Improved set featured question
* Improved vote counts
* improved voting
* Improved terms query
* reduced more queries
* Improving query
* Removed more post metas
* Removed flags post meta
* Replaced get_post to ap_get_post
* Improved select answer
* Replaced vote post meta
* Improved query filter
* Replaced deprecated functions
* Removed many unused functions
* Added qameta table

= 3.0.7 =

* Fixed: Inline link in tinyMCE.

= 3.0.6 =

* Fixed: failed to uninstall
* Added action dropdown test
* Fixed: Anonymous name is not showing

= 3.0.5 =

* Fixed: Watch.js not enqueued

= 3.0.4 =

* Fixed: undefined var in tinyMce init


= 3.0.3 =

* Fixed: product update fails
* Improved tip style
* Fixed: warning in ap_get_link_to() if array is passed with ap_page
* i18n: it not a tranlation strings. remove the _x() function
* i18n: breake down the translation string into two strings - a message and an action link
* i18n: avoide using HTML tags in translation strings
* i18n:rewrite the translation string for easier translation in RTL languages
* i18n: simpler translation strings, without unneeded placeholders.
* Fixed: Ajax editor loading
* Fixed: toggling text editor not working in ask form
* Fixed: missing hook
* Editor not expanding for answer form
* Fix: question and answer added from wp-admin are not showing
* Fixed: mention toggling
* Fixed checkbox option not saving properly

= 3.0.2 =

* Fixed reputation issue
* Fixed checkbox not saving in AnsPress options

= 3.0.1 =

* Fixed translation loading

= 3.0.0 =

* Added reset button in option form
* Fixed editor issue
* The function ap_user_get_member_for() previously gave no output for users who joined today.
* Some bug fixes
* Improved question list and home page styles
* Fixed array unique warning
* Fixed featured post sql query
* Fixed: Missing ap-functions.js in wp-admin
* Added ap_canonical_url()
* Improved canonical url
* Improved dashboard
* Define wp-browser version
* Added question sorting by views
* Added filter ap_ask_btn_link
* Improve about page
* Keep dropdown inside window. Also filter dropdown inside window
* Improved toggle user profile
* Improved reputation toggle
* Added option to toggle mention functionality
* Fix: avatar upload
* Fix: question suggestion not loading in wp-amdin
* Detect anspress.php template
* Fix: Prevent moderate answer to be selected as best
* Fix: set featured question not working
* Question widget issue in pagination page
* Added attachment in answers
* Show lists of attachments in questions
* History shows answer author selected best author instead of selector
* Added post action link to convert a question into a post
* Hide notification for trashed items
* Delete notifications after deleting activity
* Fix user widget issue with page pagination
* Added restore post action link.
* Improved post history when answer or comments get deleted.
* Fix: Activity not deleted after deleting parent post
* Improved search form
* Fix: Mark notification as read not working
* Fixed: notification pagination
* Fixed comment edit
* Load comments using JS templates
* Added filter to disable question suggestion while asking.
* FIX: Comment is not removed from DOM after delete
* Deleted question does not return 404
* Fix: Subscribe Widget fatal error
* Made ap_get_questions overridable.
* Search page show wrong message if no results
* Fix: wrong message when there is no question or answer in user profile
* Delete lock for comment is not working
* Add sortby args in AnsPress shortcode
* Fix: Wrong comment count in user profile
* Added ap_no_moderation cap
* Add anonymous post for moderation
* Clear existing form error before submitting form
* Check for duplicate posts
* FIXED: get_currentuserinfo is deprecated since version 4.5!
* Added mention suggestion
* Improved uninstaller
* Do not hold for moderation if moderator
* Improved permanent_delete_post ajax callback
* Improved delete_post ajax callback
* Fixed: question is getting closed after selecting answer if option is enabled.
* Improved select_best_answer ajax callback.
* Improved flag ajax callbacks
* Improved vote ajax callback
* Improved subscribe button
* Converted methods to static and moved to hooks.php
* Removed load_user_field_form ajax callback
* Improved post status update
* Load notification and profile menu dropdown using ajax
* Fixed license saving issue
* Added search-form.php and improved list-head.php
* Load AnsPress assets in menu editor
* Improved filters
* Improved sort filter
* Improved list filter
* Improved base page shortcode
* Fixed: Activation redirect warning
* Moved all add_action and add_filter to hooks.php
* Improved question and answer form.
* Load TinyMCE using ajax.
* Check if required PHP version is installed.
* Fix: Remove hyperlink from post time
* Fix: User can answer even after selecting best answer
* Improved answer form processing
* Load question actions using JS template and improved dropdown
* Improved JS template and hover card
* Added simple JS template (like angular.js) parser
* Improved hover card
* Improved tip and hover card
* Improved admin assets loading
* Fixed wrong method name comment_unapproved in add_action
* Added test for ap_user_can_approve_comment
* Added comment approve button
* Improved comment form for answer
* Improvd display_name filter
* Improved comment and hover card
* Function added install test
* Fixed role bugs and added role unit tests
* Improved ask form
* Fixed ap_user_can_read_post()
* Improving comment template
* Improved comment subscription
* Added subs_answer_id column in subscribers table
* Fix: Hide comment form before loading new
* Fixed: do not allow user to change status if post status is moderate or closed.
* Improved comments form and submission
* language fix
* Fix human_time_diff strings translatable
* Fixed shortcode not to show "AnsPress shortcode cannot be used inside AnsPress content"
* Updated registering option functions and hooks
* Fixed PHP warnings and improved option save action


= 2.4.8 =

* Fixed AnsPress menu not appearing in menu editor.

= 2.4.7 =

  * Do not allow using [anspress] shortcode inside [anspress]
  * Do not pass $this to class AnsPress_Common_Pages
  * Added filter ap_future_post_notice
  * Merged question and answer options.
  * Added option to show default date format.
  * Add translation support in ap_human_time()
  * Added future post
  * Added comment count in user profile
  * Fixed activation error
  * Added image uploader in test
  * Load AnsPress in plugin_loaded hook
  * Added before_loading_anspress action
  * Add "have-comments" class when load comment form
  * Fixed: overrides.css loading order
  * Updated test
  * Fix: GMT offset in activity time
  * Improved: activities hook and activity time stamp
  * Added option to keep stop words in slug
  * Added filter:  ap_active_user_page
  * Improved: status change permission
  * Improved: User can delete capability check
  * Improved: ap_user_can_edit_question function
  * Fixed: bugs reported by CI
  * Moved classes to class folder

= 2.4.6 =

  * Check Github

= 2.4.5 =

  * Check Github

= 2.4.2 =

  * Check Github

= 2.4.1 =

  * Check Github

= 2.4 =

 * Check Github

= 2.3.6 =
= 2.3.5 =

* Check Github

= 2.3.4 =

* Check Github

= 2.3.3 =

* Check Github

= 2.3.1 =

* Check github

= 2.3 =

* Check github

= 2.2 =

* Check github
