<?php
/**
*
* @package phpBB3 User Blog
* @copyright (c) 2007 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Handle basic posting setup and some basic checks
*/
function handle_basic_posting_data($check = false, $page = 'blog', $mode = 'add')
{
	global $auth, $blog_attachment, $blog_id, $blog_plugins, $config, $db, $template, $user, $phpbb_root_path, $phpEx;

	$submit = (isset($_POST['submit'])) ? true : false;
	$preview = (isset($_POST['preview'])) ? true : false;
	$refresh = (isset($_POST['add_file']) || isset($_POST['delete_file']) || isset($_POST['cancel_unglobalise'])) ? true : false;
	$submitted = ($submit || $preview || $refresh) ? true : false; // shortcut for any of the 3 above

	if ($check)
	{
		$error = array();

		// check the captcha
		if ($mode == 'add')
		{
			if (!handle_captcha('check'))
			{
				$error[] = $user->lang['CONFIRM_CODE_WRONG'];
			}
		}

		// check the form key
		if (!check_form_key('postform'))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}

		return $error;
	}
	else
	{
		$above_subject = $above_message = $above_submit = $panel_data = '';

		$panels = array(
			'options-panel'			=> $user->lang['OPTIONS'],
		);

		if ($page == 'blog')
		{
			// The category display box
			$category = request_var('category', array('' => ''));
			if (!count($category))
			{
				$category = request_var('c', 0);
			}
			$category_list = make_category_select($category);

			if ($category_list)
			{
				$panels['categories-panel'] = $user->lang['CATEGORIES'];
			}

			if ($user->data['is_registered'])
			{
				// Build permissions box
				permission_settings_builder(true, $mode);
				$panels['permissions-panel'] = $user->lang['PERMISSIONS'];
			}

			// Some variables
			$template->assign_vars(array(
				'CATEGORY_LIST'				=> $category_list,

				'S_CAT_0_SELECTED'			=> ((is_array($category) && in_array(0, $category)) || $category === 0),
				'S_SHOW_CATEGORY_BOX'		=> ($category_list) ? true : false,
				'S_SHOW_PERMISSIONS_BOX'	=> true,
			));
		}

		if ($mode == 'add')
		{
			// setup the captcha
			handle_captcha('build');
		}

		// Subscriptions
		if ($config['user_blog_subscription_enabled'])
		{
			$panels['subscriptions-panel'] = $user->lang['SUBSCRIPTION'];

			$subscription_types = get_blog_subscription_types();
			$subscribed = array();

			if ($page == 'blog' && $mode == 'add' && !$submitted)
			{
				// check default subscription settings from user_settings
				global $user_settings;
				get_user_settings($user->data['user_id']);

				if (isset($user_settings[$user->data['user_id']]))
				{
					foreach ($subscription_types as $type => $name)
					{
						// Bitwise check
						if ($user_settings[$user->data['user_id']]['blog_subscription_default'] & $type)
						{
							$subscribed[$type] = true;
						}
					}
				}
			}
			else if (!$submitted)
			{
				// check set subscription settings
				$sql = 'SELECT * FROM ' . BLOGS_SUBSCRIPTION_TABLE . '
					WHERE sub_user_id = ' . $user->data['user_id'] . '
						AND blog_id = ' . intval($blog_id);
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$subscribed[$row['sub_type']] = true;
				}
			}

			foreach ($subscription_types as $type => $name)
			{
				$template->assign_block_vars('subscriptions', array(
					'TYPE'		=> 'subscription_' . $type,
					'NAME'		=> ((isset($user->lang[$name])) ? $user->lang[$name] : $name),
					'S_CHECKED'	=> ((($submitted && request_var('subscription_' . $type, false)) || isset($subscribed[$type])) ? true : false),
				));
			}
		}

		// Attachments
		$attachment_data = $blog_attachment->attachment_data;
		$filename_data = $blog_attachment->filename_data;
		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || @ini_get('file_uploads') == '0' || !$config['allow_attachments'] || !$auth->acl_get('u_attach')) ? '' : ' enctype="multipart/form-data"';
		posting_gen_inline_attachments($attachment_data);
		if (($auth->acl_get('u_blogattach')) && $config['allow_attachments'] && $form_enctype)
		{
			$allowed_extensions = $blog_attachment->obtain_blog_attach_extensions();

			if (count($allowed_extensions['_allowed_']))
			{
				$blog_attachment->posting_gen_attachment_entry($attachment_data, $filename_data);

				$panels['attach-panel'] = $user->lang['ADD_ATTACHMENT'];
			}
		}

		// Add the forum key
		add_form_key('postform');

		// Generate smiley listing
		generate_smilies('inline', false);

		// Build custom bbcodes array
		display_custom_bbcodes();

		$temp = compact('page', 'mode', 'panels', 'panel_data', 'above_subject', 'above_message', 'above_submit');
		$blog_plugins->plugin_do_ref('function_handle_basic_posting_data', $temp);
		extract($temp);

		$template->assign_vars(array(
			'EXTRA_ABOVE_SUBJECT'		=> $above_subject,
			'EXTRA_ABOVE_MESSAGE'		=> $above_message,
			'EXTRA_ABOVE_SUBMIT'		=> $above_submit,
			'EXTRA_PANELS'				=> $panel_data,
			'JS_PANELS_LIST'			=> "'" . implode("', '", array_keys($panels)) . "'",

			'UA_PROGRESS_BAR'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=popup", false),

			'S_BLOG'					=> ($page == 'blog') ? true : false,
			'S_REPLY'					=> ($page == 'reply') ? true : false,
			'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,
			'S_FORM_ENCTYPE'			=> $form_enctype,
		));

		foreach ($panels as $name => $title)
		{
			$template->assign_vars(array(
				'S_' . strtoupper(str_replace('-', '_', $name))		=> true,
			));
			$template->assign_block_vars('panel_list', array(
				'NAME'		=> $name,
				'TITLE'		=> $title,
			));
		}
	}
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
	global $db, $template, $phpbb_root_path, $phpEx, $user, $config, $s_hidden_fields, $blog_plugins;

	if ($user->data['user_id'] != ANONYMOUS || !$config['user_blog_guest_captcha'])
	{
		return true;
	}

	$blog_plugins->plugin_do_arg('function_handle_captcha', $mode);

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
			$message = sprintf($user->lang['BLOG_REPORT_PM'], $user->data['username'], blog_url($user->data['user_id'], $id));
			$subject = $user->lang['BLOG_REPORT_PM_SUBJECT'];
			break;
		case 'reply_report' :
			$message = sprintf($user->lang['REPLY_REPORT_PM'], $user->data['username'], blog_url($user->data['user_id'], false, $id));
			$subject = $user->lang['REPLY_REPORT_PM_SUBJECT'];
			break;
		case 'blog_approve' :
			$message = sprintf($user->lang['BLOG_APPROVE_PM'], $user->data['username'], blog_url($user->data['user_id'], $id));
			$subject = $user->lang['BLOG_APPROVE_PM_SUBJECT'];
			break;
		case 'reply_approve' :
			$message = sprintf($user->lang['REPLY_APPROVE_PM'], $user->data['username'], blog_url($user->data['user_id'], false, $id));
			$subject = $user->lang['REPLY_APPROVE_PM_SUBJECT'];
			break;
		default:
			$blog_plugins->plugin_do_arg('function_inform_approve_report', compact('mode', 'id'));
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
		'from_username'		=> $user->lang['ADMINISTRATOR'],
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
 * Check permission and settings for bbcode, img, url, etc
 */
