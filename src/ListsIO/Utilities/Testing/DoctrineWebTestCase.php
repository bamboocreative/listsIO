<?php
/**
 * Created by PhpStorm.
 * User: jesserosato
 * Date: 2/17/14
 * Time: 12:30 AM
 */

namespace ListsIO\Utilities\Testing;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

// Code adapted from http://dev4theweb.blogspot.com/2012/07/yet-another-look-at-isolated-symfony2.html
class DoctrineWebTestCase extends WebTestCase
{
    protected static $entityManager;
    protected static $client;
    protected static $application;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        static::$client = static::createClient();

        $this->databaseInit();
        $this->loadFixtures();
    }

    /**
     * Load tests fixtures
     */
    protected function loadFixtures()
    {
        $this->runConsole("doctrine:fixtures:load");
    }

    /**
     * Initialize database
     */
    protected function databaseInit()
    {
        static::$entityManager = static::$kernel
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        static::$application = new Application(static::$kernel);

        static::$application->setAutoExit(false);
        $this->runConsole("doctrine:schema:drop", array("--force" => true));
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("cache:warmup");
    }

    /**
     * Executes a console command
     *
     * @param type $command
     * @param array $options
     * @return type integer
     */
    protected function runConsole($command, Array $options = array())
    {
        $options["--env"] = "test";
        $options["--quiet"] = null;
        $options["--no-interaction"] = null;
        $options = array_merge($options, array('command' => $command));
        return static::$application->run(new ArrayInput($options));
    }
}