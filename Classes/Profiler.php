<?php
namespace SandstormMedia\PhpProfiler;


class Profiler {
	protected static $instance;

	protected $currentlyRunningProfilingRun;

	protected $options = array();

	protected function __construct() {

	}

	public function setOption($key, $value) {
		$this->options[$key] = $value;
	}

	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Profiler();
		}
		return self::$instance;
	}

	public function start() {
		if ($this->currentlyRunningProfilingRun !== NULL) {
			throw new \Exception('Profiling already started');
		}
		$this->currentlyRunningProfilingRun = new Domain\Model\ProfilingRun();
		$this->currentlyRunningProfilingRun->start();
		return $this->currentlyRunningProfilingRun;
	}

	public function getRun() {
		return $this->currentlyRunningProfilingRun;
	}

	public function stop() {
		if (!$this->currentlyRunningProfilingRun) return;
		$this->currentlyRunningProfilingRun->stop();

		$run = $this->currentlyRunningProfilingRun;
		$this->currentlyRunningProfilingRun = NULL;
		return $run;
	}

	public function save(Domain\Model\ProfilingRun $run) {
		if (!isset($this->options['profilePath'])) throw new \Exception('Profiling path not set');

		$filename = $this->options['profilePath'] . '/' . microtime(TRUE) . '.profile';
		$run->save($filename);
	}
}
?>