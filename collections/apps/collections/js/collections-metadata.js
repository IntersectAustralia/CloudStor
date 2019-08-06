/**
 * [collections-metadata.js]
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

var submitMetadataAndCloseEdit = false;
// Overrides the metadata form submission event so that AJAX can be used instead
function overrideMetadataFormSubmisisonEvent() {
    $("#metadataForm").submit(function(event) {
        event.preventDefault();
        saveMetadata();
        if (submitMetadataAndCloseEdit) {
            metadataViewMode();
            setMetadataViewToEditValues();
            submitMetadataAndCloseEdit = false;
        }
    });
}

function addCollapseEventHandlers() {
    var $metadataPanel = $('#meta-data');

    $metadataPanel.on('show.bs.collapse', function(e) {
        if (e.target.classList.contains('metadata_category')) {
            $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-right').addClass('fa-caret-down');
        } else if (e.target.classList.contains('group_fields')) {
            $(e.target).parent().find('.fa-caret-right').removeClass('fa-caret-right').addClass('fa-caret-down');
        }
    });

    $metadataPanel.on('hide.bs.collapse', function(e) {
        if (e.target.classList.contains('metadata_category')) {
            $(e.target).siblings('.panel-heading').find('.fa').removeClass('fa-caret-down').addClass('fa-caret-right');
        } else if (e.target.classList.contains('group_fields')) {
            $(e.target).parent().find('.fa-caret-down').removeClass('fa-caret-down').addClass('fa-caret-right');
        }
    });

    $('#expand-all-categories, #menu-expand-all-categories').click(function() {
        expandAllMetadataCategories();
    });

    $('#collapse-all-categories, #menu-collapse-all-categories').click(function() {
        collapseAllMetadataCategories();
    });
}

function expandAllMetadataCategories() {
    $('.metadata_category.panel-collapse').collapse('show');
}

function collapseAllMetadataCategories() {
    $('.metadata_category.panel-collapse.in').collapse('hide');
}

function initResizableSidebar() {
    var $sidebar = $('#sidebar');
    var minWidth = 290;
    var maxWidth = $(window).width() * 0.8;
    $sidebar.resizable({
        handles: 'w',
        minWidth: minWidth,
        maxWidth: maxWidth,
        resize: function(event, ui) {
            sessionStorage['collectionsSidebarWidth'] = ui.size.width;
            resizeFileTreeWidth();
            displayMetadataActionBarOrMenu(ui.size.width);
        },
        stop: function() {
            $sidebar.css('left', '');
        }
    });
}

function displayMetadataActionBarOrMenu(currentMetadataPanelWidth) {
    var minLabelledPanelWidth;
    var minIconPanelWidth;

    if (isEditMode()) {
        minLabelledPanelWidth = 735;
        minIconPanelWidth = 410;
        displayRelevantActionGroup();
    } else {
        minLabelledPanelWidth = 315;
        minIconPanelWidth = 150;
        displayRelevantActionGroup();
    }

    function displayRelevantActionGroup() {
        var $metadataActionBarButtonText = $('#metadata-action-bar button .button-text');
        if (currentMetadataPanelWidth < minIconPanelWidth) {
            toggleMetadataFormSubmitButtons(true);
            $('#metadata-action-menu').show();
            $('#metadata-action-bar').hide();
        } else if (currentMetadataPanelWidth < minLabelledPanelWidth) {
            toggleMetadataFormSubmitButtons(false);
            $('#metadata-action-menu').hide();
            $('#metadata-action-bar').show();
            $metadataActionBarButtonText.hide();
            $metadataActionBarButtonText.addClass('hoverable-text');
        } else {
            toggleMetadataFormSubmitButtons(false);
            $('#metadata-action-menu').hide();
            $('#metadata-action-bar').show();
            $metadataActionBarButtonText.show();
            $metadataActionBarButtonText.removeClass('hoverable-text');
        }
    }

    function toggleMetadataFormSubmitButtons(enableMenu) {
        if (enableMenu) {
            $('#menu-save-metadata').removeAttr("disabled");
            $('#menu-save-metadata-and-continue').removeAttr("disabled");
            $('#save-metadata').attr("disabled", "disabled");
            $('#save-metadata-and-continue').attr("disabled", "disabled");
        } else {
            $('#menu-save-metadata').attr("disabled", "disabled");
            $('#menu-save-metadata-and-continue').attr("disabled", "disabled");
            $('#save-metadata').removeAttr("disabled");
            $('#save-metadata-and-continue').removeAttr("disabled");
        }
    }
}

function isEditMode() {
    return !($('#edit-metadata').is(':visible') || $('#menu-edit-metadata').is(':visible'));
}

function setSideBarToSessionWidth() {
    var savedWidth = sessionStorage['collectionsSidebarWidth'];
    var minWidth = 290;
    var maxWidth = $(window).width() * 0.8;
    if (typeof(savedWidth) == 'undefined') {
        setSideBarToDefaultWidth();
    } else if (savedWidth > maxWidth) {
        setSideBarToCustomWidth(maxWidth);
    } else if (savedWidth < minWidth) {
        setSideBarToCustomWidth(minWidth);
    } else {
        setSideBarToCustomWidth(savedWidth);
    }
}

function setSideBarToDefaultWidth() {
    var defaultWidth = $(window).width() * 0.4;
    var $sidebar = $('#sidebar');
    $sidebar.width(defaultWidth);
    resizeFileTreeWidth();
}

function setSideBarToCustomWidth(width) {
    $('#sidebar').width(width);
    resizeFileTreeWidth();
}

function initEditMetadataMode() {
    hideMetadataSaveCloseButtons();
    hideAddRemoveMetadataButtons();

    $('#edit-metadata, #menu-edit-metadata').click(function() {
        metadataEditMode();
        displayMetadataActionBarOrMenu($('#sidebar').width());
    });

    $('#save-metadata, #menu-save-metadata').click(function() {
        submitMetadataAndCloseEdit = true;
        expandAllMetadataCategories();
    });

    $('#save-metadata-and-continue, #menu-save-metadata-and-continue').click(function() {
        submitMetadataAndCloseEdit = false;
        expandAllMetadataCategories();
    });

    $('#cancel-metadata, #menu-cancel-metadata').click(function(){
        $('#cancelMetadataModal').modal('show');
    });

    $('#cancel_metadata_confirmed').click(function(){
        location.reload();
    });
}

function hideMetadataSaveCloseButtons() {
    $('#save-metadata, #menu-save-metadata').prop('disabled', true).hide();
    $('#save-metadata-and-continue, #menu-save-metadata-and-continue').prop('disabled', true).hide();
    $('#cancel-metadata, #menu-cancel-metadata').prop('disabled', true).hide();
}

function showMetadataSaveCloseButtons() {
    $('#save-metadata, #menu-save-metadata').prop('disabled', false).show();
    $('#save-metadata-and-continue, #menu-save-metadata-and-continue').prop('disabled', false).show();
    $('#cancel-metadata, #menu-cancel-metadata').prop('disabled', false).show();
}

function metadataEditMode() {
    $('#edit-metadata, #menu-edit-metadata').prop('disabled', true).hide();
    $('#legend').show();
    showMetadataSaveCloseButtons();
    showAddRemoveMetadataButtons();
    expandAllMetadataCategories();

    if (typeof(sessionStorage['collectionsSidebarWidth']) == 'undefined') {
        setSideBarToCustomWidth($(window).width() * 0.66);
    }
}

function showAddRemoveMetadataButtons() {
    $('.edit-button').prop('disabled', false).show();
    $('.remove-field-occurrence').prop('disabled', false).show();
    $('.remove-group-occurrence').prop('disabled', false).show();

    $('.add-field-occurrence').each(function() {
        if (!$(this).data('occurrence-limit-reached')) {
            $(this).prop('disabled', false).show();
        }
    });

    $('.add-group-occurrence').each(function() {
        if (!$(this).data('occurrence-limit-reached')) {
            $(this).prop('disabled', false).show();
        }
    });
}

function hideAddRemoveMetadataButtons() {
    $('.edit-button').prop('disabled', true).hide();
    $('.remove-field-occurrence').prop('disabled', true).hide();
    $('.remove-group-occurrence').prop('disabled', true).hide();
    $('.add-field-occurrence').prop('disabled', true).hide();
    $('.add-group-occurrence').prop('disabled', true).hide();
}

function metadataViewMode() {
    $('#legend').hide();
    hideMetadataSaveCloseButtons();
    hideAddRemoveMetadataButtons();
    collapseAllMetadataCategories();
    $('#edit-metadata, #menu-edit-metadata').prop('disabled', false).show();

    if (typeof(sessionStorage['collectionsSidebarWidth']) == 'undefined') {
        setSideBarToDefaultWidth();
    }
}

function resetMetadataPanel() {
    $('#meta-data').html('');
}

function renderCollectionMetadata (collection) {
    renderMetadata($.parseJSON(collection.metadataSchema), $.parseJSON(collection.savedMetadata));
}

/**
 * Renders the collection metadata
 * @param metadataSchema - metadata schema as JSON
 * @param savedMetadata - saved metadata values as JSON
 */
