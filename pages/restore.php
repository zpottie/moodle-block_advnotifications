<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Created by LearningWorks Ltd
 * Date: 4/07/16
 * Time: 1:02 PM
 */

// Load in Moodle config.
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
// Load in Moodle's Tablelib lib.
require_once($CFG->dirroot . '/lib/tablelib.php');
// Call in block's table file.
require_once($CFG->dirroot . '/blocks/advanced_notifications/classes/restore_table.php');

global $CFG;

// PARAMS.
$params = array();

// Determines which notification the user wishes to restore.
$restore = optional_param('restore', null, PARAM_INT);

// Determines which notification the user wishes to delete.
$delete = optional_param('delete', null, PARAM_INT);

// Determines whether or not to download the table.
$download = optional_param('download', null, PARAM_ALPHA);

if ( !!$download ) {
    $params['download'] = 1;
}

global $DB, $USER, $PAGE;

if ( !!$delete ) {
    // If wanting to delete a notification, delete from DB immediately before the table is rendered.

    $DB->delete_records('block_advanced_notifications', array('id' => $delete));
}

$context = context_system::instance();
$url = new moodle_url($CFG->wwwroot . '/blocks/advanced_notifications/pages/restore.php');

// Set PAGE variables.
$PAGE->set_context($context);
$PAGE->set_url($url, $params);

// Force the user to login/create an account to access this page.
require_login();

if ( !has_capability('block/advanced_notifications:managenotifications', $context) ) {
    require_capability('block/advanced_notifications:managenotifications', $context);
}

// Set the layout - allows for customisation.
// Moodle automatically falls back to the "standard" layout if this is not in the theme's config.php "layouts" array.
$PAGE->set_pagelayout('adv_notifications');

$table = new advanced_notifications_restore_table('advanced-notifications-list-restore');
$table->is_downloading($download, 'advanced-notifications-list-restore', 'Advanced Notifications List Restore');

if (!$table->is_downloading()) {
    // Only print headers if not asked to download data.
    // Print the page header.
    $PAGE->set_title(get_string('advanced_notifications_restore_table_title', 'block_advanced_notifications'));
    $PAGE->set_heading(get_string('advanced_notifications_restore_table_heading', 'block_advanced_notifications'));
    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/advanced_notifications/javascript/custom.js'));

    echo $OUTPUT->header();

    printf('<h1 class="page__title">%s</h1>',
            get_string('advanced_notifications_restore_table_title', 'block_advanced_notifications')
    );
}

// Configure the table.
$table->define_baseurl($url, $params);

$table->set_attribute('class', 'admin_table general_table notifications_restore_table');
$table->collapsible(false);

$table->is_downloadable(true);
$table->show_download_buttons_at(array(TABLE_P_BOTTOM));

$table->set_sql('*', "{block_advanced_notifications}", "deleted = 1");

// Print warning about permanently deleting notifications.
echo '<div class="restore_notification-block-wrapper">
        <div class="alert alert-danger">
            ' . get_string('advanced_notifications_restore_table_warning', 'block_advanced_notifications') . '
        </div>
      </div>';

// Add navigation controls before the table.
echo '<div id="advanced_notifications_manage">
      <a class="btn instance" href="' . $CFG->wwwroot .
        '/blocks/advanced_notifications/pages/notifications.php">Manage</a>&nbsp;&nbsp;
      <a class="btn instance" href="' . $CFG->wwwroot .
        '/admin/settings.php?section=blocksettingadvanced_notifications">Settings</a><br><br></div>';

// Add a wrapper with an id, which makes reloading the table easier (when using ajax).
echo '<div id="advanced_notifications_restore_table_wrapper">';
$table->out(20, true);
echo '</div>';

if (!$table->is_downloading()) {
    echo $OUTPUT->footer();
}
