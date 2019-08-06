<?php
/**
 * [collectionanalyticmapper.php]
 * Collections - Research data packaging for the rest of us
 * Copyright (C) 2017 Intersect Australia Ltd (https://intersect.org.au)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\collections\Mapper;


use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

use OCA\collections\Entity\CollectionAnalytic;

/**
 * Class CollectionAnalytic
 * Responsible for tracking analytic statistics regarding new collections.
 * @package OCA\collections\Mapper
 */
class CollectionAnalyticMapper extends Mapper {

    /**
     * CollectionAnalyticMapper constructor.
     * @param IDBConnection $db Instance of the Db abstraction layer
     */
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'crateit_collection_analytics', 'OCA\collections\Entity\CollectionAnalytic');
    }

    /**
     * Creates a collection analytic entity within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_insert
     * @param CollectionAnalytic $object the entity that should be created
     * @return CollectionAnalytic the saved entity
     */
    public function newCollectionAnalytic(CollectionAnalytic $object) {
        \OCP\Util::writeLog('collections', __METHOD__."($object)", \OCP\Util::DEBUG);
        return $this->insert($object);
    }

    /**
     * Updates a collection analytic entry within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_update
     * @param CollectionAnalytic $object entity to update the database with
     * @return CollectionAnalytic the updated entity
     */
    public function updateCollectionAnalytic(CollectionAnalytic $object) {
        \OCP\Util::writeLog('collections', __METHOD__."($object)", \OCP\Util::DEBUG);
        return $this->update($object);
    }

    /**
     * Finds a collection analytic entity within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_findEntity
     * @param integer $id id of the entity to find
     * @return CollectionAnalytic the fetched entity
     */
    public function find($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_collection_analytics` WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    /**
     * Finds the collection analytic associated with a collection id
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_findEntities
     * @param string $collection_id id of the collection
     * @return null|CollectionAnalytic the fetched entity
     */
    public function find_by_collection_id($collection_id) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection_id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_collection_analytics` WHERE `collection_id` = ?';
        try {
            return $this->findEntity($sql, [$collection_id]);
        } catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }
    }

    /**
     * Deletes a collection analytic entity from the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_delete
     * @param CollectionAnalytic $object the entity to delete
     * @return CollectionAnalytic the deleted entity
     */
    public function deleteCollectionAnalytic(CollectionAnalytic $object) {
        \OCP\Util::writeLog('collections', __METHOD__."($object)", \OCP\Util::DEBUG);
        return $this->delete($object);
    }
}