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
 * This file keeps track of upgrades to the jwc2ical module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    local
 * @subpackage jwc2ical
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute jwc2ical upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_jwc2ical_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    $table = new xmldb_table( 'jwc_schedule');
    if ( !$dbman->table_exists($table))
    {
	    // Adding fields to table message_popup
	    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

	    // Adding keys to table message_popup
	    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

	    // Conditionally launch create table for message_popup
	    $dbman->create_table($table);
    }

    if ($oldversion < 2012032100) {
	$talbe = new xmldb_table( 'jwc_schedule');
	$field = new xmldb_field( 'id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', '');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

	$field = new xmldb_field( 'class', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
	if ( $dbman->field_exists( $table, $field))
	{
		$index = new xmldb_index('class', XMLDB_INDEX_NOTUNIQUE, array('class'));
		$dbman->drop_index( $table, $index);
		$dbman->drop_field( $table, $field);
	}

	$field = new xmldb_field( 'class', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'id');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'class');
        if (!$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
        }

        $field = new xmldb_field('teacher', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'name');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $field = new xmldb_field('location', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'teacher');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $field = new xmldb_field('repeats', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'location');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $field = new xmldb_field('time', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'repeats');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $field = new xmldb_field('length', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'time');
	if ( !$dbman->field_exists( $table, $field))
	{
		$dbman->add_field( $table, $field);
	}

        $index = new xmldb_index('class', XMLDB_INDEX_NOTUNIQUE, array('class'));
        if (!$dbman->index_exists( $table, $index))
	{
            $dbman->add_index( $table, $index);
        }

#	set_config( 'current_version', '0-0-0', 'local_jwc2ical');
#	set_config( 'jwc_version', '2012-2-27', 'local_jwc2ical');

        upgrade_plugin_savepoint(true, 2012032100, 'local_jwc2ical', 'db');
    }

    return true;
}
