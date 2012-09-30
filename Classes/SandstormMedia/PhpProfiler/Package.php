<?php
namespace SandstormMedia\PhpProfiler;

/*                                                                        *
 * This script belongs to the FLOW3 package "SandstormMedia.PhpProfiler". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use \TYPO3\FLOW3\Package\Package as BasePackage;

/**
 * FLOW3 Package Bootstrap
 */
class Package extends BasePackage {

	/**
	 * Disable object management for this package.
	 * @var boolean
	 */
	protected $objectManagementEnabled = FALSE;
}
?>