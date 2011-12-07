<?php
namespace SandstormMedia\PhpProfiler\Domain\Model;


class ProfilingRun {

	protected $startTime;

	protected $timers;

	protected $xhprofTrace;

	protected $options = array();

	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

	public function getOptions() {
		return $this->options;
	}

	public function start() {
		$this->timers = array();
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

	public function stopTimer($name) {
		if (!is_array($this->timers[$name])) {
			$this->timers[$name] = array();
		}
		$this->timers[$name][] = array(
			'time' => microtime(TRUE),
			'start' => FALSE
		);
	}

	public function stop() {
		$this->stopTimer('Profiling Run');
		if (function_exists('xhprof_disable')) {
			$this->xhprofTrace = xhprof_disable();
		}

		$this->convertTimersRelativeToStartTime();
	}

	public function save($filename) {
		file_put_contents($filename . '.xhprof', serialize($this->xhprofTrace));
		$this->xhprofTrace = $filename . '.xhprof';
		file_put_contents($filename, serialize($this));
	}

	public function getStartTime() {
		return \DateTime::createFromFormat('U', (int)$this->startTime);
	}

	public function getTimers() {
		return $this->timers;
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
		//var_dump($events);
		//die();
		return $events;
	}

	protected function convertTimersRelativeToStartTime() {
		foreach ($this->timers as $name => &$t) {
			foreach ($t as &$v) {
				$v['time'] -= $this->startTime;
			}
		}
	}
}