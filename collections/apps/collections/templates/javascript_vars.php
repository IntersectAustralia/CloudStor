<?php
/**
 * [javascript_vars.php]
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
?>
<!-- workaround to make var available to javascript -->
<div id="hidden_vars" hidden="hidden">
    <span id="publish_warning_mb"><?php p(\OCA\collections\Config::$PUBLISH_WARNING_MB) ?></span>
    <span id="max_zip_mb"><?php p(\OCA\collections\Config::$PUBLISH_MAX_MB) ?></span>
</div>
