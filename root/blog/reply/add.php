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

// get some data on the blog and user
if ($blog_id == 0)
{
	trigger_error('BLOG_NOT_EXIST');
}

// Add the language Variables for posting
$user->add_lang('posting');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['REPLY']);

// Posting permissions
$post_options = new post_options;
$post_options->set_status(!isset($_POST['disable_bbcode']), !isset($_POST['disable_smilies']), !isset($_POST['disable_magic_url']));
$post_options->set_in_template();

$blog_plugins->plugin_do('reply_add_start');

// If they did submit or hit preview
if ($submit || $preview || $refresh)
{
	// see if they tried submitting a message or suject(if they hit preview or submit) put it in an array for consistency with the edit mode
	$reply_subject = utf8_normalize_nfc(request_var('subject', '', true));
	$reply_text = utf8_normalize_nfc(request_var('message', '', true));

	// set up the message parser to parse BBCode, Smilies, etc
	$message_parser = new parse_message();
	$message_parser->message = $reply_text;
	$message_parser->parse($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, $post_options->img_status, $post_options->flash_status, $post_options->bbcode_status, $post_options->url_status);

	// check the captcha if required
	if (!handle_captcha('check'))
	{
		$error[] = $user->lang['CONFIRM_CODE_WRONG'];
	}

	// check the form key
	if (!check_form_key('postform'))
	{
		$error[] = $user->lang['FORM_INVALID'];
	}

	// If they did not include a subject, give them the empty subject error
	if ($reply_subject == '' && !$refresh)
	{
		$error[] = $user->lang['EMPTY_MESSAGE_SUBJECT'];
	}

	// If any errors were reported by the message parser add those as well
	if (sizeof($message_parser->warn_msg) && !$refresh)
	{
		$error[] = implode('<br />', $message_parser->warn_msg);
	}

	// Attachments
	$blog_attachment->get_submitted_attachment_data();
	$blog_attachment->parse_attachments('fileupload', $submit, $preview, $refresh, $reply_text);

	if (sizeof($blog_attachment->warn_msg))
	{
		$error[] = implode('<br />', $blog_attachment->warn_msg);
	}
}
else
{
	$reply_subject = '';
	$reply_text = '';

	// if they are trying to quote a message
	if ($mode == 'quote')
	{
		if ($reply_id != 0)
		{
			$reply_subject = 'RE: ' . reply_data::$reply[$reply_id]['reply_subject'];
			decode_message(reply_data::$reply[$reply_id]['reply_text'], reply_data::$reply[$reply_id]['bbcode_uid']);
			$reply_text = '[quote="' . user_data::$user[$reply_user_id]['username'] . '"]' . reply_data::$reply[$reply_id]['reply_text'] . '[/quote]';;
		}
		else
		{
			decode_message(blog_data::$blog[$blog_id]['blog_text'], blog_data::$blog[$blog_id]['bbcode_uid']);
			$reply_subject = 'RE: ' . blog_data::$blog[$blog_id]['blog_subject'];
			$reply_text = '[quote="' . user_data::$user[$user_id]['username'] . '"]' . blog_data::$blog[$blog_id]['blog_text'] . '[/quote]';;
		}
	}
	else
	{
		$reply_subject = 'RE: ' . blog_data::$blog[$blog_id]['blog_subject'];
	}
}

$temp = compact('reply_subject', 'reply_text', 'error');
$blog_plugins->plugin_do_arg_ref('reply_add_after_setup', $temp);
extract($temp);
unset($temp);

