<?php
/**
 * [packaging_job_modal.php]
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
<div class="modal" id="packageHistoryModal" tabindex="-1" role="dialog" aria-labelledby="packageHistoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="packageHistoryModalLabel">Package history</h4>
      </div>
      <div class="modal-body">
        <div>
          <img id="packagingHistorySpinner" class="center-block"
               src="<?php print_unescaped(image_path('collections', 'ajax-spinner-loader.gif')); ?>" style="width: 50px">
        </div>
        <span id="packaging-history-message"></span>
        <table id="packaging-jobs-table" class="table table-striped">
          <thead>
            <tr>
              <!-- results get loaded by js -->
            </tr>
          </thead>
          <tbody>
            <!-- results get loaded by js -->
          </tbody>
        </table>          
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>