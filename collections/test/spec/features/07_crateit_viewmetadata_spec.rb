#
 # [07_crateit_viewmetadata_spec.rb]
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
require 'active_support/core_ext/integer/inflections'

# README: These tests require the Cr8it server to be run in the dev/test environment for the appropriate seed data
describe 'When viewing crate metadata' do

  before (:each) do
    initialise_collections
  end

  it 'renders metadata categories' do
    # Expect to see crate categories set in cr8it server seed data and collapsed by default
    assert_collapsed_category(MetadataHelper::CATEGORIES[:collection_info][:id])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:data_creators][:id])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:grants][:id])

    # Expect category headers to open the clicked category and collapse all other categories
    click_link(MetadataHelper::CATEGORIES[:data_creators][:name])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:collection_info][:id])
    assert_open_category(MetadataHelper::CATEGORIES[:data_creators][:id])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:grants][:id])
    click_link(MetadataHelper::CATEGORIES[:grants][:name])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:collection_info][:id])
    assert_collapsed_category(MetadataHelper::CATEGORIES[:data_creators][:id])
    assert_open_category(MetadataHelper::CATEGORIES[:grants][:id])
  end

  it 'renders text fields' do
    # Note: The selectors used in this test assume that the title box only contains one metadata occurrence
    title_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id]
    title_name = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:name]
    title_char_limit = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:char_limit]
    title_placeholder = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:placeholder]
    title_occurrence_id = nil
    within ('div#meta-data') do
      # Expect to see Title field in Collection Information metadata category
      click_link(MetadataHelper::CATEGORIES[:collection_info][:name])
      assert_selector('div#'+title_id+'_box')
      metadata_field_box = find('div#'+title_id+'_box')
      expect(metadata_field_box).to have_content(title_name.upcase)

      within ('div#'+title_id+'_box') do
        # See https://css-tricks.com/attribute-selectors/ for a description on how to use CSS attribute selectors in place of regular expressions
        title_occurrence_id = find("div[id^='#{title_id}'].metadata.field_value")[:id]

        # When editing text value, expect to see placeholder
        find('button[placeholder="Edit"]').click
        title_input = find("textarea[id^='input_#{title_id}']")
        expect(title_input[:maxlength]).to eq(title_char_limit.to_s)
        expect(title_input[:placeholder]).to eq(title_placeholder)
        click_button('Cancel')

        # When editing text value, expect character limit to be validated and text to be truncated if character limit is reached
        validation_error = title_name+' has reached the limit of '+title_char_limit.to_s+' characters'
        valid_text_under_limit = "a" * (title_char_limit - 1)
        valid_text_at_limit = "a" * title_char_limit
        invalid_text_over_limit = "a" * (title_char_limit + 1)

        find('button[placeholder="Edit"]').click
        title_input.set(valid_text_under_limit)
        expect(metadata_field_box).to_not have_content(validation_error)
        expect(title_input.value).to eq(valid_text_under_limit)

        title_input.set(valid_text_at_limit)
        expect(metadata_field_box).to have_content(validation_error)
        expect(title_input.value).to eq(valid_text_at_limit)

        title_input.set(invalid_text_over_limit)
        expect(metadata_field_box).to have_content(validation_error)
        expect(title_input.value).to eq(valid_text_at_limit)
        click_button("cancel_#{title_occurrence_id}")
      end
    end

    # When editing text value, expect save button to modify content
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)

    # When editing text value, expect cancel button to not modify content
    edit_and_cancel_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_2)
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
  end

  it 'contains default values' do

    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    # Assert default values are shown for initial/minimum occurrences
    language_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:id]
    language_occurrence_id = nil
    within ('div#meta-data') do
      within ("div##{language_id}_box") do
        language_occurrence_id = first("div[id^='#{language_id}'].metadata.field_value")[:id]
      end
    end
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], language_id, language_occurrence_id, MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:default_value])

    

    # Assert default values are shown for additional occurrences
    description_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:id]
    add_occurrence(description_id)

    within ('div#meta-data') do
      within ("div##{description_id}_box") do
        occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
        occurrences.each do |occurrence|
          within (occurrence) do
            expect(find('div.field_value').text).to eq(MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:default_value])
          end
        end
      end
    end
  end

  it 'saves and loads text field values' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    title_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id]
    title_occurrence_id = get_first_occurrence_id(MetadataHelper::CATEGORIES[:collection_info][:id], title_id)

    # After saving text field value, expect saved value to be displayed when reloading the page
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
    visit_owncloud
    visit_collections
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)

    # After saving a blank text field value, expect blank value to be loaded when reloading the page
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::BLANK_TEXT)
    visit_owncloud
    visit_collections
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::BLANK_TEXT)

    # After saving text, expect value to be loaded after logging out and back in
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
    log_out
    log_in
    visit_collections
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)

    # After creating a new crate, expect crate specific text field values to be loaded
    new_crate('test')
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    new_crate_title_occurrence_id = get_first_occurrence_id(MetadataHelper::CATEGORIES[:collection_info][:id], title_id)
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, new_crate_title_occurrence_id, MetadataHelper::BLANK_TEXT)
    edit_and_save_field(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, new_crate_title_occurrence_id, MetadataHelper::SAMPLE_TEXT_2)
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, new_crate_title_occurrence_id, MetadataHelper::SAMPLE_TEXT_2)
    select_crate(TestHelper::DEFAULT_COLLECTION_NAME)
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    expect_field_content(MetadataHelper::CATEGORIES[:collection_info][:id], title_id, title_occurrence_id, MetadataHelper::SAMPLE_TEXT_1)
  end

  it 'displays metadata field tooltips' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    within ('div#meta-data') do
      within ('div#'+MetadataHelper::CATEGORIES[:collection_info][:id]) do
        within ('div#'+MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id]+'_box') do
          expect(find('h6.field_title')['title']).to eq MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:tooltip]
        end
      end
    end
  end

  it 'renders date fields' do
    open_category_panel(MetadataHelper::CATEGORIES[:dates][:id], MetadataHelper::CATEGORIES[:dates][:name])
    current_date_id = MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:id]
    current_date_occurrence_id = get_first_occurrence_id(MetadataHelper::CATEGORIES[:dates][:id], current_date_id)
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:dates][:id]}") do
        # Expect to see Current Date field in Dates metadata category
        assert_selector("div##{current_date_id}_box")
        metadata_field_box = find("div##{current_date_id}_box")
        expect(metadata_field_box).to have_content(MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:name].upcase)

        # Assert tool-tip of field title matches the field tooltip in the schema
        expect(metadata_field_box.first('.field_title')['title']).to eq(MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:tooltip])

        # Expect to see the 'today' default date value converted to the current date
        expect(metadata_field_box).to have_content(Time.now.strftime("%Y-%m-%d"))

        # When editing date, expect to see current date
        within ("div##{current_date_id}_box") do
          click_button("edit_#{current_date_occurrence_id}")
          assert_selector('div#date_picker_button')
          within ('div#date_picker_button') do
            # Assert the icon of the date picker button is as expected
            assert_selector('span.add-on')
            date_picker = find("i#datepicker_#{current_date_occurrence_id}")
            expect(date_picker[:class]).to eq('fa fa-calendar datetime_icon icon-calendar')

            # Assert the tooltip of the date picker button specifies the max date
            date = Date.strptime(MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:max_date], '%Y-%m-%d')
            formatted_date = "#{date.day.ordinalize} " + date.strftime('%B %Y')
            expect(date_picker[:title]).to eq("The date must be less than or equal to #{formatted_date}")

            # Assert the text box is disabled
            text_box = find("input#input_#{current_date_occurrence_id}")
            expect([true, 'true', 'disabled']).to include(text_box[:disabled])
          end
          click_button("cancel_#{current_date_occurrence_id}")
        end
      end
    end

    # Assert the date picker can be used to select a date
    edit_and_save_active_date(MetadataHelper::CATEGORIES[:dates][:id], MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:id], current_date_occurrence_id)
    expect(find('#meta-data').find("##{MetadataHelper::CATEGORIES[:dates][:id]}").find("div##{current_date_id}_box")).to have_content(Time.now.strftime("%Y-%m-%d"))
  end

  it 'allows date fields to be cleared' do
    open_category_panel(MetadataHelper::CATEGORIES[:dates][:id], MetadataHelper::CATEGORIES[:dates][:name])
    current_date_id = MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:id]
    current_date_occurrence_id = get_first_occurrence_id(MetadataHelper::CATEGORIES[:dates][:id], current_date_id)
    metadata_field_box = find("div##{current_date_id}_box")
    expect(metadata_field_box).to have_content(Time.now.strftime("%Y-%m-%d"))
    find("#edit_#{current_date_occurrence_id}").click
    clear_date_selector = "#clear_#{current_date_occurrence_id}"
    assert_selector(clear_date_selector)
    find(clear_date_selector).click
    expect(metadata_field_box).to have_content('')
  end

  it 'renders the add and remove occurrence buttons when appropriate' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:collection_info][:id]}") do

        # Expect to not see the add occurrence button when the field maxOccurrences is 1
        title_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id]
        within ("div##{title_id}_box") do
          # Assert add occurrence button is not shown
          assert_no_selector("button#add_occurrence_#{title_id}")

          # Assert remove occurrence button is not shown for minimum occurrences
          within ('div.field_body') do
            occurrence = find("div.field_occurrence[id^='occurrence_#{title_id}']")
            within (occurrence) do
              assert_no_selector("button[id^='remove_occurrence_#{title_id}']")
            end
          end
        end

        # Expect to not see the add occurrence button when the field maxOccurrences is equal to the minOccurrences
        language_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:id]
        within ("div##{language_id}_box") do
          # Assert add occurrence button is not shown
          assert_no_selector("button#add_occurrence_#{language_id}")

          # Assert remove occurrence button is not shown for minimum occurrences
          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{language_id}']")
            occurrences.each do |occurrence|
              within (occurrence) do
                assert_no_selector("button[id^='remove_occurrence_#{language_id}']")
              end
            end
          end
        end

        # Expect to see the add occurrences button when the field maxOccurrence is greater than the minOccurrences
        description_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:id]

        within ("div##{description_id}_box") do
          # Assert button is shown and tooltip is correct
          assert_selector("button#add_occurrence_#{description_id}")
          add_occurrence_button = find("button#add_occurrence_#{description_id}")
          expect(add_occurrence_button[:title]).to eq('Add occurrence')

          # Assert additional occurrences have the remove button shown
          add_occurrence(description_id)

          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
            within (occurrences[1]) do
              assert_selector("button[id^='remove_occurrence_#{description_id}']")
              remove_occurrence_button = find("button[id^='remove_occurrence_#{description_id}']")
              expect(remove_occurrence_button[:title]).to eq('Remove occurrence')
            end
          end

          # Assert add occurrence button is not shown when the total number of minimum and additional occurrences reaches the occurrence limit
          assert_no_selector("button#add_occurrence_#{description_id}")

          # Assert clicking the remove occurrence button removes the corresponding field occurrence
          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
            expect(occurrences.length).to eq(2)
            within (occurrences[1]) do
              find("button[id^='remove_occurrence_#{description_id}']").click
            end
            occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
            expect(occurrences.length).to eq(1)
          end

          # Assert when an occurrence is removed the add occurrence button is re-shown
          assert_selector("button#add_occurrence_#{description_id}")
        end
      end
    end
  end

  it 'sets placeholder and character limit for occurrences' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:collection_info][:id]}") do
        language_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:id]
        within ("div##{language_id}_box") do
          # Assert character limit and placeholder is set for each occurrence
          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{language_id}']")
            occurrences.each do |occurrence|
              within (occurrence) do
                find('button[placeholder="Edit"]').click
                language_input = find("textarea[id^='input_#{language_id}']")
                expect(language_input[:maxlength]).to eq(MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:char_limit].to_s)
                expect(language_input[:placeholder]).to eq(MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:placeholder])
                click_button('Cancel')
              end
            end
          end
        end
      end
    end
  end

  it 'allows setting of occurrence values' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:collection_info][:id]}") do
        language_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:id]
        within ("div##{language_id}_box") do
          # Assert occurrences can have different values
          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{language_id}']")
            occurrences.each do |occurrence|
              occurrence_id = occurrence[:id].sub(/occurrence_/, '')
              random_text = rand(36**10).to_s(36)
              find("button[id='edit_#{occurrence_id}']").click
              find("textarea[id='input_#{occurrence_id}']").set(random_text)
              scroll_into_view("occurrence_#{occurrence_id}")
              find("input[id='save_#{occurrence_id}']").click
              sleep(1)
              expect(find("div##{occurrence_id}").text).to eq(random_text)
            end
          end
        end
      end
    end
  end

  it 'loads saved occurrence values when reloading the page' do
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    description_id = MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:id]
    # Set value of initial and additional description occurrences
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:collection_info][:id]}") do
        within ("div##{description_id}_box") do
          find("button#add_occurrence_#{description_id}").click
          occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
          within (occurrences[0]) do
            find('button[placeholder="Edit"]').click
            language_input = find("textarea[id^='input_#{description_id}']")
            language_input.set(MetadataHelper::SAMPLE_TEXT_1)
            click_button('Save')
          end
          within (occurrences[1]) do
            sleep 1
            find('button[placeholder="Edit"]').click
            language_input = find("textarea[id^='input_#{description_id}']")
            language_input.set(MetadataHelper::SAMPLE_TEXT_2)
            click_button('Save')
          end
        end
      end
    end
    # Reload the page
    visit_owncloud
    visit_collections
    open_category_panel(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name])
    # Check the values of the initial language occurrences, the initial description occurrence and the additional description occurrence
    within ('div#meta-data') do
      within ("div##{MetadataHelper::CATEGORIES[:collection_info][:id]}") do
        within ("div##{description_id}_box") do
          within ('div.field_body') do
            occurrences = all("div.field_occurrence[id^='occurrence_#{description_id}']")
            within (occurrences[0]) do
              expect(find("div[id^='#{description_id}']").text).to eq(MetadataHelper::SAMPLE_TEXT_1)
            end
            within (occurrences[1]) do
              expect(find("div[id^='#{description_id}']").text).to eq(MetadataHelper::SAMPLE_TEXT_2)
            end
          end
        end
      end
    end
  end

  it 'displays which fields are mandatory' do
    # Assert some legend is displayed that conveys the meaning of the mandatory field indicator (*)
    legend = find('.container-metadata').find('#legend')
    expect(legend.text).to eq('* indicates required field')
    expect(legend.find('.required').text).to eq('*')

    # Assert mandatory text fields display the mandatory indicator
    assert_mandatory_indicator(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name],
                               MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:id], MetadataHelper::CATEGORIES[:collection_info][:fields][:title][:name])
    assert_mandatory_indicator(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name],
                               MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:id], MetadataHelper::CATEGORIES[:collection_info][:fields][:description][:name])
    assert_mandatory_indicator(MetadataHelper::CATEGORIES[:collection_info][:id], MetadataHelper::CATEGORIES[:collection_info][:name],
                               MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:id], MetadataHelper::CATEGORIES[:collection_info][:fields][:language][:name])

    # Assert mandatory date fields display the mandatory indicator
    assert_mandatory_indicator(MetadataHelper::CATEGORIES[:dates][:id], MetadataHelper::CATEGORIES[:dates][:name],
                               MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:id], MetadataHelper::CATEGORIES[:dates][:fields][:current_date][:name])

  end

  it 'renders metadata groups' do
    open_category_panel(MetadataHelper::CATEGORIES[:data_creators][:id], MetadataHelper::CATEGORIES[:data_creators][:name])
    within('div#meta-data') do
      within("##{MetadataHelper::CATEGORIES[:data_creators][:id]}") do
        # Expect to see the metadata group with an appropriate css class
        group_id = MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:id]
        group_name = MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:name]
        group_tooltip = MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:tooltip]
        assert_selector("div.metadata_group##{group_id}_box")
        within ("div.metadata_group##{group_id}_box") do
          # Expect to see the group title and tooltip
          title = find('.group_title')
          expect(title.text).to eq(group_name.upcase)
          expect(title['title']).to eq(group_tooltip)

          # Expect to see the metadata fields within the group occurrence
          within('.group_occurrence') do
            MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields].each do |field_id, field|
              assert_selector("div.metadata_field##{field[:id]}_box")
              within("div.metadata_field##{field[:id]}_box") do
                # Expect to see field title and mandatory indicator if field is required
                title = find('.field_title')
                if field[:mandatory]
                  expect(title.text).to eq(field[:name].upcase + ' *')
                  expect(title.find('.required').text).to eq('*')
                else
                  expect(title.text).to eq(field[:name].upcase)
                end
                # Expect to see title tooltip if defined
                if field.key?('tooltip')
                  expect(title['title']).to eq(field[:tooltip])
                end
                if field[:minOccurs] == field[:maxOccurs]
                  # Expect to not see the remove occurrence button for each field occurrence if the field has equal minimum and maximum occurrences
                  all("div[id^='occurrence_#{field[:id]}'].field_occurrence").each do |field_occurrence|
                    assert_no_selector("button#remove_#{field_occurrence[:id]}")
                  end
                elsif field[:minOccurs] < field[:maxOccurs]
                  # Expect to see the add occurrence button if the field has more maximum than minimum occurrences
                  assert_selector("button#add_occurrence_#{field[:id]}")
                  add_occurrence_button = find("button#add_occurrence_#{field[:id]}")
                  expect(add_occurrence_button[:title]).to eq('Add occurrence')
                  # Expect to be able to add an additional field occurrence
                  expect(all("div[id^='occurrence_#{field[:id]}'].field_occurrence").size).to eq(field[:minOccurs])
                  # add_occurrence_button.click
                  add_occurrence(field[:id])
                  field_occurrences = all("div[id^='occurrence_#{field[:id]}'].field_occurrence")
                  expect(field_occurrences.size).to eq(field[:minOccurs] + 1)
                  # Expect to see the the remove occurrence button for additional field occurrences and
                  #  also expect to not see the remove occurrence button for initial/minimum field occurrences
                  field_occurrences.each_with_index do |field_occurrence, index|
                    if index < field[:minOccurs]
                      assert_no_selector("button#remove_#{field_occurrence[:id]}")
                    else
                      assert_selector("button#remove_#{field_occurrence[:id]}")
                    end
                  end
                end
              end
            end
          end

          # Expect to see only one group occurrence initially
          num_group_occurrences = all("div[id^='occurrence_#{group_id}'].group_occurrence").size
          expect(num_group_occurrences).to eq(1)

          # Expect to see two group occurrences when the add group occurrence button is clicked
          find('.group_title').find("button#add_occurrence_#{group_id}").click
          num_group_occurrences = all("div[id^='occurrence_#{group_id}'].group_occurrence").size
          expect(num_group_occurrences).to eq(2)

          # Expect to see only one group occurrence when the second group occurrence is removed
          new_group_occurrence_id = all("div[id^='occurrence_#{group_id}'].group_occurrence").collect { |occurrence|
            occurrence[:id].split('occurrence_')[1]
          }[1]
          scroll_into_view("occurrence_#{new_group_occurrence_id}")
          find(".group_occurrence#occurrence_#{new_group_occurrence_id}").find("button#remove_occurrence_#{new_group_occurrence_id}").click
          num_group_occurrences = all("div[id^='occurrence_#{group_id}'].group_occurrence").size
          expect(num_group_occurrences).to eq(1)

        end
      end
    end
    # Expect to be able to edit and save the text and date fields contained within a group
    group = find("div.metadata_group##{MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:id]}_box")
    phone_number_occurrence = get_first_occurrence_id(MetadataHelper::CATEGORIES[:data_creators][:id],
                                                      MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:phone_number][:id])
    scroll_into_view(phone_number_occurrence)
    edit_and_save_field(MetadataHelper::CATEGORIES[:data_creators][:id],
                        MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:phone_number][:id],
                        phone_number_occurrence, '123456789')
    sleep(1)
    expect(group.find("##{phone_number_occurrence}").text).to eq('123456789')
    email_occurrence = get_first_occurrence_id(MetadataHelper::CATEGORIES[:data_creators][:id],
                                               MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:email][:id])
    scroll_into_view(email_occurrence)
    edit_and_save_field(MetadataHelper::CATEGORIES[:data_creators][:id],
                        MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:email][:id],
                        email_occurrence, 'test@place.org')
    sleep(1)
    expect(group.find("##{email_occurrence}").text).to eq('test@place.org')
    date_of_birth_occurrence = get_first_occurrence_id(MetadataHelper::CATEGORIES[:data_creators][:id],
                                                       MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:date_of_birth][:id])
    scroll_into_view(date_of_birth_occurrence)
    edit_and_save_active_date(MetadataHelper::CATEGORIES[:data_creators][:id],
                              MetadataHelper::CATEGORIES[:data_creators][:groups][:creators][:fields][:date_of_birth][:id],
                              date_of_birth_occurrence)
    sleep(1)
    expect(group.find("##{date_of_birth_occurrence}").text).to eq(Time.new.strftime('%Y-%m-%d'))
  end
end
