#
 # [03_crateit_addcrate_spec.rb]
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
require 'date'

describe 'When adding a new crate' do

  before (:each) do
    initialise_collections
  end

  it 'can add a new crate with a name' do
    crate_name = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name)
    expect(page).to have_select('crates', :selected => crate_name)
  end

  it 'can cancel adding a new crate' do
    find('a.button#create').click
    assert_selector('div#createCrateModal')
    find_button('Cancel').click
    assert_no_selector('div#createCrateModal')
    find('a.button#create').click
    assert_selector('div#createCrateModal')
    find_button('×').click
    assert_no_selector('div#createCrateModal')
  end

  it 'has a metadata schema select' do
    find('a.button#create').click
    metadata_select_label = find('div.modal-body > p:nth-child(1)')
    expect(page).to have_content('Metadata Schema')
    expect(page).to have_select('crate_metadata_schema', selected: 'Test metadata schema v2.0.1 - Test')
  end

  it 'only shows schema I have access to' do
    find('a.button#create').click
    expect(page).to have_select('crate_metadata_schema', :options => ['Test metadata schema v2.0.1 - Test', 'Test metadata schema v2.0.1 - Visible'])
  end

  it 'only shows the metadata developer user all schemas' do
    log_out
    fill_in('user', :with => TestHelper::SCHEMA_DEVELOPER_USER)
    fill_in('password', :with => TestHelper::SCHEMA_DEVELOPER_PASSWORD)
    click_button('Log in')
    visit_collections
    find('a.button#create').click
    expect(page).to have_select('crate_metadata_schema', :options => ['Test metadata schema v2.0.1 - Test', 'Test metadata schema v2.0.1 - Visible', 'Test metadata schema v2.0.1 - Private'])
  end

  # * see all schemas
  # multiple schemas but only one has email

  it "can't add a blank crate" do
    find('a.button#create').click
    expect(page).to have_button('Create', :disabled => true)
  end


  it "can't add special characters" do
    find('a.button#create').click
    special_chars = %w(\ / < > : " | ? *)
    special_chars.each do |char|
      fill_in('crate_input_name', :with => char)
      expect(page).to have_content("Invalid name. Illegal characters '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed")
      expect(page).to have_button('Create', :disabled => true)
    end
  end

  it 'can switch between crates and the tree reloads' do
    crate_name1 = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name1)
    crate_name2 = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name2)
    expect(find('span.jqtree-title')).to have_content(crate_name2)
    select_crate(crate_name1)
    expect(find('span.jqtree-title')).to have_content(crate_name1)
  end

  it 'when switching crates the add file action item adds to the selected crate' do
    crate_name1 = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name1)
    crate_name2 = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name2)
    select(crate_name1, :from => 'crates')
    select(crate_name2, :from => 'crates')
    visit_owncloud
    add_file_to_collection(test_file_ending)
    visit_collections
    expect(find('span.jqtree-title[aria-level="1"]')).to have_content(crate_name2)
    expect(find('span.jqtree-title[aria-level="2"]')).to have_content(test_file_ending)
  end

  it 'the crate name should be unique' do
    crate_name = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name)
    find('a.button#create').click
    fill_in('crate_input_name', :with => crate_name)
    click_button('create_crate_submit')
    # Expect the create modal to still be open since the crate name is not unique
    assert_selector('div#createCrateModal.modal.in')
    expect(page).to have_content('There was an error: Name '+crate_name+' has already been taken.')
  end


  it 'the crate name is mandatory' do
    find('a.button#create').click
    expect(page).to have_button('Create', :disabled => true)
    fill_in('crate_input_name', :with => ' ')
    expect(page).to have_content('Collection name cannot be blank')
    expect(page).to have_button('Create', :disabled => true)
    fill_in('crate_input_name', :with => 'mandatory_name')
    expect(page).to have_button('Create', :disabled => false)
  end

  it 'the crate name truncate at 128 characters' do
    name128 = "a" * 128
    name129 = name128 + "a"
    find('a.button#create').click
    fill_in('crate_input_name', :with => name128)
    expect(page).to_not have_content("Collection name has reached the limit of 128 characters")
    fill_in('crate_input_name', :with => name129)
    expect(page).to have_content("Collection name has reached the limit of 128 characters")
    expect(page).to have_field('crate_input_name', :with => name128)
  end
end