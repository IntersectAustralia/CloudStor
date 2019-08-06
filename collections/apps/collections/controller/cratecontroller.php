<?php
/**
 * [cratecontroller.php]
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

namespace OCA\collections\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;

use OCA\collections\Config;
use OCA\collections\Service\CrateService;
use OCA\collections\Service\FileService;
use OCA\collections\Service\MetadataSchemaService;
use OCA\collections\Service\PackagingJobService;
use OCA\collections\lib\Mailer;
use OCA\collections\lib\Util;
use OCA\collections\lib\OCCommonAPI;
use OCA\collections\lib\CollectionsException;

class CrateController extends Controller {

    private $userId;
    private $crateService;
    private $fileService;
    private $packagingJobService;
    private $metadataSchemaService;

    public function __construct($AppName, IRequest $request, CrateService $crateService, FileService $fileService, $UserId,
                                PackagingJobService $packagingJobService, MetadataSchemaService $metadataSchemaService) {
        parent::__construct($AppName, $request);
        $this->userId = $UserId;
        $this->crateService = $crateService;
        $this->fileService = $fileService;
        $this->packagingJobService = $packagingJobService;
        $this->metadataSchemaService = $metadataSchemaService;
    }

    /**
     * Home page index, displays default collection or last selected collection
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        $selectedCrate = $this->crateService->getSelectedCrate($this->userId);
        $allCrates = $this->crateService->getAllCrates($this->userId);
        $promptPackagingEmail = empty(OCCommonAPI::getEMailAddress());
        return new TemplateResponse($this->appName, 'index', array('selected_crate' => $selectedCrate,
            'crates' => $allCrates, 'prompt_packaging_email' => $promptPackagingEmail,
            'metadata_schemas' => $this->metadataSchemaService->getAvailableMetadataSchemas(),
            'user_guide_url' => \OCP\Util::linkToRoute('collections.crate.user_guide')));
    }

    /**
     * User help guide
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function userGuide()
    {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        return new TemplateResponse($this->appName, 'user_guide', []);
    }

    /**
     * Updates the parentFolderId of a node
     * @Ajax
     * @NoAdminRequired
     */
    public function moveNode() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $newParentId = $this->params('parentFolderId');
            $nodeType = $this->params('type');
            $nodeId = $this->params('id');
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $node = $this->crateService->moveNode($nodeId, $nodeType, $newParentId);
            return new JSONResponse(array('msg' => "Node moved successfully", 'node' => $node));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot move the node {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to move node";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Rename a node
     * @Ajax
     * @NoAdminRequired
     */
    public function renameNode() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $newName = trim($this->params('name'));
            $nodeType = $this->params('type');
            $nodeId = $this->params('id');
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $node = $this->crateService->renameNode($nodeId, $nodeType, $newName);
            return new JSONResponse(array('msg' => "Node renamed successfully", 'node' => $node));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot rename the node {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to rename node";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Delete a node
     * @Ajax
     * @NoAdminRequired
     */
    public function deleteNode() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $nodeType = $this->params('type');
            $nodeId = $this->params('id');
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $crate = $this->crateService->deleteNode($crateId, $nodeId, $nodeType);
            return new JSONResponse(array('msg' => "Node deleted successfully", 'crate' => $crate));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot delete the node {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to delete node";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Updates the selected_crate for the current user
     * @Ajax
     * @NoAdminRequired
     */
    public function selectCrate()
    {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $this->crateService->updateSelectedCrate($this->userId, $crateId);
            $crate = $this->crateService->getFullCrate($crateId);
            return new JSONResponse($crate);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot update selected collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to select collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }
    /**
     * Updates the details of a crate
     * @Ajax
     * @NoAdminRequired
     */
    public function updateCrate() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $fields = $this->params('fields');
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $crate = $this->crateService->updateCrate($crateId, $fields, $this->userId);
            return new JSONResponse(array('msg' => "collection successfully updated", 'crate' => $crate));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot update collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to update collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Saves collection metadata
     * @Ajax
     * @NoAdminRequired
     */
    public function saveMetadata(){
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $this->crateService->saveCollectionMetadata($crateId, $this->params('metadata'));
            return new JSONResponse(array('msg' => "Collection metadata saved"));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot save collection metadata {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to save collection metadata";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Create crate with name and description
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function createCrate() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $name = trim($this->params('name'));
            $schema = trim($this->params('schema'));
            $permitted = $this->metadataSchemaService->accessPermitted($schema);
            if($permitted) {
                $crate = $this->crateService->createCrate($this->userId, $name, $schema);
            } else {
                throw new CollectionsException("Cannot access $schema");
            }
            return new JSONResponse(array('msg' => "collection successfully created", "crate" => $crate));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot create collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $msg = "Unable to create collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function getCrate()
    {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $crate = $this->crateService->getFullCrate($crateId);
            return new JSONResponse($crate);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot get collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to get collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Delete Crate and re-set selected crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function deleteCrate() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $selectedId = $this->params('selected_id');
            $crate = $this->crateService->deleteCrate($crateId);
            $this->crateService->updateSelectedCrate($this->userId, $selectedId);
            return new JSONResponse(array('msg' => "Collection '" . $crate->getName(). "'' has been deleted"));
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot delete collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to delete collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Removes all files from a collection
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function removeAllFiles()
    {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $collectionId = $this->params('collection_id');
            $this->validateCrateAuthorization($collectionId);
            $collection = $this->crateService->removeAllFiles($collectionId);
            return new JSONResponse(array('msg' => "Collection '" . $collection->getName(). "'' has been cleared",
                'crate' => $collection));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot remove all files from collection ".
                "{$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to remove all files from collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Adds a file path to a Crate. Used to add files from a user's owncloud file system to a crate.
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function add() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $name = $this->params('file');
            $selected_crate = $this->crateService->getSelectedCrate($this->userId);
            $this->crateService->addPathToCrate($selected_crate, $name);
            return new JSONResponse(array('msg' => "$name added to collection ". $selected_crate->getName()));
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot add to collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $msg = "Unable to add to collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add a new, empty folder to a Crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function addFolder() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $parentFolderId = $this->params('parentFolderId');
            $folderName = trim($this->params('folderName'));
            $newFolder = $this->crateService->addFolderToCrate($parentFolderId, $folderName);
            return new JSONResponse($newFolder);
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot add folder to collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to add folder to collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Packages/Self-publishes Collection to user's OwnCloud folder
     *
     * @return JSONResponse
     * @NoAdminRequired
     */
    public function publishCrate() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        $packagingInitiated = false;
        $packageSucceeded = false;
        $emailTemplateParams = array();
        $packagingJob = null;
        try {
            ini_set('max_execution_time', 86400); /* If we can't zip it in a day... */
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $collection = $this->crateService->getFullCrate($crateId);
            $emailTemplateParams['collection_name'] = $collection->getName();
            $msg = "Crate '".$collection->getName()."' has not been packaged";
            $crateCheckResults = $this->crateService->checkCrate($collection);
            $metadataValidity = $this->crateService->validateMetadata($collection);
            if ($metadataValidity['metadataValid'] && $crateCheckResults['crateValid']) {
                $packagingJob = $this->packagingJobService->newPackagingJob($collection);
                $packagingInitiated = true;
                $packageId = $this->crateService->publishCollection($collection, $packagingJob, $this->params('destination')['location']);
                $packagePath = Util::joinPaths($this->params('destination')['location'], basename($this->fileService->getPath($packageId)));
                $packagePath = ltrim($packagePath, '/');
                $msg= "Collection packaged to your Files: ".$packagePath;
                $packageSucceeded = true;
                $emailTemplateParams['collection_destination'] = $packagePath;
                $emailTemplateParams['collection_url'] = \OCP\Util::linkToRemote('webdav').$packagePath;
                $this->packagingJobService->updatePackagingJobStatus($packagingJob, 'Completed');
            }
            return new JSONResponse(array('crateValid' => $crateCheckResults['crateValid'], 'msg' => $msg,
                'filesValid' => $crateCheckResults['filesValid'], 'metadataValidity' => $metadataValidity));
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot package collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to package collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            $emailTemplateParams['failure_message'] = $msg;
            if(isset($packagingJob)) {
                $this->packagingJobService->updatePackagingJobStatus($packagingJob, 'Failed');
            }
            return new JSONResponse(array('msg' => $msg), $status);
        } finally {
            if ($packagingInitiated) {
                $emailTemplateParams['package_succeeded'] = $packageSucceeded;
                $emailSubject = 'Collections Packaging Failure Notification';
                if ($packageSucceeded) {
                    $emailSubject = 'Collections Packaging Completion Notification';
                }
                $this->sendEmailNotification($this->params('email_recipient'), $emailSubject,
                    $this->getPackagingEmailContent($emailTemplateParams), true);
            }
        }
    }

    /**
     * Gets a list of packaging jobs for the collection
     * @Ajax
     * @NoAdminRequired
     */
    public function getPackagingJobs() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            return new JSONResponse($this->packagingJobService->getPackagingJobs($this->userId));
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot get packaging jobs {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $msg = "Unable to get collection packaging jobs";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            return new JSONResponse (array('msg' => $msg), $status);
        }
    }

    /**
     * Check crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function checkCrate() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $fullCrate = $this->crateService->getFullCrate($crateId);
            $results = $this->crateService->checkCrate($fullCrate);
            return new JSONResponse($results);
        } catch (\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot check collection {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $msg = "Unable to check collection";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            return new JSONResponse (array('msg' => $msg), $status);
        }
    }

    /**
     * Get the metadata schema
     *
     * @Ajax
     * @NoAdminRequired
     * @return JSONResponse
     */
    public function metadataSchema() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        $instituteId = Config::$INSTITUTE_ID;
        $schemaName = Config::$SCHEMA_NAME;
        // ToDo: Lookup actual institute ID from AARNet rather than config file
        try {
            $schema = $this->crateService->getMetadataSchema($instituteId, $schemaName);
            return new JSONResponse($schema);
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot get collection metadata schema {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $msg = "Unable to get metadata schema";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exports Crate Metadata to user's OwnCloud folder
     *
     * @NoAdminRequired
     */
    public function exportMetadata() {
        \OCP\Util::writeLog('collections', __METHOD__, \OCP\Util::DEBUG);
        try {
            $crateId = $this->params('crate_id');
            $this->validateCrateAuthorization($crateId);
            $fullCrate = $this->crateService->getFullCrate($crateId);
            $msg = "Metadata of collection '".$fullCrate->getName()."' has not been exported";
            $metadataValidity = $this->crateService->validateMetadata($fullCrate);
            if ($metadataValidity['metadataValid']) {
                $exportedFilename = $this->crateService->exportMetadata($fullCrate);
                $msg = 'Metadata exported to '.$exportedFilename;
            }
            return new JSONResponse(array('msg' => $msg, 'metadataValidity' => $metadataValidity));
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot export collection metadata {$e->getMessage()} : {$e->getTraceAsString()}", \OCP\Util::ERROR);
            $status = ($e->getCode() == Http::STATUS_UNAUTHORIZED) ? Http::STATUS_UNAUTHORIZED : Http::STATUS_INTERNAL_SERVER_ERROR;
            $msg = "Unable to export collection metadata";
            if ($e instanceof CollectionsException) {
                $msg = $e->getMessage();
            }
            return new JSONResponse(array('msg' => $msg), $status);
        }
    }

    /**
     * Checks if the current user is authorised to access the specified crate
     * @param $crateId - Id of the crate to check
     * @return bool - true if user is authorised, false otherwise
     */
    private function isAuthorized($crateId) {
        return $this->userId == $this->crateService->getCrateOwner($crateId);
    }

    /**
     * Throws an exception if the current user is unauthorised to access the specified crate
     * @param $crateId - Id of the crate to check
     * @throws CollectionsException - thrown if user is unauthorised
     */
    private function validateCrateAuthorization($crateId) {
        if(!$this->isAuthorized($crateId)) {
            throw new CollectionsException("Unauthorized access to this collection", Http::STATUS_UNAUTHORIZED);
        }
    }

    /**
     * Gets the HTML content to include in the packaging completion or failure email
     * @param array $metadata set of parameters to pass into the template
     * @return string packaging email content
     */
    private function getPackagingEmailContent($metadata) {
        $content = Util::renderTemplate('packaging_email', $metadata);
        return $content;
    }

    /**
     * Sends an email notification to the user
     * @param string $userProvidedEmail email address to send to if user account doesn't have associated email address set
     * @param string $subject subject to display within the email
     * @param string $content content to display within the email
     * @param bool $contentIsHtml true if email content type is HTML, false otherwise
     */
    private function sendEmailNotification($userProvidedEmail, $subject, $content, $contentIsHtml=false) {
        try {
            $mailer = new Mailer();
            $from = Config::$NOTIFICATION_EMAIL_SENDER_ADDRESS;
            $to = OCCommonAPI::getEMailAddress();
            if (empty($to)) {
                $to = $userProvidedEmail;
            }
            if (!empty($to)) {
                if ($contentIsHtml) {
                    $mailer->sendHtml($to, $from, $subject, $content);
                } else {
                    $mailer->send($to, $from, $subject, $content);
                }
            }
        } catch(\Exception $e) {
            \OCP\Util::writeLog('collections', "Cannot send email notification {$e->getMessage()} :
            {$e->getTraceAsString()}", \OCP\Util::ERROR);
        }
    }


}
