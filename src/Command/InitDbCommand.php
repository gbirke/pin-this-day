<?php

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Birke\PinThisDay\Db\DbSchema;

class InitDbCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('initdb')
            ->setDescription('Initialize DB schema')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dsn = getenv("DB_DSN");
        if (!$dsn) {
            throw new \RuntimeException("Please configure DB in DB_DSN");
        }
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'url' => $dsn,
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $dbPlatform = $conn->getSchemaManager()->getDatabasePlatform();
        $schema = new DbSchema();
        $queries = $schema->getSchemaSql($dbPlatform);
        foreach ($queries as $q) {
            //$output->writeln($q);
            $conn->exec($q);
        }
        if ($dbPlatform->getName() == "mysql") {
            // Add auto-increments, see http://www.doctrine-project.org/jira/browse/DBAL-1118
            foreach(["bookmarks", "btags", "users"] as $table) {
                $conn->exec("ALTER TABLE `$table` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT");
            }
        }
        $conn->close();
    }
}
