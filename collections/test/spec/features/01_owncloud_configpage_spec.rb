#
 # [01_owncloud_configpage_spec.rb]
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

describe 'Visiting ownCloud for the fist time' do

  before (:each) do
    visit '/'
    unless is_logged_in
      log_in_as_admin
    end
    close_wizard
  end

  it 'collections app link is installed' do
    visit_collections
    expect(page).to have_current_path(TestHelper::COLLECTIONS_PATH)
  end

  it 'allows creation of a new non-admin user' do
    find('span#expandDisplayName').click
    click_link('Users')
    fill_new_user_credentials
    click_button('Create')
  end

  it 'shares a folder with a non-admin user' do
    create_owncloud_folder(TestHelper::TEST_SHARED_FOLDER)
    share_owncloud_folder(TestHelper::TEST_SHARED_FOLDER, TestHelper::TEST_USERNAME)
  end

  it 'allows setting of user email' do
    find('div#expand').click
    find('div#expanddiv').click_link('Personal')
    fill_in('email', with: TestHelper::ADMIN_EMAIL)
    sleep(1)
    set_email_msg = find('#lostpassword').find('span.msg', :visible => :all)
    expect(set_email_msg.visible?).to eq(true)
    set_email_msg.assert_text('Email saved')
  end

end