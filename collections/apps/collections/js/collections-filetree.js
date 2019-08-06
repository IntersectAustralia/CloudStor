/**
 * [collections-filetree.js]
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

function initFileTreeHeight() {
    $('#files').height($('#content').outerHeight() - $('.bar-actions').outerHeight() - $('.attribution').outerHeight());
}

function resizeFileTreeWidth() {
    var filesScrollBarPadding = 5;
    $('#files').width($(window).width() - $('#sidebar').width() - filesScrollBarPadding);
}

function indentTree() {
    $tree.find('.jqtree-element').each(function() {
        var indent = $(this).parents('li').length * 20;
        $(this).css('padding-left', indent);
        $(this).css('background-position', indent + 20 + 'px');
    });

    setFileTreeElementsTitleWidth();
}

function setFileTreeElementsTitleWidth() {
    $('.jqtree-element').each(function () {
        var $title = $(this).children('.jqtree-title');
        // Need to estimate action menu width as actual outer width is unavailable when node is initially collapsed
        var actionMenuEstimatedWidth = 30;
        var extraSpacing = 7;
        $title.width($('#files').outerWidth() - parseInt($(this).css('padding-left')) - parseInt($title.css('margin-left')) - actionMenuEstimatedWidth - extraSpacing);

        // Hide show more menu if space for name would be too narrow to appear
        if ($title.width() < (actionMenuEstimatedWidth - extraSpacing)) {
            $(this).children('.crate-actions').find('.show-more').hide();
        } else {
            $(this).children('.crate-actions').find('.show-more').show();
        }
    });
}

function attachModalHandlers($modal, confirmCallback) {
    var $confirm = $modal.find('.btn-primary');
    var confirmDisabled = $confirm.prop('disabled');

    var clearInput = function() {
        var $input = $modal.find('input');
        if ($input) {
            $input.val('');
        }
        var $label = $modal.find('label');
        if ($label) {
            $label.hide();
        }
    };

    $confirm.click(function() {
        confirmCallback();
        $modal.modal('hide');
    });


    $modal.on('hide.bs.modal', function() {
        $confirm.off('click');
        $confirm.prop('disabled', confirmDisabled);
        clearInput();
    });

    $modal.modal('show');
}

function transformCollection(collection) {
    return [{id: 'rootfolder', folderId:collection.rootFolderId, crateId: collection.id, label: collection.name, children: transformFolder(collection.rootFolder)}];
}

function transformFolder(folder) {
    subfolders = _.map(folder.folders, function(subfolder){
        return {id: 'folder', folderId: subfolder.id, label: subfolder.name, children: transformFolder(subfolder)}
    });
    subfiles = _.map(folder.files, function(file){
        return {id: 'file', fileId: file.id, label: file.name, mime: file.mimeType}
    });
    return subfolders.concat(subfiles);
}

function buildFileTree(crate) {
    var createImgUrl = function(node) {
        var icon_set = ['application-pdf', 'application', 'audio', 'file',  'folder-drag-accept', 'folder-external',
            'folder-public', 'folder-shared', 'folder-starred', 'folder', 'image', 'package-x-generic', 'text-calendar',
            'text-code', 'text-vcard', 'text', 'video', 'x-office-document', 'x-office-presentation',
            'x-office-spreadsheet'
        ];
        var icon = 'file';
        if (node.id == 'rootfolder') {
            return 'url(' + OC.filePath('collections', 'img', 'milk-crate-dark.png') + ')';
        } else if (node.id == 'folder') {
            icon = 'folder';
        } else if (node.id == 'file') {
            var mime_base = node.mime.split('/')[0];
            var mime = node.mime.replace(/\//, '-');
            if ($.inArray(mime, icon_set) > -1) {
                icon = mime;
            } else if ($.inArray(mime_base, icon_set) > -1) {
                icon = mime_base;
            }
        } else {
            icon = 'file';
        }

        // Need to check icon type as custom theme may not include all filetype icon images
        if (icon == 'folder') {
            return 'url(' + OC.imagePath('cloudstortheme', '../core/img/filetypes/' + icon + '.svg') + ')';
        } else {
            return 'url(' + OC.imagePath('core', 'filetypes/' + icon + '.svg') + ')';
        }
    };

    var addFolder = function(parentNode) {
        var $modal = $('#addFolderModal');
        $('#add-folder').keyup(function() {
            var $input = $('#add-folder');
            var $error = $('#add_folder_error');
            var $confirm = $modal.find('.btn-primary');
            validateItemName($input, $error, $confirm, parentNode.children);
        });
        var confirmCallback = function() {
            var parentFolderId = parentNode.folderId;
            var newFolderName = $('#add-folder').val();
            var collectionId = getIdOfSelectedCollection();
            var c_url = OC.generateUrl('apps/collections/crate/addFolder');
            $.ajax({
                url: c_url,
                type: 'post',
                dataType: 'json',
                data: {
                    'crate_id': collectionId,
                    'parentFolderId': parentFolderId,
                    'folderName': newFolderName
                },
                success: function(newFolder) {
                    $tree.tree('appendNode', {
                        id: 'folder',
                        folderId: newFolder.id,
                        label: newFolder.name,
                        children: []
                    }, parentNode);
                    $tree.tree('openNode', parentNode);
                    indentTree();
                },
                error: function(jqXHR) {
                    displayError(jqXHR.responseJSON.msg);
                }
            });
        };
        attachModalHandlers($modal, confirmCallback);
    };

    var renameItem = function(node) {
        var $modal = $('#renameItemModal');
        var $renameItem = $('#rename-item');
        $renameItem.val(node.name);
        $renameItem.on("input",function() {
            var $input = $('#rename-item');
            var $error = $('#rename_item_error');
            var $confirm = $modal.find('.btn-primary');
            validateItemName($input, $error, $confirm, node.parent.children);
        });
        var confirmCallback = function() {
            var newName = $('#rename-item').val();
            var c_url = OC.generateUrl('apps/collections/crate/rename_node');
            $.ajax({
                url: c_url,
                type: 'post',
                dataType: 'json',
                data: {
                    'crate_id': getIdOfSelectedCollection(),
                    'name': newName,
                    "type": node.id,
                    "id": node.fileId==undefined? node.folderId:node.fileId
                },
                success: function() {
                    $tree.tree('updateNode', node, newName);
                    indentTree();
                },
                error: function(jqXHR) {
                    displayError(jqXHR.responseJSON.msg);
                }
            });

        };
        attachModalHandlers($modal, confirmCallback);
    };

    var removeItem = function(node) {
        var $modal = $('#removeCrateModal');
        var msg = "Remove item '" + node.name + "' from collection?";
        $modal.find('.modal-body > p').text(msg);
        var confirmCallback = function() {
            var c_url = OC.generateUrl('apps/collections/crate/delete_node');
            $.ajax({
                url: c_url,
                type: 'post',
                dataType: 'json',
                data: {
                    'crate_id': getIdOfSelectedCollection(),
                    'type': node.id,
                    'id': node.fileId == undefined? node.folderId : node.fileId
                },
                success: function(data) {
                    var collection = data['crate'];
                    updateCollectionSize(collection);
                    $tree.tree('removeNode', node);
                    indentTree();
                },
                error: function(jqXHR) {
                    displayError(jqXHR.responseJSON.msg);
                }
            });
        };
        attachModalHandlers($modal, confirmCallback);
    };

    $tree = $('#files').tree({
        data: crate,
        autoOpen: false,
        dragAndDrop: true,
        saveState: false,
        selectable: false,
        useContextMenu: false,
        onCreateLi: function(node, $li) {
            $div = $li.find('.jqtree-element');
            $div.addClass('thumbnail');
            $div.css('background-image', createImgUrl(node));
            $ul = $div.append('<ul class="crate-actions pull-right"></ul>').find('ul');
            $title = $div.find('.jqtree-title');
            // append consistency checker icon
            var valid = node.valid;
            if (valid == 'false') {
                $title.prepend('<i class="fa fa-times" style="color:red;  padding-right: 5px;"></i>');
            }
            else if (valid == 'true') {
                $title.prepend('<i class="fa fa-check" style="color:green; padding-right: 5px;"></i>');
            }
            // Hack to truncate the node title without overwriting the prepended validity icon html
            var text = $title.text(); // assumes that title name is the only inner text
            var html = $title.html();
            html = html.substring(0, html.length - text.length); // assumes text is at end of html
            $title.html(html + text); // necessary as $title.text() overwrites the inner html as well as the inner text

            var type = node.id;

            if (type == 'rootfolder') {
                $ul.append('' +
                    '<li>' +
                    '<i class="show-more"> &nbsp; </i>' +
                    '</li>');

                $ul.find('.show-more').parent().one("click", function() {
                    $(".pop-up-menu").hide();
                    var menuItems = '<div class="pop-up-menu">' +
                        '<li class="li-height">' +
                        '<a id="addFolder">' +
                        '<i class="fa fa-plus icon-item-space"></i>' +
                        'Add Folder Item' +
                        '</a>' +
                        '</li>' +
                        '</div>';
                    $(this).append(menuItems);

                    $(".pop-up-menu", this).hide();

                    $("#addFolder", this).click(function() {
                        addFolder(node);
                    });

                });

                $ul.find('.show-more').parent().click(function() {
                    $(".pop-up-menu", this).slideToggle(10, function() {
                        $(".pop-up-menu").not(this).hide();
                    });
                });
            }

            if(type == 'folder'){
                $ul.append('' +
                    '<li>' +
                    '<i class="show-more"> &nbsp; </i>' +
                    '</li>');

                $ul.find('.show-more').parent().one("click", function() {
                    $(".pop-up-menu").hide();

                    var menuItems = '<div class="pop-up-menu">' +
                        '<li class="li-height">' +
                        '<a id="addFolder">' +
                        '<i class="fa fa-plus icon-item-space"></i>' +
                        'Add Folder Item' +
                        '</a>' +
                        '</li>' +
                        '<li class="li-height">' +
                        '<a id="renameCrate">' +
                        '<i class="fa fa-pencil icon-item-space"></i>' +
                        'Rename Item' +
                        '</a>' +
                        '</li>'+
                        '<li class="li-height">' +
                        '<a id="removeItem">' +
                        '<i class="fa fa-trash-o icon-item-space"></i>' +
                        'Remove Item' +
                        '</a>' +
                        '</li>' +
                    '</div>';
                    $(this).append(menuItems);

                    $(".pop-up-menu", this).hide();

                    $("#addFolder", this).click(function() {
                        addFolder(node);
                    });

                    $("#renameCrate", this).click(function() {
                        renameItem(node);
                    });

                    $("#removeItem", this).click(function() {
                        removeItem(node);
                    });


                });

                $ul.find('.show-more').parent().click(function() {
                    $(".pop-up-menu", this).slideToggle(10, function() {
                        $(".pop-up-menu").not(this).hide();
                    });
                });
            }

            if(type != 'rootfolder' && type != 'folder'){

                $ul.append('' +
                    '<li>' +
                    '<i class="show-more"> &nbsp; </i>' +
                    '</li>');

                $ul.find('.show-more').parent().one("click", function() {
                    $(".pop-up-menu").hide();

                    var menuItems = '<div class="pop-up-menu">' +
                        '<li class="li-height">' +
                        '<a id="renameCrate">' +
                        '<i class="fa fa-pencil icon-item-space"></i>' +
                        'Rename Item' +
                        '</a>' +
                        '</li>'+
                        '<li class="li-height">' +
                        '<a id="removeItem">' +
                        '<i class="fa fa-trash-o icon-item-space"></i>' +
                        'Remove Item' +
                        '</a>' +
                        '</li>' +
                    '</div>';
                    $(this).append(menuItems);

                    $(".pop-up-menu", this).hide();

                    $("#renameCrate", this).click(function() {
                        renameItem(node);
                    });

                    $("#removeItem", this).click(function() {
                        removeItem(node);
                    });
                });

                $ul.find('.show-more').parent().click(function() {
                    $(".pop-up-menu", this).slideToggle(10, function() {
                        $(".pop-up-menu").not(this).hide();
                    });
                });
            }

            $("body").click(function(event) {
                var target = $(event.target);
                if (!target.is("i")) {
                    $(".pop-up-menu").hide()
                }
            });
        },
        onCanMove: function(node) {
            var result = true;
            // Cannot move root node
            if (!node.parent.parent) {
                result = false;
            }
            return result;
        },
        onCanMoveTo: function(moved_node, target_node, position) {
            // Can move before or after any node.
            // Can only move INSIDE of a node whose id ends with 'folder'
            if (target_node.id.indexOf('folder', target_node.id.length - 'folder'.length) == -1) {
                return (position != 'inside');
            } else if (target_node.id == 'rootfolder') {
                return (position != 'before' && position != 'after');
            } else {
                return true;
            }
        }
    });


    $tree.bind('tree.move', function(event) {
        event.preventDefault();
        var move_info = event.move_info;
        var json_moved_node = {
            'crate_id': getIdOfSelectedCollection(),
            "name": move_info.moved_node.name,
            "type": move_info.moved_node.id,
            "id": move_info.moved_node.fileId==undefined? move_info.moved_node.folderId:move_info.moved_node.fileId,
            "parentFolderId":move_info.position=='after' ? move_info.target_node.parent.folderId:move_info.target_node.folderId
        };
        var c_url = OC.generateUrl('apps/collections/crate/move_node');
        $.ajax({
            url: c_url,
            type: 'post',
            dataType: 'json',
            data: json_moved_node,
            success: function() {
                move_info.do_move();
                indentTree();
            },
            error: function(jqXHR) {
                displayError(jqXHR.responseJSON.msg);
                indentTree();
            }
        });

    });

    expandRoot();

    return $tree;
}

function getRootNode() {
    var tree = $('#files').tree('getTree');
    return tree.children[0];
}

function expandRoot() {
    $tree.tree('openNode', getRootNode());
}

function updateTreeValidityIcons(collectionValid, filesValid) {
    if (collectionValid) {
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
}

function getMatchingNode(path) {
    var root_node = getRootNode();
    return checkChildrenForTargetNode(root_node, null, path);
}

function checkChildrenForTargetNode(node, currentPath, targetPath) {
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
}

function getCollectionNameViaFileTree() {
    return getRootNode().name;
}