// if they did not submit or they have an error
if ( (!$submit) || (sizeof($error)) )
{
	// if they are trying to preview the message and do not have an error
	if ($preview && !sizeof($error))
	{
		$preview_message = $message_parser->format_display($post_options->enable_bbcode, $post_options->enable_magic_url, $post_options->enable_smilies, false);

		// Attachments
		if (sizeof($blog_attachment->attachment_data))
		{
			$template->assign_var('S_HAS_ATTACHMENTS', true);

			$update_count = array();
			$attachment_data = $blog_attachment->attachment_data;

			$blog_attachment->parse_attachments_for_view($preview_message, $attachment_data, $update_count, true);

			if (count($attachment_data))
			{
				foreach ($attachment_data as $row)
				{
					$template->assign_block_vars('attachment', array(
						'DISPLAY_ATTACHMENT' => $row,
					));
				}
			}

			unset($attachment_data);
		}

		$blog_plugins->plugin_do_arg_ref('reply_add_preview', $preview_message);

		// output some data to the template parser
		$template->assign_vars(array(
			'S_DISPLAY_PREVIEW'			=> true,
			'PREVIEW_SUBJECT'			=> censor_text($reply_subject),
			'PREVIEW_MESSAGE'			=> $preview_message,
			'POST_DATE'					=> $user->format_date(time()),
		));
	}

	$blog_plugins->plugin_do('reply_add_after_preview');

	// handles the basic data we need to output for posting
	handle_basic_posting_data('reply');

	// Assign some variables to the template parser
	$template->assign_vars(array(
		'ERROR'						=> (sizeof($error)) ? implode('<br />', $error) : '',
		'MESSAGE'					=> $reply_text,
		'SUBJECT'					=> $reply_subject,

		'L_MESSAGE_BODY_EXPLAIN'	=> (intval($config['max_post_chars'])) ? sprintf($user->lang['MESSAGE_BODY_EXPLAIN'], intval($config['max_post_chars'])) : '',
		'L_POST_A'					=> $user->lang['POST_A_NEW_REPLY'],
	));

	// Tell the template parser what template file to use
	$template->set_filenames(array(
		'body' => 'blog/blog_posting_layout.html'
	));
}
else // user submitted and there are no errors
{
	// insert array, not all of these really need to be inserted, since some are what the fields are as default, but I want it this way. :P
	$sql_data = array(
		'blog_id'				=> $blog_id,
		'user_id' 				=> $user->data['user_id'],
		'user_ip'				=> $user->data['user_ip'],
		'reply_time'			=> time(),
		'reply_subject'			=> $reply_subject,
		'reply_text'			=> $message_parser->message,
		'reply_checksum'		=> md5($message_parser->message),
		'reply_approved' 		=> ($auth->acl_get('u_blogreplynoapprove')) ? 1 : 0,
		'enable_bbcode' 		=> $post_options->enable_bbcode,
		'enable_smilies'		=> $post_options->enable_smilies,
		'enable_magic_url'		=> $post_options->enable_magic_url,
		'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
		'bbcode_uid'			=> $message_parser->bbcode_uid,
		'reply_edit_reason'		=> '',
		'reply_attachment'		=> (count($blog_attachment->attachment_data)) ? 1 : 0,
	);

	$blog_plugins->plugin_do_arg_ref('reply_add_sql', $sql_data);

	$sql = 'INSERT INTO ' . BLOGS_REPLY_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
	$db->sql_query($sql);
	$reply_id = $db->sql_nextid();

	$blog_search->index('add', $blog_id, $reply_id, $message_parser->message, $reply_subject, $user->data['user_id']);

	// update the URLS to include the new reply_id
	generate_blog_urls();

	$blog_attachment->update_attachment_data(false, $reply_id);

	$blog_plugins->plugin_do_arg('reply_add_after_sql', $reply_id);

	unset($message_parser, $sql_data);

	handle_blog_cache('add_reply', $user_id);

	// update the reply count for the blog
	if ($auth->acl_get('u_blogreplynoapprove'))
	{
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count + 1, blog_real_reply_count = blog_real_reply_count + 1 WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		handle_subscription('new_reply', censor_text($reply_subject));
	}
	else
	{
		$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_real_reply_count = blog_real_reply_count + 1 WHERE blog_id = ' . intval($blog_id);
		$db->sql_query($sql);

		inform_approve_report('reply_approve', $reply_id);
	}

	$message = ((!$auth->acl_get('u_blogreplynoapprove')) ? $user->lang['REPLY_NEED_APPROVE'] : $user->lang['REPLY_SUBMIT_SUCCESS']) . '<br /><br />'; 
	$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/>';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', user_data::$user[$user_id]['username'], '</a>') . '<br/>';
		$message .= sprintf($user->lang['RETURN_BLOG_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	blog_meta_refresh(3, $blog_urls['view_reply']);

	trigger_error($message);
}
?>