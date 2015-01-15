<?php

/**
 * This file contains the class AuthConfig
 * 
 * @author birkeg
 */

namespace Birke\PinThisDay\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Configure username and Password for a command class 
 * and get an auth string
 *
 * @author birkeg
 */
class AuthConfig
{
    public function configureCommand(Command $command)
    {
        $command->addOption(
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
        ;
    }
    
    public function getApiKeyFromInput(InputInterface $input)
    {
        $user = $input->getOption('user');
        $apiKey = $input->getOption('api_key');
        if (!$user || !$apiKey) {
            throw new \RuntimeException("Missing credentials.");
        }
        return "$user:$apiKey";
    }
}
