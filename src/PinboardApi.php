<?php

/**
 * This file contains the class PinboardApi
 * 
 * @author birkeg
 */

namespace Birke\PinThisDay;

use \PinboardAPI as BaseAPI;

/**
 * Description of PinboardApi
 *
 * @author birkeg
 */
class PinboardApi extends BaseAPI
{
    protected $nextAllowedCall = 0;


    /**
     * Rate limit automatically
     * 
     * @param type $method
     * @param type $args
     * @param type $use_json
     * @return type
     */
    protected function _remote($method, $args = array(), $use_json = true)
    {
        switch ($method) {
            case "posts/all":
                $waitSeconds = 5 * 60;
                break;
            case "posts/recent":
                $waitSeconds = 60;
                break;
            default:
                $waitSeconds = 3;
        }
        $now = time();
        if ($now < $this->nextAllowedCall) {
            sleep($this->nextAllowedCall - $now);
        }
        $this->nextAllowedCall = $now + $waitSeconds;
        return parent::_remote($method, $args, $use_json);
    }
}
