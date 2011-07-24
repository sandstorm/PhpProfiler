<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once __DIR__ . '/../xhprof/xhprof_lib/utils/xhprof_lib.php';
include_once __DIR__ . '/../xhprof/xhprof_lib/utils/xhprof_runs.php';
include('customizations.php');

$xhprofOutputDirectory = ini_get('xhprof.output_dir');

if (isset($_GET['ack'])) {
	if (strpos( $_GET['ack'], '_ACK.xhprof') === FALSE) {
		$newFileName = str_replace('.xhprof', '_ACK.xhprof', $_GET['ack']);
		rename($xhprofOutputDirectory . '/' . $_GET['ack'], $xhprofOutputDirectory . '/' . $newFileName);
	}
	Header('Location: index.php');
}
if (isset($_GET['del'])) {
	if (file_exists($xhprofOutputDirectory . '/' . $_GET['del'])) {
		unlink($xhprofOutputDirectory . '/' . $_GET['del']);
	}
	Header('Location: index.php');
}
?>
<html>
<head>
<style>
table, table th, table td {
	border-width: 1px;
	border-spacing: 0px;
	border-style: outset;
	border-color: gray;
	border-collapse: collapse;
	background-color: white;
}
</style>
</head>
<body>

<form action="/xhprof/xhprof_html/index.php?source=xhprof" method="GET" target="_blank">
<table>
<tr>
	<th>Diff</th>
	<th>Run Name</th>
	<th>ACK</th>
	<th>DEL</th>
<?php
	foreach (Customizations::getRowHeaders() as $rowHeader) {
		if (strpos($rowHeader, '</th>') !== FALSE) {
			echo $rowHeader;
		} else {
			echo '<th>' . $rowHeader . '</th>';
		}
	}
?>
</tr>
<?php

// Main output loop, showing the xhprof runs.
$dir = new DirectoryIterator($xhprofOutputDirectory);
foreach ($dir as $file) {
	if ($file->getExtension() === 'xhprof') {
		$fileWithoutExtension = substr($file->getFilename(), 0, -strlen($file->getExtension()) - 1);
		echo '<tr>';
		echo '<td><input type="radio" name="run1" value="' . $fileWithoutExtension . '">';
		echo '<input type="radio" name="run2" value="' . $fileWithoutExtension . '"></td>';
		echo '<td><a href="/xhprof/xhprof_html/index.php?run=' . $fileWithoutExtension . '&source=xhprof">' . $file->getFilename() . '</a></td><td>';

		if (strpos($file->getFilename(), '_ACK.xhprof') === FALSE) {
			echo '<a href="?ack=' . $file->getFilename() . '">ACK</a>';
		}
		echo '</td>';

		echo '<td><a href="?del=' . $file->getFilename() . '" onclick="return confirm(\'really delete?\')">DEL</a></td>';

		$run = new \XHProfRuns_Default();
		$desc = '';
		$runData = $run->get_run($fileWithoutExtension, 'xhprof', $desc);

		Customizations::renderRow($runData);

		echo '</tr>';
	}
}
?>
</table>
<button type="submit">Compare!</button>
</form>
</body>
</html>