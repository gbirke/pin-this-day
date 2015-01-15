<?php

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Birke\PinThisDay\PinboardApi;

class ImportCommand extends Command
{
    /**
     * @var Birke\PinThisDay\PinboardApi
     */
    protected $api;


    protected function configure()
    {
        $this
            ->setName('import:date')
            ->setDescription('Import bookmarks for a specific day and user')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'pinboard.in user name'
            )

            ->addOption(
                'api_key',
                'a',
                InputOption::VALUE_REQUIRED,
                'pinboard.in API key'
            )

            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Date for which to look. Format YYYY-MM-DD'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getOption('user');
        $apiKey = $input->getOption('api_key');
        if (!$user || !$apiKey) {
            $output->writeln("Missing credentials.");
            return;
        }
        $this->initApi("$user:$apiKey");
        $date = new \DateTime($input->getOption("date"));
        try {
            $dates = $this->getDates($date);
        } catch (\RuntimeException $ex) {
            $output->writeln($ex->getMessage());
        }
        
        
        
        $output->writeln("Dates:".implode(", ", $dates));
    }
    
    protected function importBookmarksFromDates($dates)
    {
        // Delete old Bookmarks on dates
        // Insert new Bookmarks
    }
    
    protected function getDates(\DateTime $date)
    {
        $postDates = $this->api->get_dates();
        if (count($postDates) < 1) {
            throw new \RuntimeException("Not enough bookmarks stored in pinboard account");
        }
        $firstdate = new \DateTime(array_pop($postDates));
        $dates = array();
        while ($firstdate <= $date) {
            $dates[] = $date->format("Y-m-d");
            $date->modify("-1 year");
        }
        return $dates;
    }
    
    protected function initApi($apiKey)
    {
        $this->api = new PinboardApi(null, $apiKey);
    }
}
