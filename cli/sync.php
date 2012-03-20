<?php

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');

// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;


$action = $_SERVER["argv"][1];
if ( $action == '-i')
{
	jwc2ical_insert_events();
}
elseif ( $action == '-d')
{
	jwc2ical_delete_events();
}
else
{
	echo "Run with -i to insert, -d to delete events.";
}
