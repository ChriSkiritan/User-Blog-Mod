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
* URL Replace
*
* Replaces tags and other items that could break the URL's
*/
function url_replace($url)
{
	$match = array('-', '?', '/', '\\', '\'', '&amp;', '&lt;', '&gt;', '&quot;', ':');

	// First replace all the above items with nothing, then replace spaces with _, then replace 3 _ in a row with a 1 _
	return str_replace(array(' ', '___'), '_', str_replace($match, '', $url));
}

/**
* URL handler
*/
function blog_url($user_id, $blog_id = false, $reply_id = false, $url_data = array(), $extra_data = array(), $force_no_seo = false)
{
	global $config, $phpbb_root_path, $phpEx, $user, $_SID;
	global $blog_data, $reply_data, $user_data;

	// don't call the generate_board_url function a whole bunch of times, get it once and keep using it!
	static $start_url = '';
	$start_url = ($start_url == '') ? generate_board_url() . '/' : $start_url;
	$extras = $anchor = '';

	if (isset($_GET['style']))
	{
		$url_data['style'] = $_GET['style'];
	}

	if (isset($config['user_blog_seo']) && $config['user_blog_seo'] && !$force_no_seo)
	{
		$title_match ='/([^a-zA-Z0-9\s_])/'; // Replace HTML Entities, and non alphanumeric/space/underscore characters
		$replace_page = true; // match everything except the page if this is set to false

		if (!isset($url_data['page']) && $user_id !== false)
		{
			// Do not do the str_replace for the username!  It would break it! :P
			$replace_page = false;

			if ($user_id == $user->data['user_id'])
			{
				$url_data['page'] = $user->data['username'];
			}
			else if (isset($extra_data['username']))
			{
				$url_data['page'] = $extra_data['username'];
			}
			else if (!empty($user_data))
			{
				if (!isset($user_data->user[$user_id]))
				{
					$user_data->get_user_data($user_id);
				}
				$url_data['page'] = $user_data->user[$user_id]['username'];
			}
		}
		else
		{
			$url_data['u'] = ($user_id) ? $user_id : '*skip*';
			$url_data['b'] = ($blog_id) ? $blog_id : '*skip*';
			$url_data['r'] = ($reply_id) ? $reply_id : '*skip*';
		}

		if ($reply_id)
		{
			$url_data['r'] = $reply_id;
			$url_data['anchor'] = (isset($url_data['anchor'])) ? $url_data['anchor'] : 'r' . $reply_id;
			if (!isset($url_data['mode']))
			{
				if (!empty($reply_data) && array_key_exists($reply_id, $reply_data->reply))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $reply_data->reply[$reply_id]['reply_subject']));
				}
				else if (array_key_exists('reply_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['reply_subject']));
				}
			}
		}
		else if ($blog_id)
		{
			$url_data['b'] = $blog_id;
			if (!isset($url_data['mode']))
			{
				if (!empty($blog_data) && array_key_exists($blog_id, $blog_data->blog))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $blog_data->blog[$blog_id]['blog_subject']));
				}
				else if (array_key_exists('blog_subject', $extra_data))
				{
					$url_data['mode'] = utf8_clean_string(preg_replace($title_match, '', $extra_data['blog_subject']));
				}
			}
		}

		if (isset($url_data['anchor']))
		{
			$anchor = '#' . $url_data['anchor'];
		}

		if (count($url_data))
		{
			foreach ($url_data as $name => $value)
			{
				if ($name == 'page' || $name == 'mode' || $name == 'anchor' || $value == '*skip*')
				{
					continue;
				}
				$extras .= '_' . url_replace($name) . '-' . url_replace($value);
			}
		}

		// Add the Session ID if required, do not add it for guests.
		if ($_SID && $user->data['is_registered'])
		{
			$extras .= "_sid-{$_SID}";
		}

		if (isset($url_data['page']))
		{
			if ($replace_page)
			{
				$url_data['page'] = url_replace($url_data['page']);
			}

			if (isset($url_data['mode']))
			{
				$url_data['mode'] = url_replace($url_data['mode']);
				$return = "blog/{$url_data['page']}/{$url_data['mode']}{$extras}.html{$anchor}";
			}
			else
			{
				if ($extras != '')
				{
					$return = "blog/{$url_data['page']}/index{$extras}.html{$anchor}";
				}
				else
				{
					$return = "blog/{$url_data['page']}/";
				}
			}
		}
		else
		{
			$return = "blog/index{$extras}.html{$anchor}";
		}

		if (isset($return))
		{
			return $start_url . $return;
		}
	}

	if (count($url_data))
	{
		foreach ($url_data as $name => $var)
		{
			// Do not add the blog/reply/user id to the url string, they get added later
			if ($name == 'b' || $name == 'u' || $name == 'r' || $var == '*skip*')
			{
				continue;
			}

			$extras .= '&amp;' . $name . '=' . $var;
		}
	}

	$extras .= (($user_id) ? '&amp;u=' . $user_id : '');
	$extras .= (($blog_id) ? '&amp;b=' . $blog_id : '');
	$extras .= (($reply_id) ? '&amp;r=' . $reply_id . '#r' . $reply_id: '');
	$extras = substr($extras, 5);
	$url = $phpbb_root_path . 'blog.' . $phpEx;
	return append_sid($url, $extras);
}

