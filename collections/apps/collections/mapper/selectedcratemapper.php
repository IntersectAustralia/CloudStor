<?php
/**
 * [selectedcratemapper.php]
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

use OCA\collections\Entity\SelectedCrate;

/**
 * Class SelectedCrateMapper
 * Responsible for performing DB operations on selected_crates.
 * @package OCA\collections\Mapper
 */
class SelectedCrateMapper extends Mapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'crateit_selected_crates', 'OCA\collections\Entity\SelectedCrate');
    }

    public function newSelectedCrate(SelectedCrate $selectedCrate) {
        \OCP\Util::writeLog('collections', __METHOD__."($selectedCrate)", \OCP\Util::DEBUG);
        return $this->insert($selectedCrate);
    }

    public function find($id) {
        \OCP\Util::writeLog('collections', __METHOD__."($id)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_selected_crates` WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    public function find_by_user_id($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        $sql = 'SELECT * FROM `*PREFIX*crateit_selected_crates` WHERE `user_id` = ?';
        try {
            return $this->findEntity($sql, [$userId]);
        } catch(\OCP\AppFramework\Db\DoesNotExistException $e) {
            return null;
        }
    }

    public function deleteSelectedCrate(SelectedCrate $selectedCrate)
    {
        \OCP\Util::writeLog('collections', __METHOD__."($selectedCrate)", \OCP\Util::DEBUG);
        return $this->delete($selectedCrate);
    }
}