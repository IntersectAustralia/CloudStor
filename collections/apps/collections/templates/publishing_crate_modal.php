<?php
/**
 * [publishing_crate_modal.php]
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
<div class="modal" id="publishingCrateModal" tabindex="-1" role="dialog" aria-labelledby="publishingCrateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="publishingCrateModalLabel">Packaging Collection...</h4>
                <p class="modal-title" id="submitHelpText">The Collection packaging is currently running in the background. If you reload the page or complete another action, the Collection packaging process will still continue and the package will be added to your Files once complete. Please be patient as this process will vary from a few seconds to several minutes depending on the Collection size.</p>
            </div>
            <div class="modal-body" style="text-align: center">
                <img class="center-block" src="<?php print_unescaped(image_path('collections', 'ajax-spinner-loader.gif')); ?>" style="width: 50px">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->