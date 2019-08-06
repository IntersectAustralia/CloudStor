<?php
/**
 * [crateservice.php]
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

use OCA\collections\Config;
use OCA\collections\Entity\Crate;
use OCA\collections\Entity\File;
use OCA\collections\Entity\Folder;
use OCA\collections\Entity\PackagingJob;
use OCA\collections\Entity\SelectedCrate;
use OCA\collections\Mapper\SelectedCrateMapper;
use OCA\collections\Mapper\CrateMapper;
use OCA\collections\Mapper\FolderMapper;
use OCA\collections\Mapper\FileMapper;
use OCA\collections\lib\Util;
use OCA\collections\lib\CollectionsException;

class CrateService {

    private $crateMapper;
    private $folderMapper;
    private $folderService;
    private $fileMapper;
    private $fileService;
    private $selectedCrateMapper;
    private $packagingJobService;
    private $templateService;
    private $collectionAnalyticService;

    public function __construct(SelectedCrateMapper $selectedCrateMapper, CrateMapper $crateMapper,
                                FolderMapper $folderMapper, FolderService $folderService, FileMapper $fileMapper,
                                FileService $fileService, PackagingJobService $packagingJobService,
                                TemplateService $templateService, CollectionAnalyticService $collectionAnalyticService) {
        $this->selectedCrateMapper = $selectedCrateMapper;
        $this->crateMapper = $crateMapper;
        $this->folderMapper = $folderMapper;
        $this->folderService = $folderService;
        $this->fileMapper = $fileMapper;
        $this->fileService = $fileService;
        $this->packagingJobService = $packagingJobService;
        $this->templateService = $templateService;
        $this->collectionAnalyticService = $collectionAnalyticService;
    }

    /**
     * Gets the currently selected crate for the user. If there are no crates for the user, a 'default_crate' will be created.
     * @param $userId String - ID of the user
     * @return Crate - The user's currently selected crate
     */
    public function getSelectedCrate($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        $selectedCrate =  $this->selectedCrateMapper->find_by_user_id($userId);
        if($selectedCrate == null) {
            $newCrate = $this::createDefaultCrate($userId);
            $defaultSchemaPath = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, Config::$DEFAULT_SCHEMA_PATH);
            $defaultCrate = $this->crateMapper->newCrate($newCrate, $defaultSchemaPath);
            $this->setSelectedCrate($selectedCrate, $userId,$defaultCrate->getId());
            return $defaultCrate;
        }else {
            return $this->crateMapper->find($selectedCrate->getCrateId());
        }
    }

    /**
     * Gets all the user's crates.
     * @param $userId String - The ID of the user
     * @return array(Crate) - The crates belonging to the user
     */
    public function getAllCrates($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        return $this->crateMapper->getCratesForUser($userId);
    }

    /**
     * Updates an existing crate.
     * @param $crateId mixed - ID of the crate to update
     * @param $fields array - An array of fields to update the crate with
     * @param $userId - The ID of the user
     * @return Crate - The updated crate
     * @throws CollectionsException - If the crate could not be updated
     */
    public function updateCrate($crateId, $fields, $userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($crateId, " . print_r($fields, TRUE) . ")", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($crateId);
        if (array_key_exists('name', $fields) ) {
            $fields['name'] = trim($fields['name']);
            if (!empty($this->crateMapper->findByName($fields['name'], $userId))) {
                $error = 'Name '.$fields['name'].' has already been taken.';
                throw new CollectionsException($error);
            } elseif (strlen($fields['name']) > 128){
                $error = 'Name '.$fields['name'].' exceeds length limit(128 characters)';
                throw new CollectionsException($error);
            }
        }
        $this::updateCrateFromFields($crate, $fields);
        return $this->crateMapper->updateCrate($crate);
    }

    /**
     * Saves crate metadata, overwriting any previously saved value
     * @param $crateId
     * @param $metadata
     */
    public function saveCollectionMetadata($crateId, $metadata) {
        \OCP\Util::writeLog('collections', __METHOD__."($crateId)", \OCP\Util::DEBUG);
        $collection = $this->crateMapper->find($crateId);
        // ToDo: validate all metadata satisfies schema validation criteria and handle if not satisfied
        $collection->setSavedMetadata(json_encode($metadata));
        $this->crateMapper->updateCrate($collection);
    }

    /**
     * Updates an existing crate tree after one of its nodes is moved
     * @param $newParentId - new parentFolderId for the moved node
     * @param $nodeType - file or folder
     * @param $nodeId - id of moved node
     * @throws CollectionsException - If the node could not be moved.
     */
    public function moveNode($nodeId, $nodeType, $newParentId) {
        \OCP\Util::writeLog('collections', __METHOD__."($nodeId, $nodeType, $newParentId)", \OCP\Util::DEBUG);
        $mapper = $this->nodeMapper($nodeType);
        $changed_node = $mapper->find($nodeId);
        $name = $changed_node->getName();
        if (!empty($mapper->find_all_with_parent_id_and_name($newParentId, $name))) {
            $error = "Unable to move ".$name." due to name conflict.";
            throw new CollectionsException($error);
        }
        $changed_node->setParentFolderId($newParentId);
        return $mapper->update($changed_node);
    }

    /**
     * Rename a node in a crate
     * @param $nodeId - id for the renamed node
     * @param $nodeType - file or folder
     * @param $newName - the new name
     * @return File|Folder - The renamed node
     * @throws CollectionsException - If the node could not be renamed.
     */
    public function renameNode($nodeId, $nodeType, $newName) {
        \OCP\Util::writeLog('collections', __METHOD__."($nodeId, $nodeType,  $newName)", \OCP\Util::DEBUG);
        $mapper = $this->nodeMapper($nodeType);
        $node = $mapper->find($nodeId);
        if (!empty($mapper->find_all_with_parent_id_and_name($node->getParentFolderId(), $newName))) {
            $error = 'Name '.$newName.' has already been taken.';
            throw new CollectionsException($error);
        }
        $changed_node = $mapper->find($nodeId);
        $changed_node->setName($newName);
        return $mapper->update($changed_node);
    }

    /**
     * Deletes a node in a crate.
     * If the node is a folder, it will recursively delete all nodes inside of the folder.
     * @param $nodeId - id of the node to be deleted
     * @param $nodeType - file or folder
     */
    public function deleteNode($crateId, $nodeId, $nodeType) {
        \OCP\Util::writeLog('collections', __METHOD__."($crateId, $nodeId, $nodeType)", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($crateId);

        if ($nodeType == 'folder') {
            $folder = $this->folderMapper->find($nodeId);
            $bytesDeleted = $this->clearFolder($folder, true);
        } else {
            $file = $this->fileMapper->find($nodeId);
            $this->fileMapper->deleteFile($file);
            $bytesDeleted = $file->getSize();
        }
        $crate->decrementSize($bytesDeleted);
        $this->crateMapper->updateCrate($crate);
        return $crate;
    }

    /**
     * Creates a new crate
     * @param $userId mixed - The owner of the crate
     * @param $name string - The name of the new crate
     * @param $schemaPath string - The path to the selected metadata schema
     * @return Crate - The new crate
     * @throws CollectionsException - If the crate could not be created
     */
    public function createCrate($userId, $name, $schemaPath) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId, $name)", \OCP\Util::DEBUG);
        if (!empty($this->crateMapper->findByName($name, $userId))) {
            $error = 'Name '.$name.' has already been taken.';
            throw new CollectionsException($error);
        } elseif (strlen($name) > 128){
            $error = 'Name '.$name.' exceeds length limit(128 characters)';
            throw new CollectionsException($error);
        }
        $crate = $this::innerCreateCrate($userId, $name);
        $newCrate = $this->crateMapper->newCrate($crate, $schemaPath);
        $this->collectionAnalyticService->newCollectionAnalytic($newCrate);
        $this->updateSelectedCrate($userId,$newCrate->getId());
        return $newCrate;
    }

    /**
     * Obtains a crate from its ID.
     * @param $crate_id mixed - The ID of the crate to lookup.
     * @return Crate - The crate.
     */
    public function getCrateById($crate_id) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate_id)", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($crate_id);
        return $crate;
    }

    /**
     * Deletes a crate
     * @param $crate_id mixed - The ID of the crate to delete.
     * @return Crate - The deleted crate.
     */
    public function deleteCrate($crate_id) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate_id)", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($crate_id);
        $rootFolder = $this->folderMapper->find($crate->getRootFolderId());
        $this->clearFolder($rootFolder, true);
        $deletedEntity = $this->crateMapper->deleteCrate($crate);
        $this->collectionAnalyticService->setDeletionTimestamp($crate_id);
        return $deletedEntity;
    }

    /**
     * Removes all files from a collection
     * @param integer $collection_id id of the collection to remove all files from
     * @return Crate The collection with all files removed
     */
    public function removeAllFiles($collection_id) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection_id)", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($collection_id);
        $rootFolder = $this->folderMapper->find($crate->getRootFolderId());
        $this->clearFolder($rootFolder, false);
        $crate->setSize(0);
        return $this->crateMapper->updateCrate($crate);
    }

    /**
     * Adds a file path item to a crate
     * @param Crate $crate - The crate to add a file path to
     * @param mixed $name - The file path that should be added to the crate
     * @throw CollectionsException - if add fails
     */
    public function addPathToCrate(Crate $crate, $name) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate, $name)", \OCP\Util::DEBUG);
        $this->recursiveAddToCrate($crate, $name, $crate->getRootFolderId());
    }

    /**
     * Gets the full object representation of a crate
     * @param $crateId - The id of the crate to get.
     * @return Crate - The full crate
     */
    public function getFullCrate($crateId) {
        \OCP\Util::writeLog('collections', __METHOD__."($crateId)", \OCP\Util::DEBUG);
        $crate = $this->crateMapper->find($crateId);
        $rootFolder = $this->folderMapper->find($crate->getRootFolderId());
        $crate->setRootFolder($rootFolder);
        $this->populateFolder($rootFolder);
        return $crate;
    }

    /**
     * Packages a collection as a Zip archive, conforming to the BagIt File Packaging Format (V0.97).
     * @link http://tools.ietf.org/html/draft-kunze-bagit-13
     * @param Crate $collection Collection to package
     * @param PackagingJob $packagingJob Packaging job corresponding to this packaging operation
     * @return string Path to the created Zip archive
     */
    public function packageCollection(Crate $collection, PackagingJob $packagingJob) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection, $packagingJob)", \OCP\Util::DEBUG);
        $this->packagingJobService->updatePackagingJobStatus($packagingJob,
            '(1/4) Creating temporary copy');
        $tempCrateDirectory =  Util::joinPaths(Util::getTempPath(), $collection->getUserId(), microtime());
        $copyDir = Util::joinPaths($tempCrateDirectory, $collection->getName());
        FileUtilities::copyCrate($collection, $copyDir, $this->fileService);
        $this->packagingJobService->updatePackagingJobStatus($packagingJob,
            '(2/4) Packaging temporary copy');
        $zipPath = BagItService::bagCrate($collection, $copyDir, $tempCrateDirectory);
        $this->packagingJobService->updatePackagingJobStatus($packagingJob,
            '(3/4) Cleaning up temporary copy');
        FileUtilities::removeDirectory($copyDir);
        return $zipPath;
    }

    /**
     * Packages/Self-publishes a collection to the user's OwnCloud folder
     * @param Crate $collection  The collection to publish
     * @param PackagingJob $packagingJob Packaging job corresponding to this packaging operation
     * @return string Path of the packaged crate within the ownCloud folder
     * @throws CollectionsException thrown if unable to copy packaged collection to ownCloud Files (such as when user quota limit is reached)
     */
    public function publishCollection(Crate $collection, PackagingJob $packagingJob, $destinationFolder) {
        \OCP\Util::writeLog('collections', __METHOD__."($collection, $packagingJob)", \OCP\Util::DEBUG);
        $zipPath = $this->packageCollection($collection, $packagingJob);
        $this->packagingJobService->updatePackagingJobStatus($packagingJob,
            '(4/4) Moving package to Files');
        $destination = Util::joinPaths($destinationFolder, basename($zipPath));
        $newFile = $this->fileService->newFile($destination);
        $newFileHandle = $this->fileService->fopen($newFile->getId(), 'w'); // uses owncloud id
        $zipHandle = fopen($zipPath, 'r');
        $bytesCopiedToNewFile = stream_copy_to_stream($zipHandle, $newFileHandle);
        fclose($newFileHandle);
        fclose($zipHandle);
        unlink($zipPath);
        rmdir(dirname($zipPath));
        $newFile->touch(); // Hack to update the file size within OwnCloud by touching the file to trigger a scan
        if ($bytesCopiedToNewFile === false) {
            $newFile->delete();
            throw new CollectionsException('Please review your quota and ensure there is enough space for the '.
                'Collection before you reattempt to package again.');
        }
        return $newFile->getId();
    }

    /**
     * Adds a new, empty folder to a crate
     * @param $parentFolderId - The id of the folder that should be the parent of this new folder.
     * @param $folderName - The name of the new folder
     * @return Folder - newly created folder
     * @throws CollectionsException - If the file could not be created.
     */
    public function addFolderToCrate($parentFolderId, $folderName) {
        $clashingFolders = $this->folderMapper->find_all_with_parent_id_and_name($parentFolderId, $folderName);
        $clashingFiles = $this->fileMapper->find_all_with_parent_id_and_name($parentFolderId, $folderName);
        if (!empty($clashingFolders) || !empty($clashingFiles)) {
            throw new CollectionsException("File or Folder with name '$folderName' already exists");
        }
        return $this->createNewFolder($folderName, $parentFolderId);
    }

    /**
     * Checks the internal consistency of each file in the given crate
     * @param Crate $fullCrate - The full crate to publish
     * @return array - key/value array containing crate validity and validity of each file in the crate: ['crateValid' => boolean, 'filesValid' => [filePathN => boolean, filePathN+1 => boolean, ...]]
     */
    public function checkCrate(Crate $fullCrate) {
        \OCP\Util::writeLog('collections', __METHOD__."($fullCrate)", \OCP\Util::DEBUG);
        $filesValid = $this->checkCrateFolder($fullCrate->getRootFolder(), null, true);
        $crateValid = true;
        foreach($filesValid as $fileId => $isValid) {
            if (!$isValid) {
                $crateValid = false;
                break;
            }
        }
        return array('crateValid' => $crateValid, 'filesValid' => $filesValid);
    }

    /**
     * Get the user Id of the given crate
     * @param $crateId - The full crate to publish
     * @return string userId - The user_id of the given crate
     */
    public function getCrateOwner($crateId) {
        $crate = $this->crateMapper->find($crateId);
        return $crate->getUserId();
    }

    /**
     * Validates the crate metadata, checking that all mandatory fields contain a saved value
     * @param Crate $crate - the crate with the saved metadata to validate
     * @return array - key/value array containing the metadata validity 'metadataValid' and a set of invalid fields 'invalidFields'
     */
    public function validateMetadata(Crate $crate) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate)", \OCP\Util::DEBUG);
        $metadataSchema = $crate->getMetadataSchema();
        $metadataValid = true;
        $missingFields = array();
        if (!is_null($metadataSchema)) {
            $metadataSchema = json_decode($metadataSchema, true);
            $savedMetadata = json_decode($crate->getSavedMetadata(), true);
            $categoriesSaved = array_key_exists('categories', $savedMetadata);
            foreach ($metadataSchema['metadata_categories'] as $schemaCategory) {
                $savedCategory = null;
                if ($categoriesSaved && array_key_exists($schemaCategory['id'], $savedMetadata['categories'])) {
                    $savedCategory = $savedMetadata['categories'][$schemaCategory['id']];
                }
                $categoryValidationResults = $this->validateMetadataCategory($schemaCategory, $savedCategory);
                if (!$categoryValidationResults['categoryValid']) {
                    $metadataValid = false;
                    $missingFields = array_merge($missingFields, $categoryValidationResults['missingFields']);
                }
            }
        }
        return array('metadataValid' => $metadataValid, 'invalidFields' => $missingFields);
    }

    /**
     * Validates a metadata category
     * @param $schemaCategory - Schema definition of the category
     * @param $savedCategory - Saved metadata for the category or null if nothing saved
     * @return array - status of category validity and array of missing fields
     */
    private function validateMetadataCategory($schemaCategory, $savedCategory) {
        $categoryValid = true;
        $missingFields = array();
        foreach ($schemaCategory['category_nodes'] as $schemaCategoryNode) {
            if ($schemaCategoryNode['type'] == 'metadata_field') {
                $schemaField = $schemaCategoryNode[$schemaCategoryNode['type']];
                $savedFieldOccurrences = array();
                if (!is_null($savedCategory) && array_key_exists($schemaField['id'], $savedCategory['fields'])) {
                    $savedFieldOccurrences = $savedCategory['fields'][$schemaField['id']]['occurrences'];
                }
                if (!$this->metadataFieldValid($schemaField, $savedFieldOccurrences)) {
                    array_push($missingFields, ['categoryName' => $schemaCategory['display_name'], 'fieldName' => $schemaField['display_name']]);
                    $categoryValid = false;
                }
            } elseif ($schemaCategoryNode['type'] == 'metadata_group') {
                $schemaGroup = $schemaCategoryNode[$schemaCategoryNode['type']];
                // Fields within a group can be mandatory, but the group itself can't be flagged as mandatory
                if (!is_null($savedCategory) && array_key_exists($schemaGroup['id'], $savedCategory['groups'])) {
                    foreach ($savedCategory['groups'][$schemaGroup['id']]['occurrences'] as $savedGroupOccurrence) {
                        foreach ($schemaGroup['metadata_fields'] as $schemaField) {
                            $savedFieldOccurrences = array();
                            if (array_key_exists($schemaField['id'], $savedGroupOccurrence['fields'])) {
                                $savedFieldOccurrences = $savedGroupOccurrence['fields'][$schemaField['id']]['occurrences'];
                            }
                            if (!$this->metadataFieldValid($schemaField, $savedFieldOccurrences)) {
                                array_push($missingFields, ['categoryName' => $schemaCategory['display_name'], 'groupName' => $schemaGroup['display_name'], 'fieldName' => $schemaField['display_name']]);
                                $categoryValid = false;
                            }
                        }
                    }
                } else {
                    // If no group occurrences are saved, then any mandatory fields within the group are missing/invalid
                    foreach ($schemaGroup['metadata_fields'] as $schemaField) {
                        if (!$this->metadataFieldValid($schemaField, array())) {
                            array_push($missingFields, ['categoryName' => $schemaCategory['display_name'], 'groupName' => $schemaGroup['display_name'], 'fieldName' => $schemaField['display_name']]);
                            $categoryValid = false;
                        }
                    }
                }
            }
        }
        return array('categoryValid' => $categoryValid, 'missingFields' => $missingFields);
    }

    /**
     * Validates a metadata field
     * @param $schemaField - Schema definition of the field
     * @param $savedFieldOccurrences - Array of saved metadata occurrences for the field
     * @return boolean - True if field is valid, false otherwise
     */
    private function metadataFieldValid($schemaField, $savedFieldOccurrences) {
        $fieldValid = true;
        if ($schemaField['mandatory'] == true) {
            // Field invalid if it doesn't have anything saved
            if (empty($savedFieldOccurrences)) {
                $fieldValid = false;
            } else {
                // Field invalid if it has some saved occurrences with an empty value
                foreach ($savedFieldOccurrences as $savedOccurrenceValue) {
                    if (strlen($savedOccurrenceValue) == 0) {
                        $fieldValid = false;
                    }
                }
                // Field invalid if it doesn't have the minimum number of occurrences saved
                if (count($savedFieldOccurrences) < $schemaField['min_occurs']) {
                    $fieldValid = false;
                }
            }
        }
        return $fieldValid;
    }

    /**
     * Looks up a metadata schema for a given institute
     * @param $instituteId String AARNet institute id
     * @param $schemaName String name of the schema
     * @return string - JSON representation of crate metadata schema
     * @throws CollectionsException - Thrown if the Cr8it Server couldn't be reached or the specified organisation schema couldn't be found
     */
    public function getMetadataSchema($instituteId, $schemaName) {
        \OCP\Util::writeLog('collections', __METHOD__."($instituteId, $schemaName)", \OCP\Util::DEBUG);
        $result = file_get_contents($this->metadataSchemaURL($instituteId, $schemaName));
        if ($result == false) {
            throw new CollectionsException('Something went wrong when contacting the metadata schema server');
        }
        return $result;
    }

    /**
     * Exports the metadata of a given crate to the ownCloud Files directory
     * @param Crate $crate - The crate to publish
     * @return string - filename of the exported metadata file
     */
    public function exportMetadata(Crate $crate) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate)", \OCP\Util::DEBUG);
        $xml = $this->crateMetadataToXML($crate);
        $newFile = $this->fileService->newFile($crate->getName().'.xml');
        $newFile->putContent($xml);
        $newFile->touch(); // Hack to update the file size within OwnCloud by touching the file to trigger a scan
        return $newFile->getName();
    }

    /**
     * Generates XML containing the metadata of a crate.
     * @param Crate $collection The crate to generate the metadata for
     * @param boolean $packaging true if packaging crate, false if exporting
     * @return string crate metadata as XML
     * @throws CollectionsException If the metadata template couldn't be found or processed
     */
    public static function crateMetadataToXML(Crate $collection, $packaging = false) {
        if ($packaging) {
            $templateType = 'packaged metadata';
        } else {
            $templateType = 'exported metadata';
        }
        try {
            if ($packaging) {
                $templatePath = CrateService::getPackagedMetadataTemplatePath($collection);
            } else {
                $templatePath = CrateService::getExportedMetadataTemplatePath($collection);
            }
            $xml = TemplateService::renderCollectionTemplate($templatePath['templateDirectory'], $templatePath['templateName'],
                $templatePath['partialsDirectory'], $collection, CrateService::getCollectionTemplateAdditionalContext($collection));

            // Reformat the template generated XML to ensure correct indentation
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            return $dom->saveXML();
        } catch (\Mustache_Exception_UnknownTemplateException $e) {
            CrateService::logException($e);
            throw new CollectionsException("Unable to package collection as {$templateType} template couldn't be found", 0, $e);
        } catch (\Mustache_Exception $e) {
            CrateService::logException($e);
            throw new CollectionsException("Unable to package collection as {$templateType} template couldn't be processed", 0, $e);
        }
    }

    /**
     * Gets the path to the packaged metadata template directory, template name and partials directory.
     * @param Crate $collection Collection to get the template path for
     * @return array ['templateName', 'templateDirectory', 'partialsDirectory']
     */
    private static function getPackagedMetadataTemplatePath(Crate $collection) {
        return CrateService::getTemplatePath($collection, 'packaged_metadata_template',
            CONFIG::$DEFAULT_PACKAGED_METADATA_TEMPLATE_NAME,
            CONFIG::$DEFAULT_PACKAGED_METADATA_TEMPLATE_DIRECTORY,
            CONFIG::$DEFAULT_PACKAGED_METADATA_PARTIALS_DIRECTORY);
    }

    /**
     * Gets the path to the exported metadata template directory, template name and partials directory.
     * @param Crate $collection Collection to get the template path for
     * @return array ['templateName', 'templateDirectory', 'partialsDirectory']
     */
    private static function getExportedMetadataTemplatePath(Crate $collection) {
        return CrateService::getTemplatePath($collection, 'exported_metadata_template',
            CONFIG::$DEFAULT_EXPORTED_METADATA_TEMPLATE_NAME,
            CONFIG::$DEFAULT_EXPORTED_METADATA_TEMPLATE_DIRECTORY,
            CONFIG::$DEFAULT_EXPORTED_METADATA_PARTIALS_DIRECTORY);
    }

    /**
     * Gets the path to a template directory, template name and partials directory.
     * @param Crate $collection Collection to get the template path for
     * @param string $schemaTemplateField Name of the template field set within the schema, e.g. 'readme_template'
     * @param string $defaultTemplateName Default template name
     * @param string $defaultTemplateDirectory Path to default template directory
     * @param string $defaultPartialsDirectory Path to default partials directory
     * @return array ['templateName', 'templateDirectory', 'partialsDirectory']
     */
    private static function getTemplatePath(Crate $collection, $schemaTemplateField, $defaultTemplateName,
                                            $defaultTemplateDirectory, $defaultPartialsDirectory) {
        $metadataSchema = json_decode($collection->getMetadataSchema(), true);
        $metadataHasTemplateDefined = false;
        if (array_key_exists($schemaTemplateField, $metadataSchema)) {
            $metadataHasTemplateDefined = true;
        }

        if ($metadataHasTemplateDefined) {
            $templateName = $metadataSchema[$schemaTemplateField]['template_name'];
            $templateDirectory = $metadataSchema[$schemaTemplateField]['template_directory'];
            $partialsDirectory = $metadataSchema[$schemaTemplateField]['partials_directory'];
        } else {
            $templateName = $defaultTemplateName;
            $templateDirectory = $defaultTemplateDirectory;
            $partialsDirectory = $defaultPartialsDirectory;
        }
        $templateDirectory = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, $templateDirectory);
        $partialsDirectory = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, $partialsDirectory);
        return array('templateName' => $templateName, 'templateDirectory' => $templateDirectory,
            'partialsDirectory' => $partialsDirectory);
    }

    private static function getCollectionTemplateAdditionalContext(Crate $collection) {
        $appName = \OCP\App::getAppInfo('collections')['name'];
        $appVersion = \OCP\App::getAppVersion('collections');
        return array(
            'appInfo' => array(
                'name' => $appName,
                'version' => $appVersion
            ),
            'package' => array(
                'fileName' => $collection->getName() . '.zip',
                'guid' => $collection->getGUID(),
                'date' => array (
                    'iso' => date('c'),
                    'long' => date('F jS, Y - H:i:s (T)')
                ),
                'size' => $collection->getSize(),
                'description' => 'This file was generated by '.$appName.' App Version '.$appVersion.' on '.
                    date('jS \o\f F Y \a\t H:i:s').' ('.date_default_timezone_get().')'
            )
        );
    }

    /**
     * Checks the internal consistency of all the files within a folder and its sub-folders.
     * @param Folder $folder - The folder to check
     * @param null $currentPath - Path to the current folder
     * @param bool $isRoot - If $folder is the crate root folder
     * @return array - Key/value array containing file path/validity where validity is a boolean
     */
    private function checkCrateFolder(Folder $folder, $currentPath=null, $isRoot=false) {
        \OCP\Util::writeLog('collections', __METHOD__."($folder)", \OCP\Util::DEBUG);
        if ($isRoot) {
            $currentPath = null; // Exclude the 'root_folder' name from the constructed file path
        } else {
            if (is_null($currentPath)) {
                $currentPath = $folder->getName();
            } else {
                $currentPath = $currentPath.'/'.$folder->getName(); // build the Cr8It folder path
            }
        }
        $results = array();

        // Recurse SubFolders
        $subFolders = $folder->getFolders();
        foreach($subFolders as $subFolder) {
            $results = array_merge($results, $this->checkCrateFolder($subFolder, $currentPath));
        }

        // Check consistency of each file
        $files = $folder->getFiles();
        foreach($files as $file) {
            // build the Cr8It file path
            if (is_null($currentPath)) {
                $filePath = $file->getName();
            } else {
                $filePath = $currentPath.'/'.$file->getName();
            }
            $fileExists = $this->fileService->fileInUserFolder($file);
            if ($fileExists) {
                // Re-compute checksum if file is modified after added to crate
                $ownCloudId = $file->getOwncloudId();
                $fileModifiedTime = $this->fileService->getModifiedTime($ownCloudId);
                if (strtotime($file->getModifiedTime()) != $fileModifiedTime)
                {
                    $checksum = $this->fileService->getChecksum($ownCloudId);
                    $file->setChecksum($checksum);
                    $file->setModifiedTime(date("Y-m-d H:i:s", $fileModifiedTime));
                    $this->fileMapper->updateFile($file);
                }
            }
            // add file validity to results
            $results[$filePath] = $fileExists;
        }
        return $results;
    }

    /**
     * Set userId or crateId for a selectedCrate entity
     * @param $selectedCrate - the selectedCrate to set
     * @param $userId - new userId
     * @param $crateId - new crateId
     * @return selectedCrate - the modified selectedCrate
     */
    private function setSelectedCrate($selectedCrate, $userId, $crateId) {
        if($selectedCrate == null) {
            $newSelectedCrate = new SelectedCrate();
            $newSelectedCrate->setUserId($userId);
            $newSelectedCrate->setCrateId($crateId);
            return $this->selectedCrateMapper->newSelectedCrate($newSelectedCrate);
        } else {
            if($crateId) {
                $selectedCrate->setCrateId($crateId);
                return $this->selectedCrateMapper->update($selectedCrate);
            } else {
                return $this->selectedCrateMapper->deleteSelectedCrate($selectedCrate);
            }
        }
    }

    /**
     * Adds a file path to a crate
     * @param Crate $crate The crate to add a file to
     * @param mixed $filePath The file path of the file that should be added to the crate
     * @param mixed $parentFolderId The id of the parent folder of the file being added to the crate
     * @return File - The added file
     */
    private function addFilePathToCrate(Crate $crate, $filePath, $parentFolderId) {
        $ownCloudId = $this->fileService->getOwnCloudId($filePath);
        $fileSize = $this->fileService->getSize($filePath);

        $newFile = new File();
        $newFile->setSize($fileSize);
        $newFile->setChecksum($this->fileService->getChecksum($ownCloudId));
        $newFile->setOwncloudId($ownCloudId);
        $newFile->setName($this->fileService->getName($filePath));
        $newFile->setParentFolderId($parentFolderId);
        //convert $modifiedTime to mysql datetime format
        $newFile->setModifiedTime(date("Y-m-d H:i:s", $this->fileService->getModifiedTime($ownCloudId)));
        $newFile->setMimeType($this->fileService->getMimeType($filePath));
        $this->fileMapper->newFile($newFile);

        // Update the size of the crate
        $crate->incrementSize($fileSize);
        $this->crateMapper->updateCrate($crate);

        return $newFile;
    }

    /**
     * Crates a new folder
     * @param $folderName - The name of the new folder
     * @param $parentFolderId - Id of the parent folder of the folder being added to the crate
     * @return Folder - The added folder
     */
    private function createNewFolder($folderName, $parentFolderId) {
        $newFolder = new Folder();
        $newFolder->setName($folderName);
        $newFolder->setParentFolderId($parentFolderId);
        return $this->folderMapper->newFolder($newFolder);
    }

    /**
     * Recursively adds a file path and all subdirectories and files within the file path to a crate.
     * @param Crate $crate - The crate to add the files and folders to
     * @param $filePath - Path of the file or folder to be added to the crate
     * @param $parentFolderId - Database id of the parent folder of the file path within the crate
     * @throws CollectionsException - When the crate already contains a file/folder name matching an file or folder to add
     */
    private function recursiveAddToCrate(Crate $crate, $filePath, $parentFolderId) {
        \OCP\Util::writeLog('collections', __METHOD__."($crate, $filePath, $parentFolderId)", \OCP\Util::DEBUG);
        $isFile = $this->fileService->isFile($filePath);

        // Check if this file or folder we are adding already exists in the parent.
        if ($isFile) {
            $filename = $this->fileService->getName($filePath);
        } else {
            $filename = $this->folderService->getName($filePath);
        }

        if (!empty($this->fileMapper->find_all_with_parent_id_and_name($parentFolderId, $filename)) || !empty($this->folderMapper->find_all_with_parent_id_and_name($parentFolderId, $filename)) ) {
            throw new CollectionsException("File or Folder with name '$filename' already exists");
        }

        if ($isFile) {
            $this->addFilePathToCrate($crate, $filePath, $parentFolderId);
        } else {
            $foldername = $this->folderService->getName($filePath);
            $newFolder = $this->createNewFolder($foldername, $parentFolderId);

            // Recursively add folder contents to crate
            $paths = $this->folderService->getContents($filePath);
            foreach($paths as $path) {
                $this->recursiveAddToCrate($crate, $path, $newFolder->getId());
            }
        }
    }


    public function updateSelectedCrate($userId, $crateId) {
        $selectedCrate = $this->selectedCrateMapper->find_by_user_id($userId);
        return $this->setSelectedCrate($selectedCrate, $userId, $crateId);
    }

    /**
     * Populates the folder object tree with the subfolders and files of that folder.
     * @param Folder $folder - The folder to populate
     */
    private function populateFolder(Folder $folder) {
        \OCP\Util::writeLog('collections', __METHOD__."($folder)", \OCP\Util::DEBUG);
        $subFolders = $this->folderMapper->find_all_with_parent_id($folder->getId());
        foreach($subFolders as $subFolder) {
            $folder->addFolder($subFolder);
            // Recursive
            $this->populateFolder($subFolder);
        }
        $files = $this->fileMapper->find_all_with_parent_id($folder->getId());
        foreach($files as $file) {
            $folder->addFile($file);
        }
    }

    /**
     * Clears the contents of a folder
     * Recursively clears all child folders and files.
     * @param Folder $folder - The folder to clear
     * @param $deleteFolder - TRUE to delete the folder, FALSE will keep the folder
     * @return int - The number of bytes deleted from clearing the folder
     */
    private function clearFolder(Folder $folder, $deleteFolder) {
        $bytesDeleted = 0;
        $subFolders = $this->folderMapper->find_all_with_parent_id($folder->getId());
        foreach($subFolders as $subFolder) {
            $bytesDeleted += $this->clearFolder($subFolder, true);
        }
        $files = $this->fileMapper->find_all_with_parent_id($folder->getId());
        foreach($files as $file) {
            $this->fileMapper->deleteFile($file);
            $bytesDeleted += $file->getSize();
        }
        if ($deleteFolder) {
            $this->folderMapper->deleteFolder($folder);
        }
        return $bytesDeleted;
    }

    private static function innerCreateCrate($userId, $name) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId, $name)", \OCP\Util::DEBUG);
        $crate = new Crate();
        $crate->setUserId($userId);
        $crate->setName($name);
        $crate->setSize(0);
        $crate->setSavedMetadata('{}');
        return $crate;
    }

    private static function createDefaultCrate($userId) {
        \OCP\Util::writeLog('collections', __METHOD__."($userId)", \OCP\Util::DEBUG);
        return CrateService::innerCreateCrate($userId, "New Collection", "");
    }

    private static function updateCrateFromFields(Crate $crate, array $fields) {
        if (array_key_exists('name', $fields)) {
            $crate->setName($fields['name']);
        }
        if (array_key_exists('metadataSchema', $fields)) {
            $crate->setMetadataSchema($fields['metadataSchema']);
        }
    }

    /**
     * Given a node, returns the appropriate mapper object to perform actions on that node
     * @param $nodeType - type of the node, either "file" or "folder"
     * @return FileMapper|FolderMapper - the mapper object corresponding to the node type
     */
    private function nodeMapper($nodeType) {
        if($nodeType=='file') {
            return $this->fileMapper;
        }
        else {
            return $this->folderMapper;
        }
    }

    /**
     * Gets the URL to a metadata schema of an institute
     * @param $instituteId String AARNet institute id
     * @param $schemaName String name of the schema
     * @return string - URL to metadata schema
     */
    private function metadataSchemaURL($instituteId, $schemaName) {
        return Config::$CR8IT_SERVER.'/organisations/'.$instituteId.'/schemas/'.$schemaName;
    }

    /**
     * Generates the README file content for a given collection
     * @param Crate $collection collection to generate README file for
     * @return string README file content
     * @throws CollectionsException If the readme template couldn't be found or processed
     */
    public static function readmeFileContent(Crate $collection) {
        $templatePath = CrateService::getReadmeTemplatePath($collection);
        try {
            return TemplateService::renderCollectionTemplate($templatePath['templateDirectory'], $templatePath['templateName'],
            $templatePath['partialsDirectory'], $collection, CrateService::getCollectionTemplateAdditionalContext($collection));
        } catch (\Mustache_Exception_UnknownTemplateException $e) {
            CrateService::logException($e);
            throw new CollectionsException("Unable to package collection as readme template couldn't be found", 0, $e);
        } catch (\Mustache_Exception $e) {
            CrateService::logException($e);
            throw new CollectionsException("Unable to package collection as readme template couldn't be processed", 0, $e);
        }
    }

    /**
     * Gets the path to the README template directory, template name and partials directory.
     * @param Crate $collection Collection to get the template path for
     * @return array ['templateName', 'templateDirectory', 'partialsDirectory']
     */
    private static function getReadmeTemplatePath(Crate $collection) {
        return CrateService::getTemplatePath($collection, 'readme_template',
            CONFIG::$DEFAULT_README_TEMPLATE_NAME,
            CONFIG::$DEFAULT_README_TEMPLATE_DIRECTORY,
            CONFIG::$DEFAULT_README_PARTIALS_DIRECTORY);
    }


    private static function logException(\Exception $e) {
        \OCP\Util::writeLog('collections', 'Exception: ' . $e->getMessage() . ' | Stack Trace: ' .
            $e->getTraceAsString(), \OCP\Util::ERROR);
    }
}