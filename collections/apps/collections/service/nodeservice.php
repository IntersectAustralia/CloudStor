<?php
/**
 * [nodeservice.php]
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

use \OCA\collections\lib\CollectionsException;

/**
 * See {@link http://api.owncloud.org/classes/OCP.Files.File.html}
 * Class NodeService performs actions on owncloud files and folders.
 * @package OCA\collections\Service
 */

class NodeService {

    private $userFolder;

    public function __construct($ServerContainer, $UserId) {
        $this->userFolder = $ServerContainer->getUserFolder($UserId);
    }

    /**
     * Gets the type of the node corresponding to a filepath.
     * @param $filePath mixed - The path of the filesystem node to check the type of.
     * @return \OCP\Files\FileInfo::TYPE_FILE | \OCP\Files\FileInfo::TYPE_FOLDER
     */
    private function getType($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $node = $this->getNode($filePath);
        $type = $node->getType();
        return $type;
    }

    /**
     * Checks if a filepath corresponds to a file.
     * @param $filePath mixed - The path of the filesystem node to check the type of.
     * @return boolean true if filePath corresponds to a file, false if it corresponds to a folder
     */
    public function isFile($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $type = $this->getType($filePath);
        return $type == \OCP\Files\FileInfo::TYPE_FILE;
    }

    /**
     * Checks if a filepath corresponds to a folder.
     * @param $filePath mixed - The path of the filesystem node to check the type of.
     * @return boolean true if filePath corresponds to a folder, false if it corresponds to a file
     */
    public function isFolder($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $type = $this->getType($filePath);
        return $type == \OCP\Files\FileInfo::TYPE_FOLDER;
    }

    /**
     * Obtains the name of a node.
     * @param $filePath mixed - The path to obtain the name for.
     * @return mixed The name of the filepath node.
     */
    public function getName($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        $file = $this->getNode($filePath);
        $name = $file->getName();
        return $name;
    }

    /**
     * Get the full mime type of the node i.e. 'image/png'
     * @param string $filePath path of the node
     * @return mixed mime type i.e. 'image/png'
     */
    public function getMimeType($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        return $this->getNode($filePath)->getMimetype();
    }

    /**
     * Obtains the path of a node relative to the user folder.
     * @param $filePath mixed - The path to obtain the relative path for.
     * @return mixed The path relative to the user folder
     */
    public function getRelativePath($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        return $this->userFolder->getRelativePath($filePath);
    }

    /**
     * Gets the OwnCloud internal file id for the file or folder
     * @param $filePath - The path to get the Id for
     * @return int - The id
     */
    public function getOwnCloudId($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        return $this->getNode($filePath)->getId();
    }

    /**
     * Get a file or folder inside the user folder by it's internal id
     * @param $owncloudId - OwnCloud internal id
     * @return \OCP\Files\Node
     * @throws CollectionsException if no node is found or if multiple nodes are found
     */
    public function getById($owncloudId) {
        \OCP\Util::writeLog('collections', __METHOD__."($owncloudId)", \OCP\Util::DEBUG);
        $nodes = $this->userFolder->getById($owncloudId);
        if (sizeof($nodes) == 0) {
            throw new CollectionsException("No nodes found matching owncloud id ".$owncloudId);
        } elseif (sizeof($nodes) > 1) {
            throw new CollectionsException("More than one node found matching owncloud id ".$owncloudId);
        }
        return $nodes[0];
    }

    /**
     * Gets an owncloud node given a path relative to the current user folder.
     * @see http://api.owncloud.org/classes/OCP.Files.Folder.html#get
     * @param $filePath - The path of the node to get
     * @return \OCP\Files\Node - The owncloud node
     */
    protected function getNode($filePath) {
        \OCP\Util::writeLog('collections', __METHOD__."($filePath)", \OCP\Util::DEBUG);
        return $this->userFolder->get($filePath);
    }

    /**
     * Get a view to user's files folder
     * @return \OCP\Files\Folder - The user folder
     */
    protected function getUserFolder() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        return $this->userFolder;
    }

}