function renderMetadata(metadataSchema, savedMetadata) {
    metadataSchema.metadata_categories.forEach(function(metadataCategory) {
        var savedMetadataCategory = null;
        if (savedMetadata.hasOwnProperty('categories') && savedMetadata.categories.hasOwnProperty(metadataCategory.id)) {
            savedMetadataCategory = savedMetadata.categories[metadataCategory.id];
        }
        renderMetadataCategory(metadataCategory, savedMetadataCategory);
    });
}

/**
 * Renders a metadata category and all its metadata fields and groups
 * @param metadataCategory - schema metadata category as JSON
 * @param savedCategoryMetadata - saved metadata of this category as JSON or null if nothing saved
 */
function renderMetadataCategory(metadataCategory, savedCategoryMetadata) {
    appendMetadataCategory(metadataCategory.id, metadataCategory.display_name);
    metadataCategory.category_nodes.forEach(function(node) {
        if (node.type == 'metadata_field') {
            var metadataField = node[node.type];
            var savedMetadataField = null;
            if (savedCategoryMetadata != null && savedCategoryMetadata.hasOwnProperty('fields')
                && savedCategoryMetadata.fields.hasOwnProperty(metadataField.id)) {
                savedMetadataField = savedCategoryMetadata.fields[metadataField.id];
            }
            var $categoryPanelBody = $('div#'+metadataCategory.id).children('div.panel-body');
            appendMetadataField(metadataCategory.id, null, null, $categoryPanelBody, metadataField, savedMetadataField, false);
        } else if (node.type == 'metadata_group') {
            var metadataGroup = node[node.type];
            var savedMetadataGroup = null;
            if (savedCategoryMetadata != null && savedCategoryMetadata.hasOwnProperty('groups')
                && savedCategoryMetadata.groups.hasOwnProperty(metadataGroup.id)) {
                savedMetadataGroup = savedCategoryMetadata.groups[metadataGroup.id];
            }
            appendMetadataGroup(metadataCategory.id, metadataGroup, savedMetadataGroup)
        }
    });
}

/**
 * Renders a metadata category
 * @param categoryId - javascript id for the category
 * @param categoryDisplayName - UI display name for the category
 */
function appendMetadataCategory(categoryId, categoryDisplayName) {
    var elemToggle = 'data-toggle="collapse" href="#'+categoryId+'" id="'+categoryId+'-head"';
    var panelHeading = '<div class="panel-heading"> ' +
        '<h4 ' + elemToggle + ' class="panel-title">' +
        '<a ' + elemToggle + ' class="collapsed">' +
        '<i class="fa fa-caret-right"></i>' +
        '<span class="category-name">'+categoryDisplayName+'</span>' +
        '</a>' +
        '</h4>' +
        '</div>';
    var panelBody = '<div id="'+categoryId+'" class="metadata_category panel-collapse collapse standard">' +
        '<div class="panel-body"></div>' +
        '</div>';
    $('div.panel-group#meta-data').append('<div class="panel panel-default">' + panelHeading + panelBody +  '</div>');
}

/**
 * Renders a metadata group within a category
 * @param categoryId - javascript id of the category to render the field within
 * @param groupMetadata - schema metadata group as JSON
 * @param savedGroupMetadata - saved metadata of this group as JSON or null if nothing saved
 */
function appendMetadataGroup(categoryId, groupMetadata, savedGroupMetadata) {
    var savedGroupOccurrences = null;
    if (savedGroupMetadata != null) {
        savedGroupOccurrences = savedGroupMetadata.occurrences;
    }
    var $categoryPanelBody = $('div#'+categoryId).children('div.panel-body');
    generateMetadataGroup(groupMetadata, $categoryPanelBody, categoryId, savedGroupOccurrences);
}

/**
 * Renders a metadata field within a category
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence to render the field within or null if not part of a group
 * @param panelBody - panel to append the metadata field to
 * @param fieldMetadata - schema metadata field as JSON
 * @param savedFieldMetadata - saved metadata of this field as JSON or null if nothing saved
 */
function appendMetadataField(categoryId, groupId, groupOccurrenceId, panelBody, fieldMetadata, savedFieldMetadata, makeEditable) {
    var savedFieldOccurrences = null;
    if (savedFieldMetadata != null) {
        savedFieldOccurrences = savedFieldMetadata.occurrences;
    }
    if (fieldMetadata.field.type == 'text_field') {
        generateTextField(fieldMetadata, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable);
    } else if (fieldMetadata.field.type == 'number_field') {
        generateNumberField(fieldMetadata, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable);
    } else if (fieldMetadata.field.type == 'date_field') {
        generateDateField(fieldMetadata, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable);
    } else if (fieldMetadata.field.type == 'single_select_field') {
        generateSingleSelectField(fieldMetadata, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable);
    }
}

/**
 * Shows the add occurrence button for a given metadata field
 * @param panelBody - JQuery object corresponding to metadata category or group that the button is located in
 * @param nodeId - javascript id of the metadata node containing the button
 */
