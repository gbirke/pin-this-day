<?php

/**
 * This file contains the class UserManager
 * 
 * @author birkeg
 */
namespace Birke\PinThisDay;

use \Doctrine\DBAL\Connection;

/**
 * Description of UserManager
 *
 * @author birkeg
 */
class UserManager
{
   /**
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;
    
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    public function getOrCreateUserId($username, $apiKey)
    {
        $ids = $this->db->fetchAll("SELECT id FROM users WHERE login = ?", array($username));
        if (!$ids) {
            $this->db->insert('users', array(
                'login' => $username,
                'password' => null,
                "api_key" => $apiKey
            ));
            return $this->db->lastInsertId();
        } else {
            return $ids[0]["id"];
        }
    }
}
