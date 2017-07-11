#
 # [test_helper.rb]
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
require 'uri'

module TestHelper

  COLLECTIONS_PATH = '/index.php/apps/collections/'
  ADMIN_USERNAME = 'admin'
  ADMIN_PASSWORD = 'admin'
  ADMIN_EMAIL = 'admin@collections.intersect.org.au'
  TEST_USERNAME = 'test@collections.intersect.org.au'
  TEST_PASSWORD = 'test'
  DEFAULT_COLLECTION_NAME = 'New Collection'
  TEST_FILE_ENDING = 'Manual.pdf'
  TEST_SHARED_FOLDER = 'Test Shared Folder'
  TEST_VISIBLE_METADATA_FOLDER = 'Visible Metadata'
  TEST_PRIVATE_METADATA_FOLDER = 'Private Metadata'
  METADATA_USER = 'metadata'
  SCHEMA_DEVELOPER_USER = 'developer@collections.intersect.org.au'
  SCHEMA_DEVELOPER_PASSWORD = 'developer'
  OC_VERSION = 9

  ##############################
  # C8it General Helper Methods#
  ##############################

  # NOTE: File ending instead of filename is for ownCloud version 
  # compatiblity
  def add_file_to_collection(file_ending)
    file = page.find("tr[data-type='file'][data-file$='#{file_ending}']")
    show_file_interaction_menu(file)
    file.click_link('Add to collection')
    wait_for_ajax
  end

  def get_test_file_size
    size = '3.9'
    if OC_VERSION < 9
      size = '2.1'
    end
    size
  end

  def test_file_ending
    TEST_FILE_ENDING
  end

  def default_collection_name
    DEFAULT_COLLECTION_NAME
  end

  def package_collection
    find('#publish').click
    find('#publishModal').click_button('Package')
    wait_for_ajax
  end

  def add_occurrence(description_id)
    add_occurrence_button = find("button#add_occurrence_#{description_id}")
    add_occurrence_button.click
    wait_for_ajax
  end

  def add_folder_to_collection(folder_name)
    folder = page.find("tr[data-type='dir'][data-file='#{folder_name}']")
    show_file_interaction_menu(folder)
    folder.click_link('Add to collection')
  end

  def toggle_collection_folder(folder_name)
    folder = page.find(:xpath, "//span[text()='#{folder_name}']/preceding-sibling::a")
    folder.click
  end

  def show_file_interaction_menu(file)
    if OC_VERSION >= 9
      file.find('span.icon-more').click
    else
      file.hover
    end
  end

  def remove_from_collection(file_ending)
    added_file = page.find(:xpath, "//span[@role='treeitem'][contains(text(), '#{file_ending}')]")
    added_file.hover
    added_file.find(:xpath, 'following-sibling::ul//a[@id="removeItem"]').click
  end

  def print_page
    print page.html
  end

  def save_screenshot
    page.save_screenshot('screenshot.png')
  end

  def fill_new_user_credentials
    fill_in('newusername', :with => TEST_USERNAME)
    fill_in('newuserpassword', :with => TEST_PASSWORD)
  end

  def search_for_term(text)
    fill_in('search_query', :with => text)
    click_button('search-btn')
  end

  def log_out
    find('span#expandDisplayName').click
    click_link('Log out')
  end

  def log_in
    fill_in('user', :with => TEST_USERNAME)
    fill_in('password', :with => TEST_PASSWORD)
    click_button('Log in')
  end

  def log_in_as_admin
    fill_in('user', :with => ADMIN_USERNAME)
    fill_in('password', :with => ADMIN_PASSWORD)
    click_button('Log in')
  end

  def is_logged_in
    !page.has_button?('Log in')
  end

  def visit_owncloud
    visit '/index.php/apps/files'
    wait_for_page_load
    wait_for_ajax
  end

  def visit_collections
    visit COLLECTIONS_PATH
    wait_for_page_load
  end

  def new_crate(name)
    find('a.button#create').click
    fill_in('crate_input_name', :with => name)
    click_button('create_crate_submit')
    wait_for_ajax
  end

  def select_crate(name)
    page.select(name, :from => 'crates')
  end

  def clear_crates
    [0..find('select#crates').all('option').size].each do
      find('a.button#delete').click
      if page.has_css?('button#delete_crate')
        click_button('delete_crate')
      end
    end
    wait_for_ajax
  end

  def click_node_link(node_class, link)
    find(node_class).hover
    find(link).click
  end

  def close_wizard
    if page.has_css?('a#closeWizard', :wait => 1)
      find('a#closeWizard').click
      sleep 1
    end
  end

  def initialise_collections
    log_into_collections
    clear_crates
  end

  def log_into_collections
    visit_collections
    unless is_logged_in
      log_in
    end
    close_wizard
  end

  # Deletes all files from ownCloud that match the start of a given basename (e.g. 'New Collection') and a mime type (e.g. 'application/xml')
  def delete_owncloud_files(file_basename, file_mime_type)
    files = page.all("tr[data-file*='#{file_basename}'][data-mime='#{file_mime_type}']")
    files.each do |file|
      show_file_interaction_menu(file)
      file.click_link('Delete')
    end
  end

  def delete_owncloud_file(file_name)
    file = find("tr[data-file='#{file_name}']")
    show_file_interaction_menu(file)
    file.click_link('Delete')
  end

  def navigate_to_owncloud_folder(folder_name)
    escaped_name = URI.escape folder_name
    find("a[href='/index.php/apps/files?dir=//#{escaped_name}']").find('.innernametext').click
  end

  def create_new_owncloud_text_file(filename)
    if OC_VERSION >= 9
      find('span.icon-add').click
      find('a.menuitem[data-action="file"]').click
      name_field = find('form.filenameform > input')
      name_field.set filename
      page.execute_script("$('form.filenameform').submit()")
      wait_for_ajax
      sleep 0.5
      page.execute_script("$('#editor_close').click()")
    else
      new_file_menu = find('#controls').find('#new')
      new_file_menu.click
      new_text_file = new_file_menu.find('.icon-filetype-text.svg')
      new_text_file.click
      new_text_file.find('#input-file').native.send_keys(:return)
    end
    wait_for_ajax
    sleep 0.5
  end

  def create_owncloud_folder(folder_name)
    if has_no_selector? "tr[data-file='#{folder_name}']"
      find('span.icon-add').click
      find('a.menuitem[data-action="folder"]').click
      name_field = find('form.filenameform > input')
      name_field.set folder_name
      page.execute_script("$('form.filenameform').submit()")
      wait_for_ajax
      sleep 0.5
    end
  end

  def share_owncloud_folder(folder_name, user)
    find("tr[data-file='#{folder_name}']").click
    share_field = find('input.shareWithField')
    share_field.set user
    find('.ui-autocomplete').click
  end

  def scroll_into_view(element_id)
    page.execute_script(%Q{$("##{element_id}").get(0).scrollIntoView();}) # Explicitly scroll category into view
  end

  # Asserts that a notification is shown with the matching message
  def expect_matching_notification(message)
    notification = page.find('#notification')
    expect(notification.text).to match(message)
    clear_notification
  end

  def wait_for_page_load
    Timeout.timeout(Capybara.default_max_wait_time) do
      loop do
        break if page.evaluate_script('window.document.readyState') == 'complete'
      end
    end
  end

  def wait_for_ajax
    Timeout.timeout(Capybara.default_max_wait_time) do
      loop do
        break if page.evaluate_script('jQuery.active').zero?
      end
    end
  end

  def pause_notifications
    page.evaluate_script('displayNotification = function(msg, time) { OC.Notification.show(msg) }')
  end

  def clear_notification
    page.evaluate_script('OC.Notification.hide()')
    sleep 0.5
  end

  def assert_open_category(category_id)
    within ('div#meta-data') do
      assert_selector('div#'+category_id+'.panel-collapse.standard.in')
      assert_selector('a#'+category_id+'-head')
      within('a#'+category_id+'-head') do
        assert_selector('i.pull-right.fa-caret-down')
      end
    end
  end

  def assert_collapsed_category(category_id)
    within ('div#meta-data') do
      assert_selector('div#'+category_id+'.panel-collapse.standard.collapse', visible: false)
      assert_selector('a#'+category_id+'-head')
      within('a#'+category_id+'-head') do
        assert_selector('i.pull-right.fa-caret-up')
      end
    end
  end

  def expect_field_content(category_id, field_id, occurrence_id, occurrence_value)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          expect(find("div##{occurrence_id}")).to have_content(occurrence_value)
        end
      end
    end
  end

  # Asserts a field display the mandatory indicator (*) using the required class (which colors the * red)
  def assert_mandatory_indicator(category_id, category_name, field_id, field_name)
    open_category_panel(category_id, category_name)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          field_title = find('.field_title')
          expect(field_title.text).to eq(field_name.upcase + ' *')
          expect(field_title.find('.required').text).to eq('*')
        end
      end
    end
  end

  def rename_collection(name)
    click_node_link('.rootfolder','#renameCrate')
    fill_in('rename-crate', :with => name)
    click_button('rename_crate')
    wait_for_ajax
  end
end