function showAddOccurrenceButton(panelBody, nodeId) {
    var $addOccurrence = panelBody.find('#add_occurrence_'+nodeId);
    $addOccurrence.data('occurrence-limit-reached', false);
    $addOccurrence.prop('disabled', false).show();
}

/**
 * Hides the add occurrence button for a given metadata node
 * @param panelBody - JQuery object corresponding to the metadata category or group that the button is located in
 * @param nodeId - javascript id of the metadata node containing the button
 */
function hideAddOccurrenceButton(panelBody, nodeId) {
    var $addOccurrence = panelBody.find('#add_occurrence_'+nodeId);
    $addOccurrence.data('occurrence-limit-reached', true);
    $addOccurrence.prop('disabled', true).hide();
}

/**
 * Removes the remove occurrence button for a given metadata field occurrence
 * @param panelBody - JQuery object corresponding to the metadata category or group that the button is located in
 * @param nodeId - javascript id of the metadata field containing the button
 * @param nodeOccurrenceId - javascript id of the metadata node occurrence the button corresponds to
 */
function removeRemoveOccurrenceButton(panelBody, nodeId, nodeOccurrenceId) {
    panelBody.find('div#'+nodeId+'_box').find('#remove_occurrence_'+nodeOccurrenceId).remove();
}

/**
 * Renders a metadata group within a category and adds associated event handlers
 * @param metadataGroup - metadata group as JSON
 * @param categoryPanelBody - JQuery object corresponding to metadata category panel to render the group within
 * @param categoryId - javascript id of the category to render the group within
 * @param savedGroupOccurrences - saved occurrences of this group as JSON or null if nothing saved
 */
function generateMetadataGroup(metadataGroup, categoryPanelBody, categoryId, savedGroupOccurrences) {
    var groupId = metadataGroup.id;
    var groupMinOccurrences = metadataGroup.min_occurs;
    var groupMaxOccurrences = metadataGroup.max_occurs;
    var occurrenceDisplayName;
    if (metadataGroup.occurrence_display_name) {
        occurrenceDisplayName = metadataGroup.occurrence_display_name;
    } else {
        occurrenceDisplayName = metadataGroup.display_name;
    }

    // Append header and body
    categoryPanelBody.append(generateMetadataGroupHTML(groupId, metadataGroup.display_name, occurrenceDisplayName, metadataGroup.tooltip));
    if (groupMinOccurrences === groupMaxOccurrences) {
        hideAddOccurrenceButton(categoryPanelBody, groupId);
    }

    // Load saved values for initial and additional occurrences
    var groupBody = categoryPanelBody.find('div#'+groupId+'_box').find('div.group_body');
    if (savedGroupOccurrences != null) {
        var numGroupOccurrencesLoaded = 0;
        $.each(savedGroupOccurrences, function(groupOccurrenceId, groupOccurrenceNodes){
            addGroupOccurrence(categoryPanelBody, categoryId, groupId, groupOccurrenceId, occurrenceDisplayName, metadataGroup.metadata_fields, groupOccurrenceNodes.fields, false);
            if (numGroupOccurrencesLoaded < groupMinOccurrences) {
                removeRemoveOccurrenceButton(categoryPanelBody, groupId, groupOccurrenceId)
            }
            numGroupOccurrencesLoaded++;
        });

        if (numGroupOccurrencesLoaded >= groupMaxOccurrences) {
            hideAddOccurrenceButton(categoryPanelBody, groupId);
        }
    }

    // Add minimum initial occurrences
    var numCurrentOccurrences = groupBody.find('.group_occurrence').length;
    for (var i = numCurrentOccurrences; i < groupMinOccurrences; i++) {
        var occurrenceId = addNewGroupOccurrence(categoryPanelBody, categoryId, groupId, occurrenceDisplayName, metadataGroup.metadata_fields, false);
        removeRemoveOccurrenceButton(categoryPanelBody, groupId, occurrenceId);
    }

    // Attach event handling for adding additional occurrences
    categoryPanelBody.find('#add_occurrence_'+groupId).click(function() {
        var numCurrentOccurrences = groupBody.find('.group_occurrence').length;
        if (numCurrentOccurrences < groupMaxOccurrences) {
            addNewGroupOccurrence(categoryPanelBody, categoryId, groupId, occurrenceDisplayName, metadataGroup.metadata_fields, true);
            if (numCurrentOccurrences + 1 >= groupMaxOccurrences) {
                hideAddOccurrenceButton(categoryPanelBody, groupId);
            }
        }
    });
}

/**
 * Adds a group occurrence with a generated id to the group body and attaches event handling
 * @param categoryPanelBody - JQuery object corresponding to metadata category panel to render the group within
 * @param categoryId - javascript id of the category to render the group within
 * @param groupId - javascript id of the group
 * @param groupOccurrenceName - display name to use for labelling the group occurrence
 * @param metadataGroupFields - metadata group fields as JSON
 * @return String - the generated occurrence id
 */
function addNewGroupOccurrence(categoryPanelBody, categoryId, groupId, groupOccurrenceName, metadataGroupFields, makeEditable) {
    var groupOccurrenceId = groupId + '_' + generateTimestampBasedId();
    addGroupOccurrence(categoryPanelBody, categoryId, groupId, groupOccurrenceId, groupOccurrenceName, metadataGroupFields, null, makeEditable);
    return groupOccurrenceId;
}

/**
 * Adds a group occurrence with a specified id to the group body and attaches event handling
 * @param categoryPanelBody -  JQuery object corresponding to metadata category panel to render the group within
 * @param categoryId - javascript id of the category to render the group within
 * @param groupId - javascript id of the group
 * @param groupOccurrenceId - javascript id to use for the group occurrence
 * @param groupOccurrenceName - display name to use for labelling the group occurrence
 * @param metadataGroupFields - metadata group fields as JSON
 * @param savedGroupOccurrenceFields - saved fields of this group occurrence as JSON or null if nothing saved
 */
function addGroupOccurrence(categoryPanelBody, categoryId, groupId, groupOccurrenceId, groupOccurrenceName, metadataGroupFields, savedGroupOccurrenceFields, makeEditable) {
    var groupBody = categoryPanelBody.find('div#'+groupId+'_box').find('div.group_body');
    groupBody.append(groupOccurrenceHTML(groupOccurrenceId, groupOccurrenceName, makeEditable));
    metadataGroupFields.forEach(function(field){
        var savedField = null;
        if (savedGroupOccurrenceFields != null) {
            savedField = savedGroupOccurrenceFields[field.id];
        }
        appendMetadataField(categoryId, groupId, groupOccurrenceId, groupBody.find('#occurrence_'+groupOccurrenceId).find('.group_fields'),
            field, savedField, makeEditable);
    });

    // Add event handling for removing the group occurrence
    groupBody.find('#remove_occurrence_'+groupOccurrenceId).click(function(){
        groupBody.find('#occurrence_'+groupOccurrenceId).remove();
        showAddOccurrenceButton(categoryPanelBody, groupId);
    });
}

/**
 * Generates HTML to display a metadata group
 * @param groupId - id of the group
 * @param groupDisplayName - display name of metadata group
 * @param occurrenceDisplayName - display name of an occurrence of the metadata group
 * @param hoverHint - hover hint for tooltip
 * @return string - group as HTML
 */
