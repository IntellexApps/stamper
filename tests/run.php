<?php require '../vendor/autoload.php';

/**
 * Class Tester is a very lightweight testing framework.
 */
class Tester {

	/** @var string|null The currently active test group. */
	private static $activeGroup = null;

	/** @var string[][] The list of encountered errors. */
	private static $errors = [];

	/** @var bool Indicates if the testing should be done in debug mode. */
	private static $debug = false;

	/** @return bool True if the testing is done in the debug mode, false otherwise. */
	public static function isDebug() {
		return static::$debug;
	}

	/**
	 * Initialize the tester.
	 */
	public static function init() {

		// Definitions
		define('INPUT', 0);
		define('OUTPUT', 1);
		define('TESTING', true);

		// Parse arguments
		if (key_exists('argv', $_SERVER)) {
			foreach ($_SERVER['argv'] as $arg) {
				switch ($arg) {

					case 'debug':
						static::$debug = true;
						\Intellex\Debugger\IncidentHandler::register();
						function debug($var) {
							\Intellex\Debugger\VarDump::from($var);
						}

						break;
				}
			}
		}
	}

	/**
	 * Test a full data.
	 *
	 * @param array[] $groups An array where key is the test group name, the first parameters is
	 *                        the clojure that tests the values and the second is the list of
	 *                        cases, where first item are the parameters for the clojure and the
	 *                        second is the case tuple containing the parameters for the test
	 *                        clojure as the first item and the expected result as the second one.
	 */
	public static function test($groups) {
		foreach ($groups as $groupName => $case) {
			static::testGroup($groupName, $case[0], $case[1]);
		}
	}

	/**
	 * Test multiple cases.
	 *
	 * @param string|null $groupName   The group this test is belonging to.
	 * @param Closure     $testClojure The Clojure that will be consuming the input parameters and
	 *                                 return the result.
	 * @param array       $cases       An array of tuples where first item are the parameters for
	 *                                 the test method and the second is the expected output.
	 */
	public static function testGroup($groupName, $testClojure, $cases) {
		foreach ($cases as $message => $case) {
			static::testCase($groupName, $message, $testClojure, $case);
		}
	}

	/**
	 * Test a single case.
	 *
	 * @param string|null $groupName   The group this test is belonging to.
	 * @param string      $message     The message if the test fails.
	 * @param Closure     $testClojure The Clojure that will be consuming the input parameters and
	 *                                 return the result.
	 * @param array[]     $case        A tuple where first item are the parameters for the test
	 *                                 method and the second is the expected output.
	 */
	public static function testCase($groupName, $message, $testClojure, $case) {
		try {
			$result = call_user_func_array($testClojure, $case[INPUT]);
		} catch (Exception $exception) {
			$result = $exception;
		}

		Tester::assert($groupName, $message, $result, $case[OUTPUT]);
	}

	/**
	 * Invoke a private method.
	 *
	 * @param stdClass $instance   The object on which to invoke the method.
	 * @param string   $methodName The name of the method.
	 * @param array    $params     The parameters for the method.
	 *
	 * @return mixed Whatever the static method returned.
	 * @throws ReflectionException
	 */
	public static function invokePrivateMethod($instance, $methodName, $params = []) {
		return static::makePublic(get_class($instance), $methodName)->invokeArgs($instance, $params);
	}

	/**
	 * Invoke a private static method.
	 *
	 * @param string $className  The name of the class, with full namespace included.
	 * @param string $methodName The name of the method.
	 * @param array  $params     The parameters for the method.
	 *
	 * @return mixed Whatever the static method returned.
	 * @throws ReflectionException
	 */
	public static function invokePrivateStaticMethod($className, $methodName, $params = []) {
		return static::makePublic($className, $methodName)->invokeArgs(null, $params);
	}

	/**
	 * Make a method accessible (public).
	 *
	 * @param string $className  The name of the class, with full namespace included.
	 * @param string $methodName The name of the method.
	 *
	 * @return ReflectionMethod The method which was just made accessible.
	 * @throws ReflectionException
	 */
	private static function makePublic($className, $methodName) {
		$method = (new ReflectionClass($className))->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}

	/**
	 * Assert that a received output matches the output.
	 *
	 * @param string|null $group    The group this check is belonging.
	 * @param string      $message  The message if the assert fails.
	 * @param mixed       $output   The actual value received from the test.
	 * @param mixed       $expected The expected result.
	 */
	public static function assert($group, $message, $output, $expected) {
		$match = $output === $expected;

		// Get the proper comparision, based on the expected value
		if (is_object($expected)) {
			if ($expected instanceof Exception && $output instanceof Exception) {
				$match = $expected->getMessage() === $output->getMessage();

			} else {
				$match = $output == $expected;
			}
		}

		// Assert the match
		if (!$match) {

			// Append the message
			if (!key_exists($group, static::$errors)) {
				static::$errors[$group] = [];
			}

			// Get the print values
			foreach ([ 'expected', 'output' ] as $var) {
				$$var = str_replace("\n", "\n        ", \Intellex\Debugger\Helper::getReadableValue($$var));
			}

			$message = <<<ERROR
{$message}
    Expected: {$expected}
    Output:   {$output}

ERROR;
			self::appendError($message, $group);
		}
	}

	/**
	 * Add a message to the list of errors.
	 *
	 * @param string      $message  The message if the assert fails.
	 * @param string|null $group    the group this check is belonging or null to use the currently
	 *                              active group, @see Tester::setActiveGroup().
	 */
	private static function appendError($message, $group = null) {

		// Default to the currently active group
		$group = $group ?: static::$activeGroup;

		// Append the message
		if (!key_exists($group, static::$errors)) {
			static::$errors[$group] = [];
		}
		static::$errors[$group][] = $message;
	}

	/**
	 * Handle the exception.
	 *
	 * @param Exception $exception The exception to handle.
	 *
	 * @throws Exception
	 */
	public static function handle(Exception $exception) {
		static::assert($exception, false, $exception->getMessage(), null);
		if (defined('DEBUG') && DEBUG) {
			throw $exception;
		}
	}

	/**
	 * End the test.
	 */
	public static function end() {

		// Exit without
		if (empty(static::$errors)) {
			if (static::isDebug()) {
				echo 'No errors!' . PHP_EOL;
			}
			exit(0);
		}

		// Show errors
		if (static::isDebug()) {
			foreach (static::$errors as $group => $errors) {
				$group = $group ? "{$group} - " : null;
				foreach ($errors as $error) {
					echo "ERROR: {$group}{$error}" . PHP_EOL;
				}
			}
		}
		exit(1);
	}

}

// Debugging
Tester::init();
try {

	// Load
	$tests = glob('./*.test.php');
	foreach ($tests as $test) {
		/** @noinspection PhpIncludeInspection */
		require $test;
	}

} catch (Exception $ex) {
	/** @noinspection PhpUnhandledExceptionInspection */
	Tester::handle($ex);
}

// Output
Tester::end();
