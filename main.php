<?php

require('Classes/Profiler.php');
require('Classes/Domain/Model/ProfilingRun.php');

/** Test code **/

$profiler = \SandstormMedia\PhpProfiler\Profiler::getInstance();
$profiler->start();

$i = 0;
$a = 0;
while ($i < 1000000) {
	$i++;
	$a = $i*$a;
}

$run = $profiler->stop();
$profiler->setoption('profilePath', '/tmp/profiles');
$profiler->save($run);