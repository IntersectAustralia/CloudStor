<?php
/**
 * [user_guide.php]
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
?>
<html>
    <head>
        <title>Collections User Guide</title>
        <link id="collection-css" rel="stylesheet" type="text/css" href="">
    </head>
    <body>
    <div id="app">
        <div id="app-content">
            <div id="app-content-wrapper">
                <div class="page-content">
                    <div class="help-section">
                        <h2>Creating Collection</h2>
                        <p>By default every user will have a Collection named ‘New Collection’. You can rename this Collection to a more relevant name or you can create a new Collection using the ‘+’ button. Collection names must be unique. At the time of creation you can select a metadata schema, which will determine the set metadata fields that can be used to describe your Collection.</p>
                    </div>

                    <div class="help-section">
                        <h2>Adding files to Collections</h2>
                        <p>You can add files to a Collection via the Files application. In the Files view select the ellipsis(...) for the file or folder and then press the ‘Add to Collection’ action. The file or folder will be added to the currently active Collection. So for example if you had a Collection, “Tiwi Analysis” selected in the Collections App and you switch to the Files App, then the files or folders added to the Collection via the Files app will be added to ‘Tiwi Analysis’.</p>
                    </div>

                    <div class="help-section">
                        <h2>Switching Collections</h2>
                        <p>You can switch Collections by selecting the drop menu and then selecting a different Collection from within the Collections app.</p>
                    </div>

                    <div class="help-section">
                        <h2>Renaming Collection</h2>
                        <p>You can rename a Collection by selecting the 'Rename' button in the top bar. The updated Collection name must be unique.</p>
                    </div>

                    <div class="help-section">
                        <h2>Organising Collection Files/Folders</h2>
                        <p>Any file/folder added to a Collection can be renamed or moved via drag and drop in the Collections App. You also have the option to create new folders via the  ‘Add folder Item’ action that is displayed when you hover the ellipsis(...) for any folder in the Collection. Please note, that any changes to files/folders in your Collection will not affect your original files in the Files view. This means you can use Collections to organise your research data in a different way, with different folders and file names, from your original data.</p>
                    </div>

                    <div class="help-section">
                        <h2>Adding Collection Metadata</h2>
                        <p>Collection metadata can be added via the right hand side panel. Each of the categories can be expanded to show the metadata fields. Please note that some metadata fields are mandatory and you cannot package or export your metadata unless they have been filled.</p>
                        <p>Some fields are repeatable; for example you can have more than one Description. If a field is repeatable, it will have a button below it. Simply click this button to add repeats of that field. Please  note that the seperator between the files and metadata panel is adjustable and can be used to adjust the width of each panel to suit your preference.</p>
                    </div>

                    <div class="help-section">
                        <h2>Checking Collection</h2>
                        <p>The ‘Check Collection’ action allows you to ensure that the Collections app still has access to all files that have been added to the Collection. If you remove a file from your CloudStor Files, Collections won’t be able to see it, and clicking Check Collection will tell you which files it cannot see. If you restore them to your Files, Collections will be able to see them again. The Collection tree will also mark valid files with a tick and missing files with a cross. You cannot package your Collection unless all the files in the Collection are valid.</p>
                        <p>Hint: If you accidentally delete files from CloudStor, you may be able to find them in the Deleted Files area and restore them by clicking ‘Restore’.</p>
                    </div>

                    <div class="help-section">
                        <h2>Exporting Collection Metadata</h2>
                        <p>You can export the Collection metadata by clicking ‘Export’. This will export the metadata you entered as XML and save it to your Files. The XML will be named after your Collection name. Please ensure that all mandatory metadata fields are filled otherwise you will not be able to export the metadata.</p>
                    </div>

                    <div class="help-section">
                        <h2>Packaging Collection</h2>
                        <p>You can package your Collection to a CloudStor location of your choice by clicking ‘Package’. As part of the packaging process Collections will initially check the files in the Collection are still valid. The Collection will be packaged to your Files and will be a ZIP file named after your Collection name. The packaged Collection will contain your Collection files and metadata. Please ensure that you have adequate quota (at least your Collection size) for the packaged ZIP before packaging otherwise the packaging process will fail. The packaging process may take a few mins or up to an hour depending on the Collection size. The packaging process runs in the background so feel free to logout and you will receive an email notification once it’s complete.</p>
                    </div>

                    <div class="help-section">
                        <h2>Viewing Package History</h2>
                        <p>You can view your packaging history by selecting the ‘Package History’ action. This will list all the Collections you have packaged in the past and their status.</p>
                    </div>

                    <div class="help-section">
                        <h2>Removing Collection Items</h2>
                        <p>You can remove all the files/folders added to a Collection by selecting the ‘Remove All’ action. This action will not affect the metadata added to the Collection. This action will also not affect the files/folders in your Files.</p>
                    </div>

                    <div class="help-section">
                        <h2>Deleting Collection</h2>
                        <p>You can delete a Collection using the ‘Delete’ action. This will delete the folders, files and metadata added to the Collection, it will not affect any files or folders in your Files.</p>
                    </div>

                    <div class="footer">
                        <hr/>
                        <a href="https://intersect.org.au/products/collections" target="_blank">
                            <img id="intersect-logo" alt="Powered by Intersect"
                                 src="<?php print_unescaped(image_path('collections', 'PoweredbyINTERSECT.png')); ?>">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
