<?php
namespace SandstormMedia\PhpProfiler;

use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * FLOW3 Package Bootstrap for the Doctrine ORM package
 */
class Package extends BasePackage {

	/**
	 * Disable object management for this package.
	 * @var boolean
	 */
	protected $objectManagementEnabled = FALSE;

}

?>