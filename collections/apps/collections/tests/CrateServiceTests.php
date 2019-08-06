<?php
/**
 * [CrateServiceTests.php]
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
use PHPUnit\Framework\TestCase;
use OCA\collections\Tests\TestHelper;

include_once(__DIR__.'/../Config.php');
include_once(__DIR__.'/../service/CrateService.php');
include_once(__DIR__.'/../service/TemplateService.php');
include_once(__DIR__.'/../tests/mocks/util.php');

class CollectionServiceTests extends TestCase{

    private $crateService;

    public function setUp()
    {
        $selectedCrateMapper = $this->getMockBuilder('OCA\collections\Mapper\SelectedCrateMapper')->getMock();
        $crateMapper = $this->getMockBuilder('OCA\collections\Mapper\CrateMapper')->getMock();
        $folderMapper = $this->getMockBuilder('OCA\collections\Mapper\FolderMapper')->getMock();
        $folderService = $this->getMockBuilder('OCA\collections\Service\FolderService')->getMock();
        $fileMapper = $this->getMockBuilder('OCA\collections\Mapper\FileMapper')->getMock();
        $fileService = $this->getMockBuilder('OCA\collections\Service\FileService')->getMock();
        $packagingJobService = $this->getMockBuilder('OCA\collections\Service\PackagingJobService')->getMock();

        parent::setUp();
        $this->crateService = new CrateService($selectedCrateMapper, $crateMapper, $folderMapper, $folderService,
            $fileMapper, $fileService, $packagingJobService, new TemplateService());
    }

    public function testGetReadmeTemplatePathReturnsExpected() {
        $collection = $this->getMockBuilder('OCA\collections\Entity\Crate')
            ->setMethods(array('getMetadataSchema'))
            ->getMock();
        $collection->method('getMetadataSchema')->willReturn(file_get_contents(dirname(__FILE__) .
            '/examples/metadata-definition/collection.json'));

        $expectedPathSet = array('templateName' => 'collections_basic_readme.html.mustache',
            'templateDirectory' => Config::$METADATA_ROOT_DIRECTORY . '/Public/templates',
            'partialsDirectory' => Config::$METADATA_ROOT_DIRECTORY . '/Public/templates/partials');
        $actualPathSet = TestHelper::invokeMethod($this->crateService, 'getReadmeTemplatePath', [$collection]);
        $this->assertEquals($expectedPathSet, $actualPathSet);
    }

    /**
     * Tests that the get readme path function is backwards compatible with Collections 1.0 and returns the expected
     *  array for a collection that does not have the expected readme template variables defined. The path used for the
     *  template and partials directory corresponds to the defaults defined in the app config.
     */
    public function testGetReadmeTemplatePathIsBackwardsCompatible() {
        $collection = $this->getMockBuilder('OCA\collections\Entity\Crate')
            ->setMethods(array('getMetadataSchema'))
            ->getMock();
        $collection->method('getMetadataSchema')->willReturn(file_get_contents(dirname(__FILE__) .
            '/examples/metadata-definition/collection_v1.0.json'));

        $expectedPathSet = array('templateName' => 'collections_basic_readme.html.mustache',
            'templateDirectory' => Config::$METADATA_ROOT_DIRECTORY . '/Public/templates',
            'partialsDirectory' => Config::$METADATA_ROOT_DIRECTORY . '/Public/templates/partials');
        $actualPathSet = TestHelper::invokeMethod($this->crateService, 'getReadmeTemplatePath', [$collection]);
        $this->assertEquals($expectedPathSet, $actualPathSet);
    }

    // ToDo: Add unit test for readmeFileContent() to ensure HTML Readme content returns as expected
}