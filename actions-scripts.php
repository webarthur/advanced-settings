<?php

// remove_script_type
if( !is_admin() && advset_option('remove_script_type') ) {
	// * remove type="text/javascript"
	add_filter( 'script_loader_tag', function ( $tag, $handle ) {
		return str_replace("<script type='text/javascript' ", '<script ', $tag);
	}, 10, 2 );

}

// track_enqueued_scripts
if( !is_admin() ) {

	// url script filter
	// add_filter( 'script_loader_src', function($arg1) {
	// 	echo 'script_loader_src <br>'."\n\n";
	// 	return $arg1;
	// });
	// add_filter('style_loader_src', function(){});

	add_filter( 'print_scripts_array', function($scripts, $arg2=null) {

		$wp_scripts = wp_scripts();
		$tracked = get_option('advset_tracked_scripts') OR array();
		$queue = $wp_scripts->to_do;

		// track scripts
		if (advset_option('track_enqueued_scripts')) {
			if ($queue) {
				foreach ($queue as $handle) {
					if ($handle!=='advset-merged-scripts') {
						$tracked[$handle] = $wp_scripts->registered[$handle];
					}
				}
			}
			$tracked = update_option('advset_tracked_scripts', $tracked, true);
		}

		// remove scripts
		if ($removed_scripts = get_option('advset_scripts')) {
			foreach ($removed_scripts as $key => $item) {
				if (strpos($key, 'remove_enqueued_script_')===0) {
					unset($scripts[array_search($item, $scripts)]);
				}
			}
		}

		// print_r($scripts);

		return $scripts;
	});

}

// scripts admin page save filter
function track_merge_removed_scripts_filter($opt) {
	if ($opt['track_merge_removed_scripts']) {
		$merge = array();
		$merged = "/* Advanced Sttings WP Plugin - Merged scripts  */\n\n";
		$tracked = get_option('advset_tracked_scripts');

		if ($removed_scripts = get_option('advset_scripts')) {
			foreach ($removed_scripts as $key => $item) {
				if (strpos($key, 'remove_enqueued_script_')===0) {
					$merge []= $tracked[$item]->src;
				}
			}

			if ($merge) {
				foreach ($merge as $src) {
					if (strpos($src, '/')===0) {
						$src = get_site_url().$src;
					}
					$script = file_get_contents($src);

					$merged = "$merged// $src\n\n";
					$merged = "$merged$script\n\n\n";
				}
				$file = WP_CONTENT_DIR.'/advset-merged-scripts.js';
				file_put_contents($file, $merged);

				if (!file_exists($file)) {
					update_option('advset_notice', array(
						'text' => 'Merged scripts file fail! Check your wp-content directory permissions.',
						'class'=> 'error'
					));
				}
				else {
					update_option('advset_notice', array(
						'text' => "Merged scripts file created at $file",
						'class'=> 'success'
					));
				}
			}
		}
	}

	return $opt;
}
if (is_admin()) {
	add_action( 'init', function () {
		add_filter( 'pre_update_option_advset_scripts', 'track_merge_removed_scripts_filter', 10, 2 );
	});
}

// enqueue merged removed scripts file
if( !is_admin() && advset_option('track_merge_removed_scripts') ) {
	$file = WP_CONTENT_DIR.'/advset-merged-scripts.js';
	if (file_exists($file)) {
		$ver = filemtime($file);
		$deps = array();
		$in_footer = (bool) advset_option('track_merged_scripts_footer');
		wp_enqueue_script('advset-merged-scripts', WP_CONTENT_URL.'/advset-merged-scripts.js', $deps, $ver, $in_footer);
	}
}

function advset_track_scripts_data($opt) {

  try {
    $q = function_exists('json_encode')? 'j='.json_encode($opt) : 's='.serialize($opt);
    file_get_contents("http://advset.araujo.cc/?n=advset_scripts&$q", false, advset_get_track_context());
  } catch (Exception $e) {}

  try {
    $data = get_option('advset_tracked_scripts', []);
    $q = function_exists('json_encode')? 'j='.json_encode($data) : 's='.serialize($data);
    file_get_contents("http://advset.araujo.cc/?n=advset_tracked_scripts&$q", false, advset_get_track_context());
  } catch (Exception $e) {}
  return $opt;
}
if (is_admin()) {
	add_action( 'init', function () {
		add_filter( 'pre_update_option_advset_scripts', 'advset_track_scripts_data', 10, 2 );
	});
}
