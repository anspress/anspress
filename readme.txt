=== AnsPress - Question and answer ===
Contributors: nerdaryan
Donate link: https://www.paypal.me/nerdaryan
Tags: question, answer, q&a, forum, profile, stackoverflow, quora, buddypress
Requires at least: 4.0
Tested up to: 4.6
Stable tag: 3.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Free question and answer plugin for WordPress. Made with developers in mind, highly customizable.

== Description ==
Demo & support forum: http://anspress.io/
GitHub repo: [Git AnsPress](https://github.com/anspress/anspress/)

Easily add question and answer section like stackoverflow.com or quora.com in your WordPress.
AnsPress is a most complete question and answer system for WordPress. AnsPress is made with developers in mind, highly customizable. AnsPress provide an easy to use override system for theme.

Extensions:

  * [AnsPress email notification](http://anspress.io/downloads/anspress-email/)
  * [Categories for AnsPress](http://anspress.io/downloads/categories-for-anspress/)
  * [Tags for AnsPress](http://anspress.io/downloads/tags-for-anspress/)

Developers tool

  * [AnsPress extension boilerplate builder](http://anspress.io/downloads/anspress-boilerplate-extension/)

List of features:

  * Notification
  * Featured Question
  * Sorting question and answer by many options.
  * Ajax based form submission
  * Theme system
  * Flag and moderate posts
  * Voting on question and answer
  * Question tags and categories
  * Question labels (i.e. Open, close, duplicate) new labels can be added easily.
  * Select best answer
  * Tags suggestions
  * Comment on question and answer
  * reCaptcha
  * User level : Participant, Editor, Moderator
  * Advance user access controls
  * Email notification
  * User can add questions to his favorite
  * User can edit profile
  * User can upload cover photo
  * Friends and followers system
  * User points system (reputation)


= Help & Support =
For fast help and support, please post on our forum http://anspress.io/questions/


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
