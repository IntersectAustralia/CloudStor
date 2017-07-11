<?php
/**
 * [fileutilities.php]
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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use OCA\collections\Entity\Crate;
use OCA\collections\Entity\File;
use OCA\collections\Entity\Folder;
use OCA\collections\lib\Util;

/**
 * Class FileUtilities performs utility functions.
 * @package OCA\collections\Service
 */

class FileUtilities {

    /**
     * Removes a directory including all its nested files and folders.
     * @param $path - Path of the directory to remove
     */
    public static function removeDirectory($path) {
        \OCP\Util::writeLog('collections', __METHOD__."($path)", \OCP\Util::DEBUG);
        $iterator = new RecursiveDirectoryIterator(realpath($path), RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $filePath = realpath($file);
            if (is_dir($filePath)) {
                rmdir($filePath);
            } elseif (is_file($filePath)) {
                unlink($filePath);
            }
        }
        rmdir($path);
    }

    /**
     * Copies the contents of crate to the given filesystem destination path
     * @param Crate $fullCrate - The crate to copy
     * @param $destination - Filesystem path to store the copy
     * @param FileService $fileService - File Service instance to use for copying files
     */
    public static function copyCrate(Crate $fullCrate, $destination, FileService $fileService) {
        \OCP\Util::writeLog('collections', __METHOD__."($fullCrate, $destination, FileService)", \OCP\Util::DEBUG);
        FileUtilities::copyCrateFolder($fullCrate->getRootFolder(), $destination, $fileService);
    }

    /**
     * Copies files from OwnCloud to a destination on the filesystem, maintaining the tree structure of the given folder.
     * @param Folder $folder - Folder whose tree structure should be copied
     * @param $destination - Target destination of the tree copy
     * @param FileService $fileService - File Service instance to use for copying files
     * @throws \Exception - Thrown if unable to make directory on filesystem
     */
    private static function copyCrateFolder(Folder $folder, $destination, FileService $fileService) {
        \OCP\Util::writeLog('collections', __METHOD__."($folder, $destination, FileService)", \OCP\Util::DEBUG);
        if (!file_exists($destination)) {
            $madeDir = mkdir($destination, 0755, true);
            if ($madeDir === false) {
                throw new \Exception('Unable to make directory '.$destination);
            }
        }
        $subFolders = $folder->getFolders();
        foreach($subFolders as $subFolder) {
            FileUtilities::copyCrateFolder($subFolder, Util::joinPaths($destination, $subFolder->getName()), $fileService);
        }
        $files = $folder->getFiles();
        foreach($files as $file) {
            FileUtilities::copyCrateFile($file, Util::joinPaths($destination, $file->getName()), $fileService);
        }
    }

    /**
     * Copies a file in chunks from OwnCloud to the given destination on the filesystem.
     * @param File $file - File to copy
     * @param $destination - Target destination of the copy
     * @param FileService $fileService - File Service instance to use for copying the file
     * @throws \Exception - Thrown if unable to add copy file to destination
     */
    private static function copyCrateFile(File $file, $destination, FileService $fileService) {
        \OCP\Util::writeLog('collections', __METHOD__."($file, $destination, FileService)", \OCP\Util::DEBUG);
        $srcHandle = $fileService->fopen($file->getOwncloudId(), 'r');
        $destHandle = fopen($destination, 'w');
        $bytesCopied = stream_copy_to_stream($srcHandle, $destHandle);
        fclose($srcHandle);
        fclose($destHandle);
        if ($bytesCopied === false) {
            throw new  \Exception('Unable to copy crate file to '.$destination);
        }
    }
}