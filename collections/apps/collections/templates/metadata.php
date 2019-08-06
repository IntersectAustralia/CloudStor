<?php
/**
 * [metadata.php]
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
<div id="sidebar" class="sidebar">
    <div class="container-metadata">
        <form id="metadataForm" method="post">
            <div id="top-bar">
                <div class="btn-group">
                    <button id="metadata-action-menu" type="button" class="dropdown-toggle" data-toggle="dropdown" title="Metadata action menu">
                        <i class="fa fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <button type="button" id="menu-expand-all-categories" class="dropdown-btn" title="Expand all metadata categories">
                                <i class="fa fa-expand"></i>
                                <span class="button-text">Expand All</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" id="menu-collapse-all-categories" class="dropdown-btn" title="Collapse all metadata categories">
                                <i class="fa fa-compress"></i>
                                <span class="button-text">Collapse All</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" id="menu-edit-metadata" class="dropdown-btn" title="Edit metadata">
                                <i class="fa fa-pencil"></i>
                                <span class="button-text">Edit</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" id="menu-cancel-metadata" class="dropdown-btn" title="Cancel metadata changes">
                                <i class="fa fa-ban"></i>
                                <span class="button-text">Cancel</span>
                            </button>
                        </li>
                        <li>
                            <button type="submit" id="menu-save-metadata" class="dropdown-btn" title="Save metadata changes">
                                <i class="fa fa-save"></i>
                                <span class="button-text">Save</span>
                            </button>
                        </li>
                        <li>
                            <button type="submit" id="menu-save-metadata-and-continue" class="dropdown-btn" title="Save metadata changes and continue editing">
                                <i class="fa fa-edit"></i>
                                <span class="button-text">Save and continue</span>
                            </button>
                        </li>
                    </ul>

                    <span id="metadata-action-bar">
                        <button type="button" id="expand-all-categories" title="Expand all metadata categories">
                            <i class="fa fa-expand"></i>
                            <span class="button-text">Expand All</span>
                        </button>
                        <button type="button" id="collapse-all-categories" title="Collapse all metadata categories">
                            <i class="fa fa-compress"></i>
                            <span class="button-text">Collapse All</span>
                        </button>
                        <button type="button" id="edit-metadata" title="Edit metadata">
                            <i class="fa fa-pencil"></i>
                            <span class="button-text">Edit</span>
                        </button>
                        <button type="button" id="cancel-metadata" title="Cancel metadata changes">
                            <i class="fa fa-ban"></i>
                            <span class="button-text">Cancel</span>
                        </button>
                        <button type="submit" id="save-metadata" title="Save metadata changes">
                            <i class="fa fa-save"></i>
                            <span class="button-text">Save</span>
                        </button>
                        <button type="submit" id="save-metadata-and-continue" title="Save metadata changes and continue editing">
                            <i class="fa fa-edit"></i>
                            <span class="button-text">Save and continue</span>
                        </button>
                    </span>
                </div>

                <span id="legend" class="hidden">
                    <span class="required">*</span> indicates required field
                </span>
            </div>
            <div class="panel-group" id="meta-data"></div>
        </form>
    </div>
</div>