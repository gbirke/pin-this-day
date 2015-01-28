<?php

/**
 * This file contains the class DbSchema
 * 
 * @author birkeg
 */

namespace Birke\PinThisDay\Db;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Description of DbSchema
 *
 * @author birkeg
 */
class DbSchema
{
    /**
     *
     * @var Schema
     */
    protected $schema;

    public function getSchemaSql(AbstractPlatform $platform)
    {
        $this->schema = new Schema;
        $this->createUserTable();
        $this->createBookmarkTable();
        $this->createTagTable();
        return $this->schema->toSql($platform);
    }
    
    protected function createBookmarkTable()
    {
        $table = $this->schema->createTable("bookmarks");
        $table->addColumn('id', "integer", array("unsigned" => true));
        $table->addColumn('url', "text");
        $table->addColumn('title', "string", array("notnull" => false));
        $table->addColumn('description', "text", array("notnull" => false));
        $table->addColumn('user_id', "integer", array("unsigned" => true));
        $table->addColumn('toread', "boolean", array("default" => 0));
        $table->addColumn('private', "boolean", array("default" => 0));
        $table->addColumn('meta', "string", array("length" => 32, "notnull" => false));
        $table->addColumn('hash', "string", array("length" => 32, "notnull" => false));
        $table->addColumn('created_at', "datetime", array("notnull" => false));
        $table->setPrimaryKey(array('id'));
        #$table->addUniqueIndex(array('user_id', 'url')); No index until http://www.doctrine-project.org/jira/browse/DBAL-404 is fixed
        $table->addIndex(array('created_at'));
        $table->addIndex(array('private'));
        $table->addIndex(array('user_id'));
        $table->addIndex(array('user_id', 'created_at'));
    }
    
    protected function createTagTable()
    {
        $table = $this->schema->createTable("btags");
        $table->addColumn('id', "integer", array("unsigned" => true));
        $table->addColumn('user_id', "integer", array("unsigned" => true));
        $table->addColumn('bookmark_id', "integer", array("unsigned" => true));
        $table->addColumn('tag', "string", array("notnull" => false));
        $table->addColumn('created_at', "datetime", array("notnull" => false));
        $table->addColumn('private', "boolean", array("default" => 0));
        $table->addColumn('seq', "smallint", array("notnull" => false));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('user_id', 'bookmark_id', 'tag'));
        $table->addIndex(array('user_id'));
        $table->addIndex(array('tag'));
        $table->addIndex(array('private'));
        $table->addIndex(array('bookmark_id'));
        $table->addIndex(array('user_id', 'tag'));
    }
    
    protected function createUserTable()
    {
        $table = $this->schema->createTable("users");
        $table->addColumn('id', "integer", array("unsigned" => true));
        $table->addColumn('login', "string", array("notnull" => false, "length" => 40));
        $table->addColumn('password', "string", array("notnull" => false, "length" => 60));
        $table->addColumn('api_key', "string", array("notnull" => false, "length" => 60));
        $table->addColumn("last_update", "datetime", array("notnull" => false));
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('login'));
    }
}
