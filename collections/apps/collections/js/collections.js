/**
 * [collections.js]
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

// ToDo: Reassess how errors are displayed, show(msg, {type: 'error'}) can be used to show a closable notification
function displayError(errorMessage) {
    OC.Notification.showTemporary('There was an error: ' + errorMessage);
}

function initialiseCollections() {
    loadTemplateVars();
    initFileTreeHeight();
    renderCollection();
    initCollectionActions();
    initResizableSidebar();
    setSideBarToSessionWidth();
    setFileTreeElementsTitleWidth();
    addCollapseEventHandlers();
    overrideMetadataFormSubmisisonEvent();
    initEditMetadataMode();
    displayMetadataActionBarOrMenu($('#sidebar').width());
    displayCollectionActionBarOrMenu();
}

function loadTemplateVars() {
    templateVars = {};
    $('#hidden_vars').children().each(function() {
        var $el = $(this);
        var key = $el.attr('id');
        var value = $el.text();
        if (!isNaN(value)) {
            value = +value;
        }
        templateVars[key] = value;
    });
}

$(window).resize(function() {
    initFileTreeHeight();
    initResizableSidebar();
    setSideBarToSessionWidth();
    setFileTreeElementsTitleWidth();
    displayMetadataActionBarOrMenu($('#sidebar').width());
    displayCollectionActionBarOrMenu();
});

function renderCollection() {
    var c_url = OC.generateUrl('apps/collections/crate/get_crate?crate_id={crateId}', {
        'crateId': encodeURIComponent(getIdOfSelectedCollection())
    });

    $.ajax({
        url: c_url,
        type: 'get',
        dataType: 'json',
        success: function(collection) {
            renderFileTreeAndMetadata(collection);
        },
        error: function(jqXHR) {
            displayError(jqXHR.responseJSON.msg)
        }
    });
}

function getIdOfSelectedCollection() {
    return $('select#crates').val();
}

function reloadCollection(collection) {
    updateCollectionSize(collection);
    // ToDo: investigate why using .empty() instead of remove and prepend doesn't load new file tree
    $('#files').remove();
    $('#main-area').prepend('<div id="files"></div>');
    renderFileTreeAndMetadata(collection);
}

function renderFileTreeAndMetadata(collectionObj) {
    $tree = buildFileTree(transformCollection(collectionObj));
    indentTree();
    resetMetadataPanel();
    renderCollectionMetadata(collectionObj);
    hideAddRemoveMetadataButtons();
}

function initCollectionActions() {

    var metadataEmpty = function() {
        var isEmpty = true;
        $('.metadata').each(function() {
            if ($(this).attr('id') != 'retention_period_value' && $(this).attr('id') != 'edit_embargo_details' && $(this).html() != "") {
                isEmpty = isEmpty && false;
            }
        });
        return isEmpty;
    };

    var updateFilesValidityModal= function(collectionValid, filesValid) {
        var msg = 'Collection has been successfully checked and all items are valid.';
        if (!collectionValid) {
            msg = 'There are 1 or more invalid items in your collection.';
            msg += ' Please review the collection file tree for the invalid items and delete these items.';
            msg += ' The following items are invalid:';
            for (var filePath in filesValid) {
                if (!filesValid[filePath]) {
                    var newRow = '<tr><td>' + filePath + '</td></tr>';
                    $("#check-results-table").last().append(newRow);
                }
            }
        }
        $("#result-message").text(msg);
    };

    var updateMetadataValidityModal = function(metadataValid, invalidFields) {
        var msg = 'Collection has been successfully checked and all metadata is valid.';
        if (!metadataValid) {
            msg = 'There are 1 or more invalid metadata fields in your collection.';
            msg += ' Please review the collection metadata for the required fields and ensure that there are no empty field values.';
            msg += ' The following required fields contain empty values:';
            var resultsTable = $("#check-results-table");
            for (var i = 0; i < invalidFields.length; i++) {
                var groupName = '';
                if (invalidFields[[i]].groupName != null) {
                    groupName = invalidFields[[i]].groupName;
                }
                var newRow = '<tr><td>'+invalidFields[i].categoryName+'</td><td>'+groupName+'</td><td>'+invalidFields[i].fieldName+'</td></tr>';
                resultsTable.append(newRow);
            }
        }
        $("#result-message").text(msg);
    };

    var resetValidityModal = function() {
        $('#result-message').text('');
        $('#check-results-table').empty();
    };

    var checkCollection = function() {
        resetValidityModal();
        var id = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/check?crate_id={crate_id}', {
            crate_id: encodeURIComponent(id)
        });
        $.ajax({
            url: c_url,
            type: 'get',
            dataType: 'json',
            async: false,
            beforeSend: function() {
                $('#checkCrateSpinner').show();
            },
            complete: function() {
                $('#checkCrateSpinner').hide();
            },
            success: function(data) {
                updateTreeValidityIcons(data.crateValid, data.filesValid);
                updateFilesValidityModal(data.crateValid, data.filesValid);
            },
            error: function(data) {
                // TODO Format errors
                displayError(jqXHR.responseJSON.msg);
            }
        });
    };

    var collectionEmpty = function() {
        return $('ul[role="group"] li').length == 0;
    };

    var createCollection = function() {
        var params = {
            'name': $('#crate_input_name').val(),
            'schema': $('#crate_metadata_schema').val()
        };
        var c_url = OC.generateUrl('apps/collections/crate/create');
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            async: false,
            data: params,
            success: function(data) {
                var collection = data.crate;
                $('#crate_input_name').val('');
                $('#createCrateModal').modal('hide');
                $("#crates").append('<option id="' + collection.id + '" value="' + collection.id + '" >' + truncateString(collection.name, 32) + '</option>');
                $("#crates").val(collection.id);
                $('#crates').trigger('change');
                OC.Notification.showTemporary('Collection ' + collection.name + ' successfully created');

            },
            error: function(jqXHR) {
                // TODO: Make sure all ajax errors are this form instead of data.msg
                displayError(jqXHR.responseJSON.msg);
            }
        });
    };

    $('#createCrateModal').find('.btn-primary').click(createCollection);

    $('#crate_input_name').keyup(function() {
        var $input = $(this);
        var $error = $('#crate_name_validation_error');
        var $confirm = $('#createCrateModal').find('.btn-primary');
        validateCollectionName($input, $error, $confirm);
    });

    $('#createCrateModal').on('show.bs.modal', function() {
        $('#crate_input_name').val('');
        $("#crate_name_validation_error").hide();
        $(this).find('.btn-primary').prop('disabled', true);
    });

    var renameCollection = function() {
        var newCollectionName = $('#rename-crate').val();
        var c_url = OC.generateUrl('apps/collections/crate/update');
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            data: {
                'crate_id': getIdOfSelectedCollection(),
                'fields': {
                    'name': newCollectionName.trim()
                }
            },
            success: function (data) {
                OC.Notification.showTemporary(data.msg);

                var crateId = getRootNode().crateId;
                $tree.tree('updateNode', getRootNode(), newCollectionName);
                indentTree();
                $('#crates option:selected').val(crateId).attr('id', crateId).text(truncateString(newCollectionName, 32));

                $('#renameCrateModal').modal('hide');
            },
            error: function (jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            }
        });
    };

    $('#renameCrateModal').find('.btn-primary').click(renameCollection);

    $('#rename-crate').keyup(function() {
        var $input = $(this);
        var $error = $('#rename_crate_error');
        var $confirm = $('#renameCrateModal').find('.btn-primary');
        validateCollectionName($input, $error, $confirm);
    });

    $('#renameCrateModal').on('show.bs.modal', function() {
        $('#rename-crate').val(getCollectionNameViaFileTree());
        $("#rename_crate_error").hide();
        $(this).find('.btn-primary').prop('disabled', true);
    });

    var deleteCollection = function() {
        var collectionId = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/delete');
        var options = $('select#crates option');
        var values = $.map(options ,function(option) {
            return option.value;
        });
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            data: {
                'crate_id': collectionId,
                'selected_id': $(_.without(values,collectionId)).get(-1)
            },
            success: function(data) {
                OC.Notification.showTemporary(data.msg);
                location.reload();
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            },
            complete: function() {
                $('#deleteCrateModal').modal('hide');
            }
        });
    };

    $('#deleteCrateModal').on('show.bs.modal', function() {
        var currentCollection = $('#crates option:selected').text().trim();
        if (!metadataEmpty() && !collectionEmpty()) {
            $('#deleteCrateMsg').text('Collection "' + currentCollection + '" has items and metadata, proceed with deletion?');
        } else if (!metadataEmpty()) {
            $('#deleteCrateMsg').text('Collection "' + currentCollection + '" has metadata, proceed with deletion?');
        } else if (!collectionEmpty()) {
            $('#deleteCrateMsg').text('Collection "' + currentCollection + '" has items, proceed with deletion?');
        }

    });

    $('#deleteCrateModal').find('.btn-primary').click(deleteCollection);

    $('#delete-collection, #menu-delete-collection').click(function() {
        if (metadataEmpty() && collectionEmpty()) {
            deleteCollection();
        } else {
            $('#deleteCrateModal').modal('show');
        }
    });

    $('#removeAllFilesModal').find('.btn-primary').click(function() {
        var collectionId = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/remove_all_files');
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            data: {
                'collection_id': collectionId
            },
            success: function(data) {
                var children = $tree.tree('getNodeById', 'rootfolder').children;
                while (children.length > 0) {
                    children.forEach(function(node) {
                        $tree.tree('removeNode', node);
                    });
                }
                var collection = data['crate'];
                updateCollectionSize(collection);
                indentTree();
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            },
            complete: function() {
                $('#removeAllFilesModal').modal('hide');
            }
        });
    });

    $('#check-collection-validity, #menu-check-collection-validity').click(checkCollection);

    $('#crates').change(function() {
        var id = $(this).val();
        var c_url = OC.generateUrl('apps/collections/crate/select_crate');
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            async: true,
            data: {
                'crate_id': id
            },
            success: function(collection) {
                reloadCollection(collection);
                metadataViewMode();
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            }
        });
    });

    var publishCollection = function() {
        var collectionId = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/publish');

        $("div#publishingCrateModal").modal();

        $.ajax({
            url: c_url,
            data: {
                'crate_id': collectionId,
                'email_recipient': $('#publish-notification-email').val(),
                'destination': {
                    'service': $('#publish-destination').val(),
                    'location': $('#cloudstor-destination').val()
                }
            },
            type: 'post',
            dataType: 'json',
            success: function(data) {
                updateTreeValidityIcons(data.crateValid, data.filesValid);
                if (data.crateValid && data.metadataValidity.metadataValid) {
                    OC.Notification.showTemporary(data.msg);
                } else {
                    resetValidityModal();
                    if (!data.crateValid) {
                        updateFilesValidityModal(data.crateValid, data.filesValid);
                    } else {
                        updateMetadataValidityModal(data.metadataValidity.metadataValid, data.metadataValidity.invalidFields);
                    }
                    $('#checkCrateSpinner').hide();
                    $("div#checkCrateModal").modal("show");
                }
                $("div#publishingCrateModal").modal("hide");
            },
            error: function(jqXHR) {
                $("div#publishingCrateModal").modal("hide");
                displayError(jqXHR.responseJSON.msg);
            }
        });
    };

    $('#publish-notification-email').keyup(function() {
        var $input = $(this);
        var $error = $('#publish-notification-email-validation-error');
        var $confirm = $('#publishModal').find('.btn-primary');
        validateEmail($input, $error, $confirm);
    });

    var $publishModal = $('#publishModal');
    $publishModal.on('show.bs.modal', function() {
        if (!$publishModal.data('shown')) {
            $('#publish-notification-email').val('');
            $('#publish-notification-email-validation-error').hide();
            $publishModal.data('shown', true);
        }
    });
    $publishModal.find('.btn-primary').click(function() {
        $('#publishModal').modal('hide');
        $publishModal.data('shown', false);
        publishCollection();
    });
    $publishModal.find('.btn-default, .close').click(function() {
        $publishModal.data('shown', false);
    });

    /**
     * Inserts a list of packaging jobs for the current collection into the appropriate modal.
     */
    $('#packageHistoryModal').on('show.bs.modal', function() {
        var packagingJobsTable = $('#packaging-jobs-table');
        var packagingHistoryMessage = $('#packaging-history-message');
        var collectionId = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/packaging_jobs');
        $.ajax({
            url: c_url,
            data: {
                'collection_id': collectionId
            },
            type: 'get',
            dataType: 'json',
            beforeSend: function() {
                packagingHistoryMessage.text('');
                packagingJobsTable.children('tbody').empty();
                packagingJobsTable.children('thead').empty();
                $('#packagingHistorySpinner').show();
            },
            success: function(jobs) {
                $('#packagingHistorySpinner').hide();
                if (jobs.length > 0) {
                    packagingHistoryMessage.text('Packaging history for all collections:');
                    packagingJobsTable.children('thead').append('<tr>' +
                        '<th>Collection</th>' +
                        '<th>Start date and time</th>' +
                        '<th>Status</th>' +
                        '</tr>'
                    );
                    for (var i = 0; i < jobs.length; i++) {
                        packagingJobsTable.children('tbody').append('<tr>' +
                            '<td>'+jobs[i].collectionName+'</td>' +
                            '<td>'+moment(jobs[i].initiationTime).toString()+'</td>' +
                            '<td>'+jobs[i].status+'</td>' +
                            '</tr>'
                        );
                    }
                } else {
                    packagingHistoryMessage.text('No collections have been packaged yet.');
                }
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            }
        });
    });

    var exportMetadata = function() {
        var collectionId = getIdOfSelectedCollection();
        var c_url = OC.generateUrl('apps/collections/crate/export_metadata');
        $.ajax({
            url: c_url,
            data: {
                'crate_id': collectionId
            },
            type: 'post',
            dataType: 'json',
            success: function(data) {
                if (data.metadataValidity.metadataValid) {
                    OC.Notification.showTemporary(data.msg);
                } else {
                    resetValidityModal();
                    updateMetadataValidityModal(data.metadataValidity.metadataValid, data.metadataValidity.invalidFields);
                    $('#checkCrateSpinner').hide();
                    $("div#checkCrateModal").modal("show");
                }
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
            }
        });
    };

    $('#exportMetadataModal').find('.btn-primary').click(function() {
        $('#exportMetadataModal').modal('hide');
        exportMetadata();
    });

    $('#choose-cloudstor-destination').click(function () {
        $("div#publishModal").modal('hide');
        var selectDestinationCallback = function (datapath) {
            $('#cloudstor-destination').val(datapath);
            $("div#publishModal").modal('show');
        };
        var cancelCallback = function () {
            $("div#publishModal").modal('show');
        };
        OCdialogs.directorypicker('Select Package Location', selectDestinationCallback, cancelCallback, true);
    });
}

