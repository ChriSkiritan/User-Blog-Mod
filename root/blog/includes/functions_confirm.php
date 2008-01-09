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
* Build Confirm
*/
function blog_confirm($title, $explain, $display_vars, $action = 'self')
{
	global $template, $user;

	$submit = (isset($_POST['submit'])) ? true : false;

	if ($submit)
	{
		$settings = request_var('setting', array('' => ''));
		validate_config_vars($display_vars, $settings);

		return $settings;
	}

	if ($action === 'self')
	{
		global $blog_urls;

		if (isset($blog_urls['self']))
		{
			$action = $blog_urls['self'];
		}
		else
		{
			$action = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
		}
	}

	$template->assign_vars(array(
		'L_TITLE'			=> (isset($user->lang[$title])) ? $user->lang[$title] : $title,
		'L_TITLE_EXPLAIN'	=> (isset($user->lang[$explain])) ? $user->lang[$explain] : $explain,

		'U_ACTION'			=> $action,
	));

	foreach ($display_vars as $key => $vars)
	{
		if (strpos($key, 'legend') !== false)
		{
			$template->assign_block_vars('options', array(
				'S_LEGEND'		=> true,
				'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
			);

			continue;
		}

		$type = explode(':', $vars['type']);
		$l_explain = '';
		if ($vars['explain'] && isset($vars['lang_explain']))
		{
			$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
		}
		else if ($vars['explain'])
		{
			$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
		}
		$template->assign_block_vars('options', array(
			'KEY'			=> $key,
			'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
			'S_EXPLAIN'		=> $vars['explain'],
			'TITLE_EXPLAIN'	=> $l_explain,
			'CONTENT'		=> build_blog_cfg_template($type, $key, $vars['default']),
		));
	}

	$template->set_filenames(array(
		'body' => 'blog/blog_confirm.html'
	));

	return 'build';
}

/**
* Build configuration template for confirm pages
*
* Originally from adm/index.php
*/
function build_blog_cfg_template($tpl_type, $name, $default)
{
	global $user, $module;

	$tpl = '';
	$name = 'setting[' . $name . ']';

	switch ($tpl_type[0])
	{
		case 'text':
		case 'password':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $default . '" />';
		break;

		case 'textarea':
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $default . '</textarea>';
		break;

		case 'radio':
			$name_yes	= ($default) ? ' checked="checked"' : '';
			$name_no		= (!$default) ? ' checked="checked"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $name_no . ' class="radio" /> ' . (($type_no) ? $user->lang['NO'] : $user->lang['DISABLED']) . '</label>';
			$tpl_yes = '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="1"' . $name_yes . ' class="radio" /> ' . (($type_no) ? $user->lang['YES'] : $user->lang['ENABLED']) . '</label>';

			$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . ' ' . $tpl_no : $tpl_no . ' ' . $tpl_yes;
		break;
	}

	return $tpl;
}

/**
* Going through a config array and validate values.
*
* From adm/index.php
*/
function validate_config_vars($config_vars, &$cfg_array)
{
	global $phpbb_root_path, $user;

	foreach ($config_vars as $config_name => $config_definition)
	{
		if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
		{
			continue;
		}

		if (!isset($config_definition['validate']))
		{
			continue;
		}

		// Validate a bit. ;) String is already checked through request_var(), therefore we do not check this again
		switch ($config_definition['validate'])
		{
			case 'bool':
				$cfg_array[$config_name] = ($cfg_array[$config_name]) ? true : false;
			break;

			case 'int':
				$cfg_array[$config_name] = (int) $cfg_array[$config_name];
			break;
		}
	}
}
?>