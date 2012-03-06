<?php

#require_once( $CFG->dirroot . '/lib/moodlelib.php');
#
#
$first_day = '2012-2-27';

function fetch_new_class( $class, &$ret)
{
	echo "<p> fetching class $class </p> " ;
	exec( "./class $class", $res, $ret);
	echo "<p> get $ret </p>";
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
//	return array( 'classes' => $all_class, 'exams' => $all_exam);
	$ret = $all;
	return true;
}

function fetch_class( $class, &$ret)
{
	global $DB;
	$ret = $DB->get_records( 'jwc_schedule', array( 'class' => $class));

	if ( count( $ret) > 0)
	{
		if ( reset( $ret)->name == 'failed')
		{
			$ret = array();
			return false;
		}
	}
	else
	{
		$exist = fetch_new_class( $class, $virgin);
		if ( $exist)
		{
			foreach( $virgin as $data)
			{
				$days = $data->week * 7 - 7 + $data->date;


				$date = new DateTime( $first_day);

				$s_time = explode( ":", $data->s_time);
				++$s_time[1]; --$s_time[1]; // To prevent 00 appears.
				$date->add( new DateInterval( "P${days}DT$s_time[0]H$s_time[1]M"));

				$end = new DateTime( $first_day);

				$t_time = explode( ":", $data->t_time);
				++$t_time[1]; --$t_time[1];
				$end->add( new DateInterval( "P${days}DT$t_time[0]H$t_time[1]M"));

//				echo "<p> last record </p>";
				$record = array(
					'class' => $class,
					'name' => $data->name,
					'teacher' => $data->teacher,
					'location' => $data->location,
					'repeats' => ( $data->repeats ? $data->repeats : 1),
					'time' => $date->getTimestamp(),
//					'length' => $date->diff( $end)->format("%s")
					'length' => 105*60*60 // I do not found any php method to calculate that.
				);

				echo "<p>";
				print_r( $record);
				echo "</p>";
				$DB->insert_record( 'jwc_schedule', $record);
				$ret[] = $record;
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

			$ret = array();
			return false;
		}
	}
	return true;
}

function jwc2ical_insert_events()
{
	echo "updating";
}

function jwc2ical_delete_events()
{
	echo "rolling back";
}
