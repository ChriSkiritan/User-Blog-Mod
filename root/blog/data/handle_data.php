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

/**
* trims the length of the text of a blog or reply
*
* @param int|bool $blog_id the blog_id for the blog we will trim the text length for (if not triming the blog text length, set to false)
* @param int|bool $reply_id same as blog_id, except for replies
* @param int str_limit the string length limit
* @param bool $always_return If it is false this function returns false if the string is not shortened, if true it always returns the text whether it was shortened or not
*
* @return Returns false if $always_return is false and the text is not trimmed, otherwise it returns the string (shortened if it was)
*/
function trim_text_length($blog_id, $reply_id, $str_limit, $always_return = false)
{
	global $phpbb_root_path, $phpEx, $user;
	global $blog_data, $reply_data, $user_data, $user_founder;

	$bbcode_bitfield = $text_only_message = $text = '';

	if ($blog_id !== false)
	{
		$data = $blog_data->blog[$blog_id];
		$original_text = $data['blog_text'];
	}
	else
	{
		if ($reply_id === false)
		{
			return false;
		}

		$data = $reply_data->reply[$reply_id];
		$blog_id = $data['blog_id'];
		$original_text = $data['reply_text'];
	}

	$text = $original_text;

	decode_message($text, $data['bbcode_uid']);

	if (utf8_strlen($text) > $str_limit)
	{
		// we will try not to cut off any words :)
		$next_space = strpos(substr($text, $str_limit), ' ');
		$next_el = strpos(substr($text, $str_limit), "\n");
		if ($next_space !== false)
		{
			if ($next_el !== false)
			{
				$str_limit = ($next_space < $next_el) ? $next_space + $str_limit : $next_el + $str_limit;
			}
			else
			{
				$str_limit = $next_space + $str_limit;
			}
		}
		else if ($next_el !== false)
		{
			$str_limit = $next_el + $str_limit;
		}
		else
		{
			$str_limit = utf8_strlen($text);
		}

		// now trim the text
		$text = substr($text, 0, $str_limit);

		// Now lets get the URL's back and nl2br
		$message_parser = new parse_message();
		$message_parser->message = $text;
		$message_parser->parse($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies']);
		$text = $message_parser->format_display($data['enable_bbcode'], $data['enable_magic_url'], $data['enable_smilies'], false);
		unset($message_parser);

		$text .= '...<br/><br/><!-- m --><a href="';
		if ($reply_id !== false)
		{
			$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b={$blog_id}r={$reply_id}#r{$reply_id}");
		}
		else
		{
			$text .= append_sid("{$phpbb_root_path}blog.$phpEx", "b=$blog_id");
		}
		$text .= '">[ ' . $user->lang['CONTINUED'] . ' ]</a><!-- m -->';

		return $text;
	}
	else
	{
		if ($always_return)
		{
			return $original_text;
		}
		else
		{
			return false;
		}
	}
}

/**
* Updates the blog and reply information to add edit and delete messages.
*
* I have this seperate so I can grab the blogs, replies, users, then update the edit and delete data (to cut on SQL queries)
*
* @param string $mode The mode (all, blog, or reply)
*/
function update_edit_delete($mode = 'all')
{
	global $auth, $user, $phpbb_root_path, $phpEx;
	global $blog_data, $reply_data, $user_data, $user_founder;

	if (!isset($user->lang['EDITED_TIME_TOTAL']))
	{
		$user->add_lang('viewtopic');
	}

	if ($mode == 'all' || $mode == 'blog')
	{
		foreach ($blog_data->blog as $row)
		{
			if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
			{
				$blog_id = $row['blog_id'];

				// has the blog been edited?
				if ($row['blog_edit_count'] != 0)
				{	
					if ($row['blog_edit_count'] == 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['blog_edit_user']]['user_colour'] != '')
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $user_data->user[$row['blog_edit_user']]['user_colour'] . '">' . $user_data->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}
					else if ($row['blog_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['blog_edit_user']]['username_full'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['blog_edit_user']]['user_colour'] != '')
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $user_data->user[$row['blog_edit_user']]['user_colour'] . '">' . $user_data->user[$row['blog_edit_user']]['username'] . '</b>', $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
							else
							{
								$blog_data->blog[$blog_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['blog_edit_user']]['username'], $user->format_date($row['blog_edit_time']), $row['blog_edit_count']);
							}
						}
					}
		
					$blog_data->blog[$blog_id]['edit_reason'] = censor_text($row['blog_edit_reason']);
				}
				else
				{
					$blog_data->blog[$blog_id]['edited_message'] = '';
					$blog_data->blog[$blog_id]['edit_reason'] = '';
				}
	
				// has the blog been deleted?
				if ($row['blog_deleted'] != 0)
				{
					$blog_data->blog[$blog_id]['deleted_message'] = sprintf($user->lang['BLOG_IS_DELETED'], $user_data->user[$row['blog_deleted']]['username_full'], $user->format_date($row['blog_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=blog&amp;mode=undelete&amp;b=$blog_id") . '">', '</a>');
				}
				else
				{
					$blog_data->blog[$blog_id]['deleted_message'] = '';
				}
			}
		}
	}

	if ($mode == 'all' || $mode == 'reply')
	{
		foreach ($reply_data->reply as $row)
		{
			if ((!isset($row['edited_message'])) && (!isset($row['deleted_message'])) )
			{
				$reply_id = $row['reply_id'];

				// has the reply been edited?
				if ($row['reply_edit_count'] != 0)
				{	
					if ($row['reply_edit_count'] == 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['reply_edit_user']]['user_colour'] != '')
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], '<b style="color: ' . $user_data->user[$row['reply_edit_user']]['user_colour'] . '">' . $user_data->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIME_TOTAL'], $user_data->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}
					else if ($row['reply_edit_count'] > 1)
					{
						if ($auth->acl_get('u_viewprofile'))
						{
							$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['reply_edit_user']]['username_full'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
						}
						else
						{
							if ($user_data->user[$row['reply_edit_user']]['user_colour'] != '')
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], '<b style="color: ' . $user_data->user[$row['reply_edit_user']]['user_colour'] . '">' . $user_data->user[$row['reply_edit_user']]['username'] . '</b>', $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
							else
							{
								$reply_data->reply[$reply_id]['edited_message'] = sprintf($user->lang['EDITED_TIMES_TOTAL'], $user_data->user[$row['reply_edit_user']]['username'], $user->format_date($row['reply_edit_time']), $row['reply_edit_count']);
							}
						}
					}
		
					$reply_data->reply[$reply_id]['edit_reason'] = censor_text($row['reply_edit_reason']);
				}
				else
				{
					$reply_data->reply[$reply_id]['edited_message'] = '';
					$reply_data->reply[$reply_id]['edit_reason'] = '';
				}
	
				// has the reply been deleted?
				if ($row['reply_deleted'] != 0)
				{
					$reply_data->reply[$reply_id]['deleted_message'] = sprintf($user->lang['REPLY_IS_DELETED'], $user_data->user[$row['reply_deleted']]['username_full'], $user->format_date($row['reply_deleted_time']), '<a href="' . append_sid("{$phpbb_root_path}blog.$phpEx", "page=reply&amp;mode=undelete&amp;r=$reply_id") . '">', '</a>');
				}
				else
				{
					$reply_data->reply[$reply_id]['deleted_message'] = '';
				}
			}
		}
	}
}

