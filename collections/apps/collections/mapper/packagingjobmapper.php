<?php
/**
 * [packagingjobmapper.php]
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

use OCA\collections\Entity\PackagingJob;

/**
 * Class PackagingJob
 * Responsible for performing DB operations on packaging_jobs.
 * @package OCA\collections\Mapper
 */
class PackagingJobMapper extends Mapper {

    /**
     * PackagingJobMapper constructor.
     * @param IDBConnection $db Instance of the Db abstraction layer
     */
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'crateit_packaging_jobs', 'OCA\collections\Entity\PackagingJob');
    }

    /**
     * Creates a packaging job entity within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_insert
     * @param PackagingJob $packagingJob the entity that should be created
     * @return PackagingJob the saved entity
     */
    public function newPackagingJob(PackagingJob $packagingJob) {
        \OCP\Util::writeLog('collections', __METHOD__."($packagingJob)", \OCP\Util::DEBUG);
        return $this->insert($packagingJob);
    }

    /**
     * Finds a packaging job entity within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_findEntity
     * @param integer $id id of the entity to find
     * @return PackagingJob the fetched entity
     */
    public function find($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_packaging_jobs` WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    /**
     * Finds a set of packaging jobs associated with a given user id
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_findEntities
     * @param string $userId id of the user
     * @return null|array all fetched entities
     */
    public function find_by_user_id($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_packaging_jobs` WHERE `user_id` = ? ORDER BY `initiation_time` DESC';
        try {
            return $this->findEntities($sql, [$userId]);
        } catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }
    }

    /**
     * Deletes a packaging job entity from the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_delete
     * @param PackagingJob $packagingJob the entity to delete
     * @return PackagingJob the deleted entity
     */
    public function deletePackagingJob(PackagingJob $packagingJob) {
        \OCP\Util::writeLog('collections', __METHOD__."($packagingJob)", \OCP\Util::DEBUG);
        return $this->delete($packagingJob);
    }

    /**
     * Updates a packaging job entry within the database
     * @link https://doc.owncloud.org/api/classes/OCP.AppFramework.Db.Mapper.html#method_update
     * @param PackagingJob $packagingJob packaging job entity to update the database with
     * @return PackagingJob the updated packaging job entity
     */
    public function updatePackagingJob(PackagingJob $packagingJob) {
        \OCP\Util::writeLog('collections', __METHOD__."($packagingJob)", \OCP\Util::DEBUG);
        return $this->update($packagingJob);
    }
}