<?php
if (!class_exists('PHP_CodeSniffer_CLI')) {
	require_once 'PHP/CodeSniffer/CLI.php';
}

class TestHelper {

	protected $_rootDir;
	protected $_dirName;
	protected $_phpcs;

	public function __construct() {
		$this->_rootDir = dirname(dirname(__FILE__));
		$this->_dirName = basename($this->_rootDir);

		set_include_path(
			$this->_rootDir .
			PATH_SEPARATOR .
			$this->_rootDir . '/Sniffs' .
			PATH_SEPARATOR .
			get_include_path()
		);
		$this->_phpcs = new PHP_CodeSniffer_CLI();
		spl_autoload_register(array($this, 'autoload'), true, true);
	}

/**
 * Because PHPCS will assume our classes will contain the name
 * of the checked out code directory we have to make a class that matches that.
 *
 * @param string $class The classname to try and load.
 */
	public function autoload($class) {
		$originalClass = $class;
		if (strpos($class, $this->_dirName) !== false) {
			$class = str_replace($this->_dirName, 'CakePHP', $class);
		}
		if (class_exists($class, false)) {
			eval('class ' . $originalClass . ' extends ' . $class . '{}');
		}
	}

/**
 * Run PHPCS on a file.
 *
 * @param string $file to run.
 * @return string The output from phpcs.
 */
	public function runPhpCs($file) {
		$options = $this->_phpcs->getDefaults();
		$options = array_merge($options, array(
			'encoding' => 'utf-8',
			'files' => array($file),
			'standard' => $this->_rootDir . '/ruleset.xml'
		));

		// New PHPCS has a strange issue where the method arguments
		// are not stored on the instance causing weird errors.
		$reflection = new ReflectionProperty($this->_phpcs, 'values');
		$reflection->setAccessible(true);
		$reflection->setValue($this->_phpcs, $options);

		ob_start();
		$this->_phpcs->process($options);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

}
