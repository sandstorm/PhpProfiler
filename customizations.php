<?php

class Customizations {

	protected static $runData;

	static public function getRowHeaders() {
		return array(
			'<th colspan="3">No. Text/VH/OA-Nodes</th>',
			'Time (ms)',
			'Mem (bytes)');
	}

	static public function renderRow($runData) {
		self::$runData = $runData;

		self::output(self::count('#==>.*TextNode::__construct#'));
		self::output(self::count('#==>.*ViewHelperNode::__construct#'));
		self::output(self::count('#==>.*ObjectAccessorNode::__construct#'));

		self::output(number_format($runData['main()']['wt']));
		self::output(number_format($runData['main()']['mu']));
	}


	/**
	 * HELPERS
	 */
	static protected function count($regExp) {
		$matchingResults = 0;
		foreach (self::$runData as $id => $data) {
			if (preg_match($regExp, $id)) {
				$matchingResults += $data['ct'];
			}
		}
		return $matchingResults;
	}

	static protected function output($data) {
		echo '<td style="text-align:right">' . $data . '</td>';
	}
}
?>