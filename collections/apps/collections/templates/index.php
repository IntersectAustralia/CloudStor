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
<div id="container" class="crateit">

  <div class="bar-actions">

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

    <div class="pull-right">

      <a id="publish" class="button" data-toggle="modal" data-target="#publishModal" title="Package collection to your Files">
        <i class="fa fa-envelope"></i>
        Package
      </a>

      <a id="package_history" class="button" data-toggle="modal" data-target="#packageHistoryModal" title="Collections packaging history">
        <i class="fa fa-tasks"></i>
        Package History
      </a>

      <a id="check" class="button" data-toggle="modal" data-target="#checkCrateModal" title="Validate collection items">
         <i class="fa fa-check-circle"></i>
         Check Collection
      </a>

      <a id="export" class="button" data-toggle="modal" data-target="#exportMetadataModal" title="Export collection metadata to your Files">
        <i class="fa fa-external-link"></i>
        Export
      </a>

      <a id="removeAllFiles" class="button" data-toggle="modal" data-target="#removeAllFilesModal"
         title="Remove all items from the collection">
        <i class="fa fa-ban"></i>
         Remove All
      </a>

      <a id="delete" class="button" title="Delete collection">
        <i class="fa fa-trash-o"></i>
         Delete
      </a>

      <div class="btn-group">      
        <button id= "help_button" type="button" class="dropdown-toggle" data-toggle="dropdown" title="Help for the Collections app">
          <i class="fa fa-question"></i>
          Help
        </button>
        <ul class="dropdown-menu" style="right: 0;left: auto;">
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

</div>


<div id="files"></div>
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

?>\