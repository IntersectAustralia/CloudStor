/**
 * [loader.js]
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

function getFileName(dir, filename) {
    var baseUrl = '';
    if (dir === '/') {
        baseUrl = filename;
    } else {
        baseUrl = dir.replace(/^\//g, '') + '/' + filename;
    }
    return baseUrl;
}

$(document).ready(function() {
    if (location.pathname.indexOf("files") != -1) {
        $('body').append(
            '<div class="modal" id="addingToCrateModal" tabindex="-1" role="dialog" aria-labelledby="addingToCrateModalLabel" aria-hidden="true">' +
                '<div class="modal-dialog">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h4 class="modal-title" id="addingToCrateModalLabel">Adding file(s) to collection...</h4>' +
                        '</div>' +
                        '<div class="modal-body" style="text-align: center">' +
                            '<img class="center-block" src="' + OC.imagePath('collections', 'ajax-spinner-loader.gif') + '" style="width: 50px">' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>');
        if (typeof FileActions !== 'undefined') {
            FileActions.register('all', 'Add to collection', OC.PERMISSION_READ, OC.imagePath('collections', 'milk-crate-dark.png'), function(filename) {
                $('div#addingToCrateModal').modal();
                var payload = {
                    file: getFileName($('#dir').val(), filename)
                };
                var c_url = OC.generateUrl('apps/collections/crate/add');
                $.ajax({
                    url: c_url,
                    type: 'post',
                    dataType: 'json',
                    data: payload,
                    async: true,
                    complete: function(jqXHR) {
                        $('div#addingToCrateModal').modal('hide');
                        OC.Notification.show(jqXHR.responseJSON.msg);
                        setTimeout(function() {
                            OC.Notification.hide();
                        }, 3000);
                    }
                });
            });
        }
    } else if (location.pathname.indexOf("collections") != -1 && location.pathname.indexOf("user_guide") == -1) {
        loadTemplateVars();
        drawCrateContents();
        initCrateActions();
        initAutoResizeMetadataTabs();
    }
});