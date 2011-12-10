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
 * Profiling run Domain Model
 */
class ProfilingRun extends EmptyProfilingRun {

	/**
	 * Start time of the profiling run in seconds (microtime(true))
	 *
	 * @var float
	 */
	protected $startTime;

	/**
	 * Collected timers. Is an associative array:
	 * key: Timer Name
	 * value: array of the "events" for the current timer, an event can be either "start" or "stop".
	 *
	 *    'time' => (float) Current time in seconds, with microtime precision; relative to $this->startTime
	 *    'data' => (array) Data payload, as specified in startTimer(). Only used if start=TRUE.
	 *    'start' => (boolean) If TRUE, is a "start" event of the timer, if FALSE, is a stop event.
	 *    'mem' => (int) Memory consumption in bytes at the current time.
	 *
	 * @var array
	 */
	protected $timers;

	/**
	 * Collected timestamps. Is an array, where each array element looks as follows:
	 *
	 *    'name' => (string) name of the timestamp.
	 *    'time' => (float) Current time in seconds, with microtime precision; relative to $this->startTime
	 *    'data' => (array) Data payload, as specified in timestamp().
	 *    'mem' => (int) Memory consumption in bytes at the current time.
	 *
	 * @var array
	 */
	protected $timestamps;

	/**
	 * If it is an array, it is an XHProf trace array. If it is a string,
	 * it is a fully qualified file name pointing to a serialized XHProf
	 * Trace array
	 *
	 * @var array|string
	 */
	protected $xhprofTrace;

	/**
	 * Associative Array of Options. Options are just global metainformation
	 * for the current profiling run, which can be shown in the overview
	 * pages.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Tags of the current profiling run
	 *
	 * @var array
	 */
	protected $tags = array();

	/**
	 * Full path to the serialized profiling run file. Not always set,
	 * purely internal.
	 *
	 * @var string
	 */
	protected $fullPath;

	/**
	 * Set an option.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @api
	 */
	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

	/**
	 * @return array
	 * @api
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return array
	 * @api
	 */
	public function getTags() {
		if (!is_array($this->tags)) return array();
		return $this->tags;
	}

	/**
	 * @param array $tags
	 * @api
	 */
	public function setTags(array $tags) {
		$this->tags = $tags;
	}

	/**
	 * Start to record this profiling run
	 */
	public function start() {
		$this->timers = array();
		$this->timestamps = array();
		$this->startTime = microtime(TRUE);
		if (function_exists('xhprof_enable')) {
			xhprof_enable();
		}
		$this->startTimer('Profiling Run');
	}

	/**
	 * Stop this profiling run recording
	 */
	public function stop() {
		$this->stopTimer('Profiling Run');
		if (function_exists('xhprof_disable')) {
			$this->xhprofTrace = xhprof_disable();
		}

		$this->convertTimersRelativeToStartTime();
	}

	/**
	 * Helper which converts the timer values relative to the start time.
	 * Is called automatically on stop().
	 */
	protected function convertTimersRelativeToStartTime() {
		foreach ($this->timers as $name => &$t) {
			foreach ($t as &$v) {
				$v['time'] -= $this->startTime;
			}
		}

		foreach ($this->timestamps as &$t) {
			$t['time'] -= $this->startTime;
		}
	}

	/**
	 * Save this profiling to disk
	 *
	 * @param string $filename
	 */
	public function save($filename = NULL) {
		if ($filename === NULL) {
			if ($this->fullPath === NULL) throw new \Exception('TODO: Full path not set');
			$filename = $this->fullPath;
		}
		if (is_array($this->xhprofTrace)) {
			file_put_contents($filename . '.xhprof', serialize($this->xhprofTrace));
			$this->xhprofTrace = $filename . '.xhprof';
		}
		file_put_contents($filename, serialize($this));
	}

	/**
	 * @param string $fullPath
	 */
	public function setFullPath($fullPath) {
		$this->fullPath = $fullPath;
	}

	/**
	 * Remove this profiling run.
	 *
	 * @api
	 */
	public function remove() {
		if ($this->fullPath !== NULL) {
			unlink($this->fullPath);
			unlink($this->fullPath . '.xhprof');
		}
	}

