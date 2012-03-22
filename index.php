<?php
require_once( dirname(__FILE__) . '/../../config.php');
require_once( $CFG->libdir . '/adminlib.php');
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');
require_once( $CFG->dirroot . '/calendar/lib.php');
require_once( $CFG->libdir . '/moodlelib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
admin_externalpage_setup('jwc2ical');

$action = optional_param( 'action', '', PARAM_ALPHA);

echo $OUTPUT->header();
if ( $action == 'update')
{
	jwc2ical_insert_events();
}
elseif ( $action == 'rollback')
{
	jwc2ical_delete_events();
}
else
{
	$render = $PAGE->get_renderer('local_jwc2ical');
	echo $render->heading( 'jwc2ical');

	if ( $action == 'refresh')
	{
		if ( !refresh_date())
		{
			echo "<p> 刷新失败，请重试！</p>";
		}
	}

	$first_day = get_config( 'local_jwc2ical', 'current_version');
	$jwc_day = get_config( 'local_jwc2ical', 'jwc_version');

	$cur_str = split_date( $first_day);
	$jwc_str = split_date( $jwc_day);

	$url = new moodle_url('/local/jwc2ical/index.php');

	$info_str = $cur_str[2] == 0 ? "现在数据库中没有课程表" : "现在课程表的版本是 ：$cur_str";
	$info_str = "<p>$info_str</p>";
	$jwc_info_str = "<p> 教务处课程表的版本是：";
	$jwc_info_str .= html_writer::link( $url . '?action=refresh', $jwc_str, array( 'title' => "点击刷新"));
	$info_str .= $jwc_info_str;

	echo $render->box( $info_str);
	echo html_writer::start_tag( 'ul');
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=update', '更新') . ':' . "将教务处的课程表插入数据库中<p><strong>请在确认教务处已有新课程表后再更新。</strong></p>");
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=rollback', '回滚') . ':' . "删除上一次由本插件加入的所有条目 <p> <strong>不可退回的操作，请慎重。<strong> </p>");
	echo html_writer::end_tag( 'ul');
}
echo $OUTPUT->footer();
