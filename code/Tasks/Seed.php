<?php

use LittleGiant\SilverStripeSeeder\CliOutputFormatter;
use LittleGiant\SilverStripeSeeder\Util\Check;
use LittleGiant\SilverStripeSeeder\Util\RecordWriter;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Seed
 */
class Seed extends CliController
{
    protected $title = "Seed the database";

    protected $description = "Populate the database with placeholder content.";

    /**
     *
     */
    function process()
    {
        $app = new Application();
        $app->add(new SeedCommand());
        $app->run();
    }
}

class SeedCommand extends Command
{
    protected function configure()
    {
        $this->setName('seed')
            ->setDescription('Seed database')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Choose class to seed')
            ->addOption('key', 'k', InputOption::VALUE_OPTIONAL, 'Choose key to seed')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Ignore current seeds when calculating how many to create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!Check::fileToUrlMapping()) {
            die('ERROR: Please set a valid path in $_FILE_TO_URL_MAPPING before running the seeder' . PHP_EOL);
        }

        // Customer overrides delete to check for admin

        // major hack to enable ADMIN permissions
        // login throws cookie warning, this will hide the error message
        error_reporting(0);
        try {
            if ($admin = Member::default_admin()) {
                $admin->logIn();
            }
        } catch (Exception $e) {
        }
        error_reporting(E_ALL);

        global $argv;

        $seeder = new Seeder(new RecordWriter(), new CliOutputFormatter());

        $className = $input->getOption('class');
        $key = $input->getOption('key');

        if ($input->getOption('force')) {
            $seeder->setIgnoreSeeds(true);
        }

        $seeder->seed($className, $key);
    }
}
