<?php

class Customizations {

	protected static $runData;

	static public function getRowHeaders() {
		return array(
			'<th colspan="7">hasLayout / Objects / Arrays / Forms / Nesting / Partials / Sections</th>',
			'<th colspan="3">No. Text/VH/OA-Nodes</th>',
			'Time (ms)',
			'Mem (bytes)');
	}

	static public function renderRow($runData, $file, $fileWithoutExtension) {
		self::$runData = $runData;

		$settingsPath = $file->getPath() . '/' . $fileWithoutExtension . '.settings';
		if (file_exists($settingsPath)) {
			$settingsData = file_get_contents($settingsPath);
			$settings = unserialize($settingsData);
		} else {
			$settings = array();
		}

		self::output($settings['layout'], 'number input');
		self::output($settings['objects'], 'number input');
		self::output($settings['arrays'], 'number input');
		self::output($settings['forms'], 'number input');
		self::output($settings['nestingLevels'], 'number input');
		self::output($settings['partials'], 'number input');
		self::output($settings['sections'], 'number input');

		self::output(self::count('#==>.*TextNode::__construct#'), 'number output');
		self::output(self::count('#==>.*ViewHelperNode::__construct#'), 'number output');
		self::output(self::count('#==>.*ObjectAccessorNode::__construct#'), 'number output');

		self::output(number_format($runData['main()']['wt']), 'number output summary');
		self::output(number_format($runData['main()']['mu']), 'number output summary');
	}

	static public function outputCss() {
		echo <<<EOD
.number {
	text-align:right;
}
.input {
	background-color: #E6FFB5;
}
.output {
	background-color: #CCEAFB;
}
.summary {
	font-weight:bold;
}

EOD;
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

	static protected function output($data, $cssClass = '') {
		echo '<td class="' . $cssClass . '">' . $data . '</td>';
	}
}
?>