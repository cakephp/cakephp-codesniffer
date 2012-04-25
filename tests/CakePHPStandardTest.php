<?php
/**
 * CakePHPStandardTest
 */
class CakePHPStandardTest extends PHPUnit_Framework_TestCase {

	/**
	 * testFiles
	 *
	 * Run simple syntax checks, if the filename ends with pass.php - expect it to pass
	 *
	 * @access public
	 * @return void
	 */
	public function testFiles() {
		$files = scandir(__DIR__ . '/files');
		foreach ($files as $file) {
			if ($file[0] === '.') {
				continue;
			}
			$expectPass = (substr($file, -8) === 'pass.php');
			$this->_testFile(__DIR__ . '/files/' . $file, $expectPass);
		}
	}

	/**
	 * Test a file
	 *
	 * @param mixed $file
	 * @param mixed $expectPass
	 * @access protected
	 * @return void
	 */
	protected function _testFile($file, $expectPass = true) {
		exec("phpcs --standard=CakePHP $file", $output, $return);
		$outputStr = implode($output, "\n");
		if ($expectPass) {
			$this->assertSame(0, $return, 'Expected return code of 0 for ' . basename($file));
			$this->assertNotRegExp(
				"/FAILURES!/",
				$outputStr,
				basename($file) . ' - expected failures, none reported. ' . $outputStr
			);
		} else {
			$this->assertSame(1, $return, 'Expected none-zero return code for ' . basename($file));
			$this->assertRegExp(
				"/FAILURES!/",
				$outputStr,
				basename($file) . ' - expected to pass with no errors, some were reported. ' . $outputStr
			);
		}
	}
}
