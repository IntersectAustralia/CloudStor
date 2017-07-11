#
 # [08_crateit_packagecrate_spec.rb]
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

describe 'When packaging a crate' do

  before (:each) do
    page.driver.browser.manage.window.maximize
    initialise_collections
  end

  before(:each, type: :cleanup) do
    visit_owncloud
    delete_owncloud_files(TestHelper::DEFAULT_COLLECTION_NAME, 'application/zip') # Delete any packages of the default collection
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
    if has_selector? "tr[data-file='#{TestHelper::TEST_SHARED_FOLDER}']"
      navigate_to_owncloud_folder TestHelper::TEST_SHARED_FOLDER
      delete_owncloud_files(TestHelper::DEFAULT_COLLECTION_NAME, 'application/zip')
    end
    visit_collections
  end

  after(:each, type: :cleanup) do
    delete_owncloud_files(TestHelper::DEFAULT_COLLECTION_NAME, 'application/zip') # Delete any packages of the default collection
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
  end

  it 'provides an appropriate package button' do
    action_bar = page.find('div.bar-actions')
    publish_button = action_bar.find('a#publish')
    expect(publish_button[:title]).to eq('Package collection to your Files')
    expect(publish_button).to have_content('Package')
  end

  it 'provides an appropriate destination label' do
    action_bar = page.find('div.bar-actions')
    publish_button = action_bar.find('a#publish').click
    destination_label = page.find('div.publish-meta:nth-child(1) > h6:nth-child(1)')
    expect(destination_label).to have_content('DESTINATION')
  end

  it 'a confirmation is displayed' do
    page.find('div.bar-actions').find('a#publish').click
    package_crate_modal = page.find('div#publishModal')
    expect(package_crate_modal['aria-hidden']).to eq('false')
    modal_title = package_crate_modal.find('#publishModalLabel')
    modal_header = package_crate_modal.find('div.modal-header')
    modal_body = package_crate_modal.find('div.modal-body')
    modal_footer = package_crate_modal.find('div.modal-footer')

    # Assert the content of the package confirmation dialog
    expect(modal_title).to have_content('Package Collection')
    expect(modal_body).to have_content('Click Package to proceed or click Cancel to exit action.')
    expect(modal_body).to have_content('COLLECTION SIZE: 0 B')
    expect(modal_footer).to have_selector('button', :text => 'Cancel')
    expect(modal_footer).to have_selector('button', :text => 'Package')
  end

  it 'can cancel packaging the crate' do
    page.find('div.bar-actions').find('a#publish').click
    package_crate_modal = page.find('div#publishModal')
    expect(package_crate_modal['aria-hidden']).to eq('false')
    package_crate_modal.click_button('Cancel')
    expect(package_crate_modal['aria-hidden']).to eq('true')
  end

  it 'generates an error if the crate contains invalid files', type: :cleanup do
    # Add file to crate, delete file and package collection
    visit_owncloud
    pause_notifications
    new_file_name = 'New text file.txt'
    create_new_owncloud_text_file new_file_name
    add_file_to_collection(new_file_name)
    expect_matching_notification("#{new_file_name} added to collection #{default_collection_name}")
    delete_owncloud_file(new_file_name)
    visit_collections
    package_collection
    
    # Assert crate validation dialog shows invalid files
    validate_crate_modal = page.find('#checkCrateModal')
    expect(validate_crate_modal['aria-hidden']).to eq('false')
    modal_title = validate_crate_modal.find('div.modal-header').find('#checkCrateModalLabel')
    expect(modal_title).to have_content('Collection Validation Results')

    modal_body = validate_crate_modal.find('div.modal-body')
    expect(modal_body).to have_content("There are 1 or more invalid items in your collection. Please review the collection file tree for the invalid items and delete these items. The following items are invalid: #{new_file_name}")
    modal_footer = validate_crate_modal.find('div.modal-footer')
    expect(modal_footer).to have_selector('button', text: 'Ok')
    modal_footer.click_button('Ok')
    expect(validate_crate_modal['aria-hidden']).to eq('true')
  end

  it 'does not create a package if a file is invalid', type: :cleanup do
    pause_notifications
    visit_owncloud
    new_file_name = 'New text file.txt'
    create_new_owncloud_text_file new_file_name
    add_file_to_collection(new_file_name)
    expect_matching_notification("#{new_file_name} added to collection #{default_collection_name}")
    visit_collections
    fill_out_required_metadata
    package_collection
    visit_owncloud
    expect(page).to have_content("#{default_collection_name}.zip")
  end

  it 'generates an error if required fields are not filled in, indicating which fields require input' do
    expected_modal_confirmation = 'Ok'
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
    # Assert crate validation is as expected when packaging
    package_collection
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

  it 'does not create a package if a required metadata field has no value', type: :cleanup do
    package_collection
    expect(page.find('#checkCrateModal')['aria-hidden']).to eq('false')
    visit_owncloud
    expect(page).to have_no_content("#{default_collection_name}.zip")
  end

  it 'displays the missing metadata notification only if all files are valid' do
    # Assert invalid file dialog is shown if there are both files invalid and missing metadata
    pause_notifications
    visit_owncloud
    new_file_name = 'New text file.txt'
    create_new_owncloud_text_file new_file_name
    add_file_to_collection(new_file_name)
    expect_matching_notification("#{new_file_name} added to collection #{default_collection_name}")
    delete_owncloud_file(new_file_name)
    visit_collections
    package_collection
    check_crate_modal = page.find('#checkCrateModal')
    expect(check_crate_modal).to have_content('There are 1 or more invalid items in your collection.')
    expect(check_crate_modal).to have_no_content('There are 1 or more invalid metadata fields in your collection.')
    check_crate_modal.click_button('Ok')
    remove_from_collection(new_file_name)
    find('#removeCrateModal').click_button('Remove')
    wait_for_ajax
    package_collection
    # Assert missing metadata dialog is shown once the invalid file is removed from the collection
    expect(check_crate_modal).to have_no_content('There are 1 or more invalid items in your collection.')
    expect(check_crate_modal).to have_content('There are 1 or more invalid metadata fields in your collection.')
  end

  it 'can package a crate', type: :cleanup  do
    fill_out_required_metadata
    find('#publish').click
    find('#publishModal').click_button('Package')
    # Assert the packaging progress dialog is shown
    packaging_dialog = page.find('#publishingCrateModal')
    expect(packaging_dialog['aria-hidden']).to eq('false')
    dialog_title = packaging_dialog.find('.modal-title#publishingCrateModalLabel')
    dialog_help_text = packaging_dialog.find('.modal-title#submitHelpText')
    expect(dialog_title).to have_content('Packaging Collection...')
    expect(dialog_help_text).to have_content('The Collection packaging is currently running in the background. If you reload the page or complete another action, the Collection packaging process will still continue and the package will be added to your Files once complete. Please be patient as this process will vary from a few seconds to several minutes depending on the Collection size.')
    # Assert the package completion notification displays the name of the created file
    expect_matching_notification("Collection packaged to your Files: #{default_collection_name}.zip")
    expect(packaging_dialog['aria-hidden']).to eq('true')
  end

  it 'can publish a crate to a shared folder', type: :cleanup do
    fill_out_required_metadata
    find('#publish').click
    find('#choose-cloudstor-destination').click
    find("li[data-entryname='#{TestHelper::TEST_SHARED_FOLDER}']").click
    find('button.primary').click
    find('#publishModal').click_button('Package')
    expect_matching_notification("Collection packaged to your Files: #{TestHelper::TEST_SHARED_FOLDER}/#{default_collection_name}.zip")
  end

  it 'automatically increments the name of the crate package', type: :cleanup do
    fill_out_required_metadata
    # Assert name shown in notification
    pause_notifications
    package_collection
    expect_matching_notification("Collection packaged to your Files: #{default_collection_name}.zip")
    package_collection
    expect_matching_notification("Collection packaged to your Files: #{default_collection_name} (2).zip")
    # Assert name of file in ownCloud
    visit_owncloud
    expect(page).to have_content("#{default_collection_name}.zip")
    expect(page).to have_content("#{default_collection_name} (2).zip")
  end

  it 'prompts for an optional email if the user does not have an email address set for their ownCloud account' do
    fill_out_required_metadata
    page.find('div.bar-actions').find('a#publish').click
    package_crate_modal = page.find('div#publishModal')
    modal_body = package_crate_modal.find('div.modal-body')
    modal_footer = package_crate_modal.find('div.modal-footer')
    expect(modal_body).to have_content(
      'If you would like to be notified when this collection has finished packaging, please enter an email address:')
    expect(modal_body).to have_selector('input#publish-notification-email')
    expect(modal_footer).to have_button('Package', :disabled => false)
  end

  # Note: requires "Visiting ownCloud for the fist timee: allows setting of user email" to have been run
  it 'does not prompt for an email if the user has an email address set for their ownCloud account' do
    log_out
    log_in_as_admin
    visit_collections
    wait_for_page_load
    fill_out_required_metadata
    page.find('div.bar-actions').find('a#publish').click
    wait_for_ajax
    package_crate_modal = page.find('div#publishModal')
    modal_body = package_crate_modal.find('div.modal-body')
    modal_footer = package_crate_modal.find('div.modal-footer')
    expect(modal_body).to have_no_content(
      'If you would like to be notified when this collection has finished packaging, please enter an email address:')
    expect(modal_body).not_to have_selector('input#publish-notification-email')
    expect(modal_footer).to have_button('Package', :disabled => false)
  end

  it 'clears the email prompt field when the modal is opened' do
    publish_button = find('div.bar-actions').find('a#publish')
    publish_button.click
    fill_in('publish-notification-email', :with => TestHelper::ADMIN_EMAIL)
    find('div#publishModal').click_button('Cancel')
    publish_button.click
    expect(find('#publish-notification-email').value).to eq('')
  end

  it 'validates the optional email address' do
    fill_out_required_metadata
    page.find('div.bar-actions').find('a#publish').click
    package_button = page.find('div#publishModal').find_button('Package')
    expect(package_button).not_to be_disabled
    validation_error = find('#publish-notification-email-validation-error', :visible => :all)
    expect(validation_error.visible?).to eq(false)
    fill_in('publish-notification-email', :with => 'invalid_email_address')
    expect(validation_error).to have_content('Not recognised as a valid email address')
    expect(validation_error.visible?).to eq(true)
    expect(package_button).to be_disabled
    fill_in('publish-notification-email', :with => TestHelper::ADMIN_EMAIL)
    expect(validation_error.visible?).to eq(false)
    expect(package_button).not_to be_disabled
    fill_in('publish-notification-email', :with => '')
    expect(validation_error.visible?).to eq(false)
    expect(package_button).not_to be_disabled
  end

  it 'does not display the invalid email address message when opening the modal after clearing an invalid email' do
    publish_button = find('div.bar-actions').find('a#publish')
    publish_button.click
    fill_in('publish-notification-email', :with => 'invalid_email_address')
    validation_error = find('#publish-notification-email-validation-error', :visible => :all)
    expect(validation_error.visible?).to eq(true)
    find('div#publishModal').click_button('Cancel')
    publish_button.click
    expect(validation_error.visible?).to eq(false)
  end

  it 'outputs the expected error message when the user runs out of quota' do
    pending 'implement this test that the user receives a packaging error notification when they run out of quota'
    fail
  end

  it 'automatically removes the package zip if packaging failed' do
    pending 'implement this test that the ZIP is automatically deleted when packaging fails from running out of quota'
    fail
  end

  it 'send the user an email with packaging completion status' do
    pending 'implement this test that an email is sent when packaging fails and when packaging succeeds'
    fail
  end

end