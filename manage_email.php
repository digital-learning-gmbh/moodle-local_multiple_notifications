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
 * Enrolment expiry notification.
 *
 * @package    local_multiple_notifications
 * @copyright 2020 Hernan Arregoces <harregoces@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/multiple_notifications/email_form.php');

defined('MOODLE_INTERNAL') || die();
require_login();

$url = new moodle_url('/local/multiple_notifications/manage_email.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', null, PARAM_RAW);

if ($action == 'delete' && $id) {
    $DB->delete_records('local_multinotif_email', array('id' => $id));
    redirect(new moodle_url('/admin/settings.php', array('section' => 'local_multiple_notifications')));
}

$draftideditor = file_get_submitted_draft_itemid('message');

$mform = new email_form(null, array('id' => $id));
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', array('section' => 'local_multiple_notifications')));
} else if ($data = $mform->get_data()) {
    $data->message = $data->message['text'];
    if (!$id) {
        $DB->insert_record('local_multinotif_email', $data);
    } else {
        $DB->update_record('local_multinotif_email', $data);
    }
    redirect(new moodle_url('/admin/settings.php', array('section' => 'local_multiple_notifications')));
} else if ($id) {
    $data = $DB->get_record('local_multinotif_email', array('id' => $id));
    $data->message = array('text' => $data->message, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);
    $mform->set_data($data);
}

$title = get_string('add_new_email', 'local_multiple_notifications');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(
    get_string('back_to_settings', 'local_multiple_notifications'),
    new moodle_url('/admin/settings.php', array('section' => 'local_multiple_notifications'))
);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
