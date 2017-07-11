<?php
/**
 * [packaging_email.php]
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
<html>
<head>
    <title><?php p($_['collection_name']) ?></title>
</head>
<body>
<article>
    <div>
        <?php if($_['package_succeeded']) { ?>
            <p> Your Collection, <?php p($_['collection_name']) ?>, has been packaged to your Files:
                <a href="<?php p($_['collection_url']) ?>"><?php p($_['collection_destination']) ?></a>
            </p>
            Your metadata has also been included in the packaged collection as an XML file ready for you to share and download.
        <?php } else { ?>
            <p> Something went wrong while attempting to package your Collection, <?php p($_['collection_name']) ?>,
                and it was unable to be packaged:
            </p>
        <?php
            p($_['failure_message']);
        } ?>
    </div>
</article>
</body>
</html>