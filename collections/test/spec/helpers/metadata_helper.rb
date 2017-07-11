#
 # [metadata_helper.rb]
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
module MetadataHelper

  BLANK_TEXT = ''
  SAMPLE_TEXT_1 = 'This is a text sample'
  SAMPLE_TEXT_2 = 'Another text sample'
  CATEGORIES = {
      collection_info: {
          id: 'collectionInformation',
          name: 'Collection Information',
          fields:  {
              title: {
                  id: 'title',
                  name: 'Title',
                  mandatory: true,
                  placeholder: 'Enter a title for this Collection',
                  char_limit: 150,
                  tooltip: 'The Collection title',
                  minOccurs: 1,
                  maxOccurs: 1
              },
              description: {
                  id: 'description',
                  name: 'Description',
                  mandatory: true,
                  placeholder: 'Enter a description of the research data package for this Collection',
                  char_limit: 150,
                  tooltip: 'A description of the Collection',
                  default_value: 'New Collection',
                  minOccurs: 1,
                  maxOccurs: 2
              },
              language: {
                  id: 'language',
                  name: 'Language',
                  mandatory: true,
                  placeholder: 'Enter a ISO639-3 language code',
                  char_limit: 150,
                  default_value: 'English',
                  minOccurs: 2,
                  maxOccurs: 2
              },
              mandatory_min: {
                  id: 'mandatoryMin',
                  name: 'Mandatory Min Occurrences',
                  mandatory: true,
                  minOccurs: 2,
                  maxOccurs: 4
              }
          }
      },
      data_creators: {
          id: 'dataCreators',
          name: 'Data Creators',
          groups: {
              creators: {
                  id: 'creators',
                  name: 'Creators',
                  tooltip: 'Data creator details',
                  fields: {
                      given_name: {
                          id: 'given_name',
                          name: 'Given name',
                          mandatory: false,
                          placeholder: 'Creator given name',
                          tooltip: "Creator's primary phone number",
                          minOccurs: 1,
                          maxOccurs: 1
                      },
                      phone_number: {
                          id: 'phone',
                          name: 'Phone Number',
                          mandatory: true,
                          minOccurs: 1,
                          maxOccurs: 1
                      },
                      email: {
                          id: 'email',
                          name: 'Email Address',
                          mandatory: true,
                          minOccurs: 1,
                          maxOccurs: 5
                      },
                      date_of_birth: {
                          id: 'dateOfBirth',
                          name: 'Date of birth',
                          mandatory: true,
                          minOccurs: 1,
                          maxOccurs: 1,
                          min_date: '1900-01-01',
                          max_date: '3000-12-31'
                      }
                  }
              }
          }
      },
      grants: {
          id: 'grants',
          name: 'Grants',
          groups: {
              embargo: {
                  fields: {
                      embargoDetails: {
                          id: 'embargoDetails',
                          name: 'Embargo Details',
                          mandatory: true,
                          placeholder: '',
                          tooltip: nil,
                          minOccurs: 1,
                          maxOccurs: 2
                      },
                      retention: {
                          id: 'retention',
                          name: 'Retention Period',
                          mandatory: false,
                          minOccurs: 1,
                          maxOccurs: 1
                      }
                  }
              }
          }
      },
      dates: {
          id: 'dates',
          name: 'Dates',
          fields: {
              current_date: {
                  id: 'currentDate',
                  name: 'Current Date',
                  mandatory: true,
                  min_date: '1900-01-01',
                  max_date: '3000-12-31',
                  default_value: 'today',
                  tooltip: 'Current date'
              },
              no_default: {
                  id: 'noDefaultDate',
                  name: 'No Default Date',
                  mandatory: true,
                  min_date: '1900-01-01',
                  max_date: '3000-12-31',
                  default_value: nil,
                  tooltip: 'A mandatory date with no default value'
              }
          }
      }
  }

  ###################################
  # Metadata Specific Helper Methods#
  ###################################

  def open_category_panel(category_id, category_name)
    within ('div#meta-data') do
      if page.has_selector?("##{category_id}-head.collapsed")
        click_link(category_name)
        # Ensures the panel opening transition has finished
        sleep(0.5)
      end
    end
  end

  # Saves a value to a textual metadata field
  def edit_and_save_field(category_id, field_id, occurrence_id, occurrence_value)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          click_button("edit_#{occurrence_id}")
          fill_in("input_#{occurrence_id}", with: occurrence_value)
          save_occurrence_button = "save_#{occurrence_id}"
          scroll_into_view(save_occurrence_button)
          click_button(save_occurrence_button)
        end
      end
    end
    wait_for_ajax
  end

  def edit_and_cancel_field(category_id, field_id, occurrence_id, occurrence_value)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          click_button("edit_#{occurrence_id}")
          fill_in("input_#{occurrence_id}", with: occurrence_value)
          click_button("cancel_#{occurrence_id}")
        end
      end
    end
  end

  # Saves the active date with a bootstrap date picker
  def edit_and_save_active_date(category_id, field_id, occurrence_id)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          click_button("edit_#{occurrence_id}")
          scroll_into_view("datepicker_#{occurrence_id}")
          find("#datepicker_#{occurrence_id}").click
        end
      end
    end
    current_date_picker = find('.bootstrap-datetimepicker-widget[style*="display: block"]')
    current_date_picker.find('.day.active', visible: :all).click
    find("#meta-data").click # Click on the screen to close the date picker
    find('#meta-data').find("##{category_id}").find("##{field_id}_box").find("#save_#{occurrence_id}").click
    wait_for_ajax
  end

  # Gets the first field occurrence id for a particular field id
  def get_first_occurrence_id(category_id, field_id)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          return first("div[id^='#{field_id}'].metadata.field_value")[:id]
        end
      end
    end
  end

  # Gets all field occurrence ids for a particular field id
  def get_all_occurrence_ids(category_id, field_id)
    within ('div#meta-data') do
      within ("div##{category_id}") do
        within ("div##{field_id}_box") do
          return all("div[id^='#{field_id}'].metadata.field_value").collect { |occurrence| occurrence[:id] }
        end
      end
    end
  end

  # Fills out a value for all required metadata fields that don't contain a default value
  def fill_out_required_metadata
    # Fill out required text fields that don't have a default value
    open_category_panel(CATEGORIES[:collection_info][:id], CATEGORIES[:collection_info][:name])
    title_occurrence_id = get_first_occurrence_id(CATEGORIES[:collection_info][:id], CATEGORIES[:collection_info][:fields][:title][:id])
    scroll_into_view(title_occurrence_id)
    edit_and_save_field(CATEGORIES[:collection_info][:id], CATEGORIES[:collection_info][:fields][:title][:id], title_occurrence_id, 'Hello World!')

    mandatory_min_field = get_all_occurrence_ids(CATEGORIES[:collection_info][:id], CATEGORIES[:collection_info][:fields][:mandatory_min][:id])
    mandatory_min_field.each do |occurrence_id|
      scroll_into_view(occurrence_id)
      edit_and_save_field(CATEGORIES[:collection_info][:id], CATEGORIES[:collection_info][:fields][:mandatory_min][:id], occurrence_id, 'Hello World!')
    end

    # Fill out required date fields that don't have a default value
    open_category_panel(CATEGORIES[:dates][:id], CATEGORIES[:dates][:name])
    no_default_date_occurrences = get_all_occurrence_ids(CATEGORIES[:dates][:id], CATEGORIES[:dates][:fields][:no_default][:id])
    no_default_date_occurrences.each do |occurrence_id|
      scroll_into_view(occurrence_id)
      edit_and_save_active_date(CATEGORIES[:dates][:id], CATEGORIES[:dates][:fields][:no_default][:id], occurrence_id)
    end

    # Fill out required group fields that don't have a default value
    open_category_panel(CATEGORIES[:data_creators][:id], CATEGORIES[:data_creators][:name])
    phone_number_occurrence = get_first_occurrence_id(CATEGORIES[:data_creators][:id],
                                                      CATEGORIES[:data_creators][:groups][:creators][:fields][:phone_number][:id])
    scroll_into_view(phone_number_occurrence)
    edit_and_save_field(CATEGORIES[:data_creators][:id], CATEGORIES[:data_creators][:groups][:creators][:fields][:phone_number][:id],
                        phone_number_occurrence, '1234567890')
    email_occurrence = get_first_occurrence_id(CATEGORIES[:data_creators][:id],
                                               CATEGORIES[:data_creators][:groups][:creators][:fields][:email][:id])
    scroll_into_view(email_occurrence)
    edit_and_save_field(CATEGORIES[:data_creators][:id], CATEGORIES[:data_creators][:groups][:creators][:fields][:email][:id],
                        email_occurrence, 'test@place.org')
    date_of_birth_occurrence = get_first_occurrence_id(CATEGORIES[:data_creators][:id],
                                                       CATEGORIES[:data_creators][:groups][:creators][:fields][:date_of_birth][:id])
    scroll_into_view(date_of_birth_occurrence)
    edit_and_save_active_date(CATEGORIES[:data_creators][:id], CATEGORIES[:data_creators][:groups][:creators][:fields][:date_of_birth][:id],
                        date_of_birth_occurrence)

    open_category_panel(CATEGORIES[:grants][:id], CATEGORIES[:grants][:name])
    embargo_occurrence = get_first_occurrence_id(CATEGORIES[:grants][:id],
                                                 CATEGORIES[:grants][:groups][:embargo][:fields][:embargoDetails][:id])
    scroll_into_view(embargo_occurrence)
    edit_and_save_field(CATEGORIES[:grants][:id], CATEGORIES[:grants][:groups][:embargo][:fields][:embargoDetails][:id],
                        embargo_occurrence, 'None')
  end
end