function generateMetadataGroupHTML(groupId, groupDisplayName, occurrenceDisplayName, hoverHint) {
    var addOccurrenceButton = '<button id="add_occurrence_'+groupId+'" class="add-group-occurrence" type="button" title="Add occurrence">Add ' + occurrenceDisplayName + '</button>';
    var tooltip = '';
    if (hoverHint != null) {
        tooltip = 'title="'+hoverHint+'"';
    }
    var groupHeader = '<h6 class="group_title" '+tooltip+'>'+ groupDisplayName + '</h6>';
    var groupBody = '<div class="group_body"></div>';
    var groupFooter = '<span class="group_footer">'+addOccurrenceButton+'</span>';
    return '<div id="'+groupId+'_box" class="metadata_group">' + groupHeader + groupBody + groupFooter + '</div>';
}

/**
 * Generates the HTML for a general metadata group occurrence
 * @param groupId - id of the group
 * @param groupOccurrenceName - display name to use for labelling the group occurrence
 * @return string - group occurrence as HTML
 */
function groupOccurrenceHTML(groupId, groupOccurrenceName, expanded) {
    var collapseClass;
    if (expanded) {
        collapseClass = 'in';
    } else {
        collapseClass = 'collapse';
    }
    return '<div id="occurrence_' + groupId + '" class="group_occurrence">' +
        '<h6>' +
        '<a data-toggle="collapse" href="#occurrence_fields_' + groupId + '">' +
        '<i class="fa fa-caret-right"></i>' +
        '<span class="group-name">' + groupOccurrenceName + '</span>' +
        '</a>' +
        '<button id="remove_occurrence_' + groupId + '" class="remove-group-occurrence pull-right trans-button" type="button" title="Remove occurrence">' +
        '<i class="fa fa-trash-o"></i>' +
        '</button>' +
        '</h6>' +
        '<div id="occurrence_fields_' + groupId + '" class="group_fields ' + collapseClass + '"></div>' +
        '</div>';
}

/**
 * Renders a metadata text field within a category and adds associated event handlers
 * @param metadataField - metadata field as JSON
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence to render the field within or null if not part of a group
 * @param savedFieldOccurrences - saved occurrences of this field as JSON or null if nothing saved
 */
function generateTextField(metadataField, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable) {
    var fieldId = metadataField.id;
    var fieldDisplayName = metadataField.display_name;
    var fieldPlaceholder = metadataField.field.text_field.placeholder;
    var fieldCharLimit = metadataField.field.text_field.char_limit;
    var fieldMinOccurrences = metadataField.min_occurs;
    var fieldMaxOccurrences = metadataField.max_occurs;
    var fieldDefaultValue = metadataField.field[metadataField.field.type].value;
    var fieldNumRows = metadataField.field.text_field.num_rows;

    // Append field header and body
    panelBody.append(generateMetadataFieldHTML(fieldId, fieldDisplayName, metadataField.tooltip, metadataField.mandatory));
    if (fieldMinOccurrences === fieldMaxOccurrences) {
        hideAddOccurrenceButton(panelBody, fieldId);
    }

    // Load saved values for initial field and additional occurrences
    if (savedFieldOccurrences != null) {
        var numLoaded = 0;
        $.each(savedFieldOccurrences, function(fieldOccurrenceId, fieldOccurrenceValue){
            addTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldCharLimit, fieldPlaceholder, fieldDisplayName, makeEditable, fieldNumRows);
            setFieldOccurrenceValue(panelBody, fieldId, fieldOccurrenceId, fieldOccurrenceValue);
            if (numLoaded < fieldMinOccurrences) {
                removeRemoveOccurrenceButton(panelBody, fieldId, fieldOccurrenceId);
            }
            numLoaded++;
        });
        if (numLoaded >= fieldMaxOccurrences) {
            hideAddOccurrenceButton(panelBody, fieldId);
        }
    }

    // Add minimum initial occurrences
    var textFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    var numCurrentOccurrences = textFieldBody.find('.field_occurrence').length;
    for (var i = numCurrentOccurrences; i < fieldMinOccurrences; i++) {
        var occurrenceId = addNewTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldCharLimit, fieldPlaceholder, fieldDisplayName, makeEditable, fieldNumRows);
        if (fieldDefaultValue != null) {
            if (makeEditable) {
                $('#input_' + occurrenceId).text(fieldDefaultValue);
            } else {
                $('#' + occurrenceId).text(fieldDefaultValue);
            }
        }
        removeRemoveOccurrenceButton(panelBody, fieldId, occurrenceId);
    }

    // Attach event handling for adding additional occurrences
    panelBody.find('#add_occurrence_'+fieldId).click(function() {
        var numCurrentOccurrences = textFieldBody.find('.field_occurrence').length;
        if (numCurrentOccurrences < fieldMaxOccurrences) {
            var newOccurrenceId = addNewTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldCharLimit, fieldPlaceholder, fieldDisplayName, true, fieldNumRows);
            if (fieldDefaultValue != null) {
                $('#input_' + newOccurrenceId).text(fieldDefaultValue);
            }
            if (numCurrentOccurrences + 1 >= fieldMaxOccurrences) {
                hideAddOccurrenceButton(panelBody, fieldId);
            }
        }
    });
}


/**
 * Adds a text field occurrence with a generated id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of text field
 * @param fieldCharLimit - text field character limit
 * @param fieldPlaceholder - placeholder text for text field
 * @param fieldDisplayName - display name of text field
 * @param fieldNumRows - number of rows to display in the text field
 * @return String - the generated occurrence id
 */
function addNewTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldCharLimit, fieldPlaceholder, fieldDisplayName, makeEditable, fieldNumRows) {
    var fieldOccurrenceId = fieldId + '_' + generateTimestampBasedId();
    addTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldCharLimit, fieldPlaceholder, fieldDisplayName, makeEditable, fieldNumRows);
    return fieldOccurrenceId;
}

function makeTextFieldEditable($fieldBody, occurrenceId, charLimit, placeholder, displayName, numRows) {
    var textField = $fieldBody.find('div#' + occurrenceId );
    var oldText = textField.text();
    textField.text('');
    var textArea = '<textarea id="input_' + occurrenceId + '"';
    if (charLimit != null) {
        textArea += ' maxlength="' + charLimit + '"';
    }
    if (numRows) {
        textArea += ' rows="' + numRows + '"';
    } else {
        textArea += ' rows="1"';
    }
    textArea += ' placeholder="' + placeholder + '">' + oldText + '</textarea>';
    textField.html(textArea + '<div id="edit_' + occurrenceId + '_validation_error" class="validation-error"></div>');

    attachTextFieldValidation(textField, charLimit, occurrenceId, displayName);
}

/**
 * Adds a text field occurrence with a specified id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of text field
 * @param fieldOccurrenceId - javascript id to use for the text field occurrence
 * @param charLimit - text field character limit
 * @param placeholder - placeholder text for text field
 * @param displayName - display name of text field
 * @param numRows - number of rows to configure the text area (defaults to 1)
 */
function addTextFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, charLimit, placeholder, displayName, makeEditable, numRows) {
    var textFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    textFieldBody.append(fieldOccurrenceHTML(fieldOccurrenceId));

    if (makeEditable) {
        makeTextFieldEditable(textFieldBody, fieldOccurrenceId, charLimit, placeholder, displayName, numRows);
    }

    $('#edit-metadata, #menu-edit-metadata').click(function(){
        makeTextFieldEditable(textFieldBody, fieldOccurrenceId, charLimit, placeholder, displayName, numRows);
    });

    textFieldBody.find('#remove_occurrence_'+fieldOccurrenceId).click(function(){
        textFieldBody.find('#occurrence_'+fieldOccurrenceId).remove();
        showAddOccurrenceButton(panelBody, fieldId);
    });
}

/**
 * Attaches event handling for clearing an edit to a field occurrence value
 * @param fieldDiv - div holding field occurrence edit box and saved value
 * @param occurrenceId - javascript id of field occurrence
 */
function attachFieldClearEvent(fieldDiv, occurrenceId) {
    fieldDiv.find('#clear_'+occurrenceId).click(function() {
        fieldDiv.find('#input_' + occurrenceId).val('');
    });
}


/**
 * Attaches event handling for character limit validation to a text field
 * @param textField - div holding text field edit box and saved text
 * @param charLimit - text field character limit
 * @param occurrenceId - javascript id of text field occurrence
 * @param fieldDisplayName - display name of text field
 */
function attachTextFieldValidation(textField, charLimit, occurrenceId, fieldDisplayName) {
    if (charLimit != null) {
        textField.find('#input_' + occurrenceId).keyup(function() {
            var error = textField.find('#edit_' + occurrenceId + '_validation_error');
            if ($(this).val().length >= charLimit) {
                error.text(fieldDisplayName + ' has reached the limit of ' + charLimit + ' characters');
                error.show();
                $(this).val($(this).val().substr(0, charLimit));
            } else {
                error.text('');
            }
        });
    }
}

/**
 * Generates HTML to display a metadata field
 * @param fieldId - id of the field
 * @param fieldDisplayName - display name of metadata field
 * @param hoverHint - hover hint for tooltip
 * @param isRequired - True if field is required/mandatory, false otherwise
 * @return string - field as HTML
 */
function generateMetadataFieldHTML(fieldId, fieldDisplayName, hoverHint, isRequired) {
    var addOccurrenceButton = '<button id="add_occurrence_'+fieldId+'" class="add-field-occurrence" type="button" title="Add occurrence">Add ' + fieldDisplayName + '</button>';
    var tooltip = '';
    if (hoverHint != null) {
        tooltip = 'title="'+hoverHint+'"';
    }
    var requiredIndicator = '';
    if (isRequired) {
        requiredIndicator = '<span class="required">*</span>'
    }
    var fieldHeader = '<h6 class="field_title" '+tooltip+'>'+ requiredIndicator + "&nbsp;" + fieldDisplayName +'</h6>';
    var fieldBody = '<div class="field_body"></div>';
    var fieldFooter = '<span class="field_footer">'+addOccurrenceButton+'</span>';
    return '<div id="'+fieldId+'_box" class="metadata_field">' + fieldHeader + fieldBody + fieldFooter + '</div>';
}

/**
 * Generates the HTML for a general metadata field occurrence
 * @param fieldId - id of the field
 * @return string - field occurrence as HTML
 */
function fieldOccurrenceHTML(fieldId) {
    return '<div id="occurrence_'+fieldId+'" class="field_occurrence">' +
        '<button id="remove_occurrence_'+fieldId+'" class="remove-field-occurrence pull-right trans-button" type="button" title="Remove occurrence">' +
        '<i class="fa fa-trash-o"></i>' +
        '</button>' +
        '<div id="'+fieldId+'" class="metadata field_value"></div>' +
        '</div>';
}

/**
 * Sets the value of a field occurrence
 * @param panelBody - JQuery object corresponding to metadata category or group containing the field
 * @param fieldId - javascript id of the metadata field
 * @param fieldOccurrenceId - javascript id of the metadata field occurrence
 * @param value - value to set the field occurrence to
 */
function setFieldOccurrenceValue(panelBody, fieldId, fieldOccurrenceId, value) {
    panelBody.find('div#'+fieldId+'_box').find('div.field_body').find('div#'+fieldOccurrenceId).text(value);
}

function generateNumberField(metadataField, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable) {
    var fieldId = metadataField.id;
    var fieldDisplayName = metadataField.display_name;
    var fieldTooltip = metadataField.tooltip;
    var fieldMinOccurrences = metadataField.min_occurs;
    var fieldMaxOccurrences = metadataField.max_occurs;
    var fieldPlaceholder = metadataField.field[metadataField.field.type].placeholder;
    var fieldDefaultValue = metadataField.field[metadataField.field.type].value;

    // Append field header and body
    panelBody.append(generateMetadataFieldHTML(fieldId, fieldDisplayName, fieldTooltip, metadataField.mandatory));
    if (fieldMinOccurrences === fieldMaxOccurrences) {
        hideAddOccurrenceButton(panelBody, fieldId);
    }

    // Load saved values for initial field and additional occurrences
    if (savedFieldOccurrences != null) {
        var numLoaded = 0;
        $.each(savedFieldOccurrences, function(fieldOccurrenceId, fieldOccurrenceValue){
            addNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, makeEditable, fieldPlaceholder);
            setFieldOccurrenceValue(panelBody, fieldId, fieldOccurrenceId, fieldOccurrenceValue);
            if (numLoaded < fieldMinOccurrences) {
                removeRemoveOccurrenceButton(panelBody, fieldId, fieldOccurrenceId);
            }
            numLoaded++;
        });
        if (numLoaded >= fieldMaxOccurrences) {
            hideAddOccurrenceButton(panelBody, fieldId);
        }
    }

    // Add minimum initial occurrences
    var numberFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    var numCurrentOccurrences = numberFieldBody.find('.field_occurrence').length;
    for (var i = numCurrentOccurrences; i < fieldMinOccurrences; i++) {
        var occurrenceId = addNewNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, makeEditable, fieldPlaceholder);
        if (fieldDefaultValue != null) {
            if (makeEditable) {
                $('#input_' + occurrenceId).val(fieldDefaultValue);
            } else {
                $('#' + occurrenceId).text(fieldDefaultValue);
            }
        }
        removeRemoveOccurrenceButton(panelBody, fieldId, occurrenceId);
    }

    // Attach event handling for adding additional occurrences
    panelBody.find('#add_occurrence_'+fieldId).click(function() {
        var numCurrentOccurrences = numberFieldBody.find('.field_occurrence').length;
        if (numCurrentOccurrences < fieldMaxOccurrences) {
            var newOccurrenceId = addNewNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, true, fieldPlaceholder);
            if (fieldDefaultValue != null) {
                $('#input_' + newOccurrenceId).val(fieldDefaultValue);
            }
            if (numCurrentOccurrences + 1 >= fieldMaxOccurrences) {
                hideAddOccurrenceButton(panelBody, fieldId);
            }
        }
    });
}

function addNewNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, makeEditable, placeholder) {
    var fieldOccurrenceId = fieldId + '_' + generateTimestampBasedId();
    addNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, makeEditable, placeholder);
    return fieldOccurrenceId;
}

function addNumberFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, makeEditable, placeholder) {
    var fieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    fieldBody.append(fieldOccurrenceHTML(fieldOccurrenceId));

    if (makeEditable) {
        makeNumberFieldEditable(fieldBody, fieldOccurrenceId, placeholder)
    }

    $('#edit-metadata, #menu-edit-metadata').click(function(){
        makeNumberFieldEditable(fieldBody, fieldOccurrenceId, placeholder);
    });

    fieldBody.find('#remove_occurrence_'+fieldOccurrenceId).click(function(){
        fieldBody.find('#occurrence_'+fieldOccurrenceId).remove();
        showAddOccurrenceButton(panelBody, fieldId);
    });
}

function makeNumberFieldEditable($fieldBody, occurrenceId, placeholder) {
    var field = $fieldBody.find('div#' + occurrenceId );
    var oldValue = field.text();
    field.text('');
    var fieldHTML = '<input type="number" id="input_' + occurrenceId + '" value="' + oldValue + '" placeholder="'+placeholder+'">';
    field.html(fieldHTML);
}

/**
 * Renders a metadata date field within a category and adds associated event handlers
 * @param metadataField - metadata field as JSON
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence to render the field within or null if not part of a group
 * @param savedFieldOccurrences - saved occurrences of this field as JSON or null if nothing saved
 */
function generateDateField(metadataField, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable) {
    var fieldId = metadataField.id;
    var fieldDisplayName = metadataField.display_name;
    var fieldTooltip = metadataField.tooltip;
    var fieldMinDate = metadataField.field.date_field.min_date;
    var fieldMaxDate = metadataField.field.date_field.max_date;
    var fieldMinOccurrences = metadataField.min_occurs;
    var fieldMaxOccurrences = metadataField.max_occurs;
    var fieldDefaultValue = metadataField.field[metadataField.field.type].value;
    if (fieldDefaultValue == "today") {
        fieldDefaultValue = moment().format("YYYY-MM-DD");
    }

    // Append field header and body
    panelBody.append(generateMetadataFieldHTML(fieldId, fieldDisplayName, fieldTooltip, metadataField.mandatory));
    if (fieldMinOccurrences === fieldMaxOccurrences) {
        hideAddOccurrenceButton(panelBody, fieldId);
    }

    // Load saved values for initial field and additional occurrences
    if (savedFieldOccurrences != null) {
        var numLoaded = 0;
        $.each(savedFieldOccurrences, function(fieldOccurrenceId, fieldOccurrenceValue){
            addDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldMinDate, fieldMaxDate, makeEditable);
            setFieldOccurrenceValue(panelBody, fieldId, fieldOccurrenceId, fieldOccurrenceValue);
            if (numLoaded < fieldMinOccurrences) {
                removeRemoveOccurrenceButton(panelBody, fieldId, fieldOccurrenceId);
            }
            numLoaded++;
        });
        if (numLoaded >= fieldMaxOccurrences) {
            hideAddOccurrenceButton(panelBody, fieldId);
        }
    }

    // Add minimum initial occurrences
    var dateFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    var numCurrentOccurrences = dateFieldBody.find('.field_occurrence').length;
    for (var i = numCurrentOccurrences; i < fieldMinOccurrences; i++) {
        var occurrenceId = addNewDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldMinDate, fieldMaxDate, makeEditable);
        if (fieldDefaultValue != null) {
            if (makeEditable) {
                $('#input_' + occurrenceId).val(fieldDefaultValue);
            } else {
                $('#' + occurrenceId).text(fieldDefaultValue);
            }
        }
        removeRemoveOccurrenceButton(panelBody, fieldId, occurrenceId);
    }

    // Attach event handling for adding additional occurrences
    panelBody.find('#add_occurrence_'+fieldId).click(function() {
        var numCurrentOccurrences = dateFieldBody.find('.field_occurrence').length;
        if (numCurrentOccurrences < fieldMaxOccurrences) {
            var newOccurrenceId = addNewDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldMinDate, fieldMaxDate, true);
            if (fieldDefaultValue != null) {
                $('#input_' + newOccurrenceId).val(fieldDefaultValue);
            }
            if (numCurrentOccurrences + 1 >= fieldMaxOccurrences) {
                hideAddOccurrenceButton(panelBody, fieldId);
            }
        }
    });
}

/**
 * Adds a date field occurrence with a generated id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of date field
 * @param fieldMinDate - minimum date permitted when selecting a date
 * @param fieldMaxDate - maximum date permitted when selecting a date
 * @return String - the generated occurrence id
 */
function addNewDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldMinDate, fieldMaxDate, makeEditable) {
    var fieldOccurrenceId = fieldId + '_' + generateTimestampBasedId();
    addDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldMinDate, fieldMaxDate, makeEditable);
    return fieldOccurrenceId;
}

/**
 * Checks if browser supports HTML5 datepicker.
 * See https://stackoverflow.com/a/10199306
 * @returns {boolean}
 */
function checkDateInputSupported() {
    var input = document.createElement('input');
    input.setAttribute('type','date');

    var notADateValue = 'not-a-date';
    input.setAttribute('value', notADateValue);

    return (input.value !== notADateValue);
}

function makeDateFieldEditable($fieldBody, occurrenceId, minDate, maxDate) {
    var tooltip = '';
    if (minDate != null && maxDate != null) {
        tooltip = 'The date must be between ' + moment(minDate, 'YYYY-MM-DD').format('Do MMMM YYYY') + ' and ' + moment(maxDate, 'YYYY-MM-DD').format('Do MMMM YYYY');
    } else if (minDate != null && maxDate == null) {
        tooltip = 'The date must be ' + moment(minDate, 'YYYY-MM-DD').format('Do MMMM YYYY') + ' or later';
    } else if (minDate == null && maxDate != null) {
        tooltip = 'The date must be ' + moment(maxDate, 'YYYY-MM-DD').format('Do MMMM YYYY') + ' or earlier';
    }

    var $dateField = $fieldBody.find('div#'+occurrenceId);
    var oldDate = $dateField.text();
    $dateField.text('');

    var editOptions = '<span class="edit-options date-options">' +
            '<button id="clear_'+occurrenceId+'" type="button" value="Clear" class="clear-button">' +
                '<i class="fa fa-times"></i>' +
            '</button>' +
        '</span>';

    if (checkDateInputSupported()) {
        $dateField.html('<input id="input_'+occurrenceId+'" type="date" value="'+oldDate+'" title ="'+tooltip+'" ' +
            'min="'+ minDate+'" max="'+maxDate+'"/>');
        $dateField.append(editOptions);
    } else {
        $dateField.html(
            '<div id="date_picker_button_'+occurrenceId+'" class="input-append date" >' +
                '<input id="input_'+occurrenceId+'" type="text" data-format="yyyy-MM-dd" disabled="disabled"/>' +
                editOptions +
                '<span class = "add-on">' +
                    '<i id="datepicker_'+occurrenceId+'" class="fa fa-calendar datetime_icon" title="'+tooltip+'"></i>' +
                '</span>' +
                '<div id="edit_'+occurrenceId+'_validation_error" class="validation-error"></div>' +
            '</div>'
        );
        $('#input_'+occurrenceId).val(oldDate);
        $('#date_picker_button_'+occurrenceId).datetimepicker();
    }

    attachFieldClearEvent($dateField, occurrenceId);
}

