<?php
/**
*
* @package phpBB3 User Blog
* @version $Id:
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (!defined('BLOGS_TABLE'))
{
	if (!isset($table_prefix))
	{
		if (!isset($phpbb_root_path) || !isset($phpEx))
		{
			global $phpbb_root_path, $phpEx;
		}

		include($phpbb_root_path . 'config.' . $phpEx);
		unset($dbpasswd);
		unset($dbuser);
		unset($dbname);
	}

	define('BLOGS_TABLE',					$table_prefix . 'blogs');
	define('BLOGS_ATTACHMENT_TABLE',		$table_prefix . 'blogs_attachment');
	define('BLOGS_CATEGORIES_TABLE',		$table_prefix . 'blogs_categories');
	define('BLOGS_IN_CATEGORIES_TABLE',		$table_prefix . 'blogs_in_categories');
	define('BLOGS_PLUGINS_TABLE',			$table_prefix . 'blogs_plugins');
	define('BLOGS_POLL_OPTIONS_TABLE',		$table_prefix . 'blogs_poll_options');
	define('BLOGS_POLL_VOTES_TABLE',		$table_prefix . 'blogs_poll_votes');
	define('BLOGS_RATINGS_TABLE',			$table_prefix . 'blogs_ratings');
	define('BLOGS_REPLY_TABLE',				$table_prefix . 'blogs_reply');
	define('BLOGS_SUBSCRIPTION_TABLE',		$table_prefix . 'blogs_subscription');
	define('BLOGS_USERS_TABLE',				$table_prefix . 'blogs_users');

	define('BLOG_SEARCH_WORDLIST_TABLE',	$table_prefix . 'blog_search_wordlist');
	define('BLOG_SEARCH_WORDMATCH_TABLE',	$table_prefix . 'blog_search_wordmatch');
	//define('BLOG_SEARCH_RESULTS_TABLE',		$table_prefix . 'blog_search_results');
}
?>