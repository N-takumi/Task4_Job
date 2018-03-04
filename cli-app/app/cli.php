<?php
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require_once '../../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

define('SQL_PASS',getenv('SQL_PASS'));

    // Using the CLI factory default services container
    $di = new CliDI();

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';


    // Create a console application
    $console = new ConsoleApp();

    $console->setDI($di);

    $di->setShared("console", $console);
    /**
    * Process the console arguments
    */
    $arguments = [];

foreach ($argv as $k => $arg) {
    if ($k === 1) {
        $arguments['task'] = $arg;
    } elseif ($k === 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}


try {
    // Handle incoming arguments
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    // Do Phalcon related stuff here
    // ..
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
} catch (\Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
