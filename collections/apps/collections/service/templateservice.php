<?php
/**
 * [templateservice.php]
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

/**
 *  A service to render templates using the Mustache template engine.
 *
 * @package OCA\collections\Service
 */
class TemplateService {
    /**
     * Renders a template using the Mustache template engine with a context corresponding to the given collection.
     * @param string $templateDirectory path of the directory containing the template file to render
     * @param string $templateName basename of the template file to render
     * @param string|null $partialsDirectory path of the directory containing the partials or null if no partials used
     * @param Crate $collection collection to generate template context from
     * @param array $context additional template context to use (default: array())
     * @return string rendered template
     */
    public static function renderCollectionTemplate($templateDirectory, $templateName, $partialsDirectory=null,
                                                    Crate $collection, $context=array()) {
        return TemplateService::renderTemplate($templateDirectory, $templateName, $partialsDirectory,
            array_merge($context, TemplateService::getCollectionMustacheContext($collection)));
    }

    /**
     * Renders a template using the Mustache template engine with the given context.
     * @param string $templateDirectory path of the directory containing the template file to render
     * @param string $templateName basename of the template file to render
     * @param string|null $partialsDirectory path of the directory containing the partials or null if no partials used
     * @param mixed  $context additional template context to use (default: array())
     * @return string rendered template
     */
    public static function renderTemplate($templateDirectory, $templateName, $partialsDirectory=null, $context=array()) {
        $mustacheOptions = array('loader' => new \Mustache_Loader_FilesystemLoader($templateDirectory));
        if (!is_null($partialsDirectory)){
            $mustacheOptions['partials_loader'] = new \Mustache_Loader_FilesystemLoader($partialsDirectory);
        }
        $mustacheEngine = new \Mustache_Engine($mustacheOptions);
        return $mustacheEngine->render($templateName, $context);
    }

    /**
     * Gets the Mustache context corresponding to a given collection. This context can be passed to the mustache engine
     *  as the array of data to use within the template.
     * @param Crate $collection Collection to get Mustache context for
     * @return array Mustache context
     */
    private static function getCollectionMustacheContext(Crate $collection) {
        $metadataSchema = json_decode($collection->getMetadataSchema(), true);
        $savedMetadata = json_decode($collection->getSavedMetadata(), true);
        $mustacheContext = array(
            'collectionName' => $collection->getName(),
            'collectionFilename' => $collection->getName().'.zip',
            'metadataSchema' => array(
                'name' => $metadataSchema['display_name'],
                'version' => $metadataSchema['version']
            ),
            'metadataCategories' => TemplateService::getCategoriesContext($metadataSchema, $savedMetadata),
            'fileList' => TemplateService::getFileListContext($collection)
        );
        return $mustacheContext;
    }

    /**
     * Gets the Mustache context corresponding to the file list of the given collection.
     * @param Crate $collection Collection to get file list context for
     * @return array Mustache context
     */
    private static function getFileListContext(Crate $collection) {
        $fileListContext = TemplateService::getDirectoryContext($collection->getRootFolder(), '', true);
        return $fileListContext;
    }

    /**
     * Gets the Mustache context corresponding to a collection directory
     * @param Folder $folder directory to get context for
     * @param string|null $path path to the current folder
     * @param bool $isRoot true if folder is collection root directory, false otherwise
     * @return array Mustache context
     */
    private static function getDirectoryContext(Folder $folder, $path, $isRoot=false) {
        if(!$isRoot) {
            $path = Util::joinPaths($path, $folder->getName());
        }
        $subDirectoriesContext = array();
        foreach ($folder->getFolders() as $subDirectory) {
            array_push($subDirectoriesContext, self::getDirectoryContext($subDirectory, $path));
        }
        $directoryContents = array_merge($subDirectoriesContext, self::getFilesContext($folder, $path));
        if(!$isRoot) {
            $directoryContents = array(
                'name' => $folder->getName(),
                'path' => $path,
                'directoryContents' => $directoryContents
            );
        }
        return $directoryContents;
    }

    private static function getFilesContext(Folder $folder, $path) {
        $filesContext = array();
        foreach ($folder->getFiles() as $file) {
            $filePath = Util::joinPaths($path, $file->getName());
            array_push($filesContext, array(
                'name' => $file->getName(),
                'path' => $filePath,
                'checksum' => $file->getChecksum(),
                'directoryContents' => null
            ));
        }
        return $filesContext;
    }


