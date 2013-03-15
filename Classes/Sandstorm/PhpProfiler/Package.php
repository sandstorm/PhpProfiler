<?php
namespace Sandstorm\PhpProfiler;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Sandstorm.PhpProfiler". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Package\Package as BasePackage;
use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\SignalSlot\Dispatcher;

/**
 * TYPO3 Flow package bootstrap
 */
class Package extends BasePackage {

	/**
	 * Sets up xhprof, some directories, the profiler and wires signals to slots.
	 *
	 * @param Bootstrap $bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		if (!file_exists(FLOW_PATH_DATA . 'Logs/Profiles')) {
			Files::createDirectoryRecursively(FLOW_PATH_DATA . 'Logs/Profiles');
		}

		$profiler = Profiler::getInstance();
		$profiler->setBootstrap($bootstrap);

		$profiler->start();
		$this->connectToSignals($profiler, $bootstrap);
	}

	/**
	 * Wire signals to slots as needed.
	 *
	 * @param Dispatcher $dispatcher
	 * @param Profiler $profiler
	 * @param Domain\Model\ProfilingRun $run
	 * @param Bootstrap $bootstrap
	 * @return void
	 */
	protected function connectToSignals(Profiler $profiler, Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$run = $profiler->getRun();
		$run->setOption('Context', $bootstrap->getContext());

		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'beforeInvokeStep', function(\TYPO3\Flow\Core\Booting\Step $step) use ($run) {
			$run->startTimer('Bootstrap Sequence: ' . $step->getIdentifier());
		});
		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function(\TYPO3\Flow\Core\Booting\Step $step) use ($run) {
			$run->stopTimer('Bootstrap Sequence: ' . $step->getIdentifier());
		});

		$dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'beforeControllerInvocation', function($request, $response, $controller) use ($run) {
			$run->setOption('Controller Name', get_class($controller));
			$data = array(
				'Controller' => get_class($controller)
			);
			if ($request instanceof \TYPO3\Flow\Mvc\ActionRequest) {
				$data['Action'] = $request->getControllerActionName();
			}

			$run->startTimer('MVC: Controller Invocation', $data);
		});
		$dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'afterControllerInvocation', function() use ($run) {
			$run->stopTimer('MVC: Controller Invocation');
		});

			// stop profiling and save data
		$dispatcher->connect('TYPO3\Flow\Core\Bootstrap', 'finishedRuntimeRun', function() use ($profiler, $bootstrap) {
			$run = $profiler->stop();
			if ($run !== NULL) {
				$run->setOption('Context', $bootstrap->getContext());
				$profiler->save($run);
			}
		});
		$dispatcher->connect('TYPO3\Flow\Core\Bootstrap', 'finishedCompiletimeRun', function() use ($profiler, $bootstrap) {
			$run = $profiler->stop();
			if ($run !== NULL) {
				$run->setOption('Context', 'COMPILE ' . $bootstrap->getContext());
				$profiler->save($run);
			}
		});
	}

}
?>