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

namespace OCA\collections\Service;

use OCA\collections\Entity\Crate;
use OCA\collections\Entity\CollectionAnalytic;
use OCA\collections\Mapper\CollectionAnalyticMapper;
use OCA\collections\lib\OCCommonAPI;

class CollectionAnalyticService {

    public function __construct(CollectionAnalyticMapper $collectionAnalyticMapper) {
        $this->collectionAnalyticMapper = $collectionAnalyticMapper;
    }

    /**
     * Creates a new collection analytic for a given collection
     * @param Crate $collection Collection that is to be tracked
     * @return CollectionAnalytic The created Collection Analytic object
     */
    public function newCollectionAnalytic(Crate $collection) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection)", \OCP\Util::DEBUG);
        $collectionAnalytic = new CollectionAnalytic();
        $collectionAnalytic->setCollectionId($collection->getId());
        $collectionAnalytic->setCollectionCreatedAt(date('c')); // Store as ISO 8601 formatted date

        $metadataSchema = json_decode($collection->getMetadataSchema(), true);
        $schemaId = $metadataSchema['id'];
        $schemaVersion = $metadataSchema['version'];
        $collectionAnalytic->setSchemaId($schemaId);
        $collectionAnalytic->setSchemaVersion($schemaVersion);

        // CloudStor stores user email address as display name
        $this->setEmailDomain($collectionAnalytic);

        $this->collectionAnalyticMapper->newCollectionAnalytic($collectionAnalytic);
        return $collectionAnalytic;
    }

    /**
     * Sets the deleted_at timestamp of the Collection Analytic record to the current timestamp (ISO 8601)
     * @param Mixed $collection_id id of the collection that is being deleted
     */
    public function setDeletionTimestamp($collection_id) {
        $collectionAnalytic = $this->collectionAnalyticMapper->find_by_collection_id($collection_id);
        if (!empty($collectionAnalytic)) {
            $collectionAnalytic->setCollectionDeletedAt(date('c'));
            $this->collectionAnalyticMapper->updateCollectionAnalytic($collectionAnalytic);
        }
    }

    /**
     * Sets the user email domain on the collection analytic based on the logged in user's display name. If the display
     *  name isn't in email format (does not contain an @) then the user email address is used
     * @param CollectionAnalytic $collectionAnalytic
     */
    private function setEmailDomain(CollectionAnalytic $collectionAnalytic) {
        $userDisplayName = OCCommonAPI::getDisplayName();
        if (!empty($userDisplayName)) {
            $str = strrchr($userDisplayName, "@");
            if ($str) {
                $domain_name = substr($str, 1);
                $collectionAnalytic->setUserEmailDomain($domain_name);
            } else {
                $this->setEmailDomainFromUserEmail($collectionAnalytic);
            }
        }
    }

    /**
     * Sets the user email domain on the collection analytic based on the logged in user's email address
     * @param CollectionAnalytic $collectionAnalytic
     */
    private function setEmailDomainFromUserEmail(CollectionAnalytic $collectionAnalytic) {
        $userEmail = OCCommonAPI::getEMailAddress();
        if (!empty($userEmail)) {
            $str = strrchr($userEmail, "@");
            if ($str) {
                $domain_name = substr($str, 1);
                $collectionAnalytic->setUserEmailDomain($domain_name);
            }
        }
    }
}