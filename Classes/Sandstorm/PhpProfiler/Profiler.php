<?php
namespace Sandstorm\PhpProfiler;

/*                                                                        *
 * This script belongs to the FLOW3 package "Sandstorm.PhpProfiler". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * PHP Profiler
 */
class Profiler {

	/**
	 * @var Profiler
	 */
	protected static $instance;

	/**
	 * @var Domain\Model\ProfilingRun
	 */
	protected $currentlyRunningProfilingRun;

	/**
	 * @var array
	 */
	protected $configuration = array();

	/**
	 * An "empty" profiling run; which does not execute anything and
	 * can be returned by getRun() if profiling is not currently running.
	 *
	 * @var Domain\Model\ProfilingRun
	 */
	protected $emptyProfilingRun;

	/**
	 * Singleton.
	 */
	protected function __construct() {
		$this->emptyProfilingRun = new Domain\Model\EmptyProfilingRun();
	}

	/**
	 * @return \Sandstorm\PhpProfiler\Profiler
	 * @api
	 */
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Profiler();
		}
		return self::$instance;
	}

	/**
	 * Set configuration options for the profiler. Currently supported
	 * configuration options:
	 *
	 * - profilePath: Directory where profiles are stored
	 *
	 * @param string $key
	 * @param string $value
	 * @api
	 */
	public function setConfiguration($key, $value) {
		$this->configuration[$key] = $value;
	}

	/**
	 * Start a profiling run and return the run instance.
	 *
	 * @return \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun
	 * @api
	 */
	public function start() {
		if ($this->currentlyRunningProfilingRun !== NULL) {
			throw new \Exception('Profiling already started');
		}
		$this->currentlyRunningProfilingRun = new Domain\Model\ProfilingRun();
		$this->currentlyRunningProfilingRun->start();
		return $this->currentlyRunningProfilingRun;
	}

	/**
	 * Get the current profiling run.
	 *
	 * @return \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun
	 */
	public function getRun() {
		if ($this->currentlyRunningProfilingRun === NULL) {
			return $this->emptyProfilingRun;
		}
		return $this->currentlyRunningProfilingRun;
	}

	/**
	 * Stop run and save it afterwards
	 * @api
	 */
	public function stopAndSave() {
		$run = $this->stop();
		if ($run !== NULL) {
			$this->save($run);
		}
	}

	/**
	 * Stop a profiling run if one is running, and return it.
	 *
	 * @return \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun the profiling run or NULL if none is running
	 */
	public function stop() {
		if (!$this->currentlyRunningProfilingRun) {
			return;
		}
		$this->currentlyRunningProfilingRun->stop();

		$run = $this->currentlyRunningProfilingRun;
		$this->currentlyRunningProfilingRun = NULL;
		return $run;
	}

	/**
	 * Save a profiling run.
	 *
	 * @param Domain\Model\ProfilingRun $run
	 */
	public function save(Domain\Model\ProfilingRun $run) {
		if (!isset($this->configuration['profilePath'])) {
			throw new \Exception('Profiling path not set');
		}

		$filename = $this->configuration['profilePath'] . '/' . microtime(TRUE) . '.profile';
		$run->save($filename);
	}
}
?>