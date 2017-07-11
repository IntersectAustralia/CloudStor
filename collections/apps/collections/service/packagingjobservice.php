<?php
/**
 * [packagingjobservice.php]
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
use OCA\collections\Entity\PackagingJob;
use OCA\collections\Mapper\PackagingJobMapper;

class PackagingJobService {

    public function __construct(PackagingJobMapper $packagingJobMapper) {
        $this->packagingJobMapper = $packagingJobMapper;
    }

    /**
     * Creates a new packaging job for a given collection
     * @param Crate $collection Collection that is to be packaged
     * @return PackagingJob The newly created packaging job
     */
    public function newPackagingJob(Crate $collection) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection)", \OCP\Util::DEBUG);
        $packagingJob = new PackagingJob();
        $packagingJob->setUserId($collection->getUserId());
        $packagingJob->setCollectionId($collection->getId());
        $packagingJob->setCollectionName($collection->getName());
        $packagingJob->setInitiationTime(date('c')); // Store as ISO 8601 formatted date
        $packagingJob->setStatus('Starting to package');
        $this->packagingJobMapper->newPackagingJob($packagingJob);
        return $packagingJob;
    }

    /**
     * Gets a list of packaging jobs for the given user
     * @param string $userId id of the user
     * @return array|null all packaging jobs associated with the user id
     */
    public function getPackagingJobs($userId) {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        return $this->packagingJobMapper->find_by_user_id($userId);
    }

    /**
     * Updates the status of a given packaging job
     * @param PackagingJob $packagingJob Packaging job to update
     * @param string $status Message to set as job status
     */
    public function updatePackagingJobStatus(PackagingJob $packagingJob, $status) {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        $packagingJob->setStatus($status);
        $this->packagingJobMapper->updatePackagingJob($packagingJob);
    }
}