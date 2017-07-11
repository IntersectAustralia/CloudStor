#
 # [02_crateit_index_spec.rb]
 # Collections - Research data packaging for the rest of us
 # Copyright (C) 2017 Intersect Australia Ltd (https://intersect.org.au)
 #
 # This program is free software: you can redistribute it and/or modify
 # it under the terms of the GNU Affero General Public License as
 # published by the Free Software Foundation, either version 3 of the
 # License, or (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 # GNU Affero General Public License for more details.
 #
 # You should have received a copy of the GNU Affero General Public License
 # along with this program. If not, see <http://www.gnu.org/licenses/>.
#
require 'spec_helper.rb'

describe 'When on the collections index page' do
  before (:each) do
    log_into_collections
    @action_bar = page.find('div#content.app-collections').find('div.bar-actions')
  end

  it 'displays the add new crate button' do
    create_button = assert_action(@action_bar, 'a#create')
    assert_icon(create_button, 'fa-plus')
    assert_tooltip(create_button, 'Create a new collection')
    assert_modal_target(create_button, '#createCrateModal')
  end

  it 'displays the swap crate list' do
    crate_selector = assert_action(@action_bar, 'select#crates')
    assert_tooltip(crate_selector, 'Swap between existing collections')
  end

  it 'displays the package crate button' do
    package_button = assert_action(@action_bar, 'a#publish')
    assert_display_text(package_button, 'Package')
    assert_icon(package_button, 'fa-envelope')
    assert_tooltip(package_button, 'Package collection to your Files')
    assert_modal_target(package_button, '#publishModal')
  end

  it 'displays the packaging history button' do
    package_history_button = assert_action(@action_bar, 'a#package_history')
    assert_display_text(package_history_button, 'Package History')
    assert_icon(package_history_button, 'fa-tasks')
    assert_tooltip(package_history_button, 'Collections packaging history')
    assert_modal_target(package_history_button, '#packageHistoryModal')
  end

  it 'displays the check crate button' do
    check_button = assert_action(@action_bar, 'a#check')
    assert_display_text(check_button, 'Check Collection')
    assert_icon(check_button, 'fa-check-circle')
    assert_tooltip(check_button, 'Validate collection items')
    assert_modal_target(check_button, '#checkCrateModal')
  end

  it 'displays the export metadata button' do
    export_button = assert_action(@action_bar, 'a#export')
    assert_display_text(export_button, 'Export')
    assert_icon(export_button, 'fa-external-link')
    assert_tooltip(export_button, 'Export collection metadata to your Files')
    assert_modal_target(export_button, '#exportMetadataModal')
  end

  it 'displays the remove all files from collection button' do
    remove_all_files_button = assert_action(@action_bar, 'a#removeAllFiles')
    assert_display_text(remove_all_files_button, 'Remove All')
    assert_icon(remove_all_files_button, 'fa-ban')
    assert_tooltip(remove_all_files_button, 'Remove all items from the collection')
    assert_modal_target(remove_all_files_button, '#removeAllFilesModal')
  end

  it 'displays the delete collection button' do
    delete_button = assert_action(@action_bar, 'a#delete')
    assert_display_text(delete_button, 'Delete')
    assert_icon(delete_button, 'fa-trash-o')
    assert_tooltip(delete_button, 'Delete collection')
    # Modal target isn't asserted as delete modal is only displayed if crate contains some files
  end

  it 'displays the help button' do
    help_menu = assert_action(@action_bar, 'button#help_button')
    assert_display_text(help_menu, 'Help')
    assert_icon(help_menu, 'fa-question')
    assert_tooltip(help_menu, 'Help for the Collections app')

    # Assert actions within the help menu don't display until the menu is opened
    @action_bar.assert_no_selector('a#about_button')
    @action_bar.assert_no_selector('a#userguide')
    help_menu.click

    about_button = assert_action(@action_bar, 'a#about_button')
    assert_display_text(about_button, 'About')
    assert_icon(about_button, 'fa-question')
    assert_tooltip(about_button, 'About the Collections app')
    assert_modal_target(about_button, '#helpModal')

    user_guide = assert_action(@action_bar, 'a#userguide')
    assert_display_text(user_guide, 'User Guide')
    assert_icon(user_guide, 'fa-book')
    assert_tooltip(user_guide, 'Guide on how to use the Collections app')
  end

end

def assert_action(node, selector)
  node.assert_selector(selector)
  node.find(selector)
end

def assert_display_text(node, text)
  node.assert_text(text)
end

def assert_tooltip(node, tooltip)
  expect(node['title']).to eq(tooltip)
end

def assert_icon(node, fa_icon)
  node.assert_selector("i.fa.#{fa_icon}")
end

def assert_modal_target(node, target)
  expect(node['data-toggle']).to eq('modal')
  expect(node['data-target']).to eq(target)
end