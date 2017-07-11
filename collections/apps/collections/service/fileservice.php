<?php
/**
 * [fileservice.php]
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

use OCA\collections\Entity\File;

/**
 * See {@link http://api.owncloud.org/classes/OCP.Files.File.html}
 * Class FileService performs actions on owncloud files.
 * @package OCA\collections\Service
 */
class FileService extends NodeService {

    public function __construct($ServerContainer, $UserId) {
        parent::__construct($ServerContainer, $UserId);
    }

    /**
     * Obtains the md5 checksum of a file.
     * @param $owncloudId mixed - Owncloud internal id of the file
     * @return mixed The md5 checksum of the file
     */
    public function getChecksum($owncloudId) {
        \OCP\Util::writeLog('collections', __METHOD__."($owncloudId)", \OCP\Util::DEBUG);
        $file = $this->getById($owncloudId);
        $md5 = $file->hash('md5');
        return $md5;
    }

    /**
     * Opens a file as a stream. Note: the stream must be closed after use e.g. fclose($handle)
     * See {@link http://php.net/manual/en/function.fopen.php} for accepted mode types.
     * @see http://api.owncloud.org/classes/OCP.Files.File.html#fopen
     * @param $owncloudId - Owncloud internal id of the file
     * @param $mode - The type of access required to the stream
     * @return resource
     */
    public function fopen($owncloudId, $mode) {
        \OCP\Util::writeLog('collections', __METHOD__."($owncloudId, $mode)", \OCP\Util::DEBUG);
        $file = $this->getById($owncloudId);
        $handle = $file->fopen($mode);
        return $handle;
    }

    /**
     * Crates a new file relative to root directory of the OwnCloud user.
     *   Uses a non-existing name if the provided path already exists.
     * @param $filePath - Path to the new file
     * @return \OCP\Files\File - The new file
     */
    public function newFile($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $userFolder = $this->getUserFolder();
        if ($userFolder->nodeExists($filePath)) {
            $filePath = $userFolder->getNonExistingName($filePath);
        }
        return $userFolder->newFile($filePath);
    }

    /**
     * Gets the path of a file identified by its Owncloud internal id
     * @param $owncloudId - Owncloud internal id of the file
     * @return mixed - Owncloud path to the file
     * @throws \Exception - See {@see getFileById}
     */
    public function getPath($owncloudId) {
        \OCP\Util::writeLog('collections', __METHOD__."($owncloudId)", \OCP\Util::DEBUG);
        return $this->getById($owncloudId)->getPath();
    }

    /**
     * Checks whether the Cr8It file is exists within the user's ownCloud folder.
     * @param File $file - The file to check
     * @return bool - True if the file exists, false otherwise.
     */
    public function fileInUserFolder(File $file) {
        $nodes = $this->getUserFolder()->getById($file->getOwncloudId());
        if (sizeof($nodes) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Gets the modified time of a file identified by its Owncloud internal id
     * @param $owncloudId - Owncloud internal id of the file
     * @return mixed - file modified time in unix timestamp format
     * @throws \Exception - See {@see getFileById}
     */
    public function getModifiedTime($owncloudId) {
        \OCP\Util::writeLog('collections', __METHOD__."($owncloudId)", \OCP\Util::DEBUG);
        return $this->getById($owncloudId)->getMtime();
    }

    /**
     * Obtains the size of the file.
     * @param $filePath mixed - The path of the file to obtain the size for.
     * @return mixed The file size
     */
    public function getSize($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $file = $this->getNode($filePath);
        $size = $file->getSize();
        return $size;
    }

}
