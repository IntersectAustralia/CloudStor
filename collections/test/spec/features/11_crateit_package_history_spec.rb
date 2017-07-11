#
 # [11_crateit_package_history_spec.rb]
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

describe 'When viewing the package history' do

  before (:each) do
    initialise_collections
  end

  before(:each, type: :cleanup) do
    delete_owncloud_files(default_collection_name, 'application/zip') # Delete default collection packages
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
    visit_collections_url
  end

  after(:each, type: :cleanup) do
    delete_owncloud_files(default_collection_name, 'application/zip') # Delete default collection packages
    delete_owncloud_files('New text file', 'text/plain') # Delete any previously created text files
  end

  it 'provides an appropriate package history button' do
    action_bar = find('div.bar-actions')
    package_history_button = action_bar.find('#package_history')
    expect(package_history_button[:title]).to eq('Collections packaging history')
    expect(package_history_button).to have_content('Package History')
  end

  it 'displays an appropriate message when no collections have been packaged yet' do
    # find('div.bar-actions').find('#package_history').click
    # package_history_modal = page.find('#packageHistoryModal')
    # expect(package_history_modal['aria-hidden']).to eq('false')
    # modal_title = package_history_modal.find('#packageHistoryModalLabel')
    # modal_body = package_history_modal.find('div.modal-body')
    # modal_footer = package_history_modal.find('div.modal-footer')
    # expect(modal_title).to have_content('Package history')
    # expect(modal_body).to have_content('No collections have been packaged yet')
    # expect(modal_footer).to have_selector('button', :text => 'Ok')
    pending 'Needs to be implemented. Requires some way of removing all previous packaging jobs in the history so that
      the "No collections have been packaged yet" message can be tested.'
    fail
  end

  it 'displays packaging information once a collection has been packaged' do
    fill_out_required_metadata
    find('div.bar-actions').find('a#publish').click
    find('div#publishModal').click_button('Package')
    wait_for_ajax
    find('div.bar-actions').find('#package_history').click
    expect(find('#packaging-history-message').text).to eq('Packaging history for all collections:')
    package_history_headers = first('#packaging-jobs-table thead tr')
    expect(package_history_headers.find('th:nth-child(1)').text).to eq('Collection')
    expect(package_history_headers.find('th:nth-child(2)').text).to eq('Start date and time')
    expect(package_history_headers.find('th:nth-child(3)').text).to eq('Status')
    package_job = first('#packaging-jobs-table tbody tr')
    expect(package_job.find('td:nth-child(1)').text).to eq(default_collection_name)
    expect(package_job.find('td:nth-child(3)').text).to eq('Completed')
  end

  it 'displays each of the package job status states' do
    pending 'Needs to be implemented. Requires some way of halting the state of a packaging job, so that each status
      message can be tested.'
    fail
  end

  it 'displays packaging information for each of the users packaged collections, not just the current collection' do
    fill_out_required_metadata
    find('div.bar-actions').find('a#publish').click
    find('div#publishModal').click_button('Package')
    wait_for_ajax
    new_crate('a_new_collection')
    find('div.bar-actions').find('#package_history').click
    wait_for_ajax
    package_job_name = first('#packaging-jobs-table tbody tr > td:nth-child(1)')
    expect(package_job_name.text).to eq(default_collection_name)
  end

  it 'displays the collection name at the time of packaging, rather than the current name' do
    fill_out_required_metadata
    find('div.bar-actions').find('a#publish').click
    find('div#publishModal').click_button('Package')
    wait_for_ajax
    rename_collection('renamed_collection')
    find('div.bar-actions').find('#package_history').click
    wait_for_ajax
    package_job_name = first('#packaging-jobs-table tbody tr > td:nth-child(1)')
    expect(package_job_name.text).to eq(default_collection_name)
  end

  it 'displays the packaging jobs within the history in order of most recent first' do
    # fill_out_required_metadata
    # package_button = find('div.bar-actions').find('a#publish')
    # package_button.click
    # package_modal = find('div#publishModal')
    # package_modal.click_button('Package')
    # wait_for_ajax
    # package_button.click
    # package_modal.click_button('Package')
    # wait_for_ajax
    # find('div.bar-actions').find('#package_history').click
    # wait_for_ajax
    # packaging_jobs = all('#packaging-jobs-table tbody tr')
    pending 'Needs to be implemented. Requires the comparison of the initiation date to the current time with some
    deviation for delay, or the usage of mocking.'
    fail
  end

end