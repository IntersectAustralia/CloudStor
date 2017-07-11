/**
 * [ocdialogs-extensions.js]
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

var OCdialogs = $.extend(OCdialogs, {

    directorypicker: function (title, selectCallback, cancelCallback, modal) {
        var self = this;
        // avoid opening the picker twice
        if (this.filepicker.loading) {
            return;
        }
        this.filepicker.loading = true;

        if (modal === undefined) {
            modal = false;
        }

        var multiselect = false;

        $.when(this._getFilePickerTemplate()).then(function ($tmpl) {
            self.filepicker.loading = false;
            var dialogName = 'oc-dialog-filepicker-content';
            if (self.$filePicker) {
                self.$filePicker.ocdialog('close');
            }
            self.$filePicker = $tmpl.octemplate({
                dialog_name: dialogName,
                title: title
            }).data('path', '');

            $('body').append(self.$filePicker);

            self.$filePicker.ready(function () {
                self.$filelist = self.$filePicker.find('.filelist');
                self.$dirTree = self.$filePicker.find('.dirtree');
                self.$dirTree.on('click', 'span:not(:last-child)', self, self._handleDirectoryTreeListSelect);
                self.$filelist.on('click', 'li', function (event) {
                    self._handleDirectoryPickerClick(event, $(this));
                });
                self._fillDirectoryPicker('');
            });

            // build buttons
            var functionToCall = function () {
                if (selectCallback !== undefined) {
                    var datapath;
                    if (multiselect === true) {
                        datapath = [];
                        self.$filelist.find('.filepicker_element_selected .filename').each(function (index, element) {
                            datapath.push(self.$filePicker.data('path') + '/' + $(element).text());
                        });
                    } else {
                        datapath = self.$filePicker.data('path');
                        datapath += '/' + self.$filelist.find('.filepicker_element_selected .filename').text();
                    }
                    selectCallback(datapath);
                    self.$filePicker.ocdialog('close');
                }
            };
            var buttonlist = [{
                text: t('core', 'Select'),
                click: functionToCall,
                defaultButton: true
            }];

            self.$filePicker.ocdialog({
                closeOnEscape: true,
                // max-width of 600
                width: Math.min((4 / 5) * $(document).width(), 600),
                height: 420,
                modal: modal,
                buttons: buttonlist,
                close: function () {
                    try {
                        $(this).ocdialog('destroy').remove();
                    } catch (e) {
                    }
                    self.$filePicker = null;
                    cancelCallback();
                }
            });
            if (!OC.Util.hasSVGSupport()) {
                OC.Util.replaceSVG(self.$filePicker.parent());
            }
        })
        .fail(function (status, error) {
            // If the method is called while navigating away
            // from the page, it is probably not needed ;)
            self.filepicker.loading = false;
            if (status !== 0) {
                alert(t('core', 'Error loading file picker template: {error}', {error: error}));
            }
        });
    },

    _fillDirectoryPicker: function(dir) {
        var dirs = [];
        var self = this;
        this.$filelist.empty().addClass('icon-loading');
        this.$filePicker.data('path', dir);
        $.when(this._getFileList(dir, this.$filePicker.data('mimetype'))).then(function(response) {

            $.each(response.data.files, function(index, file) {
                // NOTE: File permissions (additive)
                // 11 - Can read shared
                // 16 - Can on-share
                //  4 - Can create
                if (file.type === 'dir' && ((file.permissions % 16) % 11) >= 4) {
                    dirs.push(file);
                }
            });

            self._fillSlug();
            var sorted = dirs;

            $.each(sorted, function(idx, entry) {
                entry.icon = OC.MimeType.getIconUrl(entry.mimetype);
                var $li = self.$listTmpl.octemplate({
                    type: entry.type,
                    dir: dir,
                    filename: entry.name,
                    date: OC.Util.relativeModifiedDate(entry.mtime)
                });
                $li.find('img').attr('src', OC.Util.replaceSVGIcon(entry.icon));
                self.$filelist.append($li);
            });

            self.$filelist.removeClass('icon-loading');
            if (!OC.Util.hasSVGSupport()) {
                OC.Util.replaceSVG(self.$filePicker.find('.dirtree'));
            }
        });
    },

    _handleDirectoryTreeListSelect:function(event) {
        var self = event.data;
        var dir = $(event.target).data('dir');
        self._fillDirectoryPicker(dir);
    },

    _handleDirectoryPickerClick: function(event, $element) {
        this._fillDirectoryPicker(this.$filePicker.data('path') + '/' + $element.data('entryname'));
    }

});
