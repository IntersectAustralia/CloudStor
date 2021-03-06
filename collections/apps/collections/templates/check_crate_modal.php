<?php
/**
 * [check_crate_modal.php]
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
<div class="modal" id="checkCrateModal" tabindex="-1" role="dialog" aria-labelledby="checkCrateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="checkCrateModalLabel">Collection Validation Results</h4>
      </div>
      <div class="modal-body">
        <img id="checkCrateSpinner" class="center-block" src="<?php print_unescaped(image_path('collections', 'ajax-spinner-loader.gif')); ?>" style="width: 50px">
        <p id="result-message"></p>
        <table id="check-results-table" class="table table-striped">
            <!-- results get loaded by js -->
        </table>          
        
      </div>
      <div class="modal-footer">
        <button id="confirm_checker" type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->