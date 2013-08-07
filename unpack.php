<?php
	$archive_root = '/var/www/ivy/';
	$cache_root = '/var/www/docs/cache/';

	class ModuleDescription {
		public $organisation, $module, $revision, $array, $isComplete = false;

		public function __construct($uri) {
			// Assumes the app is being hosted on the root of the domain
			$uri_parts = explode('/', $uri);
			
			if (count($uri_parts) >= 2) {
				$organisation = $uri_parts[1];
				$array[] = $organisation;
			}

			if (count($uri_parts) >= 3) {
				$module = $uri_parts[2];
				$array[] = $module;
			}

			if (count($uri_parts) >= 4) {
				$revision = $uri_parts[3];
				$array[] = $revision;
				$is_complete = true;
			}
		}
	}
	
	function make_index($module_description, $archive_root) {
		$archive_path = $archive_root . '/' . join('/', $module_description->array);

		if (!is_dir($archive_path)) {
			header("HTTP/1.0 404 Not Found");
			return;
		}

		echo '<!doctype html><head><title>JivyDocs</title></head><body><h1>Documentations</h1><h2>' . join(' / ', $module_description->array) . '</h2><ul>';
 
		foreach (scandir($archive_path) as $dir) {
			$javadocs_pattern = $archive_path . '/' . $dir . str_repeat('/*', 2 - count($module_description->array)) . '/javadocs/*.jar';
			if (count(glob($javadocs_pattern)) > 0) {
				echo '<li><a href="' . $dir . '/">' . $dir . '</a></li>';
			}
		}		

		echo '</ul></body></html>';
	}

	function extract_javadocs($module_description, $archive_root, $cache_root) {
		$archive_path = join('/', $module_description->array);
		$javadoc_archive = $archive_root . '/' . $archive_path . '/javadocs/' . $module_description->module . '.jar';
		$cache_path = $cache_root . '/' . $archive_path;

		$zip = new ZipArchive;
		if ($zip->open($javadoc_archive) !== TRUE) {
			header("HTTP/1.0 404 Not Found");
			return;
		}

		if (mkdir($cache_path, 0777, true)) {
			$zip->extractTo($cache_path);
			header('Location: ' . $_SERVER['REQUEST_URI']);
		} else {
			echo 'unpacking archive ' . $archive_path . ' to ' . $cache_path . ' failed';
		}
		$zip->close();
	}

	$module_description = new ModuleDescription($_SERVER['REQUEST_URI']);

	if (!$module_description->isComplete) {
		make_index($module_description, $archive_root);
	} else {
		extract_javadocs($module_description, $archive_root, $cache_root);
	}
?>