/**
* Fix Where SQL function
*
* Checks to make sure there is a WHERE if there are any AND sections in the SQL and fixes them if needed
*
* @param string $sql The (possibly) broken SQL query to check
* @return The fixed SQL query.
*/
function fix_where_sql($sql)
{
	if (!strpos($sql, 'WHERE') && strpos($sql, 'AND'))
	{
		return substr($sql, 0, strpos($sql, 'AND')) . 'WHERE' . substr($sql, strpos($sql, 'AND') + 3);
	}

	return $sql;
}

/**
* Outputs data as a Feed.
 *
* @param int|array $blog_ids The id's of blogs that are going to get outputted,
 * @param string $feed_type The type of feed we are outputting
*/
function feed_output($blog_ids, $feed_type)
{
	global $template, $phpbb_root_path, $phpEx, $page, $mode, $limit, $config, $user, $blog_data;

	if (!is_array($blog_ids))
	{
		$blog_ids = array($blog_ids);
	}

	$board_url = generate_board_url();

	$template->assign_vars(array(
		'FEED'				=> $feed_type,
		'SELF_URL'			=> "{$board_url}/blog.{$phpEx}?page={$page}&amp;mode={$mode}&amp;feed={$feed_type}&amp;limit={$limit}",
		'TITLE'				=> $config['sitename'] . ' ' . $user->lang['FEED'],
		'SITE_URL'			=> $board_url,
		'SITE_DESC'			=> $config['site_desc'],
		'SITE_LANG'			=> $config['default_lang'],
		'CURRENT_TIME'		=> date('r'),
	));

	// the items section is only used in RSS 1.0
	if ($feed_type == 'RSS_1.0')
	{
		// output the URLS for the items section
		foreach ($blog_ids as $id)
		{
			$template->assign_block_vars('items', array(
				'URL'	=> "{$board_url}/blog.{$phpEx}?b=$id",
			));
		}
	}

	// Output the main data
	foreach ($blog_ids as $id)
	{
		$blog_row = $blog_data->handle_blog_data($id, true);

		$row = array(
			'URL'		=> $board_url . "/blog.{$phpEX}?b=$id",
			'USERNAME'	=> $user_data->user[$blog_data->blog[$id]['user_id']]['username'],
		);

		$template->assign_block_vars('item', $blog_row + $row);
	}

	// tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog_feed.xml'
	));
}

