<?php
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');

function jwc2ical_update_array( $stus)
{
	global $DB;

	echo "Get ". count( $stus) . " students.\n";

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
	set_config( 'current_version', $dtstart, PNAME);

	return $errors;
}

function jwc2ical_update_new()
{
	global $DB;

	echo "Updating new\n";
	$timestamp = get_config( PNAME, 'timestamp'); 
	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\'' . ' AND timecreated >= ' . $timestamp);

	return jwc2ical_update_array( $stus);
}

function jwc2ical_insert_events()
{
	global $DB;

	echo "Updating\n";
	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\'');

	return jwc2ical_update_array( $stus);
}

function jwc2ical_delete_events()
{
	echo "rolling back\n";
	global $DB;
	$DB->delete_records( 'event', array( 'uuid' => PNAME));
	clear_jwc_table();
	set_config( 'current_version', '0-0-0', PNAME);
}

// From where are we corrupted? idnumber $stu_idnumber
function jwc2ical_fix_corrupt( $stu_idnumber)
{
	echo "Fixing corrupts. If this script corrupts again, you can rerun it.\n";
	global $DB;
	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\'' . " AND idnumber = '$stu_idnumber'");

	if ( count( $stus) != 1)
	{
		echo "Are you sure $stu is the correct idnumber?\n";
		return false;
	}
	else
	{
		reset( $stus);

		$stu = current( $stus);
		if ( !fix_corrupt_single( $stu))
		{
			echo "Failed to fix $stu->id.\n";
			return false;
		}
	}

	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\'' . ' AND id >= ' . current( $stu)->id);

	return jwc_update_array( $stus);
}

function refresh_date()
{
	global $bin_path;
	chdir( $bin_path);
	$jwc_day = get_config( PNAME, 'jwc_version');
	exec( "./date", $res, $ret);
	$res = $res[0];
	if ( $ret !== 0)
	{
		return false;
	}
	elseif ( $res !== $jwc_day)
	{
		$jwc_day = $res;
		echo "refreshed date: $jwc_day\n";
		clear_jwc_table();
		set_config( 'jwc_version', $jwc_day, PNAME);
	}
	return true;
}