	/**
	 * Start a timer
	 *
	 * @param string $name
	 * @param array $data
	 * @api
	 */
	public function startTimer($name, $data = array()) {
		if (!isset($this->timers[$name])) {
			$this->timers[$name] = array();
		}
		$this->timers[$name][] = array(
			'time' => microtime(TRUE),
			'data' => $data,
			'start' => TRUE,
			'mem' => memory_get_peak_usage(TRUE)
		);
	}

	/**
	 * Stop a timer
	 *
	 * @param string $name
	 * @api
	 */
	public function stopTimer($name) {
		if (!isset($this->timers[$name])) {
			$this->timers[$name] = array();
		}
		$this->timers[$name][] = array(
			'time' => microtime(TRUE),
			'start' => FALSE,
			'mem' => memory_get_peak_usage(TRUE)
		);
	}

	/**
	 * Record a timestamp
	 *
	 * @param string $name
	 * @param array $data
	 */
	public function timestamp($name, $data = array()) {
		$this->timestamps[] = array(
			'name' => $name,
			'time' => microtime(TRUE),
			'data' => $data,
			'mem' => memory_get_peak_usage(TRUE)
		);
	}

	/**
	 * @return \DateTime the start time
	 */
	public function getStartTime() {
		return \DateTime::createFromFormat('U', (int)$this->startTime);
	}

	/**
	 * Get memory consumption. Returned is a sorted-by-time array
	 * where each array element is again an array with the following structure:
	 *
	 * 'time' => (float) Current time in seconds, with microtime precision; relative to $this->startTime
	 * 'mem'  => (int) Current memory consumption in bytes.
	 *
	 * @return array
	 */
	public function getMemory() {
		$output = array();
		foreach ($this->timestamps as $t) {
			$output[] = array(
				'time' => $t['time'],
				'mem' => $t['mem']
			);
		}
		foreach ($this->timers as $tmp) {
			foreach ($tmp as $t) {
				$output[] = array(
					'time' => $t['time'],
					'mem' => $t['mem']
				);
			}
		}

		// now, sort events by start time
		usort($output, function($a, $b) {
			return (int)(1000*$a['time'] - 1000*$b['time']);
		});
		return $output;
	}

	/**
	 * Get the full XHProf Trace array
	 *
	 * @return array
	 */
	public function getXhprofTrace() {
		if (is_string($this->xhprofTrace)) {
			$this->xhprofTrace = unserialize(file_get_contents($this->xhprofTrace));
		}
		return $this->xhprofTrace;
	}

	/**
	 * Get all timestamps; see $this->timestamps for the format
	 * description.
	 *
	 * @return array
	 */
	public function getTimestamps() {
		return $this->timestamps;
	}

	/**
	 * Get all timers as "duration" with a start and end time.
	 * Returned is a (sorted by time) array of events
	 * where each array element has the following structure:
	 *
	 * 'start' => (float) start time in seconds, with microtime precision; relative to $this->startTime
	 * 'stop'  => (float) stop time in seconds, with microtime precision; relative to $this->startTime
	 * 'name'  => (string) Name of the timer
	 * 'data'  => (array) additional payload which has been specified in $this->startTimer()
	 *
	 * @return array
	 */
	public function getTimersAsDuration() {
		$events = array();
		$currentlyOpenTimers = array();

		foreach ($this->timers as $timerName => $timerValues) {
			$currentlyOpenTimers[$timerName] = array();
			foreach ($timerValues as $timerValue) {
				if ($timerValue['start'] === TRUE) {
					$currentlyOpenTimers[$timerName][] = $timerValue;
				} else {
					$startTime = array_pop($currentlyOpenTimers[$timerName]);
					if (is_array($startTime)) {
						$stopTime = $timerValue['time'];
						$events[] = array(
							'start' => $startTime['time'],
							'stop' => $stopTime,
							'name' => $timerName,
							'data' => $startTime['data']
						);
					}
				}
			}
		}

		// now, sort events by start time
		usort($events, function($a, $b) {
			return (int)(1000*$a['start'] - 1000*$b['start']);
		});
		return $events;
	}


}