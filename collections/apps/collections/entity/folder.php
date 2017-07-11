<?php
/**
 * [folder.php]
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

namespace OCA\collections\Entity;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Folder extends Entity implements JsonSerializable {

    protected $name;
    protected $parentFolderId;

    protected $folders = [];
    protected $files = [];

    public function __construct() {
        $this->addType('size', 'integer');
        $this->addType('parentFolderId', 'integer');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parentFolderId' => $this->parentFolderId,
            'files' => $this->files,
            'folders' => $this->folders
        ];
    }

    public function __toString() {
        return "Folder[id=$this->id]";
    }

    public function addFile(File $file) {
        array_push($this->files, $file);
        usort($this->files, array($this, "compareName"));
    }

    public function addFolder(Folder $folder) {
        array_push($this->folders, $folder);
        usort($this->folders, array($this, "compareName"));
    }

    // Compares array elements by object name attribute
    private function compareName($a, $b) {
        return strcmp($a->getName(), $b->getName());
    }
}