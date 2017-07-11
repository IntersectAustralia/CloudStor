#
 # [05_crateit_deletefromcrate_spec.rb]
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

describe 'When deleting files or folders from a crate' do

  before (:each) do
    initialise_collections
  end

  it 'can delete a file within a crate' do
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    expect(page).to have_no_content(test_file_ending)
  end

  it 'can delete a folder within a crate recursively including the files within it' do
    visit_owncloud
    folder_name = 'Documents'
    add_folder_to_collection(folder_name)
    visit_collections
    toggle_collection_folder(folder_name)
    expect(page).to have_content()
    file_name = 'Example.odt'
    remove_from_collection(folder_name)
    find('div#removeCrateModal').click_button('Remove')
    expect(page).to have_no_content(folder_name)
    expect(page).to have_no_content(file_name)
  end

  it 'can cancel deleting a file or folder in a crate' do
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    remove_from_collection(test_file_ending)
    find('div#removeCrateModal').click_button('Cancel')
    expect(page).to have_content(test_file_ending)
  end

  it 'the crate size gets automatically updated' do
    expect(find('div#crate-size')).to have_content('Collection Size: 0 B')
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    expect(find('div#crate-size')).to have_content("Collection Size: #{get_test_file_size} MB")
    remove_from_collection(test_file_ending)
    find('div#removeCrateModal').click_button('Remove')
    expect(find('div#crate-size')).to have_content('Collection Size: 0 B')
  end

end