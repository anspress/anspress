# AnsPress - Question and answer #
**Contributors:** nerdaryan  
**Donate link:** https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development  
**Tags:** question, answer, q&a, forum, profile, stackoverflow, quora, buddypress  
**Requires at least:** 4.2  
**Tested up to:** 4.4  
**Stable tag:** 2.4  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Free question and answer plugin for WordPress. Made with developers in mind, highly customizable.

## Description ##
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
  * Email notification
  * User can add questions to his favorite
  * User can edit profile
  * User can upload cover photo
  * Friends and followers system
  * User points system (reputation)


### Help & Support ###
For fast help and support, please post on our forum http://anspress.io/questions/


**Page Shortcodes**

Use this shortcode in base to AnsPress work properly
`[anspress]`


## Installation ##

Read full documentation here http://anspress.io/documents/


## Frequently Asked Questions ##

Read full FAQ here http://anspress.io/documents/doc_page=faq

## Screenshots ##

### 1. Question list page. ###
![Question list page.](http://ps.w.org/anspress---question-and-answer/assets/screenshot-1.png)


### 2. Single question page. ###
![Single question page.](http://ps.w.org/anspress---question-and-answer/assets/screenshot-2.png)


### 3. User profile ###
![User profile](http://ps.w.org/anspress---question-and-answer/assets/screenshot-3.png)


### 4. User hover card ###
![User hover card](http://ps.w.org/anspress---question-and-answer/assets/screenshot-4.png)


### 5. Users directory ###
![Users directory](http://ps.w.org/anspress---question-and-answer/assets/screenshot-5.png)


### 6. Notifications ###
![Notifications](http://ps.w.org/anspress---question-and-answer/assets/screenshot-6.png)




## Changelog ##

### 2.4 ###

 * New: added activity feed page
 * Fix: Answers left through admin don’t show up in user stats, but show up for admin stats
 * Fix: The name of question author is not completely shown on the back-end
 * Fix: The avatar of “… started following …” is incorrect
 * Fix: Tag validation
 * Tweak: Improved reputation functions
 * Tweak: Improved ap_human_time()
 * Fix: table name in user query
 * Fix:  view data update
 * Fix: Don't check minimum characters when it is zero
 * Fix: view data update
 * Fix: utf-8 encoding in message
 * Fix: answer edit redirect
 * Fix: Reputation points on new registration
 * Tweak: Improved ap ajax response
 * New: Added option to switch environment
 * Fix: widget deprecated notice
 * Language: Updated de_DE
 * Tweak: Add unique body class for anspress pages
 * Fix: Shows wrong user in notification for answer
 * Fix: Question widget sorting not working
 * Tweak: Improved flag button
 * Tweak: Remove link from anonymous user
 * Fix: Non Logged Users can Initiate an image Upload
 * Tweak: Confirm changed email
 * Tweak: subscribe button
 * Language: Updated Russian from POT
 * Tweak: Change base title in wp hook
 * Tweak: Improved mention RegX
 * Fix: page title issue :)
 * Fix: coding style in ap-functions.js
 * Fix: overqualified style
 * Tweak: Remove unused class AP_Notification
 * Tweak: Update options-page.php
 * New: Added mention functionality
 * Fix: New lines are cleared on saving email options
 * Fix: missing version number in main plugin file
 * Tweak: Locate anspress directory in parent theme if not found in child
 * New: Added question shortcode
 * Fix: Asker can answer own question even if option is disabled.
 * New: Added option to switch user profile
 * New: Added an option in user profile so they can hide themselves from public
 * New: Added notification widget
 * Tweak: Updated notification functions for new notification table
 * Tweak: Show notification as sidebar
 * Fix: subscription and notification
 * Tweak: Edit question notification
 * Tweak: Improved question suggestion
 * Language: Added Japanese .po file
 * Language: added zh-CN and zh-TW translation files
 * Tweak: Improved activity system
 * Tweak: Improved subscriber system
 * Tweak: When answer is posted as private ajax shows anonymous avatar
 * Fix: Hide suggestion if no related question found
 * Tweak: Improved anspress tooltip
 * Tweak: Wrap post time in anchor
 * New: Replaced history by activity
 * Fix: Set header as 404 if question id is false
 * New: Add link to cancel comment (add/edit)
 * Fix: Comment form not loading after once loaded
 * New: Add page attribute in anspresss shortcode
 * New: Tool to customize anspress capabilities
 * Tweak: Improved admin option page
 * Tweak: Removed extension specific functions
 * Fix: When Anonymous posting is activate, there is a Status option shown
 * Fix: breadcrumbs widget
 * Fix: If user page slug is anything other then "user" it gives 404 error
 * Tweak: Show a warning message if ajax does not return valid JSON
 * Tweak: Added #anspress before grid class
 * Fix: Duplicate query strings while sorting using ajax
 * Fix: Do not include trash post status in question query
 * New: overrides via a child-theme
 * Fix: Insert notification only if profile is active
 * Language: French translation update
 * Tweak: Removed span form avatar
 * Fix: Exclude anspress base page from "Front page" page list
 * Tweak: Improved CS for form
 * Tweak: Improved CS of class-user.php
 * Tweak: Improved CS of class-form.php
 * Tweak: Improved CS of answer-form.php, answer-loop.php
 * Language: Turkish translation submitted by nsaral
 * Fix: compatibility issue with multisite subsites
 * Tweak: Modified activate.php to use $wpdb->prefix instead of base_prefix for compatibility on multisite subsites.
 * Fix: ap_is_my_profile()
 * Fix: Removed print_r from profile form
 * Fix:  undefined object
 * Tweak: Added multipart form
 * Tweak: Improved hooks.php
 * Tweak: Improved standard of hooks.php
 * Tweak: Improved coding standard of common-pages.php
 * Tweak: class-theme.php code improved
 * Fix: Search post by blank search phrase
 * Fix:  user menu link
 * New: Search question and answer by user_login in wp-admin
 * Fix: pagination in home page
 * New: Added post search by author_id in wp-admin post table
 * New: Added "banned" user role
 * New: Renamed actions.php to hooks.php
 * New: Improved roles check
 * Tweak: Improved coding standard of class-roles-cap.php
 * Tweak: Improved coding standard of ajax.php
 * Tweak: Improved coding standard in theme.php
 * New: Added comment flagging
 * Language: Italian translation

### 2.3.6 ###
### 2.3.5 ###

* Updated paged variable when in home page
* Removed unused arg
* Fixed pagination on home page
* Restore question history after deleting answer
* Upload field triggered twice
* All links of user menu not shown if in other profile page
* Updated french translation by Shad
* User active time shown as Last seen 46 years ago
* Escaped cover photo url
* Added option for disabling user menu collapse
* Improved user page and stats
* Registered user widget position
* Added widget wrapper div
* Warning: Illegal string offset ‘file’
* Get paged args in user loop
* Fixed infinite loop in question page

### 2.3.4 ###

* Added option for toggle [solved] prefix
* Added custom follow button text
* Improved user meta function
* Return default cover when there is not cover photo

### 2.3.3 ###

* Fallback for form if JS/Ajax fails
* Moved upload field before filter, so it can be filtered
* enqueue script dependency
* Italian translation .mo missing #305
* Allow empty question description when limit is set to 0
* Added a filter to disable logging IP for view count
* Remove notification item after ajax delete
* Fix for "anonymous ask" and "only admin can answer" option conflict
* Multiple click on comments open more then one comment field
* Added responsive styles for question page
* Fix for Icon fonts are not rendering
* Added responsive styles for question list
* Change subscribe to "subscribe" to "follow question"
* Password cannot be chnaged
* Fixed warning on buddyPress pages
* Improved question loop to use global
* Unsubscribe is not working
* Added option for disabling vote down
* Update POT
* Fixed ago translation
* Notice: ap_questions_tab is deprecated
* Comment is not shown after editing
* Syntax error: unrecognized expression
* Notice: Trying to get property of non-object
* Improved login and answer tab UI
* Moved question.php inside loop
* Added filter for main question query
* Wrong variable $ap_rules
* Added filters for overriding question and answer CPT
* Added after sorting callback
* Added french translation by Romain DELOUF
* Sanitized ap_sort

### 2.3.1 ###

* Mark notification as read after viewing
* Upvotes are misinterpreted as Downvotes in User’s Reputation page
* Reputation notification should hyperlinked to user’s reputation page
* Dont show follow button for own profile
* Grammatical error: comment to commented
* fix: division by zero warning in about page
* User profile fields empty, account tab redirect to admin profile.
* Polish language updated to 2.3

### 2.3 ###

* Updated version and pot
* Improved categories page
* Close question after selecting if option enabled
* Notification as dropdown
* Redirect user to login page if non loggedin user try to visit profile
* Error message on description field is overlapped by upload field
* How to ask tab
* Add category and tags filter in question list
* Added category sorting
* Improve category widget
* Image upload size
* add option to disable hover card
* added users widget
* Paragraph in comments
* improved method update_menu_url
* Add [solved] in question title
* User page show anonymous when permalink not enabled
* Allow filtering page slug
* Fixed UI and typo issues* Don't check for basic roles
* Updated plugin version
* Fixed minor and major bugs
* Notification system
* Fixed breadcrumbs for user pages
* The breadcrumb on the base page is duplicating
* if recaptcha is enabled and keys missing then show warning
* Added user dropdown menu item
* User hover card
* Added cover upload
* Improved user pages
* Improved coding standards
* Removed plugin-activation.php
* fixed Complexity in codes
* Added "more" in user menu
* Add subscribe page for user
* Add subscribe page for user
* Improved question query clauses
* member search
* Question sorting tab to select
* Added vote count meta
* Added followers widget
* Added followers and following count meta
* Improved users loop,
* Added followers page button
* Added users sorting by answers and best answers
* Added users sorting by last active
* Users page tab UI improvement
* fixed avatar
* Added last seen meta
* Added profile views
* User registered date
* Improved reputation chart data
* fixed reputation count
* added user question and answer meta
* Avatar upload ajax error
* Added about page
* Added reputation page
* User reputation loop
* Improved user profile fields
* fix canonical url when wpseo plugin installed
* Title conflict when wpseo plugin installed
* Removed base from permalink when ap_page is base
* Direct user link
* [fix] Auto flush rewrite rule when base page updated
* [fix] include current users post in list
* [fix] Safe CSS attributes
* [theme] Login popup improved
* [fix] Image uploader not working in webkit browser
* [theme]Added login modal
* [fix] Max images per post option as number
* [fix] ap_the_answer_content on bp_init
* [fix] Load BP hooks on bp_init
* [fix] conical and short link in head
* [new] Added Crayon Syntax Highlighter button
* [new] Add tinymce Autogrow
* [improved] Image upload in posts
* Check file exist before triggering error
* Check for category and tags extension
* [new] Hide how to answer tab if no page selected
* Allowed support for GitHub Updater plugin
* [fix] Do not log view in meta table if already viewed or anonymous
* [fix] View counts updated for every page view
* [fix] Strip shortcode from Answer and question
* [fix] Format get cleared while editing
* [fix] remove extra white space from contents
* [fix] Editor toggle  in answer form
* [fix] Switch editors not working in ask form, image upload in quick-tag editor
* [fix] Buddypress hooks
* [fix] Image upload not working
* updated plugin.json
* [fix] missing colon
* fixed translation function to the wordpress one
* fixed translation issue cussed by missing gettext function
* fixed translation function to the wordpress one
* added translation support for author credits
* [fix] require loading wrong path
* [new] Featured question (sticky post)
* [added] Improved subscriber system, allow community to subscribe for tags and categories
* [translation] Spanish-Spain by Pelayo de Salvador Morell
* [translation] German translation added by bloggerpro.de
* [fix] db table prefix
* Updated languages/ap.pot
* Added about page
* Updated dashboard
* [option] label update: Asker can answer
* [fix] Form error message below input field
* [fix] minor doc bug fixes
* [fix] Minor and major issues
* [fix] DB error in Answer sorting
* [fix] BuddyPress reputation count shows 1
* [new] load minified assets
* [theme] Improved admin dashboard
* [added] Added breadcrumbs widget
* [new] Breadcrumbs function
* [fix] Improve popup notification style
* [fix] Don't show delete button if question is already trashed

### 2.2 ###

* Form error message below input field
* DB error in Answer sorting
* While try to edit answer format get cleared
* BuddyPress reputation count shows 1
* Load minified assets
* Improved admin dashboard
* popup add link box in tinymce cuts off content.
* Add breadcrumbs widget
* Add breadcrumbs function
* Improve popup notification style
* Dont show delete button if post already trashed
* Add permanenet delete button from frontend
* error 404 when no trailing slash
* highlight trashed post
* Changes author when editing a question
* Private answers are not visible to its author
* Improve editor mode toggle buttons
* Add loading animation in post/edit comment button
* Improve subscribe button
* disable view count ap_meta entry
* Add icons in stats widget
* Check comments as general user
* Show question filter tab on Category and Tag pages
* BuddyPress not working
* Short category dropdown in ask form as defined
* Rename "voted" tab to vote
* Unsolved also shows questions with selected answers
* On Single Question page , Active | Voted | Newest | Oldest should be ajax based
* Ask page title option not working
* Dont let reputation to be negative
* Improve UI of button
* Add sticky answer navigation
* Comment loading shows success
* Add answer form help content
* Improve answer form UI
* Added fullscreen toggle in Answer form
* Improve UI of question page
* Check UI of comments when comments are shown by default
* Change font weight of question and answer action links
* Improve UI of comments
* Pending comment is highlighted
* Add comment author name and time
* Commentrs are not subscribed by default
* User is not unsubscribed when his comment or answer is deleted
* Anonymous cant answer if question is created by anonymous
* Reputation showing wrong count
* white space when user don't have permission to view answers
* Improve admin answer table
* Improve admin question table
* Added image from link in question and answer
* Add image uploader in question and answer
