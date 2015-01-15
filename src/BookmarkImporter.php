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

    public $ignoreErrors = true;
    
    public function __construct(Connection $db, $ignoreErrors = true)
    {
        $this->db = $db;
        $this->ignoreErrors = $ignoreErrors;
    }
    
    public function importBookmarks($bookmarks, $userid)
    {
        $this->db->beginTransaction();
        foreach ($bookmarks as $bookmark) {
            try {
                $this->insertBookmark($bookmark, $userid);
            } catch (\Doctrine\DBAL\Exception\DriverException $e) {
                error_log($e->getMessage());
                if (!$this->ignoreErrors) {
                    $this->db->rollBack();
                    return;
                }
            }
        }
        $this->db->commit();
    }

    protected function insertBookmark(\PinboardBookmark $bookmark, $userid) {
        $createdAt = date("Y-m-d H:i:s", $bookmark->timestamp);
        $data = [
            "user_id" => $userid,
            "url" => $bookmark->url,
            "title" => $bookmark->title,
            "description" => $bookmark->description,
            "toread" => intval($bookmark->is_unread),
            "private" => intval(!$bookmark->is_public),
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
                "private" => intval(!$bookmark->is_public),
                "tag" => $tag,
                "seq" => $seq
            ];
            $this->db->insert("btags", $data);
        }
    }
}
