<?php
/**
 * [app.php]
 * Collections - Research data packaging for the rest of us
 * Copyright (C) 2017 Intersect Australia Ltd (https://intersect.org.au)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\collections;

$dir = dirname(dirname(__FILE__));
require $dir . '/vendor/autoload.php';


use OCA\collections\lib\OCCommonAPI;

\OCP\App::addNavigationEntry(array(
    // the string under which your app will be referenced in owncloud
    'id' => 'collections',

    // sorting weight for the navigation. The higher the number,
    // the higher will it be listed in the navigation
    'order' => 250,

    // the route that will be shown on startup
    'href' => \OCP\Util::linkToRoute('collections.crate.index'),

    // the icon that will be shown in the navigation
    "icon" => \OCP\Util::imagePath('collections', 'milk-crate-grey.png'),

    // the title of your application. This will be used in the
    // navigation or on the settings page of your app
    'name' => OCCommonAPI::l10nGet('collections', 'Collections')
    
    )
);


//load the required files
\OCP\Util::addScript('collections', 'jquery.jeditable');
\OCP\Util::addScript('collections', 'tree.jquery');
\OCP\Util::addScript('collections', 'filesize.min');
\OCP\Util::addScript('collections', 'loader');
\OCP\Util::addScript('collections', 'initializers');
\OCP\Util::addScript('collections', 'includeme');
\OCP\Util::addStyle('collections', 'collection');

// Font awesome
\OCP\Util::addStyle('collections', 'font-awesome.min');
\OCP\Util::addStyle('collections', 'font-awesome.overrides');

// Bootstrap
\OCP\Util::addStyle('collections', 'bootstrap');
\OCP\Util::addScript('collections', 'bootstrap.min');
\OCP\Util::addStyle('collections', 'bootstrap.overrides');
\OCP\Util::addStyle('collections', 'jqtree');

// Bootstrap Datetime Picker
\OCP\Util::addScript('collections', 'moment.min');
\OCP\Util::addScript('collections', 'bootstrap-datetimepicker.min');
\OCP\Util::addStyle('collections', 'bootstrap-datetimepicker.min');

// Underscore
\OCP\Util::addScript('collections', 'underscore.min');