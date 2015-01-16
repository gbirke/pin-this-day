<?php

/**
 * This file contains the class UserQuery
 * 
 * @author birkeg
 */
namespace Birke\PinThisDay\Db;

use \Doctrine\DBAL\Connection;

/**
 * UserQuery contains all DB queries concerning the users table
 *
 * @author birkeg
 */
class UserQuery
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

    public function getIdForUsername($name)
    {
        return $this->db->fetchColumn("SELECT id FROM users WHERE login = ?", array($name));
    }
}