    /**
     * Gets the Mustache context corresponding to the metadata categories within a collection.
     * @param array $metadataSchema Metadata schema/definition used by the collection
     * @param array $savedMetadata Saved metadata of the collection
     * @return array Mustache context
     */
    private static function getCategoriesContext($metadataSchema, $savedMetadata) {
        $categoriesContext = array();
        if (!empty($savedMetadata)) {
            $metadataCategoryDefinitions = $metadataSchema['metadata_categories'];
            foreach ($metadataCategoryDefinitions as $metadataCategoryDefinition) {
                $categoriesContext = array_merge($categoriesContext, TemplateService::getCategoryContext($metadataCategoryDefinition, $savedMetadata['categories']));
            }
        }
        return $categoriesContext;
    }

    /**
     * Gets the Mustache context corresponding to a metadata category within a collection.
     * @param array $metadataCategoryDefinition Metadata definition of the category
     * @param array $savedMetadataCategories Saved data corresponding to the category
     * @return array Mustache context
     */
    private static function getCategoryContext($metadataCategoryDefinition, $savedMetadataCategories) {
        $fieldsContext = array();
        $groupsContext = array();
        foreach ($metadataCategoryDefinition['category_nodes'] as $metadataCategoryNode) {
            if (array_key_exists($metadataCategoryDefinition['id'], $savedMetadataCategories)) {
                if ($metadataCategoryNode['type'] == 'metadata_group') {
                    $metadataGroupDefinition = $metadataCategoryNode[$metadataCategoryNode['type']];
                    $savedMetadataGroups = $savedMetadataCategories[$metadataCategoryDefinition['id']]['groups'];
                    if (array_key_exists($metadataGroupDefinition['id'], $savedMetadataGroups)) {
                        $savedMetadataGroup = $savedMetadataGroups[$metadataGroupDefinition['id']];
                        $groupsContext = array_merge($groupsContext, TemplateService::getGroupContext($metadataGroupDefinition, $savedMetadataGroup));
                    }
                } elseif ($metadataCategoryNode['type'] == 'metadata_field') {
                    $metadataFieldDefinition = $metadataCategoryNode[$metadataCategoryNode['type']];
                    $savedMetadataFields = $savedMetadataCategories[$metadataCategoryDefinition['id']]['fields'];
                    if (array_key_exists($metadataFieldDefinition['id'], $savedMetadataFields)) {
                        $savedMetadataField = $savedMetadataFields[$metadataFieldDefinition['id']];
                        $fieldsContext = array_merge($fieldsContext, TemplateService::getFieldContext($metadataFieldDefinition, $savedMetadataField));
                    }
                }
            }
        }

        return array($metadataCategoryDefinition['id'] =>
            array(
                'displayName' => $metadataCategoryDefinition['display_name'],
                'metadataFields' => $fieldsContext,
                'metadataGroups' => $groupsContext
            )
        );
    }

    /**
     * Gets the Mustache context corresponding to a metadata group within a collection.
     * @param array $metadataGroupDefinition Metadata definition of the group
     * @param array $savedMetadataGroup Saved metadata corresponding to the group
     * @return array Mustache context
     */
    private static function getGroupContext($metadataGroupDefinition, $savedMetadataGroup) {
        $groupOccurrencesContext = array();
        foreach($savedMetadataGroup['occurrences'] as $savedGroupOccurrence) {
            $groupOccurrenceContext = array();
            foreach($metadataGroupDefinition['metadata_fields'] as $metadataGroupFieldDefinition) {
                $groupOccurrenceFieldsContext = array();
                $savedGroupFields = $savedGroupOccurrence['fields'];
                if (array_key_exists($metadataGroupFieldDefinition['id'], $savedGroupFields)) {
                    $savedGroupField = $savedGroupFields[$metadataGroupFieldDefinition['id']];
                    array_push($groupOccurrenceFieldsContext, TemplateService::getFieldContext($metadataGroupFieldDefinition, $savedGroupField));
                }
                array_push($groupOccurrenceContext, array('metadataFields' => $groupOccurrenceFieldsContext));
            }
            array_push($groupOccurrencesContext, $groupOccurrenceContext);
        }

        return array(
            $metadataGroupDefinition['id'] => array(
                'displayName' => $metadataGroupDefinition['display_name'],
                'occurrences' => $groupOccurrencesContext
            )
        );
    }

    /**
     * Gets the Mustache context for a saved metadata field within a collection.
     * @param array $metadataFieldDefinition Metadata definition of the field
     * @param array $savedMetadataField Saved metadata corresponding to the field
     * @return array Mustache context
     */
    private static function getFieldContext($metadataFieldDefinition, $savedMetadataField) {
        $savedFieldOccurrences = array();
        foreach($savedMetadataField['occurrences'] as $savedOccurrenceValue) {
            array_push($savedFieldOccurrences, array('value' => $savedOccurrenceValue));
        }

        return array(
            $metadataFieldDefinition['id'] => array(
                'displayName' => $metadataFieldDefinition['display_name'],
                'occurrences' => $savedFieldOccurrences
            )
        );
    }
}