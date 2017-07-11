<?php
/**
 * [create_crate_modal.php]
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

<div class="modal" id="createCrateModal" tabindex="-1" role="dialog" aria-labelledby="createCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="createCrateModalLabel">New Collection</h4>
      </div>
      <div class="modal-body">
        <p>
            Metadata Schema
        </p>
        <select id="crate_metadata_schema">
            <?php foreach($_['metadata_schemas'] as $schema) { ?>
                <option value="<?php p($schema['path']); ?>" <?php if($schema['default']) { p('selected'); }?>><?php p($schema['description']); ?></option>
            <?php } ?>
        </select>
      	<p>
        New Collection Name *
        </p>	
        <input id="crate_input_name" name="New Crate Name" type="text" class="modal-input"></input>
        <p/>
        <label id="crate_name_validation_error" validates="New Crate Name" style="color:red;display:none"></label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button id="create_crate_submit" type="button" class="btn btn-primary" disabled>Create</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->