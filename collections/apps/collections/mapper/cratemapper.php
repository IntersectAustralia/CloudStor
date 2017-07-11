<?php
/**
 * [cratemapper.php]
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

use OCA\collections\Entity\Folder;
use OCA\collections\lib\CollectionsException;
use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

use OCA\collections\Entity\Crate;


use OCA\collections\Config;
use OCA\collections\lib\Util;

/**
 * Class CrateMapper
 * Responsible for performing DB operations on crates.
 * @package OCA\collections\Mapper
 */
class CrateMapper extends Mapper {

    private $folderMapper;

    public function __construct(IDBConnection $db, FolderMapper $folderMapper) {
        parent::__construct($db, 'crateit_crates', 'OCA\collections\Entity\Crate');
        $this->folderMapper = $folderMapper;
    }

    public function find($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_crates` WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    public function findByName($name, $userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($name, $userId)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_crates` WHERE `name` = ? AND `user_id` = ?';
        return $this->findEntities($sql, [$name, $userId]);
    }

    public function getCratesForUser($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_crates` WHERE `user_id` = ?';
        return $this->findEntities($sql, [$userId]);
    }

    /**
     * @param Crate $crate string - Name of the collection
     * @param $schemaPath string - $schemaPath string - The path to the selected metadata schema
     * @return the saved entity with the set id
     */
    public function newCrate(Crate $crate, $schemaPath) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate)", \OCP\Util::DEBUG);
        // Construct the root folder & persist it
        $rootFolder = new Folder();
        $rootFolder->setName('root_folder');
        $rootFolder = $this->folderMapper->newFolder($rootFolder);
        $crate->setRootFolder($rootFolder);
        $schema = $this->getMetadataSchema($schemaPath);
        $crate->setMetadataSchema($schema);
        return $this->insert($crate);
    }

    public function updateCrate(Crate $crate) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate)", \OCP\Util::DEBUG);
        return $this->update($crate);
    }

    public function deleteCrate(Crate $crate) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate)", \OCP\Util::DEBUG);
        return $this->delete($crate);
    }

    /**
     * @param $schemaPath string - The path to the selected metadata schema
     * @return string - The unparsed JSON contents of the specified metadata schema
     * @throws CollectionsException If it fails to read the metadata schema
     */
    private function getMetadataSchema($schemaPath) {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        $contents = file_get_contents($schemaPath);
        if(!$contents) {
            throw new CollectionsException("Unable to read metadata schema: {$schemaPath}");
        }
        return $contents;
    }

}