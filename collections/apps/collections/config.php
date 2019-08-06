<?php
/**
 * [config.php]
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

namespace OCA\collections;

/**
 * Class Config provides a set of variables for configuring an instance of Collections
 * @package OCA\collections
 */
class Config {

    /**
     * Maximum size of a collection in megabytes at which a warning will be displayed when attempting to package.
     * @todo This functionality has not been fully implemented
     * @var int
     */
    public static $PUBLISH_WARNING_MB = 100000;

    /**
     * Maximum size of a collection in megabytes at which the packaging functionality will be disabled.
     * @todo This functionality has not been fully implemented
     * @var int
     */
    public static $PUBLISH_MAX_MB = 100000;

    /**
     * Root folder containing the metadata schema specification, ACLs, and metadata schema folders.
     *
     * @var string
     */
    public static $METADATA_ROOT_DIRECTORY = '/var/www/html/data/metadata/files';

    /**
     * @var string username of the developer user, which bypasses ACL list viewing restrictions for development purposes.
     */
    public static $SCHEMA_DEVELOPER_USER = 'cloudstor@intersect.org.au';

    /**
     * Folder path for public schema, relative to the metadata schema directory.
     *
     * @var string
     */
    public static $PUBLIC_SCHEMA_DIRECTORY = '/Public';

    /**
     * Path to the default default metadata schema relative the metadata schema directory.
     *
     * @var string
     */
    public static $DEFAULT_SCHEMA_PATH = '/Public/collections_basic.json';

    /***
     * Name of the json schema specification to validate metadata schema against.
     *
     * @var string
     */
    public static $JSON_SCHEMA = 'schema_spec.json';

    /**
     * Sender email address to use when providing Collections notifications to the user. Defaults to the ownCloud "From
     * address" if this variable is set to the empty string or null.
     * @var string
     */
    public static $NOTIFICATION_EMAIL_SENDER_ADDRESS = 'no-reply@collections.app';

    /***
     * Base name of the default README template file relative to the metadata schema directory. Used when generating the
     * collection README file for collections created using a version of Cloudstor Collections earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_README_TEMPLATE_NAME = 'collections_basic_readme.html.mustache';

    /***
     * Path of the default README template directory relative to the metadata schema directory. Used when generating the
     * collection README file for collections created using a version of Cloudstor Collections earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_README_TEMPLATE_DIRECTORY = 'Public/templates';

    /***
     * Path of the default README template partials directory relative to the metadata schema directory. Used when generating
     * the collection README file for collections created using a version of Cloudstor Collections earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_README_PARTIALS_DIRECTORY = 'Public/templates/partials';

    /***
     * Base name of the default packaged metadata template file relative to the metadata schema directory. Used when
     * generating the collection packaged metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_PACKAGED_METADATA_TEMPLATE_NAME = 'collections_basic_packaged_metadata.xml.mustache';

    /***
     * Path of the default packaged metadata template directory relative to the metadata schema directory. Used when
     * generating the collection packaged metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_PACKAGED_METADATA_TEMPLATE_DIRECTORY = 'Public/templates';

    /***
     * Path of the default packaged metadata template partials directory relative to the metadata schema directory. Used when
     * generating the collection packaged metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_PACKAGED_METADATA_PARTIALS_DIRECTORY = null;

    /***
     * Base name of the default exported metadata template file relative to the metadata schema directory. Used when
     * generating the collection exported metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_EXPORTED_METADATA_TEMPLATE_NAME = 'collections_basic_exported_metadata.xml.mustache';

    /***
     * Path of the default exported metadata template directory relative to the metadata schema directory. Used when
     * generating the collection exported metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_EXPORTED_METADATA_TEMPLATE_DIRECTORY = 'Public/templates';

    /***
     * Path of the default exported metadata template partials directory relative to the metadata schema directory. Used when
     * generating the collection exported metadata file for collections created using a version of Cloudstor Collections
     * earlier than 1.1.
     *
     * @var string
     */
    public static $DEFAULT_EXPORTED_METADATA_PARTIALS_DIRECTORY = null;
}