//ToDo: remove usused parameters
/**
 * Adds a date field occurrence with a specified id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of field
 * @param fieldOccurrenceId - javascript id to use for the field occurrence
 * @param fieldMinDate - minimum date permitted when selecting a date
 * @param fieldMaxDate - maximum date permitted when selecting a date
 */
function addDateFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldMinDate, fieldMaxDate, makeEditable) {
    var $dateFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    $dateFieldBody.append(fieldOccurrenceHTML(fieldOccurrenceId));

    if (makeEditable) {
        makeDateFieldEditable($dateFieldBody, fieldOccurrenceId, fieldMinDate, fieldMaxDate);
    }

    $('#edit-metadata, #menu-edit-metadata').click(function(){
        makeDateFieldEditable($dateFieldBody, fieldOccurrenceId, fieldMinDate, fieldMaxDate);
    });

    $dateFieldBody.find('#remove_occurrence_'+fieldOccurrenceId).click(function(){
        $dateFieldBody.find('#occurrence_'+fieldOccurrenceId).remove();
        showAddOccurrenceButton(panelBody, fieldId);
    });
}

/**
 * Renders a metadata single select field within a category and adds associated event handlers
 * @param metadataField - metadata field as JSON
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence to render the field within or null if not part of a group
 * @param savedFieldOccurrences - saved occurrences of this field as JSON or null if nothing saved
 */
function generateSingleSelectField(metadataField, panelBody, categoryId, groupId, groupOccurrenceId, savedFieldOccurrences, makeEditable) {
    var fieldId = metadataField.id;
    var fieldDisplayName = metadataField.display_name;
    var fieldTooltip = metadataField.tooltip;
    var fieldMinOccurrences = metadataField.min_occurs;
    var fieldMaxOccurrences = metadataField.max_occurs;
    var fieldDefaultValue = metadataField.field[metadataField.field.type].value;
    var fieldSelectableValues = metadataField.field[metadataField.field.type].options;

    // Append field header and body
    panelBody.append(generateMetadataFieldHTML(fieldId, fieldDisplayName, fieldTooltip, metadataField.mandatory));
    if (fieldMinOccurrences === fieldMaxOccurrences) {
        hideAddOccurrenceButton(panelBody, fieldId);
    }

    // Load saved values for initial field and additional occurrences
    if (savedFieldOccurrences != null) {
        var numLoaded = 0;
        $.each(savedFieldOccurrences, function(fieldOccurrenceId, fieldOccurrenceValue){
            addSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldSelectableValues, makeEditable);
            setFieldOccurrenceValue(panelBody, fieldId, fieldOccurrenceId, fieldOccurrenceValue);
            if (numLoaded < fieldMinOccurrences) {
                removeRemoveOccurrenceButton(panelBody, fieldId, fieldOccurrenceId);
            }
            numLoaded++;
        });
        if (numLoaded >= fieldMaxOccurrences) {
            hideAddOccurrenceButton(panelBody, fieldId);
        }
    }

    // Add minimum initial occurrences
    var selectFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    var numCurrentOccurrences = selectFieldBody.find('.field_occurrence').length;
    for (var i = numCurrentOccurrences; i < fieldMinOccurrences; i++) {
        var occurrenceId = addNewSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldSelectableValues, makeEditable);
        if (fieldDefaultValue != null) {
            if (makeEditable) {
                $('#' + occurrenceId + ' input[name=radio_' + occurrenceId + '][value=' + fieldDefaultValue + ']').prop('checked', true);
            } else {
                $('#' + occurrenceId).text(fieldDefaultValue);
            }
        }
        removeRemoveOccurrenceButton(panelBody, fieldId, occurrenceId);
    }

    // Attach event handling for adding additional occurrences
    panelBody.find('#add_occurrence_'+fieldId).click(function() {
        var numCurrentOccurrences = selectFieldBody.find('.field_occurrence').length;
        if (numCurrentOccurrences < fieldMaxOccurrences) {
            var newOccurrenceId = addNewSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldSelectableValues, true);
            if (fieldDefaultValue != null) {
                $('#' + newOccurrenceId + ' input[name=radio_' + newOccurrenceId + '][value=' + fieldDefaultValue + ']').prop('checked', true);
            }
            if (numCurrentOccurrences + 1 >= fieldMaxOccurrences) {
                hideAddOccurrenceButton(panelBody, fieldId);
            }
        }
    });
}

/**
 * Adds a single select field occurrence with a generated id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of date field
 * @param fieldSelectableValues - list of selectable string values to include as options within the field
 * @return String - the generated occurrence id
 */
function addNewSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldSelectableValues, makeEditable) {
    var fieldOccurrenceId = fieldId + '_' + generateTimestampBasedId();
    addSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, fieldSelectableValues, makeEditable);
    return fieldOccurrenceId;
}

function makeRadioFieldEditable(fieldBody, occurrenceId, radioValues) {
    var selectField = fieldBody.find('div#'+occurrenceId);
    var name = "radio_"+occurrenceId;
    var oldValue = selectField.text();
    selectField.text('');

    $.each(radioValues, function(i, val) {
        var id = name + "_" + i;
        var radioButton = "type='radio' name='" + name + "' id='" + id + "' value='" + val + "'";
        var radioField = "<span class='radio'>";
        if (val == oldValue) {
            radioField += ("<input " + radioButton + " checked />");
        } else {
            radioField += ("<input " + radioButton + "/>");
        }
        radioField += ("<label for='" + id + "'>" + val + "</label>");
        radioField += ("</span>");
        selectField.append(radioField);
    });

    var editOptions = '<span class="edit-options">' +
            '<button id="clear_'+occurrenceId+'" type="button" value="Clear" class="clear-button">' +
                '<i class="fa fa-times"></i>' +
            '</button>' +
        '</span>';
    selectField.append(editOptions);

    attachSelectionFieldClearEvent(selectField, occurrenceId);

}

/**
 * Adds a single select field occurrence with a specified id to the field body and attaches event handling
 * @param panelBody - JQuery object corresponding to metadata category or group to render the field within
 * @param categoryId - javascript id of the category to render the field within
 * @param groupId - id of the group which the field belongs to or null if not part of a group
 * @param groupOccurrenceId - id of the group occurrence the field belongs to or null if not part of a group
 * @param fieldId - javascript id of field
 * @param fieldOccurrenceId - javascript id to use for the field occurrence
 * @param radioValues - list of selectable string values to include as options within the field
 */
