<?php defined('ABSPATH') or exit; ?>

<div class="wrap">

	<?php advset_powered(); ?>

	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('Advanced Settings &rsaquo; Styles'); ?></h2>

	<?php if ($notice = get_option('advset_notice')) { ?>
		<div class="notice notice-<?php echo $notice['class'] ?> is-dismissible">
			<p><b><?php echo $notice['size'] ?> <?php _e( $notice['text'] ); ?></b><?php echo $notice['files'] ?></p>
		</div>
	<?php delete_option('advset_notice') ?>
	<?php } ?>

	<form action="options.php" method="post">

		<input type="hidden" name="advset_group" value="styles" />

		<?php settings_fields( 'advanced-settings' ); ?>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e('Track'); ?></th>
				<td>
					<p>
						<label for="track_enqueued_styles">
							<input name="track_enqueued_styles" type="checkbox" id="track_enqueued_styles" value="1" <?php advset_check_if('track_enqueued_styles') ?> />
							<?php _e('Track enqueued styles') ?>
							</label>
					</p>
					<p>
						<label for="track_merge_removed_styles">
							<input name="track_merge_removed_styles" type="checkbox" id="track_merge_removed_styles" value="1" <?php advset_check_if('track_merge_removed_styles') ?> />
							<?php _e('Merge and include removed styles') ?>
							</label>
					</p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e('Tracked Styles <br /> <i style="color:#999">Check to remove styles</i>'); ?></th>
				<td>
					<?php $tracked = get_option('advset_tracked_styles');
					if ($tracked) {
						echo '<fieldset>';
						foreach ($tracked as $script) {
							// print_r($script);
							if (!$script->ver) {
								$script->ver = '0';
							}

							$check_name = 'remove_enqueued_style_'.$script->handle;
							$cheked = advset_check_if($check_name, false);

							$src = (strpos($script->src, '/')===0? get_site_url() : '') . $script->src;

							$css = file_get_contents($src);
							$urlTest = strpos($css, 'url(')>-1;

							echo "<label style='width:100%; display:inline-block;' for='$check_name'> <input id='$check_name' name='$check_name' type='checkbox' style='float:left; margin-top:0' value='$script->handle' $cheked /> ";
							echo "<div style='overflow:auto'><b>$script->handle</b> ($script->ver)";
							if ($src) {
								echo "<br /><small>$src</small>";
							}
							if ($script->deps) {
								echo '<br /> <small style="color:#888">dependency: '.implode(', ', $script->deps).'</small>';
							}
							if ($urlTest) {
								$urls = preg_match_all('/url\([^\)]+\)/', $css, $matches);
								echo '<br /> <small style="color:red">Image URL replaces:</small>';
								foreach ($matches[0] as $match) {
									if (!preg_match('/url\([^a-z]*(http|data)/i', $match)) {
										$newUrl = preg_replace("/(url\(['\"]*)/", "$0".dirname($src).'/', $match);
									}
									else {
										$newUrl = $match;
									}
									echo '<br /> <small> <small> &nbsp; &nbsp; &bull; '.$match.' &rsaquo; '.$newUrl.'</small></small>';
								}
							}
							echo '</div></label>';
						}
						echo '</fieldset>';
					}
					else {
						echo '<i>No tracked styles yet. Try browsing your website.</i>';
					} ?>
				</td>
			</tr>

		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save changes') ?>"></p>
	</form>
</div>
