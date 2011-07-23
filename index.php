<html>
<head>

</head>
<body>

<form action="/xhprof/xhprof_html/index.php?source=xhprof" method="GET" target="_blank">
<table>

<?php

$xhprofOutputDirectory = ini_get('xhprof.output_dir');

if (isset($_GET['ack']) && strpos( $_GET['ack'], '_ACK.xhprof') === FALSE) {
	$newFileName = str_replace('.xhprof', '_ACK.xhprof', $_GET['ack']);
	rename($xhprofOutputDirectory . '/' . $_GET['ack'], $xhprofOutputDirectory . '/' . $newFileName);
}

$dir = new DirectoryIterator($xhprofOutputDirectory);
foreach ($dir as $file) {
	if ($file->getExtension() === 'xhprof') {
		$fileWithoutExtension = substr($file->getFilename(), 0, -strlen($file->getExtension()) - 1);
		echo '<tr>';
		echo '<td><input type="radio" name="run1" value="' . $fileWithoutExtension . '">';
		echo '<input type="radio" name="run2" value="' . $fileWithoutExtension . '"></td>';
		echo '<td><a href="/xhprof/xhprof_html/index.php?run=' . $fileWithoutExtension . '&source=xhprof">' . $file->getFilename() . '</a></td><td>';

		if (strpos($file->getFilename(), '_ACK.xhprof') === FALSE) {
			echo '<a href="?ack=' . $file->getFilename() . '">Acknowledge</a>';
		}
		echo '</td></tr>';
	}
}
?>
</table>
<button type="submit">Compare!</button>
</form>
</body>
</html>