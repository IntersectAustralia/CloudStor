#
 # [spec_helper.rb]
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
require 'rspec'
require 'capybara'
require 'capybara/dsl'
require 'capybara/rspec'
require 'capybara/poltergeist'
require 'capybara-screenshot/rspec'
require 'helpers/test_helper'
require 'helpers/metadata_helper'

RSpec.configure do |config|
  config.include Capybara::DSL
  config.include TestHelper
  config.include MetadataHelper
end

if ENV['BUILD'] == 'headless'
  # Setup for poltergeist
  Capybara.register_driver :poltergeist do |app|
    Capybara::Poltergeist::Driver.new(app, {:js_errors => false})
  end

  Capybara.javascript_driver = :poltergeist
  Capybara.default_driver = :poltergeist
else
  Capybara.javascript_driver = :selenium
  Capybara.default_driver = :selenium
end

Capybara.app_host = ENV['URL'] ? ENV['URL'] : 'http://192.168.99.100/'
Capybara.asset_host = ENV['URL'] ? ENV['URL'] : 'http://192.168.99.100/'
Capybara.run_server = false
Capybara.automatic_reload = true

Capybara.default_max_wait_time = 5

Capybara::Screenshot.prune_strategy = :keep_last_run
