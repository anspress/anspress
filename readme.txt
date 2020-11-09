=== AnsPress - Question and answer ===
Contributors: nerdaryan
Donate link: https://www.paypal.me/anspress
Tags: question, answer, q&a, forum, profile, stackoverflow, quora, buddypress
Requires at least: 4.7
Tested up to: 5.3
Stable tag: ANSPRESS_RELEASE_VERSION
License: GPLv2 or later
Demo: https://anspress.net/demo/?product=anspress
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A free question and answer plugin for WordPress. Made with developers in mind, and highly customizable.

== Description ==
AnsPress is an open source, developer friendly, question and answer plugin for WordPress. AnsPress adds a full question and answer system to your existing WordPress site. It can be used to create a Q&A network similar to StackOverflow and Quora, or be a single page on existing site. The plugin supports multiple languages, shortcodes, reCAPTCHA, email and push notifications, and more.

AnsPress is not just limited to questions and answers. It can be used in many different ways, like a bug tracker, an internal Q&A board, support ticket forum, FAQ, and more. Anspress is trusted and used by many popular companies and sites at scale.

**Performance and Optimizations:**

In our latest release, we've made big improvements to performance and have tested the plugin in many different server environments. It has run smoothly on every type of server with minimal impact on load times. AnsPress (including all of its functionalities) is very fast compared to other Q&A plugins. In the latest release we have made major improvements to reduce MySQL queries and to increase the page load speed for all page templates within the plugin.

**Developer Friendly:**

AnsPress is built with developers in mind and can easily be extended to fit your needs. All parts of the template layouts can be overridden. Our source code follows WP coding standards and is properly commented. We are working on a documentation site which will be available soon.

**Contributors:**

This plugin wouldn't be possible without the huge amount of contributions we have received. Check our contributors at https://anspress.net/contributors/

**Support and Demo:**

We provide support on our site: https://anspress.net/questions
Our support section uses the actual version of AnsPress, so you can check out the support site if you want to see full working version.
You can also take the demo site for a test drive at https://anspress.net/demo/

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

AnsPress is frequently updated and more features are added based on feedback from our users. This means you are welcome to give us feedback and suggestions as to what you would like to see or need in the plugin. Visit our Github project https://github.com/anspress/anspress  or visit our community https://anspress.net/questions/ to get updates and leave feedback.


**Page Shortcodes**

Use this shortcode in our base for AnsPress to work properly
`[anspress]`


== Installation ==

Read the full documentation here https://anspress.net/resources/


== Frequently Asked Questions ==

Read the full FAQ here https://anspress.net/resources/faq/

== Screenshots ==

1. Question list page.

2. Single question page.

3. User profile

4. User hover card

5. Users directory

6. Notifications



== Changelog ==

Since 4.1.19 changelog can be seen at https://github.com/anspress/anspress/releases

= 4.1.18 =

* Use user_trailingslashit() in the links of questions, categories..etc

= 4.1.16 =

* Fixed: user profile questions page pagination

= 4.1.0 =

* Fixed: wp_mail first argument cannot be empty.
* Set question archive link and include front slug while adding rewrites
* Added hooks in form class: ap_before_prepare_field, ap_generate_form_args, ap_after_form_field
* Improved question and answer schema
* Prevent polluting JavaScript global namespace.
* Fixed: question shortcode
* Fixed: undefined index in questions widget.
* Added new methods set_values()
* Added email template
* Check other templates exists before loading anspress.php template
* Load page-*.php for archives if available
* Deprecated wp_head related hooks
* Fixed: comments modal not showing once closed
* Fix: Comments posted from wp-admin aren't visible in frontend.
* Fixed: User profile shows anonymous when rendering using shortcode
* Removed options tags_page_title and tags_page_slug
* Removed option categories_page_slug and categories_page_title
* Fixed: anonymous cannot answer if allow op to answer option is unchecked
* Removed old form and validation classes
* Removed 3.x to 4.x upgrader
* Added recaptcha in comment form
* Anonymous name not showing in comment
* Show category and tags in single question page
* Fixed tags pagination
* Improved categories page template
* Added more dependent pages: categories, tags and user.
* Removed JS template
* Removed notifications.html js template
* Removed question.html JS template
* Added single comment route
* Removed ap_the_comment() from answer.php
* delete list.php
* Renamed question.php to single-question.php :tractor:
* Improved comments interface :lipstick:
* Added new option post_comment_per :snowflake:
* Removed option only_admin_can_answer
* Removed logged_in_can_see_comment
* Removed option logged_in_can_see_ans
* Added new option post_answer_per for answer permission
* Removed option allow_anonymous
* Added new option post_question_per
* Removed option only_logged_in
* Added a new option read_comment_per to control comments visibility
* Added new option read_answer_per
* Added new option read_question_per. This option will control visibility of a question for users.
* Do not allow post author to change own post status regardless of moderator role.
* Strictly remove all tinymce toolbar except first one
* Added ap_rewrites filter hook
* Improved rewrite
* Added new question permalink structure: QUESTION_SLUG/question-name-213/
* Added new question permalink structure: QUESTION_SLUG/213-question-name/
* Added new question permalink structure: https://domain.com/QUESTION_SLUG/question-name/question-id
* Improved question rewrite rules
* Added embed support for question and answer.
* Add excerpt support question and answer CPT
* Fixed JS issue and improved loading
* Use single hierarchy for displaying single question.
* Fixed: qameta fields are not appending properly
* Fixed menu items
* Removed "ask_page_title" option.
* Removed "ask_page_slug" option.
* let admin select ask question page
* add "answer" prefix before answer permalink
* Show loading while submitting question or answer form
* Added new addons page
* Improved tag addon options
* Improved reputation options
* Improved recaptcha addon options
* Improved notification addon options
* Improved email addon fields
* Added textarea field
* Process addon options form
* Added new addons page
* Make reputations date accurate
* Deprecated: AnsPress_Validation and AnsPress_Form classes.
* Improved wp-admin new answer page.
* Deleted old options page
* Check access rights in options page
* Deprecated options page related functions.
* Improved options page. Using new form class.
* Added radio field
* Added bad words in validation class and removed addon
* Delete attachments if image not found in content
* Improved answer form
* Moved function of includes/answer-form.php and deleted file.
* Fixed: removed hyphen from custom html tags
* Deprecated ap_read_env() and ap_env_dev()
* Deprecated ap_tinymce_editor_settings()
* Improved answer form
* Added jquery-form plugin as WP is using older version of this plugin
* Improved question suggestion
* Deleted ask-form.php
* Added recpatcha field and deprecated few functions.
* Improved question processing
* Improved upload field
* Added new form library
* Fixed reputation re-count
* Replace post-message tags with postMessage
* Fixed html tags ap-comments to apComments and post-actions to postActions
* Added new argument in ap_new_question_email_tags hook
* Allow overriding question moderation message.
* Fixed: If anonymous post, `post_author` get replaced by current user id if `anonymous_name` is empty.
* Load local avatar only if no gravatar exists
* FIXED: Wrong field type for comment_number
* HOOK: Added `ap_addon_avatar_colors` to let avatar colors customizable.
* fix: editing broken demo link
* Fixed: tags filter redirect to 404 on home page.
* Updated translations
* Fixed translation text domain
* Make some strings translatable without context
* Make AP feed metabox title translatable.

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