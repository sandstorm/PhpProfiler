<?php
namespace SandstormMedia\PhpProfiler\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "SandstormMedia.PhpProfiler". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Empty Profiling Run; provides method stubs which do not do anything.
 *
 * This is needed such that the user can do ...getRun()->startTimer() even
 * when profiling is disabled.
 */
class EmptyProfilingRun {

	public function setOption($key, $value) {
	}

	public function startTimer($name, $data = array()) {
	}

	public function stopTimer($name) {
	}

	public function timestamp($name, $data = array()) {
	}
}
?>