<?php

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Birke\PinThisDay\PinboardApi;
use Birke\PinThisDay\UserManager;
use Birke\PinThisDay\BookmarkImporter;

class ImportAllCommand extends Command
{
    /**
     * @var Birke\PinThisDay\PinboardApi
     */
    protected $api;

    /**
     *
     * @var AuthConfig
     */
    protected $authConfig;

    protected function configure()
    {
        $this
            ->setName('import:all')
            ->setDescription('Import all bookmarks for a user')
          
        ;
        $this->authConfig = new AuthConfig;
        $this->authConfig->configureCommand($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            list($user, $apiKey) = $this->authConfig->getCredentials($input, false);
            $apiToken = "$user:$apiKey";
        } catch (\RuntimeException $ex) {
            $output->writeln($ex->getMessage());
            return;
        }
        $this->initApi($apiToken);
        
        $dsn = getenv("DB_DSN");
        if (!$dsn) {
            throw new \RuntimeException("Please configure DB in DB_DSN");
        }
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'url' => $dsn,
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
        $manager = new UserManager($conn);
        $userId = $manager->getOrCreateUserId($user, $apiKey);
        // use "get" instead of "get_all" for testing, so we don't run into the rate limit
        $bookmarks = $this->api->get();
        //$bookmarks = $this->api->get_all();
        $importer = new BookmarkImporter($conn);
        $importer->importBookmarks($bookmarks, $userId);
    }
    
    
    protected function initApi($apiKey)
    {
        $this->api = new PinboardApi(null, $apiKey);
    }
}
