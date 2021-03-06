<?php
/**
*
* @package phpBB3 User Blog
* @version $Id$
* @copyright (c) 2008 EXreaction
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/*
* To add a new user selectable style, you must add just the following line and put it in a file named style.php
* The name field is what is shown to the user, the value field is the location of the style off of the blog/styles/ directory.
*/
$available_styles[] = array('name' => 'Coda', 'value' => 'coda', 'blog_css' => true, 'demo' => $phpbb_root_path . 'blog/styles/coda/demo.png');

?>