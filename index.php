<?php
require_once( dirname(__FILE__) . '/../../config.php');
require_once( $CFG->libdir . '/adminlib.php');
require_once( $CFG->dirroot . '/local/jwc2ical/locallib.php');
require_once( $CFG->dirroot . '/calendar/lib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
admin_externalpage_setup('jwc2ical');

$action = optional_param( 'action', '', PARAM_ALPHA);

read_days( $first_day, $jwc_day);

echo $OUTPUT->header();
if ( $action == 'update')
{
	global $dtstart;
	$dtstart = $jwc_day;
	echo "本学期第一个周一是：$dtstart";
	echo "<p> 如果上述信息有问题，请检查/local/jwc2ical/dtstart文件</p>";

	jwc2ical_insert_events();
	write_days( $jwc_day, $jwc_day);
}
elseif ( $action == 'rollback')
{
	jwc2ical_delete_events();
	write_days( "0-0-0", $jwc_day);
}
else
{
	$render = $PAGE->get_renderer('local_jwc2ical');
	echo $render->heading( 'jwc2ical');

	if ( $action == 'refresh')
	{
		exec( "./date", $res, $ret);
		$res = $res[0];
		if ( $ret !== 0)
		{
			echo "<p> 刷新失败，请重试！</p>";
		}
		elseif ( $res !== $jwc_day)
		{
			$jwc_day = $res;
			write_days( $first_day, $jwc_day);
		}
	}

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
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=update', '更新') . ':' . "将教务处的课程表插入数据库中 请在确认教务处已有新课程表后再更新！");
	echo html_writer::tag( 'li', html_writer::link( $url . '?action=rollback', '回滚') . ':' . "删除上一次由本插件加入的所有条目 不可退回的操作，请慎重！");
	echo html_writer::end_tag( 'ul');
}
echo $OUTPUT->footer();
