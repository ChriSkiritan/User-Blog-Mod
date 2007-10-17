<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

// If the file that requested this does not have IN_PHPBB defined or the user requested this page directly exit.
if (!defined('IN_PHPBB'))
{
	exit;
}

// include the files for this mod
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

include($phpbb_root_path . 'blog/functions.' . $phpEx);
include($phpbb_root_path . 'blog/permissions.' . $phpEx);
include($phpbb_root_path . 'blog/data/blog_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/reply_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/user_data.' . $phpEx);
include($phpbb_root_path . 'blog/data/handle_data.' . $phpEx);
include($phpbb_root_path . 'blog/plugins/plugins.' . $phpEx);

// set some initial variables that we will use
$blog_data = new blog_data();
$reply_data = new reply_data();
$user_data = new user_data();
$blog_plugins = new blog_plugins();
$error = $blog_urls = $foe_list = array();
$s_hidden_fields = $subscribed_title = '';
$subscribed = false;

$blog_plugins_path = $phpbb_root_path . 'blog/plugins/';
$blog_plugins->load_plugins();
$blog_plugins->plugin_do('blog_start');

// get some initial data
$submit = (isset($_POST['post'])) ? true : false;
$preview = (isset($_POST['preview'])) ? true : false;
$print = (request_var('view', '') == 'print') ? true : false;
$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
$cancel = (isset($_POST['cancel'])) ? true : false;

// get some more initial data
$page = request_var('page', '');
$mode = request_var('mode', '');
$user_id = ($page == 'blog' && $mode == 'add') ? $user->data['user_id'] : intval(request_var('u', 0));
$username = request_var('un', '');
$blog_id = intval(request_var('b', 0));
$reply_id = intval(request_var('r', 0));
$feed = request_var('feed', '');
$hilit_words = request_var('hilit', '', true);
$start = intval(request_var('start', 0));
$limit = intval(request_var('limit', ($blog_id || $reply_id) ? ($print) ? 99999 : 10 : 5 ));
$sort_days = request_var('st', ((!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0));
$sort_key = request_var('sk', 't');
$sort_dir = request_var('sd', ($blog_id || $reply_id) ? 'a' : 'd');
$user_founder = ($user->data['user_type'] == USER_FOUNDER && $config['user_blog_founder_all_perm']) ? true : false;

// setting some variables for sorting
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
$order_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';

// for highlighting
if ($hilit_words)
{
	$highlight_match = $highlight = '';
	foreach (explode(' ', trim($hilit_words)) as $word)
	{
		if (trim($word))
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('*', '\w*?', preg_quote($word, '#'));
		}
	}
	$highlight = urlencode($hilit_words);
}
else
{
	$highlight_match = false;
}

// Get the Zebra data
get_zebra_info();

// get the replies data if it was requested
if ($reply_id != 0)
{
	if ($reply_data->get_reply_data('reply', $reply_id) === false)
	{
		trigger_error('NO_REPLY');
	}

	$reply_user_id = $reply_data->reply[$reply_id]['user_id'];
	$blog_id = $reply_data->reply[$reply_id]['blog_id'];

	if (intval(request_var('start', -1)) == -1)
	{
		$total_replies = $reply_data->get_reply_data('page', array($blog_id, $reply_id), array('start' => $start, 'limit' => $limit, 'order_dir' => $order_dir, 'sort_days' => $sort_days));
		$start = (intval($total_replies / $limit) * $limit);
	}
}

// get the blog's data if it was requested
if ($blog_id != 0)
{
	if ($blog_data->get_blog_data('blog', $blog_id) === false)
	{
		trigger_error('NO_BLOG');
	}

	$user_id = $blog_data->blog[$blog_id]['user_id'];

	$subscribed = get_subscription_info($blog_id);
	$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_BLOG'] : $user->lang['SUBSCRIBE_BLOG'];
}

// if they sent the username instead of the user_id, get the user_id from that username
if ($username != '' && $user_id == 0)
{
	$user_id = $user_data->get_user_data(false, false, $username);
}
else if ($user_id != 0)
{
	array_push($user_data->user_queue, $user_id);
}

if ($user_id != 0)
{
	// find out if they are subscribed to this user's blogs
	if ($blog_id == 0)
	{
		$subscribed = get_subscription_info(false, $user_id);
		$subscribed_title = ($subscribed) ? $user->lang['UNSUBSCRIBE_USER'] : $user->lang['SUBSCRIBE_USER'];
	}
}

