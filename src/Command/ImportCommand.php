<?php

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import')
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
        $api = new \PinboardAPI(null, "$user:$apiKey");
        $postDates = $api->get_dates();
        $lastdate = array_shift($postDates);
        $firstdate = array_pop($postDates);
        $dates = array();
        

        $output->writeln(sprintf("first: %s, last: %s", $firstdate, $lastdate));
    }

}