<?php

#require_once( $CFG->dirroot . '/lib/moodlelib.php');

function jwc2ical_insert_events()
{
	echo "updating";
	exec( './class 0903101', $res);

	$classes = array();
	$exams = array();
	$all_class = array();
	$all_exam = array();
	$class_parts = array();
	$exam_parts = array();
	$class_flag = false;
	$exam_flag = false;
	foreach ( $res as $line)
	{
		$parts = explode( ' ', $line);
		/*
		if ( strstr( $line, 'Class'))
		{
			if ( !$class_flag)
			{
				$class_parts = $parts;
				$class_flag = true;
			}
			else
			{
				$class = new stdClass();
				for ( $i = 1; $i < count( $class_parts); $i++)
				{
					$class->$class_parts[$i] = $parts[$i];
				}
				$classes[] = $class;
			}
		}
		else
		{
			if ( !$exam_flag)
			{
				$exam_parts = $parts;
				$exam_flag = true;
			}
			else
			{
				$exam = new stdClass();
				for ( $i = 1; $i < count( $exam_parts); $i++)
				{
					$exam->$exam_parts[$i] = $parts[$i];
				}
				$exams[] = $exam;
			}
		}

		 */
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
		}
	}
	/*
	print_r( $classes);
	print_r( $exams);
	 */
	print_r( $all_class);
	echo '<p>';
	print_r( $all_exam);
}

function jwc2ical_delete_events()
{
	echo "roling back";
}
