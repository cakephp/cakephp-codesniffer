<?php
/**
 * CakePHPStandardTest
 */
class CakePHPStandardTest extends PHPUnit_Framework_TestCase {

/**
 * testFiles
 *
 * Run simple syntax checks, if the filename ends with pass.php - expect it to pass
 */
	public function testFiles() {
		$standard = dirname(__DIR__);
		if (basename($standard) !== 'CakePHP') {
			$this->fail("The dirname for the standard must be CakePHP");
		}

		$files = scandir(__DIR__ . '/files');
		foreach ($files as $file) {
			if ($file[0] === '.') {
				continue;
			}
			$expectPass = (substr($file, -8) === 'pass.php');
			$this->_testFile(__DIR__ . '/files/' . $file, $standard, $expectPass);
		}
	}

/**
 * _testFile
 *
 * @param string $file
 * @param string $standard
 * @param boolean $expectPass
 */
	protected function _testFile($file, $standard, $expectPass) {
		exec("phpcs --standard=$standard $file", $output, $return);
		$outputStr = implode($output, "\n");
		if ($expectPass) {
			$this->assertNotRegExp(
				"/FOUND \d+ ERROR/",
				$outputStr,
				basename($file) . ' - expected failures, none reported. ' . $outputStr
			);
			$this->assertSame(0, $return, 'Expected return code of 0 for ' . basename($file));
		} else {
			$this->assertRegExp(
				"/FOUND \d+ ERROR/",
				$outputStr,
				basename($file) . ' - expected to pass with no errors, some were reported. ' . $outputStr
			);
			$this->assertSame(1, $return, 'Expected none-zero return code for ' . basename($file));
		}
	}
}
