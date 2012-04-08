<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once( $CFG->dirroot . '/local/jwc2ical/lib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

$action = $_SERVER['argc'] !== 1 ? $_SERVER["argv"][1] : '';
if ( $action == '-i')
{
	if ( !refresh_date())
	{
		echo "Refresh failed, make sure you have network connection.\n";
		echo "Please rerun this scripte again later.\n";
	}
	else
	{
		jwc2ical_insert_events();
	}
}
elseif ( $action == '-d')
{
	jwc2ical_delete_events();
}
elseif ( $action == '-f')
{
	jwc2ical_fix_corrupt( $_SERVER['argv'][2]);
}
elseif ( $action == '-n')
{
	jwc2ical_update_new();
}
else
{
	echo "Run with -i to insert, -d to delete events, -f idnumber to fix corrupts.\n";
}
