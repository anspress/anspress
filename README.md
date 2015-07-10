# AnsPress - Question and answer #
**Contributors:** nerdaryan  
**Donate link:** https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development  
**Tags:** question, answer, q&a, forum, profile, stackoverflow, quora, buddypress  
**Requires at least:** 4.1.1  
**Tested up to:** 4.2  
**Stable tag:** 2.3.2  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Free question and answer plugin for WordPress. Made with developers in mind, highly customizable.

## Description ##
**Demo & support forum:** http://anspress.io/  
**GitHub repo:** [Git AnsPress](https://github.com/anspress/anspress/)  

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
**  * User level :** Participant, Editor, Moderator  
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
![Question list page.](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-1.png)


### 2. Single question page. ###
![Single question page.](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-2.png)


### 3. User profile ###
![User profile](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-3.png)


### 4. User hover card ###
![User hover card](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-4.png)


### 5. Users directory ###
![Users directory](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-5.png)


### 6. Notifications ###
![Notifications](http://s.wordpress.org/extend/plugins/anspress---question-and-answer/screenshot-6.png)




## Changelog ##

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

### 2.1.6 ###

* [option] added option for toggling question sidebar
* [fix] Search widget and form
* [fix] Check if array
* [fix] anspress trash action hooks
* [fix] Add translation support for "ago"-string #283
* [fix] User can't edit his own answer
* [fix] Some documentation error


### 2.1.5 ###

* [fix] minor bugs
* [fix] Check undefined in ap_get_avatar_scr()
* [new] Added avatar upload
* [fix] ask and answer form button on update
* [fix] Hide close test in editor modal
* [fix] added blockquote style in editor
* [fix] Dropdown not work for ajax content

### 2.1.4 ###

* [fix] allowed style align property
* [fix] allowed strikethorugh (del) tag for editor
* [fix] Allowed ul, ol, li tags in editor
* [new] Added filter for ask and answer form editors
* [fix] "status updated" message when post status get updated
* [fix] Answers shorting
* [fix] sanitize_text_field breaks loading question with non-ansi symbols in url #281

### 2.1.1 ###

* Tested with WP 4.2
* [option] Toggle question permalink

### 2.1.1 ###

* Minor issues fixed

### 2.1 ###

* [new]adding user profile fields
* [theme] improved button
* [fix] Questions widget #279
* [fix] let admin answer even if question is closed
* [fix] Answer permalink #279
* [fix] Possible XSS vulnerability
* [theme] added default widget in question sidebar
* [fix] dropdown #279
* [new] subscribe on own question
* [fix] User page not found if space in user_name #274
* [theme] Answer form
* [fix] Answers pagination
* [fix] chnage_status to change_status #278
* [fix] Answers loop and count
* [new] Ajaxified change status
* [new] Change post status
* [theme] added post status sticker
* [fix] Query "private_post" and "moderate" only if user have permission
* Padding for no-questions element
* [theme] Question list meta improved
* [theme] Improved single question page layout
* [theme] question page layout
* [fix] Answer loop
* [added] search form before questions list
* Updated question permalink, removed base page
* Replaced .anspress-container to #anspress
* [fix] Bugs
* [removed] Unused file responce_message.php
* [fix] 12 major bugs
* [fix] Removed var dump
* [fix] Major bugs
* [fix] Question and answer moderation
* [new] user question and answer page
* User posts styled
* [new] User posts page added
* Improved question query and loop
* [fix] Shows login message when user dont have permission to ask or answer
* [added] user page, initial.min.js
* Improved user menu
* [added] option for toggle user directory page
* user link improved
* [fix] Sorting user by reputation
* [fix] page titles
* [added] styles for users loop
* [added] option for "users per page"
* [added] user page and loop
* [added] Option for toggling reputations
* [fix] 404 causing infinite loop
* Added ap_ prefix


### 2.0.5 ###

* [fix] Removed anonymous function for older version compatibility
* Improved user answers list
* [Added] do_action( 'wordpress_social_login' );
* Removed history loading after plugin initiate
* [fix] History
* [disabled] ap_post_votes() for each post to decrease DB query
* Added trailing slash in question url to prevent 301 redirect
* [fix] Related questions was missing
* Order reputation by date



### 2.0.4 ###

* [fix] AnsPress options
* [fix] hide any existing loding animation
* [fix] Wrong redirect after deleting question
* [added] ap_get_all_reputation
* Updated admin menu position
* Loading animation for select button
* [added] loading animation and improved notification


### 2.0.3 ###

* Check empty comment before posting
* refresh question suggestion on backspace
* Include previous version point count
* Remove unused options


### 2.0.2 ###

* Rename old tags
* Added category widget
* Added widget position in base page
* fix button sizes
* Added two options; - Disable OP to answer their question (not applied for admin) - Disallow multiple answer per question 9not applied for admin) [fix] - Show wrong message when user already answered
* fixed voting button on answer
* Added requirement checker
* [fix] undefined index
* Removed unused functions
* Load buddypress file on bp_loaded
* Added notification for answers and comments
* fixed some major bugs
* added doc comments for anspress_sc
* Added parameters for [anspress] shortcode
* [fix] sorting on home page
* [fix] run flush_rules only ap_opt('ap_flush') == 'true'
* Best practice issues
* Don't let reputation to negative
* flush apmeta when related post gets deleted
* [fix] delete flag meta on post trash
* fixed bug in reputation.php
* Improved question page style
* Improved list style
* added reputation page in buddypress
* Disable reputation for anonymous
* Check metions in answers
* Check mention in questions
* Added reputation and fixed meta delete
* replace action link
* [new] add question and answer to buddypress activity stream
* [fix] pagination in buddypress
* Added answers in buddypress
* Added questions tab in buddypress
* Detect buddypress and change user link to BP profile
* removed .updated class
* Added captcha in Answer form
* Added reCaptcha
* Styled editor
* Added questions list widget
* hook option on admin_init
* #262 Disable quicktags in questions
* [fix] related questions widget
* Improved subscribe button
* Exclude best answer from main Answers_Query
* [fix] Answer sorting
* Added author credit
* Clear editor after posting answer
* Added option to disable voting
* Update comment count on new comment
* Auto update comment count
* Added publish class in time
* Fix answer count in answers list
* [fix] ajax loading comments in answers
* [fix] ajax loading after editing comment
* Posts actions as dropdown menu
* Checkbox inline description
* [fix] allow anonymous for answer
* Removed editor toggle and moved media button
* Added option showing title in question page
* Fixed ajax loading while answer is first.
* Added .published class in time
* Fixed user link
* Lost MEDIA library option from admins menu
* Updated style
* declare the visibility for methods
* Added uninstall hook
* Removed unused files
* [fix] questions page paging rewrite
* Missing argument 2 for AnsPress_Theme::the_title()
* Improved admin style and added option for locking delete action
* Show CPT menu for delete_pages
* Show AnsPress menu if user have manage_options
* Load shortcode on init
* Held for moderation
* Fixed some bugs and added grunt
* [fix] Subscribe button
* Fixed conflicts with profile
* [fix] use question title in answer title
* Made it compatible with profile plugin
* [fix] Rewrite rule when base page is child page
* Disable comments option added
* [fix] Question detail is appearing for all posts
* [fix] Search page
* Added anspress menu
* [fix] form get cleared even if there is error
* [fix] Toggle private post
* [fix] Language files were not loading
* Added option for switching text editor as default
* [fix] TwentyFifteen compatibility
* Show this label "No questions match your search criteria" when nothing found in search
* Improved question list
* Improved dashboard
* Added question select
* Improved flagging system
* Disallow answer for closed question
* Add private question & moderate sticker in question
* Enable question sidebar only if there is widget in it
* Flag button fixed
* Hide comments be default
* Added related questions widget
* Added question stats widget
* Added participants and subscribe widget
* Improved question and list page style
* Added action and filter for search
* Added search functionality
* Removed user.php and added required functions to functions.php
* Enable user link only if user extension installed
* Added extensions listing
* [fix] replaced icon- class to apicon-
* Check array before output menu
* [fix] Editing own comment only possible after refresh
* [removed] ap-site.js
* [new] Dynamic option group and fields
* Removed main.php
* Removed messages.php
* [fix] Participants caps were empty
* [fix] After deleting a question, troublesome redirection
* Improved form validation
* Added vote and view meta in list
* [fix] show register or login message for non-logged in
* [fix] Annonymous is unchecked in the backend but user can still ask question
* [fix] link of ask question button when there is no question
* [fixed] Answer permalink
* [fixed] Participant is not being inserted to DB
* [fixed] After comment actions are not working
* [fixed] Entering space in ask question title field show all questions
* [fixed] User page ap_meta_array_map function missing
* [fix]When AnsPress is set to home page then sorting does not work
* Rename sort query arg to ap_sort
* [fixed] unsolved question listing
* [fixed] Answer count update on ajax request
* [fixed] Wrong answer count
* [fixed] delete button
* [fixed] Select best answer
* [fixed] JS and ajax request
* [fixed] Answer form validation
* [fixed] Ajax answer submission
* [removed] Installation screen and cleaned up codes.

### 1.4.3 ###

* Made compatible with WordPress 4.1.1
