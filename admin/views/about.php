<?php
/**
 * Control the output of AnsPress about page
 *
 * @link     https://anspress.net
 * @since    2.2
 * @author   Rahul Aryan <rah12@live.com>
 * @package  AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$credits = ap_sanitize_unslash( 'credits', 'r', false );

?>
<div class="wrap about-wrap">
	<h1>Welcome to AnsPress <?php echo AP_VERSION; ?></h1>
	<div class="about-text">
		Thank you for updating! AnsPress <?php echo AP_VERSION; ?> has many new features that you will enjoy. </div>
	<div class="ap-badge apicon-anspress-icon"></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab<?php echo ! $credits ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=anspress_about' ); ?>">What’s New</a>
		<a class="nav-tab <?php echo $credits ? ' nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=anspress_about&credits=true' ); ?>">Credits</a>
		<a class="nav-tab" href="https://anspress.net/questions/" target="_blank">Support</a>
	</h2>

	<?php if ( ! $credits ) : ?>
		<div class="ap-headline-feature">
			<div class="ap-headline">
				<img src="<?php echo ANSPRESS_URL; ?>/assets/images/laptop.svg" />
				<div class="no-overflow">
					<h3 class="headline-title">Performance and optimizations</h3>
					<p class="introduction">Huge improvements to performance. Optimized MySql queries, caching and prefetching.</p>
				</div>
			</div>
		</div>
		<div class="ap-features-section">
			<h3 class="headline-title">For Developers &amp; Site Builders</h3>
			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/plug.svg" />
					<h4 class="feature-title">Merged all extensions</h4>
					<p>All AnsPress extensions are merged to core for improving performance and update.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/line-chart.svg" />
					<h4 class="feature-title">Lightning Fast</h4>
					<p>We have made many improvements to reduce MySQL queries and to increase speed of page load.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/coding.svg" />
					<h4 class="feature-title">Improved UI/UX</h4>
					<p>Improved AnsPress template structure. Redesigned UI/UX of question, answer and comments.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/backbone-js.svg" />
					<h4 class="feature-title">Integarted backbone.js</h4>
					<p>Re-written whole JavaScript. Using backbone and underscore.js for dynamic layout generation.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/server.svg" />
					<h4 class="feature-title">Improved MySql tables</h4>
					<p>Removed <b>apmeta</b> table and added <b>qameta</b> table. Created individual tables for storing data types.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/cancel.svg" />
					<h4 class="feature-title">Improved uninstaller</h4>
					<p>Fixed uninstall bug. Added controls to remove all data of AnsPress from database.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/php.svg" />
					<h4 class="feature-title">PHP7 Tested</h4>
					<p>AnsPress is well tested with PHP 7. It runs very fast comparing to previous versions of PHP.</p>
				</div>
			</div>

			<div class="ap-feature">
				<div class="no-overflow">
					<img src="<?php echo ANSPRESS_URL; ?>/assets/images/user.svg" />
					<h4 class="feature-title">BuddyPress as default profile</h4>
					<p>Default AnsPress profile is set to BuddyPress. It is widely used, well tested and extensible.</p>
				</div>
			</div>

			<div class="clear"></div>
		</div>
		<div class="ap-changelog-section">
			<h3 class="changelog-title">And so much more!</h3>
			<p class="ap-changelog-url"><a href="https://anspress.net/releases/version-4.0.0-beta.1/">Changelog for AnsPress <?php echo AP_VERSION; ?>.</a></p>
		</div>
		<div class="ap-assets">
			<span class="flaticon-cp">All colored icons used in this page were taken from flaticon.com</span>
		</div>
	<?php else : ?>
		<p class="about-description">AnsPress exists because of friendly folks like these.</p>

		<h3 class="wp-people-group">AnsPress Team</h3>
		<ul class="wp-people-group " id="wp-people-group-project-leaders">
			<li class="wp-person" id="wp-person-nerdaryan">
				<a class="web" href="https://profiles.wordpress.org/nerdaryan"><img alt="" class="gravatar" src="//www.gravatar.com/avatar/0c8cfd3bc56d97fe6bebc035fe9b8c80?s=60">Rahul Aryan</a>
				<span class="title">Lead Developer</span>
			</li>
			<li class="wp-person" id="wp-person-campdoug">
				<a class="web" href="https://campdoug.com/"><img alt="" class="gravatar" src="//anspress.net/wp-content/themes/site/images/contributors/barry.jpg">Barry Sevig</a>
				<span class="title">Supporter &amp; Developer</span>
			</li>
			<li class="wp-person" id="wp-person-ravi">
				<a class="web" href="#"><img alt="" class="gravatar" src="//anspress.net/wp-content/themes/site/images/contributors/ravi_kumar.jpg">Ravi Kumar</a>
				<span class="title">Core Developer</span>
			</li>
			<li class="wp-person" id="wp-person-geetikaaryan">
				<a class="web" href="https://anspress.net/"><img alt="" class="gravatar" src="//www.gravatar.com/avatar/ef5b41129a9a4c2573ef5daf05f03eb7?s=60">Geetika Aryan</a>
				<span class="title">Core Developer</span>
			</li>
		</ul>

		<h3 class="wp-people-group">Contributors</h3>
		<ul class="wp-people-group " id="wp-people-group-project-leaders">
			<li class="wp-person">
				<a class="web" href="https://profiles.wordpress.org/priard"><img alt="" class="gravatar" src="//www.gravatar.com/avatar/703e4fed411643c389342ec39c385e7d?s=60">Lukasz Ladecki</a>
				<span class="title">Translations &amp; Core</span>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//www.gravatar.com/avatar/c56c012899e6de744ce6b5dc391ffe4e?s=60">Costas Zividis</a>
				<span class="title">Support</span>
			</li>
			<li class="wp-person">
				<a class="web" href="https://profiles.wordpress.org/fred-flq"><img alt="" class="gravatar" src="//www.gravatar.com/avatar/989cbf51eaf91893886a44bfb7769dd2?s=60">Fred - FLQ</a>
				<span class="title">Support &amp; Translations</span>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//anspress.net/wp-content/themes/site/images/contributors/chad.jpg">Chad Fullerton</a>
				<span class="title">Support &amp; UI</span>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/576623?v=3">Rami Yushuvaev</a>
				<span class="title">Support &amp; UI</span>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/5292321?v=3">Alex Alexandru</a>
				<span class="title">Support &amp; Core</span>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/1368405?v=3">Florian Gareis</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/8053423?v=3">Shad Gagnon</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/147401?v=3">Milad Nekofar</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/1370909?v=3">Dima Stefantsov</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/5995181?v=3">Mert S. Kaplan</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/1868702?v=3">Peter</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/12552467?v=3">Yuji Nagaoka</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/388584?v=3">Zhandos Ulan</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/1431189?v=3">Benjamin</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/3998868?v=3">Davi Menezes</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/2650828?v=3">Kevin Fodness</a>
			</li>
			<li class="wp-person">
				<a class="web" href="#"><img alt="" class="gravatar" src="//avatars.githubusercontent.com/u/717939?v=3">Miguel Sirvent</a>
			</li>
		</ul>
	<?php endif; ?>
</div>
