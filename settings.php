<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('root', new admin_externalpage('jwc2ical',
            'local_jwc2ical',
            new moodle_url('/local/jwc2ical/index.php')));
}