class post_options
{
	// the permissions, so I can change them later easier if need be for a different mod or whatever...
	var $auth_bbcode = false;
	var $auth_smilies = false;
	var $auth_img = false;
	var $auth_url = false;
	var $auth_flash = false;

	// whether these are allowed or not
	var $bbcode_status = false;
	var $smilies_status = false;
	var $img_status = false;
	var $url_status = false;
	var $flash_status = false;

	// whether or not they are enabled in the post
	var $enable_bbcode = false;
	var $enable_smilies = false;
	var $enable_magic_url = false;

	/**
	 * Automatically sets the defaults for the $auth_ vars
	 */
	function post_options()
	{
		global $auth, $blog_plugins;

		$this->auth_bbcode = ($auth->acl_get('u_blogbbcode')) ? true : false;
		$this->auth_smilies = ($auth->acl_get('u_blogsmilies')) ? true : false;
		$this->auth_img = ($auth->acl_get('u_blogimg')) ? true : false;
		$this->auth_url = ($auth->acl_get('u_blogurl')) ? true : false;
		$this->auth_flash = ($auth->acl_get('u_blogflash')) ? true : false;

		$blog_plugins->plugin_do('post_options');
	}

	/**
	 * set the status to the  variables above, the enabled options are if they are enabled in the posts(by who ever is posting it)
	 */
	function set_status($bbcode, $smilies, $url)
	{
		global $config, $auth, $blog_plugins;

		$this->bbcode_status = ($config['allow_bbcode'] && $this->auth_bbcode) ? true : false;
		$this->smilies_status = ($config['allow_smilies'] && $this->auth_smilies) ? true : false;
		$this->img_status = ($this->auth_img && $this->bbcode_status) ? true : false;
		$this->url_status = ($config['allow_post_links'] && $this->auth_url && $this->bbcode_status) ? true : false;
		$this->flash_status = ($this->auth_flash && $this->bbcode_status) ? true : false;

		$this->enable_bbcode = ($this->bbcode_status && $bbcode) ? true : false;
		$this->enable_smilies = ($this->smilies_status && $smilies) ? true : false;
		$this->enable_magic_url = ($this->url_status && $url) ? true : false;

		$blog_plugins->plugin_do('post_options_set_status');
	}

	/**
	 * Set the options in the template
	 */
	function set_in_template()
	{
		global $template, $user, $phpbb_root_path, $phpEx, $blog_plugins;

		// Assign some variables to the template parser
		$template->assign_vars(array(
			// If they hit preview or submit and got an error, or are editing their post make sure we carry their existing post info & options over
			'S_BBCODE_CHECKED'			=> ($this->enable_bbcode) ? '' : ' checked="checked"',
			'S_SMILIES_CHECKED'			=> ($this->enable_smilies) ? '' : ' checked="checked"',
			'S_MAGIC_URL_CHECKED'		=> ($this->enable_magic_url) ? '' : ' checked="checked"',

			// To show the Options: section on the bottom left
			'BBCODE_STATUS'				=> ($this->bbcode_status) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'				=> ($this->img_status) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'				=> ($this->flash_status) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'			=> ($this->smilies_status) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'				=> ($this->url_status) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],

			// To show the option to turn each off while posting
			'S_BBCODE_ALLOWED'			=> $this->bbcode_status,
			'S_SMILIES_ALLOWED'			=> $this->smilies_status,
			'S_LINKS_ALLOWED'			=> $this->url_status,

			// To show the BBCode buttons for each on top
			'S_BBCODE_IMG'				=> $this->img_status,
			'S_BBCODE_URL'				=> $this->url_status,
			'S_BBCODE_FLASH'			=> $this->flash_status,
		));

		$blog_plugins->plugin_do('post_options_set_in_template');
	}
}
?>