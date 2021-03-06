<?php
/**
*
* @package phpBB3 User Blog Custom Title
* @version $Id: functions.php 245 2008-02-09 04:02:27Z exreaction@gmail.com $
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

function custom_title_user_handle_data(&$args)
{	
	global $config;

	if (blog_data::$user[$args['USER_ID']]['user_custom_title'] != '')
	{
		switch ($config['custom_title_mode'])
		{
			case CUSTOM_TITLE_MODE_INDEPENDENT:
				$args['CUSTOM_TITLE'] = blog_data::$user[$args['USER_ID']]['user_custom_title'];
				break;
			case CUSTOM_TITLE_MODE_REPLACE_RANK:
				$args['RANK_TITLE'] = blog_data::$user[$args['USER_ID']]['user_custom_title'];
				break;
			case CUSTOM_TITLE_MODE_REPLACE_BOTH:
				$args['RANK_TITLE'] = blog_data::$user[$args['USER_ID']]['user_custom_title'];
				$args['RANK_IMG'] = '';
				$args['RANK_IMG_SRC'] = '';
				break;
			default:
				break;
		}
	}
}
?>