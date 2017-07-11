<?php
/**
 * [bagitservice.php]
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
use OCA\collections\Entity\Folder;
use OCA\collections\lib\Util;
use OCA\collections\lib\CollectionsException;
use OCA\collections\service\TemplateService;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class BagItService performs bagging of Crates, conforming to the BagIt File Packaging Format (V0.97).
 * @link http://tools.ietf.org/html/draft-kunze-bagit-13
 *
 * @package OCA\collections\Service
 */

class BagItService {

    /**
     * Bags a Crate and serialises it as a ZIP, conforming to the BagIt File Packaging Format (V0.97).
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13
     * @param Crate $crate The crate to bag
     * @param string $cratePath Path to a copy of the crate on the filesystem
     * @param string $destination Destination to store created BagIt ZIP archive
     * @throws CollectionsException Thrown if unable to create the ZIP archive
     * @return string - Path to the created BagIt ZIP archive
     */
    public static function bagCrate(Crate $crate, $cratePath, $destination) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate, $cratePath, $destination)", \OCP\Util::DEBUG);
        $collectionName = $crate->getName();
        $zipPath = Util::joinPaths($destination, $collectionName . '.zip');
        $zip = new ZipArchive;
        $response = $zip->open($zipPath, ZipArchive::CREATE);
        if ($response === TRUE) {
            self::addBagItDeclaration($zip, $collectionName);
            $manifestContent = self::addGUIDFile($zip, $collectionName, $crate->getGUID());
            $manifestContent .= self::addReadmeFile($zip, $crate);
            $manifestContent .= self::bagCrateMetadata($zip, $collectionName, $crate);
            $manifestContent .= self::generatePayloadManifestContent($crate);
            self::addPayloadManifest($zip, $collectionName, $manifestContent);
            self::addPayloadDirectory($zip, $collectionName, $cratePath);
            $zip->close();
        } else {
            throw new CollectionsException('Unable to create '.$destination.' '.$response);
        }
        return $zipPath;
    }

    /**
     * Adds the GUID file to the payload directory and returns corresponding line to be added to the payload manifest.
     * @param ZipArchive $zip An open ZIP file archiving the BagIt structure
     * @param string $collectionName Name of the collection
     * @param string $guid Globally Unique Identifier (GUID) for the lifespan of a Crate instance
     * @return string Content to be added to payload manifest
     */
    private static function addGUIDFile(ZipArchive $zip, $collectionName, $guid) {
        \OCP\Util::writeLog('collections', __METHOD__."(ZipArchive, $guid)", \OCP\Util::DEBUG);
        $relativePath = Util::joinPaths('data', "$guid.guid");
        $path = Util::joinPaths($collectionName, $relativePath);
        self::addToZipFromString($zip, $path, $guid, 'Unable to package GUID file');
        return $checksum = md5($guid) . " $relativePath\n";
    }

    /**
     * Adds the README file to the payload directory and returns corresponding line to be added to the payload manifest.
     * @param ZipArchive $zip An open ZIP file archiving the BagIt structure
     * @param Crate $collection The collection being bagged
     * @return string Content to be added to payload manifest
     */
    private static function addReadmeFile(ZipArchive $zip, $collection) {
        $relativePath = Util::joinPaths('data', 'README.html');
        $path = Util::joinPaths($collection->getName(), $relativePath);
        $readme = CrateService::readmeFileContent($collection);
        self::addToZipFromString($zip, $path, $readme, 'Unable to package README file');
        return $checksum = md5($readme) . " $relativePath\n";
    }

    /**
     * Adds crate metadata to payload directory and returns corresponding line to be added to the payload manifest.
     * @param ZipArchive $zip An open ZIP file archiving the BagIt structure
     * @param string $baseDirectory The base directory of the BagIt structure
     * @param Crate $crate The crate being bagged
     * @return string Content to be added to payload manifest
     * @throws CollectionsException If unable bag crate metadata
     */
    private static function bagCrateMetadata(ZipArchive $zip, $baseDirectory, Crate $crate) {
        \OCP\Util::writeLog('collections', __METHOD__."(ZipArchive, $baseDirectory, $crate)", \OCP\Util::DEBUG);
        // Generate md5 hash of metadata file
        $metadataContent = CrateService::crateMetadataToXML($crate, true);
        $metadataFileName = $crate->getName() . '.xml';
        $tempFile = tmpfile();
        fwrite($tempFile, $metadataContent);
        $tempFileMetadata = stream_get_meta_data($tempFile);
        $tempFilePath = $tempFileMetadata["uri"];
        $metadataChecksum = md5_file($tempFilePath);
        fclose($tempFile);
        if ($metadataChecksum === False) {
            throw new CollectionsException('Unable to generate checksum for metadata file');
        }

        // Add metadata file to payload directory
        $metadataPayloadPath = Util::joinPaths('data', $metadataFileName);
        $baggedMetadataPath = Util::joinPaths($baseDirectory, $metadataPayloadPath);
        BagItService::addToZipFromString($zip, $baggedMetadataPath, $metadataContent, 'Unable to package metadata file');
        return $metadataChecksum . " " . $metadataPayloadPath . "\n";
    }

    /**
     * Adds a BagIt declaration to the ZIP archive for a crate being bagged.
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13#section-2.1.1
     * @param ZipArchive $zip - An open ZIP file archiving the BagIt structure
     * @param $baseDirectory - The base directory of the BagIt structure
     *  @throws CollectionsException - Thrown if unable to add bagIt declaration to zip
     */
    private static function addBagItDeclaration(ZipArchive $zip, $baseDirectory) {
        \OCP\Util::writeLog('collections', __METHOD__."(ZipArchive, $baseDirectory)", \OCP\Util::DEBUG);
        $content = "BagIt-Version: 0.97\nTag-File-Character-Encoding: UTF-8";
        $path = Util::joinPaths($baseDirectory, 'bagit.txt');
        BagItService::addToZipFromString($zip, $path, $content, 'Unable to package BagIt declaration');
    }

    /**
     * Adds a payload directory to the ZIP archive for a crate being bagged.
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13#section-2.1.2
     * @param ZipArchive $zip - An open ZIP file archiving the BagIt structure
     * @param $baseDirectory - The base directory of the BagIt structure
     * @param $copyDirectory - Filesystem path to the copy of the crate
     * @throws CollectionsException - Thrown if $copyDirectory is not a directory, or unable to zip a file or directory
     */
    private static function addPayloadDirectory(ZipArchive $zip, $baseDirectory, $copyDirectory)  {
        \OCP\Util::writeLog('collections', __METHOD__."(ZipArchive, $baseDirectory, $copyDirectory)", \OCP\Util::DEBUG);
        if (is_file($copyDirectory)) {
            throw new CollectionsException('BagIt base directory must be a directory and not a file');
        }
        $payloadDirectory = Util::joinPaths($baseDirectory, 'data', 'CollectionData');
        $copyDirectory = realpath($copyDirectory);
        $iterator = new RecursiveDirectoryIterator($copyDirectory, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $filePath = realpath($file);
            if (is_dir($filePath)) {
                $bagItPath = str_replace($copyDirectory, $payloadDirectory, $filePath . '/');
                $response = $zip->addEmptyDir($bagItPath);
            } else if (is_file($filePath)) {
                $bagItPath = str_replace($copyDirectory, $payloadDirectory, $filePath);
                $response = $zip->addFile($filePath, $bagItPath);
            } else {
                $response = False;
            }
            if ($response === False) {
                throw new CollectionsException('Unable to package all files in the collection');
            }
        }
    }

    /**
     * Generates the content of the payload manifest for the crate being bagged.
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13#section-2.1.3
     * @param Crate $fullCrate - The crate to bag
     * @return string - Path of manifest file within bag
     */
    private static function generatePayloadManifestContent(Crate $fullCrate) {
        \OCP\Util::writeLog('collections', __METHOD__."($fullCrate)", \OCP\Util::DEBUG);
        $checksums = BagItService::getChecksums($fullCrate->getRootFolder(), Util::joinPaths('data', 'CollectionData'));
        $manifestContent = "";
        foreach ($checksums as $filename => $checksum) {
            $manifestContent .= $checksum . " " . $filename . "\n";
        }
        return $manifestContent;
    }

    /**
     * Adds a payload manifest to the ZIP archive for the crate being bagged.
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13#section-2.1.3
     * @param ZipArchive $zip - An open ZIP file archiving the BagIt structure
     * @param $baseDirectory - The base directory of the BagIt structure
     * @param string $manifestContent - The payload manifest content
     * @throws CollectionsException - Thrown if unable to add payload manifest
     */
    private static function addPayloadManifest(ZipArchive $zip, $baseDirectory, $manifestContent) {
        \OCP\Util::writeLog('collections', __METHOD__."(ZipArchive, $baseDirectory, $manifestContent)", \OCP\Util::DEBUG);
        $manifestPath = Util::joinPaths($baseDirectory, 'manifest-md5.txt');
        BagItService::addToZipFromString($zip, $manifestPath, $manifestContent, 'Unable to package BagIt payload manifest');
    }

    /**
     * Gets the checksum of each file within the given Folder of a Crate.
     * @param Folder $folder - Crate folder to get checksums of
     * @param $payloadPath - Path of $folder within the BagIt payload directory
     * @return array - Key/value array containing filename/checksum for each file
     */
    private static function getChecksums(Folder $folder, $payloadPath){
        \OCP\Util::writeLog('collections', __METHOD__."($folder, $payloadPath)", \OCP\Util::DEBUG);
        $checksums = array();
        // Recurse SubFolders
        $subFolders = $folder->getFolders();
        foreach($subFolders as $subFolder) {
            $path = Util::joinPaths($payloadPath, $subFolder->getName());
            $checksums = array_merge($checksums, BagItService::getChecksums($subFolder, $path));
        }
        // Store name and checksum of each file
        $files = $folder->getFiles();
        foreach($files as $file) {
            $path = Util::joinPaths($payloadPath, $file->getName());
            $checksums[$path] = $file->getChecksum();
        }
        return $checksums;
    }

    /**
     * Adds a file to a Zip archive from string content, raising an exception if unable to add to the Zip.
     * @param ZipArchive $zip - An open ZIP file archiving the BagIt structure
     * @param string $path - Path to add the file to within the Zip, including the filename and extension
     * @param string $content - Content to add into the file upon creation
     * @param string $errorMsg - Error message to include in exception if unable to add file to zip.
     * @throws CollectionsException - Thrown if unable to add file to the zip.
     */
    private static function addToZipFromString(ZipArchive $zip, $path, $content, $errorMsg) {
        \OCP\Util::writeLog('collections', __METHOD__."($zip->filename, $path, $content, $errorMsg)", \OCP\Util::DEBUG);
        $response = $zip->addFromString($path, $content);
        if ($response === False) {
            throw new CollectionsException($errorMsg);
        }
    }
}
