<?php
/**
 *
 * @package phpBB3 User Blog
 * @copyright (c) 2007 EXreaction, Lithium Studios
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
	'ACP_BLOGS'							=> 'User Blog Mod',
	'ADD_BLOG'							=> 'Add a new blog',
	'ALREADY_INSTALLED'					=> 'You have already installed the user blog mod.<br/><br/>Click %shere%s to return to the main blog page.',
	'ALREADY_UPDATED'					=> 'You are running the latest version of the User Blog Mod.<br/><br/>Click %shere%s to return to the main blog page.',
	'APPROVE_BLOG'						=> 'Approve Blog',
	'APPROVE_BLOG_CONFIRM'				=> 'Are you sure you want to approve this blog?',
	'APPROVE_BLOG_SUCCESS'				=> 'You have successfully approved this blog for public viewing.',
	'APPROVE_REPLY'						=> 'Approve Reply',
	'APPROVE_REPLY_CONFIRM'				=> 'Are you sure you want to approve this reply?',
	'APPROVE_REPLY_SUCCESS'				=> 'You have successfully approved this reply for public viewing.',
	'ARCHIVES'							=> 'Archives',
	'AUTHOR_CONTACT'					=> 'Contact & Info',

	'BLOG'								=> 'Blog',
	'BLOGS_COUNT'						=> '%s blogs',
	'BLOG_ALREADY_APPROVED'				=> 'This blog is already approved.',
	'BLOG_ALREADY_DELETED'				=> 'This blog has already been deleted.',
	'BLOG_ALWAYS_SHOW_URL'				=> 'Always show blog link in profile',
	'BLOG_ALWAYS_SHOW_URL_EXPLAIN'		=> 'If this is set to no it will not show the Blog link in each users\'s profile unless they have posted a blog.',
	'BLOG_APPROVE_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just posted <a href="%2$s">this blog</a> and it needs approval before it is publically viewable.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'BLOG_APPROVE_PM_SUBJECT'			=> 'Blog Approval Needed!',
	'BLOG_AUTHOR'						=> 'About the Author',
	'BLOG_COUNT'						=> '1 blog',
	'BLOG_CREDITS'						=> 'Blog System powered by <a href="http://www.lithiumstudios.org/">User Blog Mod</a> &copy; 2007 EXreaction',
	'BLOG_DELETED'						=> 'Blog has been deleted.',
	'BLOG_EDIT_LOCKED'					=> 'This blog is locked for editing.',
	'BLOG_INFO'							=> 'About the Blog',
	'BLOG_INFORM'						=> 'Users to inform of reports or posts needing approval via PM',
	'BLOG_INFORM_EXPLAIN'				=> 'Enter the user_id\'s of the users you want to receive a Private Message when a blog or reply is reported, or a blog or reply is newly posted and needs approval.  Separate multiple users by a comma, do not add spaces.',
	'BLOG_IS_DELETED'					=> 'This blog was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this blog.',
	'BLOG_MCP'							=> 'Blog Moderator CP',
	'BLOG_NEED_APPROVE'					=> 'A moderator or administrator must approve your blogs before they are public.',
	'BLOG_NOT_DELETED'					=> 'This blog is not deleted.  Why are you trying to un-delete it?',
	'BLOG_POST_IP'						=> 'IP Address used to post',
	'BLOG_REPLIES'						=> 'There are <b>%1$s</b> %2$sreplies%3$s to this blog',
	'BLOG_REPLY'						=> 'There is <b>1</b> %sreply%s to this blog',
	'BLOG_REPLY_COUNT'					=> 'Reply count',
	'BLOG_REPORTED'						=> 'Blog has been reported, click to close the report',
	'BLOG_REPORTED_SHORT'				=> 'Blog has been reported',
	'BLOG_REPORT_CONFIRM'				=> 'Are you sure you want to report this blog?',
	'BLOG_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this blog</a>.<br/>Please take the time to read over the blog and decide what needs to be done.',
	'BLOG_REPORT_PM_SUBJECT'			=> 'Blog Reported!',
	'BLOG_SETTINGS'						=> 'Blog Settings',
	'BLOG_SETTINGS_EXPLAIN'				=> 'Here you can set the settings for the User Blog mod.',
	'BLOG_SUBJECT'						=> 'Blog Subject',
	'BLOG_UNAPPROVED'					=> 'Blog Needs Approval',
	'BLOG_UNDELETED'					=> 'The blog has been un-deleted.',
	'BLOG_VIEW'							=> 'This blog has been viewed <b>1</b> time',
	'BLOG_VIEWS'						=> 'This blog has been viewed <b>%s</b> times',

	'CONTINUED'							=> 'Continued',

	'DEFAULT_TEXT_LIMIT'				=> 'Default text limit for main blog pages',
	'DEFAULT_TEXT_LIMIT_EXPLAIN'		=> 'After this amount it will trim the rest of the text out of a message (to shorten it)',
	'DELETED_BLOGS'						=> 'Deleted Blogs',
	'DELETED_MESSAGE'					=> 'These blogs have all been deleted.',
	'DELETED_MESSAGE_EXPLAIN'			=> 'There is a link in every "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_MESSAGE_EXPLAIN_SINGLE'	=> 'There is a link in the "This blog was deleted by..." section to un-delete the blog.',
	'DELETED_MESSAGE_SINGLE'			=> 'This blog has been deleted.',
	'DELETED_REPLY_SHOW'				=> 'This reply has been soft deleted.  Click here to show the reply.',
	'DELETED_REPLY_SHOW_NO_JS'			=> 'You must enable Javascript to view this post.',
	'DELETE_BLOG'						=> 'Delete Blog',
	'DELETE_BLOG_CONFIRM'				=> 'Are you sure you want to delete this blog?',
	'DELETE_BLOG_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this blog',
	'DELETE_REPLY'						=> 'Delete Reply',
	'DELETE_REPLY_CONFIRM'				=> 'Are you sure you want to delete this reply?',
	'DELETE_REPLY_WARN'					=> 'Once deleted, only a moderator or administrator can un-delete this reply',
	'DISAPPROVED_BLOGS'					=> 'These blogs need approval before they can be viewed by the public.',
	'DISAPPROVED_REPLIES'				=> 'These replies need approval before they can be viewed by the public.',

	'EDIT_BLOG'							=> 'Edit Blog',
	'EDIT_REPLY'						=> 'Edit Reply',
	'ENABLE_BLOG_CUSTOM_PROFILES'		=> 'Display custom profile fields in the User Blog pages',
	'ENABLE_USER_BLOG'					=> 'Enable or Disable the entire User Blog Mod.',
	'ENABLE_USER_BLOG_EXPLAIN'			=> 'Note that the ACP sections of the User Blog Mod will always stay enabled as long as it is installed.',

	'FEED'								=> 'Blog Feed',
	'FOUNDER_ALL_PERMISSION'			=> 'Give Board Founders all permissions for the User Blog Mod.',

	'INSTALL_BLOG_DB'					=> 'Install User Blog Mod',
	'INSTALL_BLOG_DB_CONFIRM'			=> 'Are you ready to install the database section of this mod?',
	'INSTALL_BLOG_DB_SUCCESS'			=> 'You have successfully installed the database section of the User Blog mod.<br/><br/>Click %shere%s to return to the main User Blogs page.',
	'INSTALL_IN_FILES_FIRST'			=> 'Do the file edits for this mod before you install it to the database.',

	'LIMIT'								=> 'Limit',
	'LOGIN_EXPLAIN_EDIT_BLOG'			=> 'You must log in before editing a blog.',
	'LOGIN_EXPLAIN_NEW_BLOG'			=> 'You must log in before creating a new blog.',
	'LOG_CONFIG_BLOG'					=> '<strong>Altered Blog Settings</strong>',

	'MUST_BE_FOUNDER'					=> 'You must be a board founder to access this page.',
	'MY_BLOGS'							=> 'My Blogs',

	'NO_BLOG'							=> 'The requested blog does not exist.',
	'NO_BLOGS'							=> 'There are no blogs.',
	'NO_BLOGS_USER'						=> 'No blogs have been posted by this user.',
	'NO_BLOGS_USER_SORT_DAYS'			=> 'No blogs were posted by this user in the last %s.',
	'NO_DELETED_BLOGS'					=> 'There are no deleted blogs by this user.',
	'NO_DELETED_BLOGS_SORT_DAYS'		=> 'No deleted blogs were posted by this user in the last %s.',
	'NO_REPLIES'						=> 'There are no replies',
	'NO_REPLY'							=> 'The requested reply does not exist.',

	'PERMANENTLY_DELETE_BLOG_CONFIRM'	=> 'Are you sure you want to permanently delete this blog?  This can not be un-done.',
	'PERMANENTLY_DELETE_REPLY_CONFIRM'	=> 'Are you sure you want to permanently delete this reply?  This can not be un-done.',
	'POPULAR_BLOGS'						=> 'Popular Blogs',
	'POST_A'							=> 'Post a new blog',
	'POST_A_REPLY'						=> 'Post a new reply',

	'RANDOM_BLOGS'						=> 'Random Blogs',
	'RECENT_BLOGS'						=> 'Recent Blogs',
	'REPLIES_COUNT'						=> '%s replies',
	'REPLY'								=> 'Reply',
	'REPLY_ALREADY_APPROVED'			=> 'This reply is already approved.',
	'REPLY_ALREADY_DELETED'				=> 'This reply has already been deleted.',
	'REPLY_APPROVE_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just posted <a href="%2$s">this reply</a> and it needs approval before it is publically viewable.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_APPROVE_PM_SUBJECT'			=> 'Blog Reply Approval Needed!',
	'REPLY_COUNT'						=> '1 reply',
	'REPLY_DELETED'						=> 'Reply has been deleted.',
	'REPLY_EDIT_LOCKED'					=> 'This reply is locked for editing.',
	'REPLY_IS_DELETED'					=> 'This reply was deleted by %1$s on %2$s.  Click <b>%3$shere%4$s</b> to un-delete this reply.',
	'REPLY_NEED_APPROVE'				=> 'A moderator or administrator must approve your replies before they are public.',
	'REPLY_NOT_DELETED'					=> 'This reply is not deleted.  Why are you trying to un-delete it?',
	'REPLY_REPORTED'					=> 'Reply has been reported, click to close the report',
	'REPLY_REPORT_CONFIRM'				=> 'Are you sure you want to report this reply?',
	'REPLY_REPORT_PM'					=> 'This is an automatically dispatched message from the User Blog Mod.<br/><br/>%1$s has just reported <a href="%2$s">this reply</a>.<br/>Please take the time to read over the reply and decide what needs to be done.',
	'REPLY_REPORT_PM_SUBJECT'			=> 'Blog Reply Reported!',
	'REPLY_SUBMITTED'					=> 'Your reply has been submitted!',
	'REPLY_UNAPPROVED'					=> 'Reply Needs Approval',
	'REPLY_UNDELETED'					=> 'The reply has been un-deleted.',
	'REPORTED_BLOGS'					=> 'These blogs have been reported by users.',
	'REPORTED_REPLIES'					=> 'These replies have been reported by users.',
	'REPORT_BLOG'						=> 'Report Blog',
	'REPORT_REPLY'						=> 'Report Reply',
	'RESYNC_BLOG'						=> 'Synchronise Blog',
	'RESYNC_BLOG_CONFIRM'				=> 'Are you sure you want to synchronise all of the blog data?  This may take a while.',
	'RESYNC_BLOG_SUCCESS'				=> 'Blog data has been successfully synchronised.',
	'RETURN_BLOG_MAIN'					=> '%sReturn to %s\'s main blog page%s',
	'RETURN_BLOG_MAIN_OWN'				=> '%sReturn to your main blog page%s',
	'RETURN_MAIN'						=> 'Click %shere%s to return to the main User Blog page',

	'SUCCESSFULLY_UPDATED'				=> 'User blog mod has been updated to %1$s.<br/><br/>Click %2$shere%3$s to return to the main blog page.',

	'UNDELETE_BLOG'						=> 'Un-Delete Blog',
	'UNDELETE_BLOG_CONFIRM'				=> 'Are you sure you want to un-delete this blog?',
	'UNDELETE_REPLY'					=> 'Un-delete Reply',
	'UNDELETE_REPLY_CONFIRM'			=> 'Are you sure you want to un-delete this reply?',
	'UNTITLED_REPLY'					=> 'Untitled Reply',
	'UPDATE_BLOG'						=> 'Update Blog',
	'UPDATE_INSTRUCTIONS'				=> 'Update',
	'UPDATE_INSTRUCTIONS_CONFIRM'		=> 'Make sure you read the upgrade instructions in the MOD History section of the main mod install document first <b>before</b> you do this.<br/><br/>Are you ready to upgrade the database for the User Blog Mod?',
	'UPGRADE_BLOG'						=> 'Upgrade Blog',
	'USERNAMES_BLOGS'					=> '%s\'s Blogs',
	'USERNAMES_DELETED_BLOGS'			=> '%s\'s Deleted Blogs',
	'USER_BLOGS'						=> 'User Blogs',
	'USER_BLOG_MOD_DISABLED'			=> 'The User Blog Mod has been disabled.',
	'USER_TEXT_LIMIT'					=> 'Default text limit for user blog page',
	'USER_TEXT_LIMIT_EXPLAIN'			=> 'Same as Default text limit, except this is for the limit on the View User page',

	'VIEW_BLOG'							=> 'View Blog',
	'VIEW_BLOGS'						=> 'View Blogs',
	'VIEW_DELETED_BLOGS'				=> 'View Deleted Blogs',
	'VIEW_REPLY'						=> 'View Reply',
));

?>