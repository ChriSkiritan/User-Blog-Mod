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

if (!$config['user_blog_enable_ratings'])
{
	trigger_error('USER_BLOG_RATINGS_DISABLED');
}

if ($blog_id == 0)
{
	trigger_error('BLOG_NOT_EXIST');
}

$delete_id = request_var('delete', 0);
$rating = request_var('rating', ($config['user_blog_min_rating'] - 1));
$rating_data = get_user_blog_rating_data($user->data['user_id']);
$did_something = false;

if (!$delete_id && $rating != $config['user_blog_min_rating'] - 1 && !isset($rating_data[$blog_id]))
{
	$sql_data = array(
		'blog_id'		=> intval($blog_id),
		'user_id'		=> $user->data['user_id'],
		'rating'		=> $rating,
	);

	$sql = 'INSERT INTO ' . BLOGS_RATINGS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_data);
	$db->sql_query($sql);

	$did_something = true;
}
else if ($delete_id && isset($rating_data[$blog_id]))
{
	$sql = 'DELETE FROM ' . BLOGS_RATINGS_TABLE . ' WHERE blog_id = ' . $delete_id . ' AND user_id = ' . $user->data['user_id'];
	$db->sql_query($sql);

	$did_something = true;
}

if ($did_something)
{
	$total_rating = $total_count = 0;
	$sql = 'SELECT * FROM ' . BLOGS_RATINGS_TABLE . ' WHERE blog_id = ' . intval($blog_id);
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$total_rating += $row['rating'];
		$total_count++;
	}
	$db->sql_freeresult($result);

	$average_rating = ($total_count) ? ceil($total_rating / $total_count) : 0;

	$sql = 'UPDATE ' . BLOGS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array('rating' => $average_rating, 'num_ratings' => $total_count)) . ' WHERE blog_id = ' . intval($blog_id);
	$db->sql_query($sql);

	$cache->destroy('_blog_rating_' . $user->data['user_id']);
}

redirect($blog_urls['view_blog']);
?>