/**
* Generates the left side menu
*
* @param int $user_id The user_id of the user whom we are building the menu for
*/
function generate_menu($user_id)
{
	global $db, $template, $phpbb_root_path, $phpEx, $user, $cache;
	global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls, $blog_plugins;

// output the data for the left author info menu
	$template->assign_vars($user_data->handle_user_data($user_id));
	$user_data->handle_user_data($user_id, 'custom_fields');

// archive menu
	// Last Month's ID(set to 0 now, will be updated in the loop)
	$last_mon = 0;

	$archive_rows = array();

	// attempt to get the data from the cache
	$cache_data = $cache->get("_blog_archive{$user_id}");

	if ($cache_data === false)
	{
		$sql = 'SELECT blog_id, blog_time, blog_subject FROM ' . BLOGS_TABLE . '
					WHERE user_id = \'' . $user_id . '\'
						AND blog_deleted = \'0\'
							ORDER BY blog_id DESC';
		$result = $db->sql_query($sql);

		while($row = $db->sql_fetchrow($result))
		{
			$date = getdate($row['blog_time']);

			// If we are starting a new month
			if ($date['mon'] != $last_mon)
			{
				$archive_row = array(
					'MONTH'			=> $date['month'],
					'YEAR'			=> $date['year'],

					'monthrow'		=> array(),
				);

				$archive_rows[] = $archive_row;
			}

			$archive_row_month = array(
				'TITLE'			=> censor_text($row['blog_subject']),
				'U_VIEW'		=> blog_url($user_id, $row['blog_id'], false, array(), array('blog_subject' => $row['blog_subject'])),
				'DATE'			=> $user->format_date($row['blog_time']),
			);

			$archive_rows[count($archive_rows) - 1]['monthrow'][] = $archive_row_month;

			// set the last month variable as the current month
			$last_mon = $date['mon'];
		}
		$db->sql_freeresult($result);

		// cache the result
		$cache->put("_blog_archive{$user_id}", $archive_rows);
		$cache_data = $archive_rows;
	}

	if (count($cache_data))
	{
		foreach($cache_data as $row)
		{
			$template->assign_block_vars('archiverow', $row);
		}
	}

	// output some data
	$template->assign_vars(array(
		// are there any archives?
		'S_ARCHIVES'	=> (count($cache_data)) ? true : false,
	));

	$blog_plugins->plugin_do('function_generate_menu');

	unset($cache_data);
}

