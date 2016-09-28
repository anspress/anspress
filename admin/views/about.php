<?php
/**
 * Control the output of AnsPress about page
 *
 * @link https://anspress.io
 * @since 2.2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

?>
<div id="anspress" class="wrap ap-about">
	<h2>
		<i class="apicon-anspress"></i>
		<span><?php echo AP_VERSION; ?></span>		
	</h2>
	<div class="ap-about-social">
		<a href="https://anspress.io" target="_blank">anspress.io</a>
		<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
        <a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
        <a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
        <a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
		<img src="<?php echo ANSPRESS_URL.'assets/images/question.png'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>Question &amp; Answer</h3>
			<strong>Voting</strong>
			<p>Let users vote on questions to express their thought for its relevance.</p>

			<strong>Featured Questions</strong>
			<p>Make questions featured so they stick to the top of list.</p>

			<strong>Comments</strong>
			<p>Let users discuss questions and answers by commenting on them. Users can comment on both questions and answers.</p>

			<strong>Moderate and Flag</strong>
			<p>Allow the community to report inappropriate or spam posts.</p>

			<strong>Private Question &amp; Answer</strong>
			<p>Create private questions which will be hidden from public and only selected users can view its content.</p>

			<strong>Activity &amp; History</strong>
			<p>Shows recent activity. All question and answer is shown to subscribers.</p>
		</div>

	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
			<img src="<?php echo ANSPRESS_URL.'assets/images/filter.gif'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>Sorting &amp; Filters</h3>
			<strong>Advance sorting</strong>
			<p>Sort question and answers by many advance filters.</p>
		</div>

	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
			<img src="<?php echo ANSPRESS_URL.'assets/images/user-profile.png'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>User Profile</h3>
			
			<strong>Creative and Informative</strong>
			<p>Modern design with cover and user avatar. Shows all stats of users along with activities. Easily customizable.</p>

			<strong>Edit profile</strong>
			<p>Allow users to edit their own profile. Custom fields can be added using hooks.</p>

			<strong>Followers</strong>
			<p>Users can follow other users to get their activity updates.</p>

			<strong>Notification Page</strong>
			<p>Shows all notifications, mentions and other activities of user.</p>

			<strong>Hide Profile</strong>
			<p>AnsPress allows an option for user to hide themselves from the public.</p>

			<strong>3rd Party Profile</strong>
			<p>Its not required to user AnsPress profile system. Admin can use other 3rd Party profile plugins.</p>
		</div>

	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
			<img src="<?php echo ANSPRESS_URL.'assets/images/notifications.png'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>Notification</h3>
			<p>AnsPress notifications will notify users for various activity happening within AnsPress. Notification system is not just limited to AnsPress, it can be easily extended to show new types of activities.</p>
			
			<strong>Mention</strong>
			<p>Users get notified when otheres mention them in posts or comments.</p>

			<strong>Comment</strong>
			<p>Users get notified when there is a comment on their posts.</p>

			<strong>Reputation</strong>
			<p>Users get notified when they gain a reputation.</p>

			<strong>New Answer</strong>
			<p>Notify users when their question get an answer.</p>

			<strong>Best Answer</strong>
			<p>Users get notified when their answer is selected as the best.</p>

			<strong>Follower</strong>
			<p>Notify users if anybody starts following them.</p>
		</div>

	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
			<img src="<?php echo ANSPRESS_URL.'assets/images/mention.gif'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>Mention</h3>
			<p>Mention user in question, answer and comments. Mentioned user will be get notified. Suggest user name while typing.</p>
		</div>

	</div>

	<div class="ap-about-block clearfix">
		<div class="ap-about-img">
			<img src="<?php echo ANSPRESS_URL.'assets/images/roles.png'; ?>" />
		</div>
		<div class="ap-about-feats">
			<h3>User Role</h3>
			<p>AnsPress roles and capabilities gives you full control over user access.</p>

			<strong>Customizable caps</strong>
			<p>Customize capabilities of AnsPress for any roles.</p>
		</div>

	</div>


</div>