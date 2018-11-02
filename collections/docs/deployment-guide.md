## Dependencies

As part of this deployment guide, it is assumed that:
* An ownCloud server version 8.2, 9.0 or 9.1 is already installed.
* Apache and PHP have been installed as part of the process of installing ownCloud.
* Email sending has been configured within ownCloud. For information on how to do this, refer to the [ownCloud 9.1 email configuration documentation](https://doc.owncloud.org/server/9.1/admin_manual/configuration_server/email_configuration.html).
* Composer has been installed. For information on how to do this, refer to the [Composer getting started guide](https://getcomposer.org/doc/00-intro.md).
* Composer has been run on the Collections application once installed and configured.

## Install Collections

These installation instructions are based on the [ownCloud 8.0 guide for adding third party apps](https://doc.owncloud.org/server/8.0/admin_manual/installation/apps_management_installation.html#adding-third-party-apps).

1. Clone the repository, using the [version 1.1](https://github.com/IntersectAustralia/CloudStor/releases/tag/v1.1) tagged release.
2. Place the Collections application directory [collections/apps/collections](https://github.com/IntersectAustralia/CloudStor/tree/v1.1/collections/apps/collections) into the apps folder in your ownCloud installation, typically owncloud/apps.
3. Ensure the permissions and ownership are similar to the other ownCloud apps. Typically, access rights are rwxr-x—, or 0750 in octal notation, and the owner and group are your HTTP user. On CentOS this is apache, Debian/Ubuntu is www-data. 

## Configuring Collections

The Collections repository includes a configuration file which can be used to configure the Collections application, [collections/apps/collections/config.php](https://github.com/IntersectAustralia/CloudStor/tree/v1.1/collections/apps/collections/config.php). The contents of this configuration file can be modified as necessary for each deployment. Refer to the comments in the configuration file for an explanation of what each variable does.

### Configuring the Schema Developer User

The default value of the Schema Developer User configuration setting should be changed to the username of an actual user that is in (or will be in) the system. This user will then be able to bypass all metadata schema ACL restrictions, which will aid in the testing and development of new metadata schemas.

### Configuring the Metadata Directory and Metadata Schema

A folder on the filesystem needs to configured to house metadata schemas. This is set with the `$METADATA_ROOT_DIRECTORY` variable in `config.php`. The root of this folder will hold Access Control Lists (ACLs), the metadata schema specification, and the folders which will contain the metadata schemas.

#### Public Schema
 
Once the metadata folder has been configured, the default metadata schemas can be added to it in a subfolder matching the `$PUBLIC_SCHEMA_DIRECTORY`, which is `Public` by default. These will be used when a new collection is created. The name of the default schema is configurable in `config.php`, but is called `collections_basic.json` by default. A schema needs to be uploaded to this folder and renamed to the configured public schema name (e.g. `collections_basic.json`). A copy of the `collections_basic.json` can be found in this repository's `apps/collections/schema/metadata-schemas` folder. Additional schemas can be added to the directory and will be available to all users.

#### Private Schema

Private schema can be created by other users, and made available to select users through the use of an access file. First the folder to host these schemas needs to be created as a subdirectory of the `$METADATA_ROOT_DIRECTORY`. These metadata schema must conform to the metadata schema specification laid out in the `schema_spec.json` placed in the metadata directory.

Schemas placed in this folder will not be made available to other users until an access file, called `<directory>.acl.txt` is placed in the metadata root folder. Each line of the access file specifies a user ID pattern, followed by the schemas within the folder that users with IDs matching the specified pattern are allowed to access. `*` can be used as a wildcard to mean all users. The following is an example of an `<directory>.acl.txt` file:

```
*:unsw_public.json
unsw.edu.au:unsw_researchers.json,unsw_library.json
stephen.hawking@cam.ac.uk:unsw_researchers.json
```

In this case, all Collections users would be able to access `unsw_public.json` schema; users with a user ID ending in `unsw.edu.au`, e.g. `jane.doe@students.unsw.edu.au`, would be able to access `unsw_researchers.json` and `unsw_library.json`. Additional, the user with the user ID `stephen.hawking@cam.ac.uk` would be able to access `unsw_researchers.json`.

## Institution Folder Structure Example

The metadata folder structure would be the following:

```
|-- Public
|   |-- collections_basic.json
|   `-- templates
|       |-- partials
|       |   `-- readme_file_tree.mustache
|       |-- test_schema_metadata.xml.mustache
|       `-- test_schema_readme.html.mustache
|-- WSU
|   |--engineering.json
|   `--templates
|      |---engineering_exported_metadata.xml.mustache
|      `---partials
|          `----readme_file_tree.mustache
|-- UNE
|   |--archaeology.json
|   `--templates
|      |---archaeology_exported_metadata.xml.mustache
|      |---archaeology_exported_metadata.rdf.mustache
|      `---partials
|          `----readme_file_tree.mustache
|-- schema_spec.json
|-- UNE.acl.txt
`-- WSU.acl.txt
```


* The public folder would ideally hold all public schemas that aarnet would like to release to researchers.
* The public folder will not be shared with university admins.
* Each institution folders for eg WSU,UNE etc will each be shared with the university admins of each institution which means they will have full control who can access the institution schemas.
* The `<institution>.acl.txt` files will contain the permissions for each schema in the institution folder.


#### README Template
When a collection is packaged a human readable README HTML document automatically gets added to the package, which contains all the saved collection metadata and a list of the collection files. This document gets generated from a README template file specified within the Collection schema. For backwards compatibility a default template needs to be added to the metadata root, since collections created in Collections version 1.0 don't contain the required template specification in their saved schema.
 
This README template file is required to follow valid Mustache syntax. The specification of Mustache syntax can be found within the [Mustache Manual](https://mustache.github.io/mustache.5.html).

* The name of the default README template file is configurable in `config.php`, but is called `collections_basic_readme.html.mustache` by default.
* The path to the directory containing the default README template file is configurable in `config.php`, but is defined as `Public/templates` by default.
* The path to the directory containing any default template partials files is configurable in `config.php`, but is defined as `Public/templates/partials` by default.

A README template needs to be uploaded to the configured default README template directory and renamed to match the configured README template file name (e.g. `Public/templates/collections_basic_readme.html.mustache`). A copy of the `collections_basic_readme.html.mustache` default template can be found in this repository's `apps/collections/templates/mustache/collections_basic` directory. This README template also utilises the partials file `readme_file_tree.mustache` which will need to be uploaded to the configured README template partials directory (e.g. `Public/templates/partials`).

#### Packaged Metadata Template
When a collection is packaged an XML document automatically gets added to the package, which contains all the saved collection metadata and some general information such as the package GUID. This document gets generated from a packaged metadata template file specified within the Collection schema. For backwards compatibility a default template needs to be added to the metadata root, since collections created in Collections version 1.0 don't contain the required packaged metadata specification in their saved schema.
 
This packaged metadata template file is required to follow valid Mustache syntax. The specification of Mustache syntax can be found within the [Mustache Manual](https://mustache.github.io/mustache.5.html).

* The name of the default packaged metadata template file is configurable in `config.php`, but is called `collections_basic_packaged_metadata.xml.mustache` by default.
* The path to the directory containing the default packaged metadata template file is configurable in `config.php`, but is defined as `Public/templates` by default.
* The path to the directory containing any default packaged metadata partials files is configurable in `config.php`, but is defined as `null` by default since no partials are used by the included packaged metadata template.

A packaged metadata template needs to be uploaded to the configured default packaged metadata directory and renamed to match the configured packaged metadata template file name (e.g. `Public/templates/collections_basic_packaged_metadata.xml.mustache`). A copy of the `collections_basic_packaged_metadata.xml.mustache` default template can be found in this repository's `apps/collections/templates/mustache/collections_basic` directory.

#### Exported Metadata Template
When collection metadata is exported an XML document automatically gets added to the user's files, which contains all the saved collection metadata and some general information such as the export timestamp. This document gets generated from an exported metadata template file specified within the Collection schema. For backwards compatibility a default template needs to be added to the metadata root, since collections created in Collections version 1.0 don't contain the required exported metadata specification in their saved schema.
 
This exported metadata template file is required to follow valid Mustache syntax. The specification of Mustache syntax can be found within the [Mustache Manual](https://mustache.github.io/mustache.5.html).

* The name of the default exported metadata template file is configurable in `config.php`, but is called `collections_basic_exported_metadata.xml.mustache` by default.
* The path to the directory containing the default exported metadata template file is configurable in `config.php`, but is defined as `Public/templates` by default.
* The path to the directory containing any default exported metadata partials files is configurable in `config.php`, but is defined as `null` by default since no partials are used by the included packaged metadata template.

A packaged metadata template needs to be uploaded to the configured default packaged metadata directory and renamed to match the configured packaged metadata template file name (e.g. `Public/templates/collections_basic_exported_metadata.xml.mustache`). A copy of the `collections_basic_exported_metadata.xml.mustache` default template can be found in this repository's `apps/collections/templates/mustache/collections_basic` directory.

## Enabling the Collections App on ownCloud
1. Login as the admin
2. Select Files -> + Apps
3. Select Not Enabled to show list of not enabled Apps
4. Select "Enable" for the Collections App
5. Now Go to OwnCloud home page and you should see the Collections App available when you select Files. 
