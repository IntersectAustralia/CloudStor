<?php
/**
 * [folderservice.php]
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

/**
 * See {@link http://api.owncloud.org/classes/OCP.Files.Folder.html}
 * Class FolderService performs actions on owncloud folders.
 * @package OCA\collections\Service
 */
class FolderService extends NodeService {

    public function __construct($ServerContainer, $UserId)
    {
        parent::__construct($ServerContainer, $UserId);
    }

    /**
     * Obtains the contents of a folder.
     * @param $folderPath mixed - The path of the file to obtain the contents of.
     * @return mixed An array containing the path of each file in the folder
     */
    public function getContents($folderPath) {
        \OCP\Util::writeLog('collections', __METHOD__."($folderPath)", \OCP\Util::DEBUG);
        $paths = array();
        $folder = $this->getNode($folderPath);
        $contents = $folder->getDirectoryListing();

        foreach($contents as $node) {
            $path = $node->getPath();
            $relativePath = $this->getRelativePath($path);
            array_push($paths, $relativePath);
        }
        return $paths;
    }

}