<?php

require_once( $CFG->dirroot . '/calendar/lib.php');
$bin_path = $CFG->dirroot . '/local/jwc2ical';

function split_date( $day)
{
	$cur = explode( "-", $day);
	$cur[1] = $cur[1] < 6 ? "春" : "秋";
	return "$cur[0]年$cur[1]";
}

/* Make sure your code and the get_record return
 * data in the same structrue.
 */
function fetch_new_class( $class, &$ret)
{
	echo "fetching class $class\n";
	global $bin_path;
	chdir( $bin_path);
	exec( "./class $class", $res, $ret);
	if ( $ret !== 0)
	{
		return false;
	}

	$all_class = array();
	$all_exam = array();
	$all = array();
	$class_parts = array();
	$exam_parts = array();
	$class_flag = false;
	$exam_flag = false;
	foreach ( $res as $line)
	{
		$parts = explode( ' ', $line);
		if ( strstr( $line, 'Class'))
		{
			$name = 'class';
		}
		else
		{
			$name = 'exam';
		}
		if ( !${$name.'_flag'})
		{
			${$name.'_parts'} = explode( ' ', $line);
			${$name.'_flag'} = true;
		}
		else
		{
			$$name = new stdClass();
			for ( $i = 1; $i < count( ${$name.'_parts'}); $i++)
			{
				${$name}->${$name.'_parts'}[$i] = $parts[$i];
			}
			${'all_' . $name}[] = $$name;
			$all[] = $$name;
		}
	}

	$ret = $all;
	return true;
}

function fetch_class( $class, $dtstart, &$ret)
{
	global $DB;
	echo "Looking for class $class.\n";
	$ret = $DB->get_records( 'jwc_schedule', array( 'class' => $class));

	if ( count( $ret) > 0)
	{
		if ( reset( $ret)->name == 'failed')
		{
			return false;
		}
	}
	else
	{
		$ret = array();
		$exist = fetch_new_class( $class, $virgin);
		if ( $exist)
		{
			foreach( $virgin as $data)
			{
				$days = $data->week * 7 - 7 + $data->date;

				$date = new DateTime( $dtstart);

				$s_time = explode( ":", $data->s_time);
				++$s_time[1]; --$s_time[1]; // To prevent 00 appears.
				$date->add( new DateInterval( "P${days}DT$s_time[0]H$s_time[1]M"));

				$end = new DateTime( $dtstart);

				$t_time = explode( ":", $data->t_time);
				++$t_time[1]; --$t_time[1];
				$end->add( new DateInterval( "P${days}DT$t_time[0]H$t_time[1]M"));

				$is_exam = isset( $data->teacher) && $data->teacher !== '' ? false : true;
				$record = array(
					'class' => $class,
					'name' => $data->name,
					'teacher' => $is_exam ? "" : $data->teacher,
					'location' => $data->location,
					'repeats' => ( !$is_exam && $data->repeats ? $data->repeats : 1),
					'time' => $date->getTimestamp(),
//					'length' => $date->diff( $end)->format("%s")
					'length' => $is_exam ? 120*60 : 105*60
					// I do not found any php method to calculate that.
				);

				$DB->insert_record( 'jwc_schedule', $record);
				$ret[] = ( object) $record;
			}
		}
		else
		{
			$record = array(
				'class' => $class,
				'name' => "failed",
				'teacher' => NULL,
				'location' => 0,
				'repeats' => 0,
				'time' => 0,
				'length' => 0
			);
			$DB->insert_record( 'jwc_schedule', $record);

			return false;
		}
	}
	return true;
}

function jwc2ical_insert_events()
{
	echo "updating\n";
	global $DB;
	$dtstart = get_config( 'local_jwc2ical', 'jwc_version');

	$now = time();
	set_config( 'timestamp', $now, 'local_jwc2ical');
	$errors = 0;
	$stus =  $DB->get_records_select( 'user', 'auth = \'cas\' AND address != \'1\'');
	echo "Get all students done.\n";
	foreach ( $stus as $stu)
	{
		$class = $stu->department;
		$flag = fetch_class( $class, $dtstart, $events);
		if ( $flag)
		{
			foreach ( $events as $event)
			{
				$entry = array (
					"eventtype" 	=>	'user', #fixed value
					"id" 	 	=>	0, #fixed value
					"courseid" 	=>	0, #fixed value
					"modulename" 	=>	'', #fixed value
					"instance" 	=>	0, #fixed value
					"action" 	=>	0, #fixed value
					"duration" 	=>	2, #fixed value
					"repeat" 	=>	1, #fixed value
					"timemodified" 	=> 	$now,

					"userid" 	=>	$stu->id, # Get from user information
					"name" 		=>	$event->name,
					"description" 	=>	"$event->location  $event->teacher",
					"timestart" 	=>	$event->time,    #time stamp
					"repeats" 	=>	$event->repeats ? $event->repeats : 1,  #repeat times
					"timeduration" 	=>	$event->length, #last, seconds
					"uuid" 		=>	'local_jwc2ical' # Stamp
				);

				$cal = new calendar_event();
				$cal->update( $entry, false);
			}
			echo "$stu->idnumber id $stu->id done\n";
		}
		else
		{
			error_log( "Processing student $stu->idnumber failed, class is $class");
			++$errors;
		}
	}
	if ( $errors !== 0)
	{
		error_log( "$errors errors occured while inserting events!");
	}

	set_config( 'current_version', $dtstart, 'local_jwc2ical');
}

function jwc2ical_delete_events()
{
	echo "rolling back\n";
	global $DB;
	$DB->delete_records( 'event', array( 'uuid' => 'local_jwc2ical'));
	clear_jwc_table();
	set_config( 'current_version', '0-0-0', 'local_jwc2ical');
}

function clear_jwc_table()
{
	global $DB;
	//Delete all thing from db.
	$DB->delete_records( 'jwc_schedule', array());
}

function refresh_date()
{
	global $bin_path;
	chdir( $bin_path);
	$jwc_day = get_config( 'local_jwc2ical', 'jwc_version');
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
		set_config( 'jwc_version', $jwc_day, 'local_jwc2ical');
	}
	return true;
}
