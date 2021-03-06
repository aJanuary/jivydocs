<?php
	$archive_root = '/var/www/ivy/';
	$cache_root = '/var/www/docs/cache/';
	$template = '/var/www/docs/template.html';

	class ModuleDescription {
		const MAX_PARTS = 3;
		const MODULE_INDEX = 1;
		private $parts;

		// Assumes the app is being hosted on the root of the domain
		public function __construct($uri) {
			if ($uri == '') {
				$this->parts = array();
			} else {
				$this->parts = array_slice(explode('/', rtrim($uri, '/')), 0, self::MAX_PARTS);
			}
		}

		public function isComplete() {
			return count($this->parts) == self::MAX_PARTS;
		}

		public function pathRelativeTo($root) {
			return $root . '/' . join('/', $this->parts);
		}

		public function javadocPathRelativeTo($root) {
			return $this->pathRelativeTo($root) . '/javadocs/' . $this->parts[self::MODULE_INDEX] . '.jar';
		}

		public function hasJavadocsFor($org_module_rev, $root) {
			$missing_parts_pattern = str_repeat('/*', self::MAX_PARTS - 1 - count($this->parts));
			$javadocs_pattern = $this->pathRelativeTo($root) . '/' . $org_module_rev . $missing_parts_pattern . '/javadocs/*.jar';
			return count(glob($javadocs_pattern)) > 0;
		}

		public function parts() {
			return $this->parts;
		}
	}

	function error404() {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
	}

	function error500() {
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	}

	function extract_javadocs($module_description, $archive_root, $cache_root) {
		$javadoc_archive = $module_description->javadocPathRelativeTo($archive_root);
		$cache_path = $module_description->pathRelativeTo($cache_root);

		$zip = new ZipArchive;
		if (!$zip->open($javadoc_archive)) {
			error404();
			return;
		}

		if (mkdir($cache_path, 0777, true)) {
			$zip->extractTo($cache_path);
			header('Location: ' . $_SERVER['REQUEST_URI']);
			$zip->close();
		} else {
			error500();
			$zip->close();
			die('unpacking archive ' . $javadoc_archive . ' to ' . $cache_path . ' failed');
		}
	}

	function render_template($template, $module_description, $org_module_revs) {
		include $template;
	}
	
	function make_index($template, $module_description, $archive_root) {
		$archive_path = $module_description->pathRelativeTo($archive_root);

		if (!is_dir($archive_path)) {
			error404();
			return;
		}

		$org_module_revs = array();
 
		foreach (scandir($archive_path) as $org_module_rev) {
			if ($module_description->hasJavadocsFor($org_module_rev, $archive_root)) {
				$org_module_revs[] = $org_module_rev;
			}
		}

		render_template($template, $module_description->parts(), $org_module_revs);
	}

	$module_description = new ModuleDescription($_GET['module_desc']);

	if ($module_description->isComplete()) {
		extract_javadocs($module_description, $archive_root, $cache_root);
	} else {
		make_index($template, $module_description, $archive_root);
	}
?>