/**
* generates the basic URL's used by this mod
*/
function generate_blog_urls()
{
	global $phpbb_root_path, $phpEx, $config, $user;
	global $blog_id, $reply_id, $user_id, $start, $category_id;
	global $blog_data, $reply_data, $user_data, $blog_urls, $blog_plugins;

	static $blog_categories = false;

	if (!function_exists('get_blog_categories'))
	{
		include("{$phpbb_root_path}blog/includes/functions_categories.$phpEx");
	}

	if ($blog_categories === false)
	{
		$blog_categories = get_blog_categories('category_id');
	}

	$self_data = $_GET;

	$blog_urls = array(
		'main'				=> blog_url(false),
		'self'				=> blog_url($user_id, $blog_id, $reply_id, $self_data),
		'self_print'		=> blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => 'print'))),
		'subscribe'			=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'subscribe')) : '',
		'unsubscribe'		=> ($config['user_blog_subscription_enabled'] && ($blog_id != 0 || $user_id != 0) && $user->data['user_id'] != ANONYMOUS) ? blog_url($user_id, $blog_id, false, array('page' => 'unsubscribe')) : '',

		'add_blog'			=> blog_url(false, false, false, array('page' => 'blog', 'mode' => 'add', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))),
		'add_reply'			=> ($blog_id) ? blog_url($user_id, $blog_id, false, array('page' => 'reply', 'mode' => 'add', 'c' => (($category_id && isset($blog_categories[$category_id])) ? $category_id : '*skip*'))) : '',

		'view_blog'			=> ($blog_id) ? (($category_id && isset($blog_categories[$category_id])) ? blog_url(false, $blog_id, false, array('page' => $blog_categories[$category_id]['category_name'], 'c' => $category_id)) : blog_url($user_id, $blog_id)) : '',
		'view_reply'		=> ($reply_id) ? (($category_id && isset($blog_categories[$category_id])) ? blog_url(false, $blog_id, $reply_id, array('page' => $blog_categories[$category_id]['category_name'], 'c' => $category_id)) : blog_url($user_id, $blog_id, $reply_id)) : '',
		'view_user'			=> ($user_id) ? blog_url($user_id) : false,
		'view_user_deleted'	=> ($user_id) ? blog_url($user_id, false, false, array('mode' => 'deleted')) : false,
		'view_user_self'	=> blog_url($user->data['user_id']),
	);

	$blog_urls['self_minus_print'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('view' => '*skip*')));
	$blog_urls['self_minus_start'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*skip*')));
	$blog_urls['start_zero'] = blog_url($user_id, $blog_id, $reply_id, array_merge($self_data, array('start' => '*start*')));

	if (method_exists($blog_plugins, 'plugin_do'))
	{
		$blog_plugins->plugin_do('function_generate_blog_urls');
	}
}
?>