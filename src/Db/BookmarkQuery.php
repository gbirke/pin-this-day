<?php
/**
 * Created by PhpStorm.
 * User: gbirke
 * Date: 16.01.15
 * Time: 10:07
 */

namespace Birke\PinThisDay\Db;

use Doctrine\DBAL\Connection;

class BookmarkQuery {

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;


    function __construct(\Doctrine\DBAL\Connection $db)
    {
        $this->db = $db;
    }

    public function getBookmarks($date, $userId)
    {
        // TODO Pagination
        $sql = "SELECT url, title, description, toread,
            GROUP_CONCAT(DISTINCT tag ORDER BY seq ASC SEPARATOR ' ') AS tags,
            YEAR(b.created_at) AS `year`, UNIX_TIMESTAMP(b.created_at) AS ts,
            b.created_at
            FROM bookmarks b
            JOIN btags t ON b.id = t.bookmark_id
            WHERE b.user_id = ?
                  AND YEAR(b.created_at) < ?
                  AND MONTH(b.created_at) = ?
                  AND DAY(b.created_at) = ?
                  AND b.private = 0
            GROUP by url
            ORDER BY b.created_at DESC
            LIMIT 100
        ";
        list($year, $month, $day) = explode("-", $date);
        return $this->db->fetchAll($sql, [$userId, $year, $month, $day]);
    }

} 