/**
* handles sending subscription notices for blogs or replies
*
* Sends a PM or Email to each user in the subscription list, depending on what they want
*
* @param string $mode The mode (new_blog, or new_reply)
* @param string $post_subject The subject of the post made
* @param int|bool $uid The user_id of the user who made the new blog (if there is one).  If this is left as 0 it will grab the global value of $user_id.
* @param int|bool $bid The blog_id of the blog.  If this is left as 0 it will grab the global value of $blog_id.
* @param int|bool $rid The reply_id of the new reply (if there is one).  If this is left as 0 it will grab the global value of $reply_id.
*/
function handle_subscription($mode, $post_subject, $uid = 0, $bid = 0, $rid = 0)
{
	global $db, $user, $phpbb_root_path, $phpEx, $config;
	global $user_id, $blog_id, $reply_id;
	global $blog_data, $reply_data, $user_data, $user_founder, $blog_urls, $blog_plugins;

	// if $uid, $bid, or $rid are not set, use the globals
	$uid = ($uid != 0) ? $uid : $user_id;
	$bid = ($bid != 0) ? $bid : $blog_id;
	$rid = ($rid != 0) ? $rid : $reply_id;

	// make sure that subscriptions are enabled and that a blog_id is sent
	if (!$config['user_blog_subscription_enabled'] || $bid == 0)
	{
		return;
	}

	$subscribe_modes = array(0 => 'send_via_pm', 1 => 'send_via_email', 2 => array('send_via_pm', 'send_via_email'));
	$blog_plugins->plugin_do_arg('function_handle_subscription', $subscribe_modes);

	// setup the arrays which will hold the to info for PM's/Emails
	$send_via_pm = array();
	$send_via_email = array();

	if ($mode == 'new_reply' && $rid != 0)
	{
		$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE blog_id = \'' . $bid . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			if (is_array($subscribe_modes[$row['sub_type']]))
			{
				foreach ($subscribe_modes[$row['sub_type']] as $var)
				{
					array_push($$var, $row['sub_user_id']);
				}
			}
			else
			{
				array_push($$subscribe_modes[$row['sub_type']], $row['sub_user_id']);
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['BLOG_SUBSCRIPTION_NOTICE'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$bid"), true), $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;b=$bid"), true));
	}
	else if ($mode == 'new_blog' && $uid != 0)
	{
		$sql = 'SELECT* FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
			WHERE user_id = \'' . $uid . '\'';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			if (is_array($subscribe_modes[$row['sub_type']]))
			{
				foreach ($subscribe_modes[$row['sub_type']] as $var)
				{
					array_push($$var, $row['sub_user_id']);
				}
			}
			else
			{
				array_push($$subscribe_modes[$row['sub_type']], $row['sub_user_id']);
			}
		}
		$db->sql_freeresult($result);

		$message = sprintf($user->lang['USER_SUBSCRIPTION_NOTICE'], $user->data['username'], redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "b=$bid"), true), redirect(append_sid("{$phpbb_root_path}blogs.$phpEx", "page=unsubscribe&amp;u=$uid"), true));
	}

	$user_data->get_user_data('2');

	// Send the PM
	if (count($send_via_pm) > 0)
	{
		if (!function_exists('submit_pm'))
		{
			// include the private messages functions page
			include("{$phpbb_root_path}includes/functions_privmsgs.$phpEx");
		}

		if (!class_exists('parse_message'))
		{
			include("{$phpbb_root_path}includes/message_parser.$phpEx");
		}

		$message_parser = new parse_message();

		$message_parser->message = $message;
		$message_parser->parse(true, true, true, true, true, true, true);

		// setup out to address list
		foreach ($send_via_pm as $id)
		{
			$address_list[$id] = 'to';
		}

		$pm_data = array(
			'from_user_id'		=> 2,
			'from_username'		=> $user_data->user[2]['username'],
			'address_list'		=> array('u' => $address_list),
			'icon_id'			=> 10,
			'from_user_ip'		=> '0.0.0.0',
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> true,
			'enable_sig'		=> false,
			'message'			=> $message_parser->message,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
		);

		submit_pm('post', $user->lang['SUBSCRIPTION_NOTICE'], $pm_data, false);
		unset($message_parser, $address_list, $pm_data);
	}

	// Send the email
	if (count($send_via_email) > 0 && $config['email_enable'])
	{
		if (!class_exists('messenger'))
		{
			include("{$phpbb_root_path}includes/functions_messenger.$phpEx");
		}

		$messenger = new messenger(false);

		$user_data->get_user_data($send_via_email);
		$reply_url_var = ($rid !== false) ? "r={$rid}#r{$rid}" : '';

		foreach ($send_via_email as $uid)
		{
			$messenger->template('blog_notify', $config['default_lang']);
			$messenger->replyto($config['board_contact']);
			$messenger->to($user_data->user[$uid]['user_email'], $user_data->user[$uid]['username']);

			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
			$messenger->headers('X-AntiAbuse: User_id - ' . $user_data->user[2]['user_id']);
			$messenger->headers('X-AntiAbuse: Username - ' . $user_data->user[2]['username']);
			$messenger->headers('X-AntiAbuse: User IP - ' . $user_data->user[2]['user_ip']);

			$messenger->assign_vars(array(
				'BOARD_CONTACT'	=> $config['board_contact'],
				'SUBJECT'		=> $user->lang['SUBSCRIPTION_NOTICE'],
				'TO_USERNAME'	=> $user_data->user[$uid]['username'],
				'TYPE'			=> ($rid !== false) ? $user->lang['REPLY'] : $user->lang['BLOG'],
				'NAME'			=> $post_subject,
				'BY_USERNAME'	=> $user->data['username'],
				'U_VIEW'		=> redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}&amp;b={$bid}" . $reply_url_var), true),
				'U_UNSUBSCRIBE'	=> ($rid !== false) ? redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}&amp;b={$bid}"), true) : redirect(append_sid("{$phpbb_root_path}blog.$phpEx", "u={$uid}")),
			));

			$messenger->send(NOTIFY_EMAIL);
		}
		unset($messenger);
	}

	$blog_plugins->plugin_do('function_handle_subscription_end');
}

