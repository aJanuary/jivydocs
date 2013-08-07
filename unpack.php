<?php
	$archive_root = '/var/www/ivy/';
	$cache_root = '/var/www/docs/cache/';
	
	function create_index($type, $module_description, $archive_root) {
		$archive_path = $archive_root . '/' . join('/', $module_description);

		if (!is_dir($archive_path)) {
			header("HTTP/1.0 404 Not Found");
			return;
		}

		echo '<!doctype html><head><title>Docs - ' . $type . '</title></head><body><h1>Documentations</h1><h2>' . join(' / ', $module_description) . '</h2><ul>';
 
		foreach (scandir($archive_path) as $dir) {
			$javadocs_pattern = $archive_path . '/' . $dir . str_repeat('/*', 2 - count($module_description)) . '/javadocs/*.jar';
			if (count(glob($javadocs_pattern)) > 0) {
				echo '<li><a href="' . $dir . '/">' . $dir . '</a></li>';
			}
		}		

		echo '</ul></body></html>';
	}

	// Assumes the app is being hosted on the root of the domain
	$uri_parts = explode('/', $_SERVER['REQUEST_URI']);

	if (count($uri_parts) == 2) {
		create_index('Organisations', array(), $archive_root);
		return;
	}

	$organisation = $uri_parts[1];

	if (count($uri_parts) == 3) {
		create_index('Modules', array($organisation), $archive_root);
		return;
	}

	$module = $uri_parts[2];

	if (count($uri_parts) == 4) {
		create_index('Revisions', array($organisation, $module), $archive_root);
		return;
	}

	$revision = $uri_parts[3];

	$archive_path = $organisation . '/' . $module . '/' . $revision;
	$javadoc_archive = $archive_root . '/' . $archive_path . '/javadocs/' . $module . '.jar';
	$cache_path = $cache_root . '/' . $archive_path;

	$zip = new ZipArchive;
	if ($zip->open($javadoc_archive) !== TRUE) {
		header("HTTP/1.0 404 Not Found");
		return;
	}

	if (mkdir($cache_path, 0777, true)) {
		$zip->extractTo($cache_path);
		header('Location: '.$_SERVER['REQUEST_URI']);
	} else {
		echo 'unpacking archive ' . $archive_path . ' to ' . $cache_path . ' failed';
	}
	$zip->close();
?>
