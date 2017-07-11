#
 # [10_crateit_checkcrate_spec.rb]
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

describe 'When checking the crate' do

  before (:each) do
    initialise_collections
  end

  before(:each, type: :cleanup) do
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
    visit_collections
  end

  after(:each, type: :cleanup) do
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
  end

  it 'displays a success dialog when all files are valid' do
    page.find('div.bar-actions').find('#check').click
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    modal_title = validate_crate_modal.find('div.modal-header').find('#checkCrateModalLabel')
    modal_body = validate_crate_modal.find('div.modal-body')
    modal_footer = validate_crate_modal.find('div.modal-footer')

    # Assert the content of the confirmation dialog
    expect(modal_title).to have_content('Collection Validation Results')
    expect(modal_body).to have_content('Collection has been successfully checked and all items are valid.')
    expect(modal_footer).to have_selector('button', text: 'Ok')
  end

  it 'allows the crate validation modal to be closed' do
    # Assert confirming modal with Ok button
    page.find('div.bar-actions').find('#check').click
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    validate_crate_modal.click_button('Ok')
    expect(validate_crate_modal['aria-hidden']).to eq('true')

    # Assert closing modal with close (x) button
    page.find('div.bar-actions').find('#check').click
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    validate_crate_modal.click_button('×')
    expect(validate_crate_modal['aria-hidden']).to eq('true')
  end

  it 'displays an error dialog when some crate files no longer exist in ownCloud', type: :cleanup do
    # Create a new file and add it to the crate
    visit_owncloud
    new_file_name = 'New text file.txt'
    create_new_owncloud_text_file(new_file_name)
    add_file_to_collection(new_file_name)
    expect_matching_notification("#{new_file_name} added to collection #{default_collection_name}")

    # Delete the new file from ownCloud and assert the crate is now invalid
    delete_owncloud_file(new_file_name)
    visit_collections
    page.find('div.bar-actions').find('#check').click
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    modal_title = validate_crate_modal.find('div.modal-header').find('#checkCrateModalLabel')
    modal_body = validate_crate_modal.find('div.modal-body')
    modal_footer = validate_crate_modal.find('div.modal-footer')

    # Assert the content of the confirmation dialog
    expect(modal_title).to have_content('Collection Validation Results')
    expect(modal_body).to have_content('There are 1 or more invalid items in your collection. Please review the collection file tree for the invalid items and delete these items. The following items are invalid:
New text file.txt')
    expect(modal_footer).to have_selector('button', text: 'Ok')
    modal_footer.click_button('Ok')
    expect(validate_crate_modal['aria-hidden']).to eq('true')
  end


end