<?php
/**
 * [collectionanalytic.php]
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

class CollectionAnalytic extends Entity implements JsonSerializable {

    protected $collectionId;
    protected $schemaId;
    protected $schemaVersion;
    protected $userEmailDomain;
    protected $collectionCreatedAt;
    protected $collectionDeletedAt;

    public function __construct() {
        $this->addType('collectionId', 'integer');
    }

    public function jsonSerialize() {
        return [
            'id' => $this->id,
            'collectionId' => $this->collectionId,
            'schemaId' => $this->schemaId,
            'schemaVersion' => $this->schemaVersion,
            'userOrganisation' => $this->userEmailDomain,
            'collectionCreatedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', $this->collectionCreatedAt)->format('c'),
            'collectionDeletedAt' => \DateTime::createFromFormat('Y-m-d H:i:s', $this->collectionDeletedAt)->format('c')
        ];
    }

    public function __toString() {
        return "CollectionAnalytic[id=$this->id]";
    }

}