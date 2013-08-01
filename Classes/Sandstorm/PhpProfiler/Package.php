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

		$profiler = Profiler::getInstance();
		$profiler->setConfigurationProvider(function() use ($bootstrap) {
			$settings = $bootstrap->getEarlyInstance('TYPO3\Flow\Configuration\ConfigurationManager')->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Sandstorm.PhpProfiler');
			if (!file_exists($settings['plumber']['profilePath'])) {
				Files::createDirectoryRecursively($settings['plumber']['profilePath']);
			}

			return $settings;
		});

		$run = $profiler->start();
		$run->setOption('Context', (string)$bootstrap->getContext());

		$dispatcher = $bootstrap->getSignalSlotDispatcher();
		$this->connectToSignals($dispatcher, $profiler, $run, $bootstrap);
		$this->connectToNeosSignals($dispatcher, $profiler, $run, $bootstrap);
	}

	/**
	 * Wire signals to slots as needed.
	 *
	 * @param \TYPO3\Flow\SignalSlot\Dispatcher $dispatcher
	 * @param Profiler $profiler
	 * @param \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun $run
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 * @return void
	 */
	protected function connectToSignals(\TYPO3\Flow\SignalSlot\Dispatcher $dispatcher, Profiler $profiler, \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun $run, \TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'beforeInvokeStep', function($step) use($run) {
			$run->startTimer('Boostrap Sequence: ' . $step->getIdentifier());
		});
		$dispatcher->connect('TYPO3\Flow\Core\Booting\Sequence', 'afterInvokeStep', function($step) use($run) {
			$run->stopTimer('Boostrap Sequence: ' . $step->getIdentifier());
		});

		$dispatcher->connect('TYPO3\Flow\Core\Bootstrap', 'finishedRuntimeRun', function() use($profiler, $bootstrap) {
			$run = $profiler->stop();
			if ($run) {
				$profiler->save($run);
			}
		});

		$dispatcher->connect('TYPO3\Flow\Core\Bootstrap', 'finishedCompiletimeRun', function() use($profiler, $bootstrap) {
			$run = $profiler->stop();
			if ($run) {
				$run->setOption('Context', 'COMPILE');
				$profiler->save($run);
			}
		});

		$dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'beforeControllerInvocation', function($request, $response, $controller) use($run) {
			$run->setOption('Controller Name', get_class($controller));
			$data = array(
				'Controller' => get_class($controller)
			);
			if ($request instanceof \TYPO3\Flow\Mvc\ActionRequest) {
				$data['Action'] = $request->getControllerActionName();
			}

			$run->startTimer('MVC: Controller Invocation', $data);
		});
		$dispatcher->connect('TYPO3\Flow\Mvc\Dispatcher', 'afterControllerInvocation', function() use($run) {
			$run->stopTimer('MVC: Controller Invocation');
		});
	}

	/**
	 * Wire signals to slots as needed in TYPO3 Neos.
	 *
	 * @param \TYPO3\Flow\SignalSlot\Dispatcher $dispatcher
	 * @param Profiler $profiler
	 * @param \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun $run
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap
	 * @return void
	 */
	protected function connectToNeosSignals(\TYPO3\Flow\SignalSlot\Dispatcher $dispatcher, Profiler $profiler, \Sandstorm\PhpProfiler\Domain\Model\ProfilingRun $run, \TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher->connect('TYPO3\TypoScript\Core\Runtime', 'beginEvaluation', function($typoScriptPath) use($run) {
			$run->startTimer('TypoScript Runtime: ' . $typoScriptPath);
		});
		$dispatcher->connect('TYPO3\TypoScript\Core\Runtime', 'endEvaluation', function($typoScriptPath) use($run) {
			$run->stopTimer('TypoScript Runtime: ' . $typoScriptPath);
		});

		$dispatcher->connect('TYPO3\Neos\View\TypoScriptView', 'beginRender', function() use($run) {
			$run->startTimer('Neos TypoScript Rendering');
		});
		$dispatcher->connect('TYPO3\Neos\View\TypoScriptView', 'endRender', function() use($run) {
			$run->stopTimer('Neos TypoScript Rendering');
		});
	}

}
?>