<?php
require_once 'config.php';

include_once $config['xhprofRootDirectory'] . 'xhprof_lib/utils/xhprof_lib.php';
include_once $config['xhprofRootDirectory'] . 'xhprof_lib/utils/xhprof_runs.php';
include('customizations.php');

$xhprofOutputDirectory = $config['xhprofOutputDirectory'];

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
if (isset($_GET['deleteAllUnAcked'])) {
	// Main output loop, showing the xhprof runs.
	$dir = new DirectoryIterator($xhprofOutputDirectory);
	foreach ($dir as $file) {
		if (substr($file->getFilename(), -7) === '.xhprof' && strpos($file->getFilename(), 'ACK.xhprof') === FALSE) {
			unlink($file->getPathName());
		}
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
	padding:2px;
}
body, table {
	font-family: sans-serif;
}
td {
	cursor: pointer;
}
table.highlightmode tr td {
	opacity: .5;
	filter: alpha(opacity=50);
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=50)";
}
table.highlightmode tr.highlight td, table.highlightmode td.highlight {
	opacity: 1;
	filter: alpha(opacity=100);
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=100)";
}
<?php

Customizations::outputCss();

?>
</style>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="main.js"></script>
</head>
<body>

<form action="" method="GET">
	<input type="text" name="filter" placeholder="Filter" value="<?php echo $_GET['filter'] ?>" /><button type="submit">Go!</button>
</form>
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
	if (substr($file->getFilename(), -7) !== '.xhprof') {
		continue;
	}
	$fileWithoutExtension = $file->getBasename('.xhprof');

	$run = new \XHProfRuns_Default($xhprofOutputDirectory);
	$desc = '';
	$runData = $run->get_run($fileWithoutExtension, 'xhprof', $desc);
	Customizations::setCurrentRunData($runData);

	echo Customizations::renderTr($file);
	echo '<td><input type="radio" name="run1" value="' . $fileWithoutExtension . '">';
	echo '<input type="radio" name="run2" value="' . $fileWithoutExtension . '"></td>';
	echo '<td><a href="index.php?run=' . $fileWithoutExtension . '&source=xhprof">' . $file->getFilename() . '</a></td><td>';

	$onclickJs = '';
	if (strpos($file->getFilename(), '_ACK.xhprof') === FALSE) {
		echo '<a href="?ack=' . $file->getFilename() . '">ACK</a>';
	} else {
		$onclickJs = 'onclick="return confirm(\'really delete?\')"';
	}
	echo '</td>';


	echo '<td><a href="?del=' . $file->getFilename() . '" ' . $onclickJs . '>DEL</a></td>';

	Customizations::renderRow($file);

	echo '</tr>';
}
?>
</table>
<button type="submit">Compare!</button>
</form>

<a href="?deleteAllUnAcked=1" onclick="return confirm('really delete?')">Delete all un-acknowledged</a>
</body>
</html>