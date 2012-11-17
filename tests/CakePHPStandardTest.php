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
	public static function testProvider() {
		$tests = array();

		$standard = dirname(dirname(__FILE__));
		if (basename($standard) !== 'CakePHP') {
			PHPUnit_Framework_TestCase::fail("The dirname for the standard must be CakePHP");
		}

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__) . '/files'));
		foreach ($iterator as $dir) {
			if ($dir->isDir()) {
				continue;
			}

			$file = $dir->getPathname();
			$expectPass = (substr($file, -8) === 'pass.php');
			$tests[] = array(
				$file,
				$standard,
				$expectPass
			);
		}
		return $tests;
	}

/**
 * _testFile
 *
 * @dataProvider testProvider
 *
 * @param string $file
 * @param string $standard
 * @param boolean $expectPass
 */
	public function testFile($file, $standard, $expectPass) {
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
			$this->assertSame(1, $return, 'Expected non-zero return code for ' . basename($file));
		}
	}
}
