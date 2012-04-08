<?php

require_once( $CFG->dirroot . '/calendar/lib.php');
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');

function jwc2ical_update_array( $stus)
{
	global $DB;
	$dtstart = get_config( PNAME, 'jwc_version');

	$now = time();

	$errors = 0;
	foreach ( $stus as $stu)
	{
		$flag = insert_events_single( $stu, $dtstart, $now);
		if ( $flag)
		{
			echo "$stu->idnumber id $stu->id done\n";
		}
		else
		{
			error_log( "Processing student $stu->idnumber id $stu->id failed, class is $stu->department");
			++$errors;
		}
	}
	if ( $errors !== 0)
	{
		error_log( "$errors errors occured while inserting events!");
	}

	set_config( 'timestamp', $now, PNAME);
	return $errors;
}

function jwc2ical_update_new()
{
	global $DB;

	echo "Updating new\n";
	$timestamp = get_config( PNAME, 'timestamp'); 
	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\' AND' . ' timecreated >= ' . $timestamp);
	echo "Get " . count( $stus) . " new students.\n";

	return jwc2ical_update_array( $stus);
}
