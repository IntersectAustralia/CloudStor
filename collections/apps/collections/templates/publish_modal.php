<?php
/**
 * [publish_modal.php]
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

    script('collections', 'ocdialogs-extensions');

?>

<div class="modal" id="publishModal" tabindex="-1" role="dialog" aria-labelledby="publishModalLabel" aria-hidden="true" data-shown="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h2 class="modal-title" id="publishModalLabel">Package Collection</h2>
      </div>
      <div class="modal-body">

       <div class="publish-meta">
           <h6>Destination</h6>
           <input type="radio" id="publish-destination" value="CloudStor" checked>CloudStor
           <input id="cloudstor-destination" type="text" value="/" readonly>
           <button id="choose-cloudstor-destination">Change</button>
       </div>

        <div class="publish-meta">
             <h6>Collection Size:
               <span id="crate_size_human_publish" class="standard">
                 <?php p(human_file_size($_['selected_crate']->getSize())) ?>
               </span>
             </h6>
          <br/>

          <div style="color:red; font-weight:bold;">
            <span id="publish-consistency"></span>
            <table id="publish-consistency-table" class="table table-striped"></table>
          </div>
        </div>

        <?php if ($_['prompt_packaging_email']) { ?>
          <br/>
          <p>If you would like to be notified when this collection has finished packaging, please enter an email address:</p>
          <label for="publish-notification-email" class="element-invisible">Email address</label>
          <input id="publish-notification-email" name="Publish Confirmation Email" type="text" class="modal-input"/>
          <p>
            <label id="publish-notification-email-validation-error" validates="Publish Confirmation Email"
                   style="color:red;display:none"></label>
          </p>
        <?php } ?>
        <p>Click Package to proceed or click Cancel to exit action.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary">Package</button>
      </div>
    </div>
  </div>
</div>