/**
* handle_captcha
*
* @param string $mode The mode, build or check, to either build the captcha/confirm box, or to check if the user entered the correct confirm_code
*
* @return Returns
*	- True if the captcha code is correct and $mode is check or they do not need to view the captcha (permissions) 
*	- False if the captcha code is incorrect, or not given and $mode is check
*/
function handle_captcha($mode)
{
	global $db, $template, $phpbb_root_path, $phpEx, $user, $config, $s_hidden_fields;

	if ($user->data['user_id'] != ANONYMOUS || !$config['user_blog_guest_captcha'])
	{
		return true;
	}

	if ($mode == 'check')
	{
		$confirm_id = request_var('confirm_id', '');
		$confirm_code = request_var('confirm_code', '');

		if ($confirm_id == '' || $confirm_code == '')
		{
			return false;
		}

		$sql = 'SELECT code
			FROM ' . CONFIRM_TABLE . "
			WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_type = " . CONFIRM_POST;
		$result = $db->sql_query($sql);
		$confirm_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (empty($confirm_row['code']) || strcasecmp($confirm_row['code'], $confirm_code) !== 0)
		{
			return false;
		}

		// add confirm_id and confirm_code to hidden fields if not already there so the user doesn't need to retype in the confirm code if 
		if (strpos($s_hidden_fields, 'confirm_id') === false)
		{
			$s_hidden_fields .= build_hidden_fields(array('confirm_id' => $confirm_id, 'confirm_code' => $confirm_code));
		}

		return true;
	}
	else if ($mode == 'build' && !handle_captcha('check'))
	{
		// Show confirm image
		$sql = 'DELETE FROM ' . CONFIRM_TABLE . "
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_type = " . CONFIRM_POST;
		$db->sql_query($sql);

		// Generate code
		$code = gen_rand_string(mt_rand(5, 8));
		$confirm_id = md5(unique_id($user->ip));
		$seed = hexdec(substr(unique_id(), 4, 10));

		// compute $seed % 0x7fffffff
		$seed -= 0x7fffffff* floor($seed / 0x7fffffff);

		$sql = 'INSERT INTO ' . CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'confirm_id'	=> (string) $confirm_id,
			'session_id'	=> (string) $user->session_id,
			'confirm_type'	=> (int) CONFIRM_POST,
			'code'			=> (string) $code,
			'seed'			=> (int) $seed)
		);
		$db->sql_query($sql);

		$template->assign_vars(array(
			'S_CONFIRM_CODE'			=> true,
			'CONFIRM_ID'				=> $confirm_id,
			'CONFIRM_IMAGE'				=> '<img src="' . append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=confirm&amp;id=' . $confirm_id . '&amp;type=' . CONFIRM_POST) . '" alt="" title="" />',
			'L_POST_CONFIRM_EXPLAIN'	=> sprintf($user->lang['POST_CONFIRM_EXPLAIN'], '<a href="mailto:' . htmlspecialchars($config['board_contact']) . '">', '</a>'),
		));
	}
}