// get the user data for what we have and update the edit and delete info
$user_data->get_user_data(false, true);
update_edit_delete();

// make sure they user they requested exists
if ($user_id != 0 && !array_key_exists($user_id, $user_data->user))
{
	trigger_error('NO_USER');
}

// now that we got the user data, let us set another variable to shorten things up later
$username = ($user_id != 0) ? $user_data->user[$user_id]['username'] : '';

// generate the blog urls
generate_blog_urls();

// check to see if they are trying to view a feed, and make sure they used a variable that we accept for the format
$feed = ((($feed == 'RSS_0.91') || ($feed == 'RSS_1.0') || ($feed == 'RSS_2.0') || ($feed == 'ATOM') || ($feed == 'JAVASCRIPT')) && $config['user_blog_enable_feeds']) ? $feed : false;

// Lets add credits for the User Blog mod...this is not the best way to do it, but it makes it so the person installing it has 1 less edit to do per style
$user->lang['TRANSLATION_INFO'] = (!empty($user->lang['TRANSLATION_INFO'])) ? $user->lang['BLOG_CREDITS'] . '<br/>' . $user->lang['TRANSLATION_INFO'] : $user->lang['BLOG_CREDITS'];

// Add some data to the template
$initial_data = array(
	'MODE'					=> $mode,
	'PAGE'					=> $page,

	'U_ADD_BLOG'			=> (check_blog_permissions('blog', 'add', true)) ? $blog_urls['add_blog'] : '',
	'U_BLOG'				=> ($print) ? generate_board_url() . "/blog.{$phpEx}?b=$blog_id" : $blog_urls['self'],
	'U_BLOG_MCP'			=> ($auth->acl_gets('m_blogapprove', 'm_blogreport', 'm_blogreplyapprove', 'm_blogreplyreport') || $user_founder) ? append_sid("{$phpbb_root_path}blog.$phpEx", 'page=mcp') : '',
 	'U_REPLY_BLOG'			=> ($blog_id != 0 && check_blog_permissions('reply', 'add', true, $blog_id)) ? $blog_urls['add_reply'] : '',

	'S_POST_ACTION'			=> $blog_urls['self'],
	'S_PRINT_MODE'			=> $print,
	'S_WATCH_FORUM_TITLE'	=> $subscribed_title,
	'S_WATCH_FORUM_LINK'	=> ($subscribed) ? $blog_urls['unsubscribe'] : $blog_urls['subscribe'],
	'S_WATCHING_FORUM'		=> $subscribed,

	'ADD_BLOG_IMG'			=> $phpbb_root_path . 'styles/' . $user->theme['imageset_path'] . '/imageset/' . $user->data['user_lang'] . '/button_blog_new.gif',
	'AIM_IMG'				=> $user->img('icon_contact_aim', 'AIM'),
	'DELETE_IMG'			=> $user->img('icon_post_delete', 'DELETE_POST'),
	'EDIT_IMG'				=> $user->img('icon_post_edit', 'EDIT_POST'),
	'EMAIL_IMG'				=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'ICQ_IMG'				=> $user->img('icon_contact_icq', 'ICQ'),
	'JABBER_IMG'			=> $user->img('icon_contact_jabber', 'JABBER'),
	'MINI_POST_IMG'			=> $user->img('icon_post_target', 'POST'),
	'MSN_IMG'				=> $user->img('icon_contact_msnm', 'MSNM'),
	'PM_IMG'				=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'PROFILE_IMG'			=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'QUOTE_IMG'				=> $user->img('icon_post_quote', 'QUOTE'),
	'REPLY_BLOG_IMG'		=> $user->img('button_topic_reply', 'REPLY_TO_TOPIC'),
	'REPORT_IMG'			=> $user->img('icon_post_report', 'REPORT_POST'),
	'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'WARN_IMG'				=> $user->img('icon_user_warn', 'WARN_USER'),
	'WWW_IMG'				=> $user->img('icon_contact_www', 'VISIT_WEBSITE'),
	'YIM_IMG'				=> $user->img('icon_contact_yahoo', 'YIM'),
);

$blog_plugins->plugin_do_arg('initial_output', $initial_data);

$template->assign_vars($initial_data);
?>