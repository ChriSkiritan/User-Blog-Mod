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
 *  Check blog permissions
 *
 * @param string $page The page requested - blog, reply, mcp, install, upgrade, update, dev, resync
 * @param string $mode The mode requested - depends on the $page requested
 * @param bool $return If you would like this function to return true or false (if they have permission or not).  If it is false we give them a login box if they are not logged in, or give them the NO_AUTH error message
 * @param int $blog_id The blog_id requested (needed for some things, like blog edit, delete, etc
 * @param int $reply_id The reply_id requested, used for the same reason as $blog_id
 *
 * @return Returns
 *	- true if the user is authorized to do the requested action
 *	- false if the user is not authorized to do the requested action
 */
function check_blog_permissions($page, $mode, $return = false, $blog_id = 0, $reply_id = 0)
{
	global $user, $auth, $blog_data;

	// lets automatically give founders ALL permissions
	if ($user->data['user_type'] == USER_FOUNDER)
	{
		return true;
	}

	$is_auth = false;
	$founder = false;

	switch ($page)
	{
		case 'blog' :
			switch ($mode)
			{
				case 'add' :
					$is_auth = ($auth->acl_get('u_blogpost')) ? true : false;
					break;
				case 'edit' :
					$is_auth = ($user->data['user_id'] != ANONYMOUS && ($auth->acl_get('u_blogedit') && ($user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) ) || $auth->acl_get('m_blogedit')) ? true : false;
					break;
				case 'delete' :
					if ($blog_data->blog[$blog_id]['blog_deleted'] == 0 || $auth->acl_get('a_blogdelete'))
					{
						$is_auth = ($user->data['user_id'] != ANONYMOUS && ($auth->acl_get('u_blogdelete') && $user->data['user_id'] == $blog_data->blog[$blog_id]['user_id']) || $auth->acl_get('m_blogdelete') || $auth->acl_get('a_blogdelete')) ? true : false;
					}
					break;
				case 'undelete' :
					$is_auth = ($auth->acl_gets('m_blogdelete', 'a_blogdelete')) ? true : false;
					break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
					break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogapprove')) ? true : false;
					break;
				case 'no_approve' :
					$is_auth = ($auth->acl_get('u_blognoapprove')) ? true : false;
			}
			break;
		case 'reply' :
			switch ($mode)
			{
				case 'add' :
				case 'quote' :
						$is_auth = ($auth->acl_get('u_blogreply')) ? true : false;
					break;
				case 'edit' :
					$is_auth = ($user->data['user_id'] != ANONYMOUS && ( (($auth->acl_get('u_blogreplyedit')) && ($user->data['user_id'] == $blog_data->reply[$reply_id]['user_id'])) || $auth->acl_get('m_blogreplyedit'))) ? true : false;
					break;
				case 'delete' :
					if ($blog_data->reply[$reply_id]['reply_deleted'] == 0 || $auth->acl_get('a_blogreplydelete'))
					{
						$is_auth = ($user->data['user_id'] != ANONYMOUS && ($auth->acl_gets('a_blogreplydelete', 'm_blogreplydelete') || ($auth->acl_get('u_blogreplydelete') && $user->data['user_id'] == $blog_data->reply[$reply_id]['user_id']) ) ) ? true : false;
					}
					break;
				case 'undelete' :
					$is_auth = ($auth->acl_get('m_blogreplydelete') || $auth->acl_get('a_blogreplydelete')) ? true : false;
					break;
				case 'report' :
					$is_auth = ($auth->acl_get('u_blogreport')) ? true : false;
					break;
				case 'approve' :
					$is_auth = ($auth->acl_get('m_blogreplyapprove')) ? true : false;
					break;
				case 'no_approve' :
					$is_auth = ($auth->acl_get('u_blogreplynoapprove')) ? true : false;
			}
			break;
		case 'mcp' :
			$is_auth = ($auth->acl_gets('m_blogapprove', 'acl_m_blogreport')) ? true : false;
			break;
		case 'install' :
		case 'update' :
		case 'upgrade' :
		case 'dev' :
		case 'resync' :
			$is_auth = ($user->data['user_type'] == USER_FOUNDER) ? true : false;
			$founder = true;
			break;
	}

	// if they do not have viewing permissions they should not be able to do anything.
	$is_auth = (!$auth->acl_get('u_blogview')) ? false : $is_auth;

	if (!$return)
	{
		if (!$is_auth)
		{
			if (!$user->data['is_registered'])
			{
				login_box();
			}
			else
			{
				if ($founder)
				{
					trigger_error('MUST_BE_FOUNDER');
				}
				else
				{
					trigger_error('NO_AUTH_OPERATION');
				}
			}
		}
	}
	else
	{
		return $is_auth;
	}
}
?>