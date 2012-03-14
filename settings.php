<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page
    $ADMIN->add('root', new admin_externalpage('jwc2ical',
            'local_jwc2ical',
            new moodle_url('/local/jwc2ical/index.php')));
}

set_config( 'current_version', '0-0-0', 'local_jwc2ical');
set_config( 'jwc_version', '2012-2-27', 'local_jwc2ical');
