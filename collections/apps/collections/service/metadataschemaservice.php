<?php

/**
 * [metadataschemaservice.php]
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
use OCA\collections\lib\Util;
use OCA\collections\lib\OCCommonAPI;

use League\JsonGuard\Validator;
use League\JsonGuard\Dereferencer;

class MetadataSchemaService {

    /**
     * Generates a list of schema available to the currently logged in User.
     *
     * @return array
     */
    public function getAvailableMetadataSchemas() {
        $paths = glob(Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, '*'), GLOB_ONLYDIR);
        $schemas = $this->processSchemaPaths($paths);
        return $schemas;
    }

    /**
     * Processes schema paths and returns a list of schema that are available to the currently logged in user.
     *
     * @param $paths paths to process
     * @return array
     */
    private function processSchemaPaths($paths) {
        $schemas = [];
        foreach($paths as $path) {
            $accessibleSchemas = $this->getAccessibleSchemas($path);
            foreach($accessibleSchemas as $accessibleSchema) {
                // NOTE: Disallow relative paths
                if(strpos($accessibleSchema, '..') === false) {
                    $contents = json_decode(file_get_contents($accessibleSchema), true);
                    $valid = $this->isMetadataSchemaValid($contents, $accessibleSchema);
                    if ($valid) {
                        array_push($schemas, $this->getSchemaDescription($contents, $accessibleSchema));
                    }
                }
            }
        }
        return $schemas;
    }


    /**
     * Gets a list of schema accessible to the currently logged in user given a folder path.
     *
     * @param $path path to search for access lists and schema
     * @return array
     */
    public function getAccessibleSchemas($path) {
        if($this->isPublicMetadataFolder($path)) {
            $schemas = $this->getPublicSchemas();
        } else {
            $schemas = $this->getPrivateSchemas($path);
        }
        return $schemas;
    }


    /**
     * Gets a list of private schema accessible to the currently logged in user given a folder path.
     *
     * @param $path path to search for access lists and schema
     * @return array
     */
    private function getPrivateSchemas($path) {
        $schemas = [];
        $accessFile = $this->getAccessFile($path);
        if(file_exists($accessFile)) {
            $accessList = fopen($accessFile, 'r');
            if($accessList) {
                $currentUserId = OCCommonAPI::getUserId();
                $schemas = $this->processAccessList($path, $accessList, $currentUserId);
            }
        }
        return $schemas;
    }


    /**
     * Gets a list of public schema accessible to the currently logged in user given a folder path.
     *
     * @return array
     */
    private function getPublicSchemas() {
        return glob(Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, Config::$PUBLIC_SCHEMA_DIRECTORY, '*.json'));
    }


    /**
     * Checks whether the supplied path is the public metadata folder.
     *
     * @param $path path to check
     * @return bool
     */
    private function isPublicMetadataFolder($path) {
        $publicMetadataFolder = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, Config::$PUBLIC_SCHEMA_DIRECTORY);
        return $path === $publicMetadataFolder;
    }


    /**
     * Processes an access list and finds the schema that are accessible to the supplied email address.
     *
     * @param $accessList path to the access list for the schema folder
     * @param $currentUserId UserID to test for accessiblity
     * @param $path path to the schema folder
     * @return array
     */
    private function processAccessList($path, $accessList, $currentUserId) {
        $schemas = [];
        while (($line = fgets($accessList)) !== false) {
            $line = trim($line);
            if(!empty($line)) {
                $parsed = $this->parseAccessLine($line);
                if($this->permitted($currentUserId, $parsed[0])) {
                    foreach($parsed[1] as $schema) {
                        $absoluteSchemaPath = Util::joinPaths($path, $schema);
                        if(!in_array($absoluteSchemaPath, $schemas)) {
                            array_push($schemas, $absoluteSchemaPath);
                        }
                    }
                }
            }
        }
        return $schemas;
    }


     /**
     * Gets the access file path.
     *
     * @param $path path to the schema folder
     * @return mixed
     */
    private function getAccessFile($path) {
        $directory = basename($path);
        $accessFile = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, "$directory.acl.txt");
        return $accessFile;
    }


    /**
     * Checks whether an UserID matches a given pattern.
     *
     * @param $currentUserId UserID to check
     * @param $pattern pattern to match
     * @return bool
     */
    private function permitted($currentUserId, $pattern) {
        $result = false;
        if($pattern == '*' ||
            ($currentUserId == Config::$SCHEMA_DEVELOPER_USER) ||
            ($currentUserId and substr_compare(strtolower($currentUserId), strtolower($pattern), strlen($currentUserId)-strlen($pattern)) == 0)) {
            $result = true;
        }
        return $result;
    }


    /**
     * Parses a line in a access.txt file into a email pattern and list of filenames.
     *
     * @param $line line to parse
     * @return array
     */
    private function parseAccessLine($line) {
        $line = preg_replace('/\s/ ','', $line);
        $parsed = explode(':', $line);
        $parsed[1] = explode(',', $parsed[1]);
        return $parsed;
    }


    /**
     * Generates an array of schema description fields for use in an options dropdown.
     *
     * @param $contents schema contents
     * @param $metadataPath path to metadata schema file
     * @return array
     * @internal param Parent $dataPath directory containing the metadata schema
     */
    private function getSchemaDescription($contents, $metadataPath) {
        $defaultSchemaPath = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, Config::$DEFAULT_SCHEMA_PATH);
        $default = $metadataPath == $defaultSchemaPath;
        $description = "{$contents['display_name']} v{$contents['version']} - {$contents['scope']}";
        return array('description' => $description, 'path' => $metadataPath, 'default' => $default);
    }

    /**
     * Checks whether the schema has been validated as valid, and otherwise validates it if it hasn't.
     *
     * @param $metadataSchema metadata schema contents
     * @param $metadataSchemaPath path to the metadata schema file
     * @return bool
     */
    private function isMetadataSchemaValid($metadataSchema, $metadataSchemaPath) {
        if(!$metadataSchema) {
            $result = false;
        } elseif(array_key_exists('valid', $metadataSchema)) {
            $result = $metadataSchema['valid'];
        } else {
            $jsonSchemaPath = Util::joinPaths(Config::$METADATA_ROOT_DIRECTORY, Config::$JSON_SCHEMA);
            $jsonSchema = json_decode(file_get_contents($jsonSchemaPath));
            $dereferencer = new Dereferencer();
            $jsonSchema = $dereferencer->dereference($jsonSchema);
            $jsonObject = json_decode(file_get_contents($metadataSchemaPath));
            $validator = new Validator($jsonObject, $jsonSchema);
            $result = $validator->passes();
            $metadataSchema['valid'] = $result;
            file_put_contents($metadataSchemaPath, json_encode($metadataSchema, JSON_PRETTY_PRINT));
        }
        return $result;
    }


    /**
     * Checks whether the current logged in user has permission to access the requested schema.
     *
     * @param $schemaPath path to the requested schema
     * @return bool
     */
    public function accessPermitted($schemaPath) {
        $pathinfo = pathinfo($schemaPath);
        $accessibleSchemas = $this->getAccessibleSchemas($pathinfo['dirname']);
        return in_array($schemaPath, $accessibleSchemas);
    }

}
