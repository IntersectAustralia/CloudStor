<?php
/**
 * [crate.php]
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

class Crate extends Entity implements JsonSerializable {

    protected $userId;
    protected $name;
    protected $size;
    protected $metadataSchema;
    protected $savedMetadata;
    protected $rootFolderId;

    private $rootFolder;
    private $guid;

    public function __construct() {
        $this->addType('size', 'integer');
        $this->addType('rootFolderId', 'integer');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'name' => $this->name,
            'size' => $this->size,
            'metadataSchema' => $this->metadataSchema,
            'savedMetadata' => $this->savedMetadata,
            'rootFolderId' => $this->rootFolderId,
            'rootFolder' => $this->rootFolder
        ];
    }

    public function __toString() {
        return "Crate[id=$this->id]";
    }

    /**
     * Generates a Globally Unique Identifier (GUID) for the lifespan of a Crate instance.
     *
     * @return string
     */
    public function getGUID() {
        if(!$this->guid) {
            $time = date("Y-m-d\TH-i-sO");
            $this->guid = uniqid("$time-$this->name-");
        }
        return $this->guid;
    }

    public function getRootFolder() {
        return $this->rootFolder;
    }

    public function setRootFolder(Folder $rootFolder) {
        $this->rootFolder = $rootFolder;
        $this->setRootFolderId($rootFolder->getId());
    }

    public function incrementSize($size) {
        $this->setSize($this->size + $size);
    }

    public function decrementSize($size) {
        $this->setSize($this->size - $size);
    }
}