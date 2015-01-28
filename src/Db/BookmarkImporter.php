<?php

/**
 * This file contains the class BookmarkImporter
 * 
 * @author birkeg
 */
namespace Birke\PinThisDay\Db;

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
    
    public function importBookmarks($bookmarks, $userId)
    {
        $this->db->beginTransaction();
        $this->db->delete('btags', ["user_id" => $userId]);
        $this->db->delete('bookmarks', ["user_id" => $userId]);
        foreach ($bookmarks as $bookmark) {
            try {
                $this->insertBookmark($bookmark, $userId);
            } catch (\Doctrine\DBAL\Exception\DriverException $e) {
                error_log($e->getMessage());
                if (!$this->ignoreErrors) {
                    $this->db->rollBack();
                    return;
                }
            }
        }
        $this->db->update("users", [
                "last_update" => date("Y-m-d H:i:s")
            ],
            ["id" => $userId]
        );
        $this->db->commit();
    }

    protected function insertBookmark(\PinboardBookmark $bookmark, $userId) {
        $createdAt = date("Y-m-d H:i:s", $bookmark->timestamp);
        $data = [
            "user_id" => $userId,
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
                "user_id" => $userId,
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
