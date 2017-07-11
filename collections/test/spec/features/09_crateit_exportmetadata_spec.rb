#
 # [09_crateit_exportmetadata_spec.rb]
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

describe 'When exporting crate metadata' do

  before (:each) do
    initialise_collections
  end

  before(:each, type: :cleanup) do
    delete_owncloud_files(TestHelper::DEFAULT_COLLECTION_NAME, 'application/xml') # Delete any packages of the default collection
    visit_collections
  end

  after(:each, type: :cleanup) do
    delete_owncloud_files(TestHelper::DEFAULT_COLLECTION_NAME, 'application/xml') # Delete any packages of the default collection
  end

  it 'displays a confirmation' do
    page.find('div.bar-actions').find('a#export').click
    export_metadata_modal = page.find('div#exportMetadataModal')
    expect(export_metadata_modal['aria-hidden']).to eq('false')
    modal_title = export_metadata_modal.find('div.modal-header').find('#exportMetadataModalLabel')
    modal_body = export_metadata_modal.find('div.modal-body')
    modal_footer = export_metadata_modal.find('div.modal-footer')

    # Assert the content of the confirmation dialog
    expect(modal_title).to have_content('Export Collection Metadata')
    expect(modal_body).to have_content('Collection metadata will be exported as XML to your Files.')
    expect(modal_body).to have_content('Click Export to proceed or click Cancel to return to your collection.')
    expect(modal_footer).to have_selector('button', :text => 'Cancel')
    expect(modal_footer).to have_selector('button', :text => 'Export')
  end

  it 'can cancel exporting the crate' do
    page.find('div.bar-actions').find('a#export').click
    export_metadata_modal = page.find('div#exportMetadataModal')
    expect(export_metadata_modal['aria-hidden']).to eq('false')
    export_metadata_modal.click_button('Cancel')
    expect(export_metadata_modal['aria-hidden']).to eq('true')
  end

  # it "displays an error if the crate metadata schema hasn't been retrieved from the cr8it server and saved yet" do
  #   page.find('div.bar-actions').find('a#export').click
  #   export_metadata_modal = page.find('div#exportMetadataModal')
  #   export_metadata_modal.click_button('Export')
  #   notification = page.find('div#notification-container').find('div#notification')
  #   expect(notification.text).to eq("There was an error: Crate doesn't contain any saved metadata")
  # end

  it 'generates an error if required fields are not filled in, indicating which fields require input' do
    expected_modal_title = 'Collection Validation Results'
    expected_modal_body = 'There are 1 or more invalid metadata fields in your collection. Please review the collection metadata for the required fields and ensure that there are no empty field values. The following required fields contain empty values:'
    expected_required_fields = '
    Category            Group     Field
    Collection Information             Title
    Collection Information             Mandatory Min Occurrences
    Data Creators       Creators  Phone Number
    Data Creators       Creators  Email Address
    Grants              Embargo   Embargo Details
    Dates                         No Default Date
    '
    expected_modal_confirmation = 'Ok'
    # Assert crate validation is as expected when exporting
    page.find('div.bar-actions').find('#export').click
    page.find('div#exportMetadataModal').click_button('Export')
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    modal_header = validate_crate_modal.find('div.modal-header')
    modal_title = modal_header.find('.modal-title')
    modal_body = validate_crate_modal.find('div.modal-body')
    modal_footer = validate_crate_modal.find('div.modal-footer')
    expect(modal_title.text).to eq(expected_modal_title)
    expect(modal_body).to have_content(expected_modal_body)
    expect(modal_body).to have_content(expected_required_fields)
    expect(modal_footer).to have_selector('button', :text => expected_modal_confirmation)
  end

  it 'generates an error if a required field has an empty value saved' do
    # Fill out all required fields and then save an empty value to one of the required fields
    fill_out_required_metadata
    pause_notifications
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    title_occurrence_id = get_first_occurrence_id(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id])
    scroll_into_view(title_occurrence_id)
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id], title_occurrence_id, '')

    # Assert crate validation displays error since a saved empty value counts as not filling in the required field
    page.find('div.bar-actions').find('#export').click
    page.find('div#exportMetadataModal').click_button('Export')
    wait_for_ajax

    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    expected_modal_body = 'There are 1 or more invalid metadata fields in your collection. Please review the collection metadata for the required fields and ensure that there are no empty field values. The following required fields contain empty values:'
    expected_required_fields = '
    Category           Group  Field
    Collection Information         Title
    '
    expect(validate_crate_modal).to have_content(expected_modal_body)
    expect(validate_crate_modal).to have_content(expected_required_fields)
  end

  it 'does not export metadata if a required metadata field has no value', type: :cleanup do
    page.find('div.bar-actions').find('#export').click
    page.find('div#exportMetadataModal').click_button('Export')
    expect(page.find('#checkCrateModal')['aria-hidden']).to eq('false')
    visit_owncloud
    packages = page.find('tbody#fileList').all("tr[data-type='file'][data-file*='#{default_collection_name}'][data-mime='application/xml']")
    expect(packages.length == 0)
  end

  it 'automatically increments the name of exported xml files', type: :cleanup do
    fill_out_required_metadata
    # Export the crate at least once
    page.find('div.bar-actions').find('a#export').click
    page.find('div#exportMetadataModal').click_button('Export')
    expect_matching_notification("Metadata exported to #{default_collection_name}.xml")

    # Assert the name of the exported crate increments
    sleep(5) # Wait a few seconds for the metadata schema to be retrieved from the Cr8it server and saved
    page.find('div.bar-actions').find('a#export').click
    page.find('div#exportMetadataModal').click_button('Export')
    expect_matching_notification("Metadata exported to #{default_collection_name} (2).xml")

    # Assert file name in ownCloud
    visit_owncloud
    exports = page.find('tbody#fileList').all("tr[data-type='file'][data-file*='#{default_collection_name}'][data-mime='application/xml']")
    expect(exports.length == 2)
    expect(exports[0]['data-file']).to eq("#{default_collection_name}.xml")
    expect(exports[1]['data-file']).to eq("#{default_collection_name} (2).xml")
  end

  it 'creates an xml file within ownCloud with the expected contents', type: :cleanup do
    fill_out_required_metadata
    # Assert the export completion notification displays the name of the exported file
    page.find('div.bar-actions').find('a#export').click
    export_metadata_modal = page.find('div#exportMetadataModal')
    export_metadata_modal.click_button('Export')
    expect_matching_notification("Metadata exported to #{default_collection_name}.xml")
    # Assert the export metadata modal is hidden upon clicking the export button
    expect(export_metadata_modal['aria-hidden']).to eq('true')

    # Assert ownCloud Files contains the exported metadata file
    visit_owncloud
    exports = page.find('tbody#fileList').all("tr[data-type='file'][data-file*='#{default_collection_name}'][data-mime='application/xml']")
    expect(exports.length == 1)
    exported_metadata = exports.first
    expect(exported_metadata['data-file']).to eq("#{default_collection_name}.xml")

    
  end

end