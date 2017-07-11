<?php
/**
 * [filemapper.php]
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

use OCA\collections\Entity\File;

/**
 * Class FolderMapper
 * Responsible for performing DB operations on files.
 * @package OCA\collections\Mapper
 */
class FileMapper extends Mapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'crateit_files', 'OCA\collections\Entity\File');
    }

    public function newFile(File $file) {
        \OCP\Util::writeLog('collections', __METHOD__."($file)", \OCP\Util::DEBUG);
        return $this->insert($file);
    }

    public function find($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_files` WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    public function find_all_with_parent_id($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_files` WHERE `parent_folder_id` = ?';
        return $this->findEntities($sql, [$id]);
    }

    /**
     * Finds all files with parent id AND name (case insensitive)
     */
    public function find_all_with_parent_id_and_name($id, $name) {
        \OCP\Util::writeLog('collections', __METHOD__."($id, $name)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_files` WHERE `parent_folder_id` = ? AND UPPER(`name`) = UPPER(?)';
        return $this->findEntities($sql, [$id, $name]);
    }

    public function deleteFile(File $file) {
        \OCP\Util::writeLog('collections', __METHOD__."($file)", \OCP\Util::DEBUG);
        return $this->delete($file);
    }

    public function updateFile(File $file) {
        \OCP\Util::writeLog('collections', __METHOD__."($file)", \OCP\Util::DEBUG);
        return $this->update($file);
    }
}