#
 # [06_crateit_removeallfiles_spec.rb]
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

describe 'When removing all files from a collection' do

  before (:each) do
    initialise_collections
  end

  it 'a confirmation is displayed' do
    page.find('div.bar-actions').find('a#removeAllFiles').click
    expect(page).to have_selector('div#removeAllFilesModal[aria-hidden="false"]')
    remove_all_files_modal = page.find('div#removeAllFilesModal[aria-hidden="false"]')
    expect(remove_all_files_modal).to have_content('Remove All From Collection')
    expect(remove_all_files_modal).to have_content('All items will be removed from this collection, Continue?')
    expect(remove_all_files_modal).to have_selector('button', :text => 'Cancel')
    expect(remove_all_files_modal).to have_selector('button', :text => 'Remove All')
  end

  it 'can cancel removing all items from the collection' do
    sleep(1)
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    expect(page).to have_content(test_file_ending)
    page.find('#removeAllFiles').click
    page.find('#removeAllFilesModal').click_button('Cancel')
    expect(page).to have_content(test_file_ending)
  end

  it 'can remove all items from the collection' do
    sleep(1)
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    expect(page).to have_content(test_file_ending)
    page.find('#removeAllFiles').click
    page.find('#removeAllFilesModal').click_button('Remove All')
    expect(page).to have_no_content(test_file_ending)
  end

end