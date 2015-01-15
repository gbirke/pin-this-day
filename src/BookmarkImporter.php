<?php

/**
 * This file contains the class BookmarkImporter
 * 
 * @author birkeg
 */
namespace Birke\PinThisDay;

use \Doctrine\DBAL\Connection;

/**
 * Description of BookmarkImporter
 *
 * @author birkeg
 */
class BookmarkImporter
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
    
    public function importBookmarks($bookmarks, $userid)
    {
        $this->db->beginTransaction();
        /* @var $bookmark \PinboardBookmark */
        foreach ($bookmarks as $bookmark) {
            $createdAt = date("Y-m-d H:i:s", $bookmark->timestamp);
            $data = [
                "user_id" => $userid,
                "url" => $bookmark->url,
                "title" => $bookmark->title,
                "description" => $bookmark->description,
                "toread" => $bookmark->is_unread,
                "private" => !$bookmark->is_public,
                "created_at" => $createdAt,
                "meta" => $bookmark->meta,
                "hash" => $bookmark->hash,
            ];
            $this->db->insert("bookmarks", $data);
            $bookmarkId = $this->db->lastInsertId();
            foreach ($bookmark->tags as $seq => $tag) {
                $data = [
                    "user_id" => $userid,
                    "bookmark_id" => $bookmarkId,
                    "created_at" => $createdAt,
                    "private" => !$bookmark->is_public,
                    "tag" => $tag,
                    "seq" => $seq
                ];
                $this->db->insert("btags", $data);
            }
        }
        $this->db->commit();
        
    }
}
