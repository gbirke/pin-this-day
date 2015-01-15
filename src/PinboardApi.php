<?php

/**
 * This file contains the class PinboardApi
 * 
 * @author birkeg
 */

namespace Birke\PinThisDay;

use \PinboardAPI as BaseAPI;
use Doctrine\Common\Cache\Cache;

/**
 * PinboardApi modifies a few key methods of the original Pinboard API
 *
 * @author birkeg
 */
class PinboardApi extends BaseAPI
{
    protected $nextAllowedCall = 0;

    /**
     * @var Doctrine\Common\Cache\Cache
     */
    public $postsCache;

    /**
     * Cache JSON result for 6 minutes for all similar calls.
     *
     * @param int $count
     * @param int $offset
     * @param array|string $tags
     * @param string|int $from
     * @param string|int $to
     * @return array
     */
    public function get_all($count = null, $offset = null, $tags = null, $from = null, $to = null)
    {
        if (!$this->postsCache) {
            return parent::get_all($count, $offset, $tags, $from, $to);
        }

        $args = array();
        if (!is_null($count) && $count > 0) $args['results'] = (int)$count;
        if (!is_null($offset) && $offset > 0) $args['start'] = (int)$offset;
        if (!is_null($tags) && !empty($tags)) $args['tag'] = $this->_normalize_tags($tags);
        if (!is_null($from)) $args['fromdt'] = $this->_to_datetime($from);
        if (!is_null($to)) $args['todt'] = $this->_to_datetime($to);

        $cacheId = "pinboard_json_".md5($this->_user.$this->_pass.implode("", $args));
        if (!$this->postsCache->contains($cacheId)) {
            $json = $this->_remote('posts/all', $args);
            $this->postsCache->save($cacheId, $json, 360);
        } else {
            $json = $this->postsCache->fetch($cacheId);
        }
        return $this->_json_to_bookmark($json);
    }

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
