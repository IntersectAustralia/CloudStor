<?php
/**
 * [MetaDataSchemaServiceTests.php]
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

use PHPUnit\Framework\TestCase;
use OCA\collections\Tests\TestHelper;


include_once(__DIR__.'/../Config.php');
include_once(__DIR__.'/../service/MetadataSchemaService.php');
include_once(__DIR__.'/../tests/mocks/OC.php');
include_once(__DIR__.'/../tests/mocks/util.php');

/**
 * Stub of the PHP internal function used to return a known value.
 *
 * @return string
 */
function file_get_contents() {
    global $file_get_contentsReturns, $file_get_contentsReturnCount;
    if(!isset($file_get_contentsReturnCount)) {
        $file_get_contentsReturnCount = 0;
    }
    return $file_get_contentsReturns[$file_get_contentsReturnCount++];
}

/**
 * Stub of the PHP internal function for test purposes. Prevents files being written while tests run.
 */
function file_put_contents() {}


/***
 * Stub of the PHP internal function used to return a set of known values.
 */
function fgets() {
    global $fgetsReturns, $fgetsReturnCount;
    if(!isset($fgetsReturns)) {
        $fgetsReturns = ['*:test.json', 'test@test.org:test.json,test2.json', false];
        $fgetsReturnCount = 0;
    }
    return $fgetsReturns[$fgetsReturnCount++];
}

class MetaDataSchemaServiceTests extends TestCase {

    private $metaDataSchemaService;

    public function setUp() {
        $this->metaDataSchemaService = new MetadataSchemaService();
        global $file_get_contentsReturns;
        $file_get_contentsReturns = [json_encode([
            '$schema' => "http://json-schema.org/draft-04/schema#",
            'title' => 'Example Schema',
            'type' => 'object',
            'properties' => [
                'field' => [
                    'description' => 'some field',
                    'type' => 'integer'
                ]
            ],
            'required' => ['field']
        ])];
    }


    public function testValidateMetadataSchemaPrevalidated() {
        $contents = ['description' => 'test', 'valid' => TRUE];
        $valid = TestHelper::invokeMethod($this->metaDataSchemaService, 'isMetadataSchemaValid', [$contents, NULL]);
        $this->assertTrue($valid);
    }

    public function testValidateMetadataSchemaSucceeds() {
        $contents = ['field' => 32];
        global $file_get_contentsReturns;
        array_push($file_get_contentsReturns, json_encode($contents));
        $valid = TestHelper::invokeMethod($this->metaDataSchemaService, 'isMetadataSchemaValid', [$contents, NULL]);
        $this->assertTrue($valid);
    }

    public function testValidateMetadataSchemaFails() {
        $contents = ['field' => 'smurf'];
        global $file_get_contentsReturns;
        array_push($file_get_contentsReturns, json_encode($contents));
        $valid = TestHelper::invokeMethod($this->metaDataSchemaService, 'isMetadataSchemaValid', [$contents, NULL]);
        $this->assertFalse($valid);
    }

    public function testProcessAccessListHasNoDuplicates() {
        $expected = ['test.json', 'test2.json'];
        $params = ['/path/to/schema/folder', 'access.txt', 'test@test.org', false];
        $actual = TestHelper::invokeMethod($this->metaDataSchemaService, 'processAccessList', $params);
        $this->assertEquals($expected, $actual);
    }

}