function updateCollectionSize(collection) {
    var maxZipMB = templateVars['max_zip_mb'];
    var publishWarningMB = templateVars['publish_warning_mb'];

    var humanCollectionSize = filesize(collection.size, {round: 1});
    $('#crate_size_human').text(humanCollectionSize);
    $('#crate_size_human_publish').text(humanCollectionSize);

    var collection_size_mb = collection.size / (1024 * 1024);
    var warnings = [];
    var notify = false;
    var disablePublish = false;
    //var disableDownload = false;

    if (maxZipMB > 0 && collection_size_mb > maxZipMB) {
        warnings.push('exceeds ZIP file limit');
        //warnings.push('package and download operations are disabled');
        warnings.push('package operation is disabled');
        //disableDownload = true;
        disablePublish = true;
        notify = true;
    } else if (publishWarningMB > 0 && collection_size_mb > publishWarningMB) {
        warnings.push('will cause publishing to take a long time');
        notify = true;
    }

    var msg = 'WARNING: Collection size ' + warnings.join(', and ') + '.';
    if (disablePublish) {
        $('#publish-collection, #menu-publish-collection').attr("disabled", "disabled");
    } else {
        $('#publish-collection, #menu-publish-collection').removeAttr("disabled");
    }
    // Todo: remove unused lines
    //if (disableDownload) {
    //    $('#download').attr("disabled", "disabled");
    //} else {
    //    $('#download').removeAttr("disabled");
    //}

    if (notify) {
        OC.Notification.showTemporary(msg);
    }
}

