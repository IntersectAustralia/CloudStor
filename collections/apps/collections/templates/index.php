<?php
/**
 * [index.php]
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

<div class="bar-actions">
  <div class="bar-actions-left">
    <img class="icon svg" src="<?php print_unescaped(image_path('collections', 'milk-crate-dark.png')); ?>">

    <a id="create" class="button" data-toggle="modal" data-target="#createCrateModal" title="Create a new collection">
      <i class="fa fa-plus"></i>
    </a>
    <label for="crates" class="element-invisible">Collection Selector</label>
    <select id="crates" title="Swap between existing collections">

      <?php foreach($_['crates'] as $crate) {
        $selected = $_['selected_crate']->getId() == $crate->getId() ? "selected" : "";
        ?>
        <option id="<?php p($crate->getId()) ?>" value="<?php p($crate->getId()) ?>" <?php p($selected) ?>>
          <?php p(strlen($crate->getName()) > 32 ? substr($crate->getName(),0,30)."..." : $crate->getName()) ?>
        </option>
      <?php } ?>
    </select>

    <div id="crate-size">
      <h6 class="info">
        Collection Size:
          <span id="crate_size_human" class="standard">
              <?php p(human_file_size($_['selected_crate']->getSize())) ?>
          </span>
      </h6>
    </div>
  </div>

  <div id="collection-actions" class="bar-actions-right pull-right">

    <a id="rename-collection" class="button" data-toggle="modal" data-target="#renameCrateModal" title="Rename the collection">
      <i class="fa fa-pencil"></i>
      <span class="button-text">Rename</span>
    </a>

    <a id="publish-collection" class="button" data-toggle="modal" data-target="#publishModal" title="Package collection to your Files">
      <i class="fa fa-envelope"></i>
      <span class="button-text">Package</span>
    </a>

    <a id="collection-package-history" class="button" data-toggle="modal" data-target="#packageHistoryModal" title="Collections packaging history">
      <i class="fa fa-tasks"></i>
      <span class="button-text">Package History</span>
    </a>

    <a id="check-collection-validity" class="button" data-toggle="modal" data-target="#checkCrateModal" title="Validate collection items">
      <i class="fa fa-check-circle"></i>
      <span class="button-text">Check Collection</span>
    </a>

    <a id="export-collection-metadata" class="button" data-toggle="modal" data-target="#exportMetadataModal" title="Export collection metadata to your Files">
      <i class="fa fa-external-link"></i>
      <span class="button-text">Export</span>
    </a>

    <a id="remove-collection-files" class="button" data-toggle="modal" data-target="#removeAllFilesModal" title="Remove all items from the collection">
      <i class="fa fa-ban"></i>
      <span class="button-text"> Remove All</span>
    </a>

    <a id="delete-collection" class="button" title="Delete collection">
      <i class="fa fa-trash-o"></i>
      <span class="button-text">Delete</span>
    </a>

    <div class="btn-group">
      <button id= "help_button" type="button" class="dropdown-toggle" data-toggle="dropdown" title="Help for the Collections app">
        <i class="fa fa-question"></i>
        <span class="button-text">Help</span>
      </button>
      <ul class="dropdown-menu">
          <li>
            <a id="about_button" class="dropdown-btn" data-toggle="modal" data-target="#helpModal" title="About the Collections app">
              <i class="fa fa-question"></i>
               About
            </a>
          </li>
        <li>
          <a id="userguide" href="<?php p($_['user_guide_url']) ?>" target="_blank"
             class="dropdown-btn" title="Guide on how to use the Collections app">
            <i class="fa fa-book"></i>
             User Guide
          </a>
        </li>
      </ul>
    </div>
  </div>

  <div id="collection-actions-dropdown" class="bar-actions-right pull-right">
    <div class="btn-group">
      <button type="button" class="dropdown-toggle" data-toggle="dropdown" title="Collections actions">
        <i class="fa fa-ellipsis-h"></i>
        <span class="button-text">Menu</span>
      </button>
      <ul class="dropdown-menu">
        <li>
          <a id="menu-rename-collection" class="dropdown-btnp" data-toggle="modal" data-target="#renameCrateModal" title="Rename the collection">
            <i class="fa fa-pencil"></i>
            <span class="button-text">Rename</span>
          </a>
        </li>
        <li>
          <a id="menu-publish-collection" class="dropdown-btn" data-toggle="modal" data-target="#publishModal" title="Package collection to your Files">
            <i class="fa fa-envelope"></i>
            <span class="button-text">Package</span>
          </a>
        </li>
        <li>
          <a id="menu-collection-package-history" class="dropdown-btn" data-toggle="modal" data-target="#packageHistoryModal" title="Collections packaging history">
            <i class="fa fa-tasks"></i>
            <span class="button-text">Package History</span>
          </a>
        </li>
        <li>
          <a id="menu-check-collection-validity" class="dropdown-btn" data-toggle="modal" data-target="#checkCrateModal" title="Validate collection items">
            <i class="fa fa-check-circle"></i>
            <span class="button-text">Check Collection</span>
          </a>
        </li>
        <li>
          <a id="menu-export-collection-metadata" class="dropdown-btn" data-toggle="modal" data-target="#exportMetadataModal" title="Export collection metadata to your Files">
            <i class="fa fa-external-link"></i>
            <span class="button-text">Export</span>
          </a>
        </li>
        <li>
          <a id="menu-remove-collection-files" class="dropdown-btn" data-toggle="modal" data-target="#removeAllFilesModal" title="Remove all items from the collection">
            <i class="fa fa-ban"></i>
            <span class="button-text"> Remove All</span>
          </a>
        </li>
        <li>
          <a id="menu-delete-collection" class="dropdown-btn" title="Delete collection">
            <i class="fa fa-trash-o"></i>
            <span class="button-text">Delete</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="btn-group">
      <button id= "help_button" type="button" class="dropdown-toggle" data-toggle="dropdown" title="Help for the Collections app">
        <i class="fa fa-question"></i>
        <span class="button-text">Help</span>
      </button>
      <ul class="dropdown-menu">
        <li>
          <a id="about_button" class="dropdown-btn" data-toggle="modal" data-target="#helpModal" title="About the Collections app">
            <i class="fa fa-question"></i>
            About
          </a>
        </li>
        <li>
          <a id="userguide" href="<?php p($_['user_guide_url']) ?>" target="_blank"
             class="dropdown-btn" title="Guide on how to use the Collections app">
            <i class="fa fa-book"></i>
            User Guide
          </a>
        </li>
      </ul>
    </div>
    </div>
</div>

<div id="main-area">
  <div id="files"></div>
</div>

<div class="attribution">
  <a href="https://intersect.org.au/products/collections" target="_blank">
    <img id="intersect-logo" alt="Powered by Intersect"
         src="<?php print_unescaped(image_path('collections', 'PoweredbyINTERSECT.png')); ?>">
  </a>
</div>

<?php

  print_unescaped($this->inc('metadata'));
  print_unescaped($this->inc('javascript_vars'));
  print_unescaped($this->inc('help_modal'));   
  print_unescaped($this->inc('publish_modal'));   
  print_unescaped($this->inc('create_crate_modal'));   
  print_unescaped($this->inc('remove_crate_modal'));   
  print_unescaped($this->inc('rename_item_modal'));   
  print_unescaped($this->inc('rename_crate_modal'));   
  print_unescaped($this->inc('add_folder_modal'));   
  print_unescaped($this->inc('clear_crate_modal'));   
  print_unescaped($this->inc('delete_crate_modal'));   
  print_unescaped($this->inc('clear_metadata_modal'));  
  print_unescaped($this->inc('check_crate_modal'));
  print_unescaped($this->inc('publishing_crate_modal'));
  print_unescaped($this->inc('packaging_job_modal'));
  print_unescaped($this->inc('export_metadata_modal'));
  print_unescaped($this->inc('cancel_metadata_modal'));

?>\