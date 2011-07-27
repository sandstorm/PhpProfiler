<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$config = array(
	'xhprofRootDirectory' => __DIR__ . '/../xhprof/',
	'xhprofOutputDirectory' => ini_get('xhprof.output_dir'),
	'xhprofBaseUri' => '/xhprof/xhprof_html/',
);
?>