// TODO: Migrate the clients of the following to the old validations.js framework
function validateEmail($input, $error, $confirm) {
    validateTextLength($input, $error, $confirm, 128, true);
    var email = $input.val();
    var isEmail = function() {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    };
    if (email.length > 0 && !isEmail()) {
        $confirm.prop('disabled', true);
        $error.text('Not recognised as a valid email address');
        $error.show();
    }
}

function validateTextLength($input, $error, $confirm, maxLength, allowEmpty) {
    if (typeof(maxLength) === 'undefined') {
        maxLength = 256;
    }
    if (typeof(allowEmpty) === 'undefined') {
        allowEmpty = false;
    }
    var inputText = $input.val();
    var emptyText = function() {
        return (!inputText || /^\s*$/.test(inputText));
    };
    if (!allowEmpty && emptyText()) {
        $confirm.prop('disabled', true);
        $error.text('Field cannot be blank');
        $error.show();
    } else if (inputText.length > maxLength) {
        $error.text('Field has reached the limit of ' + maxLength + ' characters');
        $input.val(inputText.substr(0, maxLength));
        $error.show();
        $confirm.prop('disabled', false);
    } else {
        $confirm.prop('disabled', false);
        $error.hide();
    }
}

// TODO: See if some of this can make use of the old validation framework
function validateCollectionName($input, $error, $confirm) {
    var inputName = $.trim($input.val());
    var collections = $.map($('#crates > option'), function(el, i) {
        return $(el).attr('id');
    });
    var emptyName = function() {
        return (!inputName || /^\s*$/.test(inputName));
    };
    var existingName = function() {
        return collections.indexOf(inputName) > -1;
    };

    var regex = /[\/\\\<\>:\"\|?\*]/;

    if (existingName() || emptyName()) {
        $confirm.prop('disabled', true);
        if (emptyName()) {
            $error.text('Collection name cannot be blank');
        } else {
            $error.text('Collection with name "' + inputName + '" already exists');
        }
        $error.show();
    } else if (inputName.length > 128) {
        $error.text('Collection name has reached the limit of 128 characters');
        $input.val(inputName.substr(0, 128));
        $error.show();
        $confirm.prop('disabled', false);
    } else if (regex.test(inputName)) {
        $confirm.prop('disabled', true);
        $error.text("Invalid name. Illegal characters '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed");
        $error.show();
    } else {
        $confirm.prop('disabled', false);
        $error.hide();
    }
}

function validateItemName($input, $error, $confirm, $siblings) {
    var inputName = $.trim($input.val()).toLowerCase();
    var emptyName = function() {
        return (!inputName || /^\s*$/.test(inputName));
    };
    var items = $.map($siblings, function(el, i) {
        return el.name.toLowerCase();
    });
    var existingName = function() {
        return $.inArray(inputName, items) > -1;
    };
    var regex = /[\/\\\<\>:\"\|?\*]/;

    if (existingName() || emptyName()) {
        $confirm.prop('disabled', true);
        if (emptyName()) {
            $error.text('Item name cannot be blank');
        } else {
            $error.text('Item with name "' + inputName + '" already exists');
        }
        $error.show();
    } else if (inputName.length > 128) {
        $error.text('Item name has reached the limit of 128 characters');
        $input.val(inputName.substr(0, 128));
        $error.show();
        $confirm.prop('disabled', false);
    } else if (regex.test(inputName)) {
        $confirm.prop('disabled', true);
        $error.text("Invalid name. Illegal characters '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed");
        $error.show();
    } else {
        $confirm.prop('disabled', false);
        $error.hide();
    }
}

function truncateString(str, length) {
    return str.length > length ? str.substring(0, length - 3) + '...' : str
}

/**
 * Generates a unique-ish id comprised of the milliseconds elapsed since 1 January 1970 00:00:00 UTC concatenated
 *  with a seven digit random number. While it is possible for collisions to occur, the chance of this happening
 *  would be minimal.
 * @returns {number} - 13 digit unique-ish id
 */
function generateTimestampBasedId() {
    return Date.now() + Math.floor((Math.random()) * 0x1000000);
}


function displayCollectionActionBarOrMenu() {
    var $actionBar = $('#content .bar-actions');
    var $collectionActions = $('#collection-actions');
    var $collectionActionsMenu = $('#collection-actions-dropdown');

    function showActionIconAndText() {
        $actionBar.addClass('collection-actions-icon-and-text');
        $actionBar.removeClass('collection-actions-icon-only');
        $actionBar.removeClass('collection-actions-menu');
        $actionBar.removeClass('collection-actions-no-size');
    }

    function showActionIconOnly() {
        $actionBar.removeClass('collection-actions-icon-and-text');
        $actionBar.addClass('collection-actions-icon-only');
        $actionBar.removeClass('collection-actions-menu');
        $actionBar.removeClass('collection-actions-no-size');
    }

    function showActionMenuOnly() {
        $actionBar.removeClass('collection-actions-icon-and-text');
        $actionBar.removeClass('collection-actions-icon-only');
        $actionBar.addClass('collection-actions-menu');
        $actionBar.removeClass('collection-actions-no-size');
    }

    function showActionMenuOnlyNoSize() {
        $actionBar.removeClass('collection-actions-icon-and-text');
        $actionBar.removeClass('collection-actions-icon-only');
        $actionBar.addClass('collection-actions-menu');
        $actionBar.addClass('collection-actions-no-size');
    }

    function actionBarOverflowing($collectionActionContainer) {
        return $actionBar.width() < ($('#content .bar-actions-left').outerWidth() + $collectionActionContainer.outerWidth());
    }

    showActionIconAndText();
    if (actionBarOverflowing($collectionActions)) {
        showActionIconOnly();

        if (actionBarOverflowing($collectionActions)) {
            showActionMenuOnly();

            if (actionBarOverflowing($collectionActionsMenu)) {
                showActionMenuOnlyNoSize();
            }
        }
    }
}