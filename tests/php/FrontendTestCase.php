<?php

namespace FjordTest;

use Exception;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\SupportsChrome;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Orchestra\Testbench\Dusk\TestCase as OrchestraDuskTestCase;

class FrontendTestCase extends OrchestraDuskTestCase
{
    use SupportsChrome, FjordTestCase;

    /**
     * The base serve host URL to use while testing the application.
     *
     * @var string
     */
    protected static $baseServeHost = '127.0.0.1';

    /**
     * The base serve port to use while testing the application.
     *
     * @var int
     */
    protected static $baseServePort = 8000;

    /**
     * Register the base URL with Dusk.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Browser::$baseUrl = $this->baseUrl();
        Browser::$storeScreenshotsAt = __DIR__ . '/../resources/screenshots';
        Browser::$storeConsoleLogAt = __DIR__ . '/../resources/console';
        Browser::$storeSourceAt = __DIR__ . '/../resources/source';

        Browser::$userResolver = function () {
            return $this->user();
        };

        $this->migrate();
    }

    /**
     * Get sqlite database.
     *
     * @return string
     */
    protected function getDatabase()
    {
        return database_path('database.sqlite');
    }

    /**
     * Get package provider
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array_merge(
            [
                \Laravel\Dusk\DuskServiceProvider::class
            ],
            static::$packageProviders
        );
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Determine the application's base URL.
     *
     * @var string
     *
     * @return string
     */
    protected function baseUrl()
    {
        return \sprintf('http://%s:%d', static::$baseServeHost, static::$baseServePort);
    }

    /**
     * Return the default user to authenticate.
     *
     * @return \App\User|int|null
     *
     * @throws \Exception
     */
    protected function user()
    {
        throw new Exception('User resolver has not been set.');
    }


    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver(['port' => 9515]);
    }

    /**
     * Begin a server for the tests.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        static::serve(static::$baseServeHost, static::$baseServePort);
    }

    /**
     * Kill our server.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        static::stopServing();
    }
}
