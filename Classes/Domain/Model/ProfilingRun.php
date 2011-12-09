<?php
namespace SandstormMedia\PhpProfiler\Domain\Model;


class ProfilingRun {

	protected $startTime;

	protected $timers;

	protected $timestamps;

	protected $xhprofTrace;

	protected $options = array();

	protected $fullPath;

	protected $tags = array();

	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

	public function getOptions() {
		return $this->options;
	}

	public function start() {
		$this->timers = array();
		$this->timestamps = array();
		$this->startTime = microtime(TRUE);
		if (function_exists('xhprof_enable')) {
			xhprof_enable();
		}
		$this->startTimer('Profiling Run');
	}

	public function startTimer($name, $data = array()) {
		if (!isset($this->timers[$name])) {
			$this->timers[$name] = array();
		}
		$this->timers[$name][] = array(
			'time' => microtime(TRUE),
			'data' => $data,
			'start' => TRUE
		);
	}

	public function setFullPath($fullPath) {
		$this->fullPath = $fullPath;
	}

	public function stopTimer($name) {
		if (!isset($this->timers[$name])) {
			$this->timers[$name] = array();
		}
		$this->timers[$name][] = array(
			'time' => microtime(TRUE),
			'start' => FALSE
		);
	}

	public function timestamp($name, $data = array()) {
		$this->timestamps[] = array(
			'name' => $name,
			'time' => microtime(TRUE),
			'data' => $data
		);
	}

	public function getTags() {
		if (!is_array($this->tags)) return array();
		return $this->tags;
	}

	public function setTags(array $tags) {
		$this->tags = $tags;
	}

	public function stop() {
		$this->stopTimer('Profiling Run');
		if (function_exists('xhprof_disable')) {
			$this->xhprofTrace = xhprof_disable();
		}

		$this->convertTimersRelativeToStartTime();
	}

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

	public function getXhprofTrace() {
		if (is_string($this->xhprofTrace)) {
			$this->xhprofTrace = unserialize(file_get_contents($this->xhprofTrace));
		}
		return $this->xhprofTrace;
	}


	public function getStartTime() {
		return \DateTime::createFromFormat('U', (int)$this->startTime);
	}

	public function getTimers() {
		return $this->timers;
	}

	public function getTimestamps() {
		return $this->timestamps;
	}

	public function remove() {
		if ($this->fullPath !== NULL) {
			unlink($this->fullPath);
			unlink($this->fullPath . '.xhprof');
		}
	}

	public function getTimersAsDuration() {
		$events = array();
		$currentlyOpenTimers = array();

			//var_dump($this->timers);
			//die();
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
}