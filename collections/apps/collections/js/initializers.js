/**
 * [initializers.js]
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

/**
 * Renders the crate file tree and metadata menu
 */
function drawCrateContents() {
  var c_url = OC.generateUrl('apps/collections/crate/get_crate?crate_id={crateId}', {
    'crateId': encodeURIComponent(getSelectedCrateId())
  });

  $.ajax({
    url: c_url,
    type: 'get',
    dataType: 'json',
    success: function(crate) {
      $tree = buildFileTree(transformCrate(crate));
      indentTree();
      renderCrateMetadata(crate);
    },
    error: function(jqXHR) {
      displayError(jqXHR.responseJSON.msg)
    }
  });
}

/**
 * Returns the id of the selected crate
 * @returns {*|jQuery} - crate id
 */
function getSelectedCrateId() {
  return $('select#crates').val();
}


function initCrateActions() {

  var metadataEmpty = function() {
    var isEmpty = true;
    $('.metadata').each(function() {
      if ($(this).attr('id') != 'retention_period_value' && $(this).attr('id') != 'edit_embargo_details' && $(this).html() != "") {
        isEmpty = isEmpty && false;
      }
    });
    return isEmpty;
  };

  var getRootNode = function() {
    var tree = $('#files').tree('getTree');
    return tree.children[0];
  };

  var getMatchingNode = function(path) {
    var root_node = getRootNode();
    return checkChildrenForTargetNode(root_node, null, path);
  };

  var checkChildrenForTargetNode = function(node, currentPath, targetPath) {
    for (var i=0; i < node.children.length; i++) {
      var child = node.children[i];
      var childPath = null;

      if (currentPath == null) {
        childPath = child.name;
      } else {
        childPath = currentPath + '/' + child.name;
      }

      if (childPath == targetPath) {
        return child;
      } else {
        // Check children of current child for target
        var recurse = checkChildrenForTargetNode(child, childPath, targetPath);
        if (recurse != null) {
          return recurse; // Only return if recursion returned the target, otherwise check siblings of this child
        }
      }
    }
    return null; // escape recursion when node has no children
  };

  var updateTreeValidityIcons = function(crateValid, filesValid) {
    if (crateValid) {
      $('#files').tree('updateNode', getRootNode(), {valid: 'true'});
    } else {
      $('#files').tree('updateNode', getRootNode(), {valid: 'false'});
    }
    for (var filePath in filesValid) {
      var node = getMatchingNode(filePath);
      if (node != null) {
        if (filesValid[filePath] == true) {
          $('#files').tree('updateNode', node, {valid: 'true'});
        } else {
          $('#files').tree('updateNode', node, {valid: 'false'});
        }
      }
    }
    indentTree();
  };

  var updateFilesValidityModal= function(crateValid, filesValid) {
    var msg = 'Collection has been successfully checked and all items are valid.';
    if (!crateValid) {
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
      resultsTable.prepend('<tr><th>Category</th><th>Group</th><th>Field</th></tr>');
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


  var checkCrate = function() {
    resetValidityModal();
    var id = getSelectedCrateId();
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
            displayError(jqXHR.responseJSON.msg);
          }
      });
  };

  var crateEmpty = function() {
    return $('ul[role="group"] li').length == 0;
  };

  var createCrate = function() {
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
        var crate = data.crate;
        $('#crate_input_name').val('');
        $('#createCrateModal').modal('hide');
        $("#crates").append('<option id="' + crate.id + '" value="' + crate.id + '" >' + truncateString(crate.name,32) + '</option>');
        $("#crates").val(crate.id);
        $('#crates').trigger('change');
        OC.Notification.showTemporary('Collection ' + crate.name + ' successfully created');

      },
      error: function(jqXHR) {
         displayError(jqXHR.responseJSON.msg);
      }
    });
  };

  var deleteCrate = function() {
    var crateId = getSelectedCrateId();
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
        'crate_id': crateId,
        'selected_id': $(_.without(values,crateId)).get(-1)
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

  $('#crate_input_name').keyup(function() {
    var $input = $(this);
    var $error = $('#crate_name_validation_error');
    var $confirm = $('#createCrateModal').find('.btn-primary');
    validateCrateName($input, $error, $confirm);
  });

  $('#createCrateModal').find('.btn-primary').click(createCrate);

  $('#createCrateModal').on('show.bs.modal', function() {
    $('#crate_input_name').val('');
    $("#crate_name_validation_error").hide();
    $(this).find('.btn-primary').prop('disabled', true);
  });

  $('#removeAllFilesModal').find('.btn-primary').click(function() {
    var collectionId = getSelectedCrateId();
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
        var crate = data['crate'];
        updateCrateSize(crate);
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

  $('#deleteCrateModal').on('show.bs.modal', function() {
    var currentCrate = $('#crates option:selected').text().trim();
    if (!metadataEmpty() && !crateEmpty()) {
        $('#deleteCrateMsg').text('Collection "' + currentCrate + '" has items and metadata, proceed with deletion?');
    } else if (!metadataEmpty()) {
        $('#deleteCrateMsg').text('Collection "' + currentCrate + '" has metadata, proceed with deletion?');
    } else if (!crateEmpty()) {
        $('#deleteCrateMsg').text('Collection "' + currentCrate + '" has items, proceed with deletion?');
    }
    
  });

  $('#deleteCrateModal').find('.btn-primary').click(deleteCrate);

  $('#delete').click(function() {
    if (metadataEmpty() && crateEmpty()) {
      deleteCrate();
    } else {        
      $('#deleteCrateModal').modal('show');
    }
  });

  $('#check').click(checkCrate);

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
      success: function(crate) {
        reloadCrateData(crate);
      },
      error: function(jqXHR) {
        displayError(jqXHR.responseJSON.msg);
      }
    });
  });

  var publishCrate = function() {
    var crateId = getSelectedCrateId();
    var c_url = OC.generateUrl('apps/collections/crate/publish');

    $("div#publishingCrateModal").modal();

    $.ajax({
      url: c_url,
      data: {
        'crate_id': crateId,
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
    publishCrate();
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
    var collectionId = getSelectedCrateId();
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
    var crateId = getSelectedCrateId();
    var c_url = OC.generateUrl('apps/collections/crate/export_metadata');
    $.ajax({
      url: c_url,
      data: {
        'crate_id': crateId
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
    }
    OCdialogs.directorypicker('Select Package Location', selectDestinationCallback, cancelCallback, true);
  });
  
}


function initAutoResizeMetadataTabs() {
  $('#meta-data').on('show.bs.collapse', function(e) {
    $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    calculateHeights();
  });
  $('#meta-data').on('hide.bs.collapse', function(e) {
    $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    calculateHeights();
  });

  $(window).resize(function() {
    calculateHeights();
  });
}