/**
* Informs users when a blog or reply was reported or needs approval
*
* Informs users in the $config['user_blog_inform'] variable (in the variable should be user_id's seperated by commas if there is more than one)
*
* @param string $mode The mode - blog_report, reply_report, blog_approve, reply_approve
*/
function inform_approve_report($mode, $id)
{
	global $phpbb_root_path, $phpEx, $config, $user, $blog_plugins;
	
	if ($config['user_blog_inform'] == '')
	{
		return;
	}

	switch ($mode)
	{
		case 'blog_report' :
			$message = sprintf($user->lang['BLOG_REPORT_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id"));
			$subject = $user->lang['BLOG_REPORT_PM_SUBJECT'];
			break;
		case 'reply_report' :
			$message = sprintf($user->lang['REPLY_REPORT_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "r=$id"));
			$subject = $user->lang['REPLY_REPORT_PM_SUBJECT'];
			break;
		case 'blog_approve' :
			$message = sprintf($user->lang['BLOG_APPROVE_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "b=$id"));
			$subject = $user->lang['BLOG_APPROVE_PM_SUBJECT'];
			break;
		case 'reply_approve' :
			$message = sprintf($user->lang['REPLY_APPROVE_PM'], $user->data['username'], append_sid("{$phpbb_root_path}blog.$phpEx", "r=$id"));
			$subject = $user->lang['REPLY_APPROVE_PM_SUBJECT'];
			break;
		default:
			$blog_plugins->plugin_do_arg('function_inform_approve_report', $mode);
	}

	$to = explode(",", $config['user_blog_inform']);

	if (!function_exists('submit_pm'))
	{
		// include the private messages functions page
		include("{$phpbb_root_path}includes/functions_privmsgs.$phpEx");
	}

	if (!class_exists('parse_message'))
	{
		include("{$phpbb_root_path}includes/message_parser.$phpEx");
	}

	$message_parser = new parse_message();
	$message_parser->message = $message;
	$message_parser->parse(true, true, true, true, true, true, true);

	// setup out to address list
	foreach ($to as $id)
	{
		$address_list[$id] = 'to';
	}

	$pm_data = array(
		'from_user_id'		=> 2,
		'from_username'		=> $user_data->user[2]['username'],
		'address_list'		=> array('u' => $address_list),
		'icon_id'			=> 10,
		'from_user_ip'		=> '0.0.0.0',
		'enable_bbcode'		=> true,
		'enable_smilies'	=> true,
		'enable_urls'		=> true,
		'enable_sig'		=> false,
		'message'			=> $message_parser->message,
		'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
		'bbcode_uid'		=> $message_parser->bbcode_uid,
	);

	submit_pm('post', $subject, $pm_data, false);
	unset($message_parser, $address_list, $to, $pm_data);
}

/**
* Syncronise Blog Data
*
* This should never need to be used unless someone manually deletes blogs or replies from the database
* It is not used by the User Blog mod anywhere, except for updates/upgrades and the resync page.
* To any potential users: Make sure you do not set this in a page where it gets ran often.  Resyncing data is a long process, especially when the number of blogs that you have is large
*
* @param string $mode can be all, reply_count, real_reply_count, delete_orphan_replies, or user_blog_count
*/
function resync_blog($mode)
{
	global $db, $blog_plugins;

	$blog_data = array();
	$reply_data = array();

	// Start by selecting all blog data that we will use
	$sql = 'SELECT blog_id, blog_reply_count, blog_real_reply_count FROM ' . BLOGS_TABLE . ' ORDER BY blog_id ASC';
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		$blog_data[$row['blog_id']] = $row;
	}
	$db->sql_freeresult($result);

	/*
	* Update & Resync the reply counts
	*/
	if ( ($mode == 'reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = \'' . $row['blog_id'] . '\' 
						AND reply_deleted = \'0\' 
						AND reply_approved = \'1\'';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = \'' . $total['total'] . '\' WHERE blog_id = \'' . $row['blog_id'] . '\'';
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Update & Resync the real reply counts
	*/
	if ( ($mode == 'real_reply_count') || ($mode == 'all') )
	{
		foreach($blog_data as $row)
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql = 'SELECT count(reply_id) AS total 
				FROM ' . BLOGS_REPLY_TABLE . ' 
					WHERE blog_id = \'' . $row['blog_id'] . '\'';
			$result = $db->sql_query($sql);
			$total = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($total['total'] != $row['blog_real_reply_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = \'' . $total['total'] . '\' WHERE blog_id = \'' . $row['blog_id'] . '\'';
				$db->sql_query($sql);
			}
		}
	}

	/*
	* Delete's all oprhaned replies (replies where the blogs they should go under have been deleted).
	*/
	if ( ($mode == 'delete_orphan_replies') || ($mode == 'all') )
	{
		// Now get all reply data we will use
		$sql = 'SELECT reply_id, blog_id FROM ' . BLOGS_REPLY_TABLE . ' ORDER BY reply_id ASC';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// if the blog_id it attached to is not in $blog_data
			if (!(array_key_exists($row['blog_id'], $blog_data)))
			{
				$sql2 = 'DELETE FROM ' . BLOGS_REPLY_TABLE . ' WHERE reply_id = \'' . $row['reply_id'] . '\'';
				$db->sql_query($sql2);
			}
		}
		$db->sql_freeresult($result);
	}

	/*
	* Updates the blog_count for each user
	*/
	if ( ($mode == 'user_blog_count') || ($mode == 'all') )
	{
		// select the users data we will need
		$sql = 'SELECT user_id, blog_count FROM ' . USERS_TABLE;
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			// count all the replies (an SQL query seems the easiest way to do it)
			$sql2 = 'SELECT count(blog_id) AS total 
				FROM ' . BLOGS_TABLE . ' 
					WHERE user_id = \'' . $row['user_id'] . '\' 
						AND blog_deleted = \'0\' 
						AND blog_approved = \'1\'';
			$result2 = $db->sql_query($sql2);
			$total = $db->sql_fetchrow($result2);
			$db->sql_freeresult($result2);

			if ($total['total'] != $row['blog_count'])
			{
				// Update the reply count
				$sql = 'UPDATE ' . USERS_TABLE . ' SET blog_count = \'' . $total['total'] . '\' WHERE user_id = \'' . $row['user_id'] . '\'';
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	// clear the user blog mod's cache
	handle_blog_cache('blog', false);

	$blog_plugins->plugin_do_arg('function_resync_blog', $mode);
}
?>