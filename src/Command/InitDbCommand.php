<?php

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Birke\PinThisDay\DbSchema;

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
        $schema = new DbSchema();
        $queries = $schema->getSchemaSql($conn->getSchemaManager()->getDatabasePlatform());
        foreach ($queries as $q) {
            $output->writeln($q);
            $conn->exec($q);
        }
        $conn->close();
    }
}
