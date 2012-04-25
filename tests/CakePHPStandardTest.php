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
		foreach($files as $file) {
			if ($file[0] === '.') {
				continue;
			}
			$expectPass = (bool)strpos('pass.php', $file);
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
			$this->assertRegExp(
				"/FAILURES!/",
				$outputStr,
				basename($file) . ' - expected failures, none reported. ' . $outputStr
			);
		} else {
			$this->assertNotRegExp(
				"/FAILURES!/",
				$outputStr,
				basename($file) . ' - expected no failure, some were reported. ' . $outputStr
			);
		}
	}
}
