<?php
require_once(__DIR__ . '/admin.php');

// @todo still show experimental modules that ARE installed now
// @todo show installed modules first

// temporary switch to make it easy to see experimental modules
$show_experimental = true;

$module_categories = array(
	'auth' => array(
		'title' => 'Authentication modules'
	),
	'email' => array(
		'title' => 'Email module'
	),
	'payment' => array(
		'title' => 'Payment engines'
	)
);

$builtin_modules = array(
	'facebook' => array(
		'class' => 'FacebookAuthenticationModule',
		'category_slug' => 'auth'
	),
	'email' => array(
		'class' => 'EmailAuthenticationModule',
		'experimental' => true,
		'category_slug' => 'auth'
	),
	'etsy' => array(
		'class' => 'EtsyAuthenticationModule',
		'category_slug' => 'auth'
	),
	'google_oauth' => array(
		'class' => 'GoogleOAuthAuthenticationModule',
		'category_slug' => 'auth'
	),
	'linkedin' => array(
		'class' => 'LinkedInAuthenticationModule',
		'category_slug' => 'auth'
	),
	'mailchimp' => array(
		'class' => 'MailChimpModule',
		'experimental' => true,
		'category_slug' => 'email'
	),
	'manual' => array(
		'class' => 'ManualPaymentEngine',
		'experimental' => true,
		'category_slug' => 'payment'
	),
	'meetup' => array(
		'class' => 'MeetupAuthenticationModule',
		'category_slug' => 'auth'
	),
	'ohloh' => array(
		'class' => 'OhlohAuthenticationModule',
		'category_slug' => 'auth'
	),
	'statusnet' => array(
		'class' => 'StatusNetAuthenticationModule',
		'category_slug' => 'auth'
	),
	'stripe' => array(
		'class' => 'StripePaymentEngine',
		'experimental' => true,
		'category_slug' => 'payment'
	),
	'twitter' => array(
		'class' => 'TwitterAuthenticationModule',
		'category_slug' => 'auth'
	),
	'usernamepass' => array(
		'class' => 'UsernamePasswordAuthenticationModule',
		'category_slug' => 'auth'
	),
);

$ADMIN_SECTION = 'modules';
require_once(__DIR__ . '/header.php');
?>
<div class="span9">
	<?php
	foreach ($module_categories as $category_slug => $module_category) {
		$category_modules = array();
		foreach ($builtin_modules as $module_slug => $module) {
			if ($module['category_slug'] == $category_slug
					&& ($show_experimental
					|| !array_key_exists('experimental', $module)
					|| !$module['experimental'])
			) {
				$category_modules[$module_slug] = $module;
			}
		}

		/*
		 * Don't show empty categories
		 */
		if (count($category_modules) == 0) {
			continue;
		}
		?>
		<h2><?php echo $module_category['title'] ?></h2>
		<div class="modules">
			<?php
			foreach ($category_modules as $module_slug => $module) {
				?>
				<?php
				UserConfig::loadModule($module_slug);

				$instances = array();
				foreach (UserConfig::$all_modules as $installed_module) {
					// checking if this built-in module was ever instantiated
					if (get_class($installed_module) == $module['class']) {
						$instances[] = $installed_module;
					}
				}

				$is_experimental = array_key_exists('experimental', $module) && $module['experimental'];

				if (count($instances) > 0) {
					// going through module objects
					foreach ($instances as $module) {
						?>
						<div class="well well-small startupapi-module">
							<?php
							$logo = $module->getLogo(100);

							if (!is_null($logo)) {
								?>
								<img src="<?php echo UserTools::escape($logo); ?>" width="100" height="100" class="pull-right"/>
								<?php
							}
							?>
							<p class="startupapi-module-title"><?php echo $module->getTitle() ?></p>

							<p>
								<span class="label label-success"><i class="icon-ok icon-white"></i>
									Installed
								</span>

								<?php
								if ($is_experimental) {
									?>
									<span class="label label-warning"><i class="icon-exclamation-sign icon-white"></i>
										Experimental
									</span>
									<?php
								}
								?>
							</p>

							<p><?php echo $module->getDescription() ?></p>
						</div>

						<?php
					}
				} else {
					?>
					<div class="well well-small startupapi-module startupapi-module-not-installed">
						<?php
						$class = $module['class'];

						// getting info from the class
						$logo = $class::getModulesLogo(100);
						if (!is_null($logo)) {
							?>
							<img src="<?php echo UserTools::escape($logo); ?>" class="pull-right startupapi-module-logo"/>
							<?php
						}
						?>
						<p class="startupapi-module-title"><?php echo UserTools::escape($class::getModulesTitle()) ?></p>

						<p>
							<span class="label">
								<i class="icon-minus icon-white"></i>
								Not Installed
							</span>

							<?php
							if ($is_experimental) {
								?>
								<span class="label label-warning"><i class="icon-exclamation-sign icon-white"></i>
									Experimental
								</span>
								<?php
							}
							?>
						</p>

						<p><?php echo $class::getModulesDescription() ?></p>
						<?php
						$url = $class::getSignupURL();
						if (!is_null($url)) {
							?>
							<p>
								<i class="icon-home"></i>
								Sign up:
								<a href="<?php echo UserTools::escape($url) ?>" target="_blank">
									<?php echo UserTools::escape($url) ?>
								</a>
							</p>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
</div>
<script src="<?php echo UserConfig::$USERSROOTURL ?>/masonry/jquery.masonry.min.js"></script>
<script>
	$('.modules').masonry({
		columnWidth: function( containerWidth ) {
			if (containerWidth > 800) {
				return containerWidth / 2;
			} else {
				return containerWidth;
			}
		}
	});
</script>
<?php
require_once(__DIR__ . '/footer.php');