function addSingleSelectFieldOccurrence(panelBody, categoryId, groupId, groupOccurrenceId, fieldId, fieldOccurrenceId, radioValues, makeEditable) {
    var selectFieldBody = panelBody.find('div#'+fieldId+'_box').find('div.field_body');
    selectFieldBody.append(fieldOccurrenceHTML(fieldOccurrenceId));

    if (makeEditable) {
        makeRadioFieldEditable(selectFieldBody, fieldOccurrenceId, radioValues);
    }

    $('#edit-metadata, #menu-edit-metadata').click(function(){
        makeRadioFieldEditable(selectFieldBody, fieldOccurrenceId, radioValues);
    });

    selectFieldBody.find('#remove_occurrence_'+fieldOccurrenceId).click(function(){
        selectFieldBody.find('#occurrence_'+fieldOccurrenceId).remove();
        showAddOccurrenceButton(panelBody, fieldId);
    });
}

/**
 * Attaches event handling for clearing a selection from a select field occurrence value
 * @param fieldDiv - div holding field occurrence selection field and saved value
 * @param occurrenceId - javascript id of selection field occurrence
 */
function attachSelectionFieldClearEvent(fieldDiv, occurrenceId) {
    fieldDiv.find('#clear_'+occurrenceId).click(function() {
        // multiple checked remove methods used to deal with various browser compatibility
        fieldDiv.find('input:radio:checked').prop("checked", false).attr("checked", false).removeAttr("checked");
    });
}

function saveMetadata() {
    var metadata = getMetadataAsJSON();
    var c_url = OC.generateUrl('apps/collections/crate/save_metadata');
    var collectionId = getIdOfSelectedCollection();
    $.ajax({
        url: c_url,
        type: 'post',
        dataType: 'json',
        data: {
            'crate_id': collectionId,
            'metadata': metadata
        },
        success: function(obj) {
            OC.Notification.showTemporary(obj.msg);
        },
        error: function(obj) {
            displayError(obj.msg);
        }
    });
}

function getMetadataAsJSON() {
    var metadata = { categories: {} };

    $('.metadata_category').each(function(index, category) {
        metadata.categories[category.id] = {
            fields: {},
            groups: {}
        };

        var $categoryFields = $(category).children('.panel-body').children('.metadata_field');
        $categoryFields.each(function(index, field) {
            //ToDo: rename div id's so that field, group and group field don't use '_box' in the name
            var fieldId = field.id.replace('_box', '');
            metadata.categories[category.id]['fields'][fieldId] = {
                occurrences: {}
            };

            var $fieldOccurrences = $(field).find('.field_occurrence');
            $fieldOccurrences.each(function(index, occurrence) {
                var inputValue;
                if ($(occurrence).find('span.radio').length != 0) {
                    inputValue = $(occurrence).find('input:radio:checked').val();
                    if (inputValue == null) {
                        inputValue = '';
                    }
                } else {
                    inputValue = $(occurrence).find('#' + occurrence.id.replace('occurrence_', 'input_')).val();
                }

                metadata.categories[category.id]['fields'][fieldId]['occurrences'][occurrence.id.replace('occurrence_', '')] = inputValue;
            });
        });

        var $categoryGroups = $(category).find('.metadata_group');
        $categoryGroups.each(function(index, group) {
            var groupId = group.id.replace('_box', '');
            metadata.categories[category.id]['groups'][groupId] = {
                occurrences: {}
            };

            var $groupOccurrences = $(group).find('.group_occurrence');
            $groupOccurrences.each(function(index, groupOccurrence) {
                var groupOccurrenceId = groupOccurrence.id.replace('occurrence_', '');
                metadata.categories[category.id]['groups'][groupId]['occurrences'][groupOccurrenceId] = {
                    fields: {}
                };

                var $groupFields = $(groupOccurrence).find('.metadata_field');
                $groupFields.each(function(index, groupField) {
                    var fieldId = groupField.id.replace('_box', '');
                    metadata.categories[category.id]['groups'][groupId]['occurrences'][groupOccurrenceId]['fields'][fieldId] = {
                        occurrences: {}
                    };

                    var $groupFieldOccurrences = $(groupField).find('.field_occurrence');
                    $groupFieldOccurrences.each(function(index, groupFieldOccurrence) {
                        var inputValue;
                        if ($(groupFieldOccurrence).find('span.radio').length != 0) {
                            inputValue = $(groupFieldOccurrence).find('input:radio:checked').val();
                            if (inputValue == null) {
                                inputValue = '';
                            }
                        } else {
                            inputValue = $(groupFieldOccurrence).find('#' + groupFieldOccurrence.id.replace('occurrence_', 'input_')).val();
                        }

                        metadata.categories[category.id]['groups'][groupId]['occurrences'][groupOccurrenceId]['fields'][fieldId]['occurrences'][groupFieldOccurrence.id.replace('occurrence_', '')] = inputValue;
                    });
                });
            });
        });

    });

    return metadata;
}

function setMetadataViewToEditValues() {
    $('.metadata_category').each(function(index, category) {

        var $categoryFields = $(category).children('.panel-body').children('.metadata_field');
        $categoryFields.each(function(index, field) {
            //ToDo: rename div id's so that field, group and group field don't use '_box' in the name
            var $fieldOccurrences = $(field).find('.field_occurrence');
            $fieldOccurrences.each(function(index, occurrence) {
                var inputValue;
                if ($(occurrence).find('span.radio').length != 0) {
                    inputValue = $(occurrence).find('input:radio:checked').val();
                    if (inputValue == null) {
                        inputValue = '';
                    }
                } else {
                    inputValue = $(occurrence).find('#' + occurrence.id.replace('occurrence_', 'input_')).val();
                }

                // Clear field and set value
                var $occurrenceDiv = $(occurrence).find('#' + occurrence.id.replace('occurrence_', ''));
                $occurrenceDiv.html('');
                $occurrenceDiv.text(inputValue);
            });
        });

        var $categoryGroups = $(category).find('.metadata_group');
        $categoryGroups.each(function(index, group) {
            var $groupOccurrences = $(group).find('.group_occurrence');
            $groupOccurrences.each(function(index, groupOccurrence) {

                var $groupFields = $(groupOccurrence).find('.metadata_field');
                $groupFields.each(function(index, groupField) {

                    var $groupFieldOccurrences = $(groupField).find('.field_occurrence');
                    $groupFieldOccurrences.each(function(index, groupFieldOccurrence) {
                        var inputValue;
                        if ($(groupFieldOccurrence).find('span.radio').length != 0) {
                            inputValue = $(groupFieldOccurrence).find('input:radio:checked').val();
                            if (inputValue == null) {
                                inputValue = '';
                            }
                        } else {
                            inputValue = $(groupFieldOccurrence).find('#' + groupFieldOccurrence.id.replace('occurrence_', 'input_')).val();
                        }

                        // Clear group field and set value
                        var $fieldOccurrenceDiv = $(groupFieldOccurrence).find('#' + groupFieldOccurrence.id.replace('occurrence_', ''));
                        $fieldOccurrenceDiv.html('');
                        $fieldOccurrenceDiv.text(inputValue);
                    });
                });
            });
        });

    });
}
