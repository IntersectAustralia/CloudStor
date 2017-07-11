#
 # [04_crateit_renamecrate_spec.rb]
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

describe 'When renaming a crate' do

  before (:each) do
    initialise_collections
  end

  it 'can rename a crate with valid input' do
    crate_name = 'new_crate_'+DateTime.now.strftime('%Q')
    rename_collection(crate_name)
    expect(page).to have_select('crates', :selected => crate_name)
  end

  it 'can cancel renaming a crate' do
    click_node_link('.rootfolder','a#renameCrate')
    assert_selector('div#renameCrateModal')
    find_button('Cancel').click
    assert_no_selector('div#renameCrateModal')
  end

  it 'cannot rename with old name' do
    click_node_link('.rootfolder','a#renameCrate')
    expect(page).to have_button('Rename', :disabled => true)
  end

  it 'cannot rename a crate to an empty name' do
    click_node_link('.rootfolder','a#renameCrate')
    fill_in('rename-crate', :with => '')
    expect(page).to have_button('Rename', :disabled => true)
  end

  it "can't add special characters" do
    click_node_link('.rootfolder','a#renameCrate')
    special_chars = %w(\ / < > : " | ? *)
    special_chars.each do |char|
      fill_in('rename-crate', :with => char)
      expect(page).to have_content("Invalid name. Illegal characters '\\', '/', '<', '>', ':', '\"', '|', '?' and '*' are not allowed")
      expect(page).to have_button('Rename', :disabled => true)
    end
  end

  it 'the crate name should be unique' do
    crate_name = 'crate_'+DateTime.now.strftime('%Q')
    new_crate(crate_name)
    click_node_link('.rootfolder','a#renameCrate')
    fill_in('rename-crate', :with => TestHelper::DEFAULT_COLLECTION_NAME)
    click_button('rename_crate')
    expect(page).to have_content("Name #{default_collection_name} has already been taken.")
  end

  it 'the crate name truncate at 128 characters' do
    name128 = "a" * 128
    name129 = name128 + "a"
    click_node_link('.rootfolder','a#renameCrate')
    fill_in('rename-crate', :with => name128)
    expect(page).to_not have_content("Collection name has reached the limit of 128 characters")
    fill_in('rename-crate', :with => name129)
    expect(page).to have_content("Collection name has reached the limit of 128 characters")
    expect(page).to have_field('rename-crate', :with => name128)
  end

end