<?php

class Customizations {

	protected static $runData;

	static public function setCurrentRunData(array $runData) {
		self::$runData = $runData;
	}

	static public function getRowHeaders() {
		return array(
			'<th colspan="7">hasLayout / Objects / Arrays / Forms / Nesting / Partials / Sections</th>',
			'<th colspan="3">No. Text/VH/OA-Nodes</th>',
			'<th colspan="2">No. args, ArgDef</th>',
			'No. Objects',
			'<th colspan="2"> Time(inline/xhprof) (ms)</th>',
			'Mem (bytes)');
	}

	static public function renderTr($file) {
		$settings = self::getSettingsForFile($file);
		$trClassName = sprintf('inputvalues-%s-%s-%s-%s-%s-%s-%s',
			$settings['layout'],
			$settings['objects'],
			$settings['arrays'],
			$settings['forms'],
			$settings['nestingLevels'],
			$settings['partials'],
			$settings['partials']
		);

		return '<tr class="' . $trClassName . '">';
	}

	static public function renderRow($file) {
		$settings = self::getSettingsForFile($file);
		self::output($settings['layout'], 'number input layout-' . $settings['layout']);
		self::output($settings['objects'], 'number input objects-' . $settings['objects']);
		self::output($settings['arrays'], 'number input arrays-' . $settings['arrays']);
		self::output($settings['forms'], 'number input forms-' . $settings['forms']);
		self::output($settings['nestingLevels'], 'number input nestingLevels-' . $settings['nestingLevels']);
		self::output($settings['partials'], 'number input partials-' . $settings['partials']);
		self::output($settings['sections'], 'number input sections-' . $settings['sections']);

		self::output(self::count('#==>.*TextNode::__construct#'), 'number output');
		self::output(self::count('#==>.*ViewHelperNode::__construct#'), 'number output');
		self::output(self::count('#==>.*ObjectAccessorNode::__construct#'), 'number output');

		self::output(self::count('#==>.*Arguments::__construct#'), 'number output');
		self::output(self::count('#==>.*ArgumentDefinition::__construct#'), 'number output');

		self::output(self::number(self::count('#==>.*__construct#')), 'number output');

		self::output(self::number($settings['time']), 'number output summary');
		self::output(self::number(self::$runData['main()']['wt']), 'number output summary');
		self::output(self::number(self::$runData['main()']['pmu']), 'number output summary');
	}

	static protected function getSettingsForFile($file) {
		$fileWithoutExtension = str_replace('_ACK', '', $file->getBasename('.xhprof'));
		$settingsPath = $file->getPath() . '/' . $fileWithoutExtension . '.settings';
		if (file_exists($settingsPath)) {
			$settingsData = file_get_contents($settingsPath);
			$settings = unserialize($settingsData);
		} else {
			$settings = array();
		}
		return $settings;
	}

	static protected function number($number) {
		return number_format($number, 0, ',', '.');
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