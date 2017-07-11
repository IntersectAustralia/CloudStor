<?php
/**
 * [occommonapi.php]
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

/***
 * Class OCCommonAPI
 * @package OCA\collections\lib
 *
 * This class provides a common interface to the ownCloud API methods
 * which have changed across versions. This allows Collections to be
 * compatible with multiple ownCloud versions.
 */
class OCCommonAPI {

    /***
     * @param $application Name of the application being localised
     * @param $string String to localise
     * @return string A localised translation
     */
    public static function l10nGet($application, $string) {
        if(\OCP\Util::getVersion()[0] >= 9) {
            $translation = \OCP\Util::getL10N($application)->t($string);
        } else {
            $translation = \OC_L10N::get($application)->t($string);
        }
        return $translation;
    }

    /**
     * Gets the email address of the currently logged in user.
     *
     * @return null|string
     */
    public static function getEMailAddress() {
        if(\OCP\Util::getVersion()[0] >= 9) {
            $userId = \OC::$server->getUserSession()->getUser()->getUID();
            $email = \OC::$server->getUserManager()->get($userId)->getEMailAddress();
        } else {
            $email = \OCP\Config::getUserValue(\OCP\User::getUser(), 'settings', 'email', null);
        }
        return $email;
    }


    public static function getUserId() {
        if(\OCP\Util::getVersion()[0] >= 9) {
            $userId = \OC::$server->getUserSession()->getUser()->getUID();
        } else {
            $userId = \OCP\User::getUser();
        }
        return $userId;
    }


    /**
     * Gets folders shared with the supplies user, relative to the data directory.
     *
     * @param $user User to get folders shared with
     * @return array
     */
    public static function getFoldersSharedWith($user) {
        $sharedFolders = [];
        if(\OCP\Util::getVersion()[0] >= 9) {
            $sharedNodes = \OC::$server->getShareManager()->getSharedWith($user, 0);
            foreach($sharedNodes as $sharedNode) {
                if($sharedNode->getNodeType() == 'folder') {
                    array_push($sharedFolders, $sharedNode->getNode()->getPath());
                }
            }
        } else {
            $sharedNodes = \OCP\Share::getItemsSharedWithUser('folder', $user);
            foreach($sharedNodes as $sharedNode) {
                array_push($sharedFolders, Util::joinPaths($sharedNode['uid_owner'], 'files', $sharedNode['file_target']));
            }
        }
        return $sharedFolders;
    }
    
}
