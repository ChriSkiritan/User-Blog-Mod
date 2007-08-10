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

// If they did not include the $reply_id give them an error...
if ($reply_id == 0)
{
	trigger_error('NO_REPLY');
}

// Was Cancel pressed? If so then redirect to the appropriate page
if ($cancel)
{
	redirect($blog_urls['view_reply']);
}

// Add the language Variables for posting
$user->add_lang('posting');

// Setup the page header and sent the title of the page that will go into the browser header
page_header($user->lang['UNDELETE_REPLY']);

// Generate the breadcrumbs
generate_blog_breadcrumbs($user->lang['UNDELETE_REPLY']);

// if someone is trying to un-delete a reply and the reply is not deleted
if ($reply_data->reply[$reply_id]['reply_deleted'] == 0)
{
	trigger_error('REPLY_NOT_DELETED');
}

if (confirm_box(true))
{
	$sql = 'UPDATE ' . BLOGS_REPLY_TABLE . ' SET reply_deleted = \'0\', reply_deleted_time = \'0\' WHERE reply_id = \'' . $reply_id . '\'';
	$db->sql_query($sql);

	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET blog_reply_count = blog_reply_count + 1 WHERE blog_id = \'' . $blog_id . '\'';
	$db->sql_query($sql);

	meta_refresh(3, $blog_urls['view_reply']);

	$message = $user->lang['REPLY_UNDELETED'] . '<br/><br/>';
	$message .= '<a href="' . $blog_urls['view_reply'] . '">' . $user->lang['VIEW_REPLY'] . '</a><br/>';
	$message .= '<a href="' . $blog_urls['view_blog'] . '">' . $user->lang['VIEW_BLOG'] . '</a><br/>';
	if ($user_id == $user->data['user_id'])
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user'] . '">', '</a>');
	}
	else
	{
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN'], '<a href="' . $blog_urls['view_user'] . '">', $user_data->user[$user_id]['username'], '</a>') . '<br/>';
		$message .= sprintf($user->lang['RETURN_BLOG_MAIN_OWN'], '<a href="' . $blog_urls['view_user_self'] . '">', '</a>');
	}

	trigger_error($message);
}
else
{
	confirm_box(false, 'UNDELETE_REPLY');
}

// they pressed No, so redirect them
redirect($blog_urls['view_reply']);
?>