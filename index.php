<?php
require_once( dirname(__FILE__) . '/../../config.php');
require_once( $CFG->libdir . '/adminlib.php');
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');
require_once( $CFG->dirroot . '/calendar/lib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
admin_externalpage_setup('jwc2ical');

$action = optional_param( 'action', 'b', PARAM_ALPHA);
#$action = 0;

echo $OUTPUT->header();
if ( $action == 'update')
{
	jwc_cur_insert_events();
}
elseif ( $action == 'rollback')
{
	jwc_cur_delete_events();
}
else
{
	$render = $PAGE->get_renderer('local_jwc2ical');
	echo $render->heading( 'jwc2ical');
	$url = new moodle_url('/local/jwc2ical/index.php');
	echo html_writer::start_tag( 'ul');
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=update', '更新课程表') . ':' . '将教务处的课程表插入数据库中');
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=rollback', '回滚课程表') . ':' . '删除上一步加入的所有条目');
	echo html_writer::end_tag( 'ul');
}
echo $OUTPUT->footer();
