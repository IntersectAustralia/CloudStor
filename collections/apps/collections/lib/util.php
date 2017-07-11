<?php
/**
 * [util.php]
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

namespace OCA\collections\lib;

use OCP\Template;

class Util {

    public static function renderTemplate($template, $params) {
        $template = new Template('collections', $template);
        foreach($params as $key => $value) {
            $template->assign($key, $value);
        }
        return $template->fetchPage();
    }


    public static function getTimestamp($format="YmdHis") {
        date_default_timezone_set('Australia/Sydney');
        $timestamp = date($format);
        return $timestamp;
    }

    public static function getConfig() {
        $configFile = Util::joinPaths(Util::getDataPath(),'cr8it_config.json');
        $config = NULL; // Allows tests to work
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        }
        return $config;
    }

    public static function getDataPath() {
        return \OC::$server->getConfig()->getSystemValue('datadirectory', self::joinPaths(\OC::$SERVERROOT, 'data'));
    }

    public static function getUserPath() {
        $userId = \OCP\User::getUser();
        $config = Util::getConfig();
        return Util::joinPaths($config['crate path'], $userId);
    }

    public static function getTempPath() {
        return Util::joinPaths(ini_get('upload_tmp_dir'), 'cr8it', 'crates');
    }

    public static function joinPaths() {
        $paths = array();
        foreach(func_get_args() as $arg) {
            if($arg !== '') {
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', join('/', $paths));
    }

    public static function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}