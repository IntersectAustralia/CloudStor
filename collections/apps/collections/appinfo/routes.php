<?php
/**
 * [routes.php]
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

return ['routes' => [
    ['name' => 'crate#index', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'crate#user_guide', 'url' => '/user_guide', 'verb' => 'GET'],
    ['name' => 'crate#update_crate', 'url' => '/crate/update', 'verb' => 'POST'],
    ['name' => 'crate#create_crate', 'url' => '/crate/create', 'verb' => 'POST'],
    ['name' => 'crate#get_crate', 'url' => '/crate/get_crate', 'verb' => 'GET'],
    ['name' => 'crate#select_crate', 'url' => '/crate/select_crate', 'verb' => 'POST'],
    ['name' => 'crate#move_node', 'url' => '/crate/move_node', 'verb' => 'POST'],
    ['name' => 'crate#rename_node', 'url' => '/crate/rename_node', 'verb' => 'POST'],
    ['name' => 'crate#delete_node', 'url' => '/crate/delete_node', 'verb' => 'POST'],
    ['name' => 'crate#delete_crate', 'url' => '/crate/delete', 'verb' => 'POST'],
    ['name' => 'crate#add', 'url' => '/crate/add', 'verb' => 'POST'],
    ['name' => 'crate#add_folder', 'url' => '/crate/addFolder', 'verb' => 'POST'],
    ['name' => 'crate#remove_all_files', 'url' => '/crate/remove_all_files', 'verb' => 'POST'],
    ['name' => 'crate#publish_crate', 'url' => '/crate/publish', 'verb' => 'POST'],
    ['name' => 'crate#get_packaging_jobs', 'url' => 'crate/packaging_jobs', 'verb' => 'GET'],
    ['name' => 'crate#check_crate', 'url' => '/crate/check', 'verb' => 'GET'],
    ['name' => 'crate#metadata_schema', 'url' => '/crate/metadata_schema', 'verb' => 'GET'],
    ['name' => 'crate#export_metadata', 'url' => '/crate/export_metadata', 'verb' => 'POST'],
    ['name' => 'crate#save_metadata', 'url' => '/crate/save_metadata', 'verb' => 'POST']
]];
