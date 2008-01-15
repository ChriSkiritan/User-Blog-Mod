/*

 $Id: $

*/


/*
	Table: 'phpbb_blogs'
*/
CREATE TABLE phpbb_blogs (
	blog_id number(8) NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	user_ip varchar2(40 char) DEFAULT '' ,
	blog_subject varchar2(255 char) DEFAULT '' ,
	blog_text clob DEFAULT '' ,
	blog_checksum varchar2(32 char) DEFAULT '' ,
	blog_time number(11) DEFAULT '0' NOT NULL,
	blog_approved number(1) DEFAULT '1' NOT NULL,
	blog_reported number(1) DEFAULT '0' NOT NULL,
	enable_bbcode number(1) DEFAULT '1' NOT NULL,
	enable_smilies number(1) DEFAULT '1' NOT NULL,
	enable_magic_url number(1) DEFAULT '1' NOT NULL,
	bbcode_bitfield varchar2(255 char) DEFAULT '' ,
	bbcode_uid varchar2(8 char) DEFAULT '' ,
	blog_edit_time number(11) DEFAULT '0' NOT NULL,
	blog_edit_reason varchar2(255 char) DEFAULT '' ,
	blog_edit_user number(8) DEFAULT '0' NOT NULL,
	blog_edit_count number(4) DEFAULT '0' NOT NULL,
	blog_edit_locked number(1) DEFAULT '0' NOT NULL,
	blog_deleted number(8) DEFAULT '0' NOT NULL,
	blog_deleted_time number(11) DEFAULT '0' NOT NULL,
	blog_read_count number(8) DEFAULT '1' NOT NULL,
	blog_reply_count number(8) DEFAULT '0' NOT NULL,
	blog_real_reply_count number(8) DEFAULT '0' NOT NULL,
	blog_attachment number(1) DEFAULT '0' NOT NULL,
	perm_guest number(1) DEFAULT '1' NOT NULL,
	perm_registered number(1) DEFAULT '2' NOT NULL,
	perm_foe number(1) DEFAULT '0' NOT NULL,
	perm_friend number(1) DEFAULT '2' NOT NULL,
	rating number(8) DEFAULT '0' NOT NULL,
	num_ratings number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs PRIMARY KEY (blog_id)
)
/

CREATE INDEX phpbb_blogs_user_id ON phpbb_blogs (user_id)
/
CREATE INDEX phpbb_blogs_user_ip ON phpbb_blogs (user_ip)
/
CREATE INDEX phpbb_blogs_blog_approved ON phpbb_blogs (blog_approved)
/
CREATE INDEX phpbb_blogs_blog_deleted ON phpbb_blogs (blog_deleted)
/
CREATE INDEX phpbb_blogs_perm_guest ON phpbb_blogs (perm_guest)
/
CREATE INDEX phpbb_blogs_perm_registered ON phpbb_blogs (perm_registered)
/
CREATE INDEX phpbb_blogs_perm_foe ON phpbb_blogs (perm_foe)
/
CREATE INDEX phpbb_blogs_perm_friend ON phpbb_blogs (perm_friend)
/
CREATE INDEX phpbb_blogs_rating ON phpbb_blogs (rating)
/

CREATE SEQUENCE phpbb_blogs_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs
BEFORE INSERT ON phpbb_blogs
FOR EACH ROW WHEN (
	new.blog_id IS NULL OR new.blog_id = 0
)
BEGIN
	SELECT phpbb_blogs_seq.nextval
	INTO :new.blog_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_attachment'
*/
CREATE TABLE phpbb_blogs_attachment (
	attach_id number(8) NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	reply_id number(8) DEFAULT '0' NOT NULL,
	poster_id number(8) DEFAULT '0' NOT NULL,
	is_orphan number(1) DEFAULT '1' NOT NULL,
	physical_filename varchar2(255 char) DEFAULT '' ,
	real_filename varchar2(255 char) DEFAULT '' ,
	download_count number(8) DEFAULT '0' NOT NULL,
	attach_comment clob DEFAULT '' ,
	extension varchar2(100 char) DEFAULT '' ,
	mimetype varchar2(100 char) DEFAULT '' ,
	filesize number(20) DEFAULT '0' NOT NULL,
	filetime number(11) DEFAULT '0' NOT NULL,
	thumbnail number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_attachment PRIMARY KEY (attach_id)
)
/

CREATE INDEX phpbb_blogs_attachment_blog_id ON phpbb_blogs_attachment (blog_id)
/
CREATE INDEX phpbb_blogs_attachment_reply_id ON phpbb_blogs_attachment (reply_id)
/
CREATE INDEX phpbb_blogs_attachment_filetime ON phpbb_blogs_attachment (filetime)
/
CREATE INDEX phpbb_blogs_attachment_poster_id ON phpbb_blogs_attachment (poster_id)
/
CREATE INDEX phpbb_blogs_attachment_is_orphan ON phpbb_blogs_attachment (is_orphan)
/

CREATE SEQUENCE phpbb_blogs_attachment_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_attachment
BEFORE INSERT ON phpbb_blogs_attachment
FOR EACH ROW WHEN (
	new.attach_id IS NULL OR new.attach_id = 0
)
BEGIN
	SELECT phpbb_blogs_attachment_seq.nextval
	INTO :new.attach_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_categories'
*/
CREATE TABLE phpbb_blogs_categories (
	category_id number(8) NOT NULL,
	parent_id number(8) DEFAULT '0' NOT NULL,
	left_id number(8) DEFAULT '0' NOT NULL,
	right_id number(8) DEFAULT '0' NOT NULL,
	category_name varchar2(255 char) DEFAULT '' ,
	category_description clob DEFAULT '' ,
	category_description_bitfield varchar2(255 char) DEFAULT '' ,
	category_description_uid varchar2(8 char) DEFAULT '' ,
	category_description_options number(11) DEFAULT '7' NOT NULL,
	rules clob DEFAULT '' ,
	rules_bitfield varchar2(255 char) DEFAULT '' ,
	rules_uid varchar2(8 char) DEFAULT '' ,
	rules_options number(11) DEFAULT '7' NOT NULL,
	blog_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_categories PRIMARY KEY (category_id)
)
/

CREATE INDEX phpbb_blogs_categories_left_right_id ON phpbb_blogs_categories (left_id, right_id)
/

CREATE SEQUENCE phpbb_blogs_categories_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_categories
BEFORE INSERT ON phpbb_blogs_categories
FOR EACH ROW WHEN (
	new.category_id IS NULL OR new.category_id = 0
)
BEGIN
	SELECT phpbb_blogs_categories_seq.nextval
	INTO :new.category_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_in_categories'
*/
CREATE TABLE phpbb_blogs_in_categories (
	blog_id number(8) DEFAULT '0' NOT NULL,
	category_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_in_categories PRIMARY KEY (blog_id, category_id)
)
/


/*
	Table: 'phpbb_blogs_plugins'
*/
CREATE TABLE phpbb_blogs_plugins (
	plugin_id number(8) NOT NULL,
	plugin_name varchar2(255 char) DEFAULT '' ,
	plugin_enabled number(1) DEFAULT '0' NOT NULL,
	plugin_version varchar2(100 char) DEFAULT '' ,
	CONSTRAINT pk_phpbb_blogs_plugins PRIMARY KEY (plugin_id)
)
/

CREATE INDEX phpbb_blogs_plugins_plugin_name ON phpbb_blogs_plugins (plugin_name)
/
CREATE INDEX phpbb_blogs_plugins_plugin_enabled ON phpbb_blogs_plugins (plugin_enabled)
/

CREATE SEQUENCE phpbb_blogs_plugins_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_plugins
BEFORE INSERT ON phpbb_blogs_plugins
FOR EACH ROW WHEN (
	new.plugin_id IS NULL OR new.plugin_id = 0
)
BEGIN
	SELECT phpbb_blogs_plugins_seq.nextval
	INTO :new.plugin_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_ratings'
*/
CREATE TABLE phpbb_blogs_ratings (
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	rating number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_ratings PRIMARY KEY (blog_id, user_id)
)
/


/*
	Table: 'phpbb_blogs_reply'
*/
CREATE TABLE phpbb_blogs_reply (
	reply_id number(8) NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	user_ip varchar2(40 char) DEFAULT '' ,
	reply_subject varchar2(255 char) DEFAULT '' ,
	reply_text clob DEFAULT '' ,
	reply_checksum varchar2(32 char) DEFAULT '' ,
	reply_time number(11) DEFAULT '0' NOT NULL,
	reply_approved number(1) DEFAULT '1' NOT NULL,
	reply_reported number(1) DEFAULT '0' NOT NULL,
	enable_bbcode number(1) DEFAULT '1' NOT NULL,
	enable_smilies number(1) DEFAULT '1' NOT NULL,
	enable_magic_url number(1) DEFAULT '1' NOT NULL,
	bbcode_bitfield varchar2(255 char) DEFAULT '' ,
	bbcode_uid varchar2(8 char) DEFAULT '' ,
	reply_edit_time number(11) DEFAULT '0' NOT NULL,
	reply_edit_reason varchar2(255 char) DEFAULT '' ,
	reply_edit_user number(8) DEFAULT '0' NOT NULL,
	reply_edit_count number(8) DEFAULT '0' NOT NULL,
	reply_edit_locked number(1) DEFAULT '0' NOT NULL,
	reply_deleted number(8) DEFAULT '0' NOT NULL,
	reply_deleted_time number(11) DEFAULT '0' NOT NULL,
	reply_attachment number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_reply PRIMARY KEY (reply_id)
)
/

CREATE INDEX phpbb_blogs_reply_blog_id ON phpbb_blogs_reply (blog_id)
/
CREATE INDEX phpbb_blogs_reply_user_id ON phpbb_blogs_reply (user_id)
/
CREATE INDEX phpbb_blogs_reply_user_ip ON phpbb_blogs_reply (user_ip)
/
CREATE INDEX phpbb_blogs_reply_reply_approved ON phpbb_blogs_reply (reply_approved)
/
CREATE INDEX phpbb_blogs_reply_reply_deleted ON phpbb_blogs_reply (reply_deleted)
/

CREATE SEQUENCE phpbb_blogs_reply_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blogs_reply
BEFORE INSERT ON phpbb_blogs_reply
FOR EACH ROW WHEN (
	new.reply_id IS NULL OR new.reply_id = 0
)
BEGIN
	SELECT phpbb_blogs_reply_seq.nextval
	INTO :new.reply_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blogs_subscription'
*/
CREATE TABLE phpbb_blogs_subscription (
	sub_user_id number(8) DEFAULT '0' NOT NULL,
	sub_type number(11) DEFAULT '0' NOT NULL,
	blog_id number(8) DEFAULT '0' NOT NULL,
	user_id number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_subscription PRIMARY KEY (sub_user_id, sub_type, blog_id, user_id)
)
/


/*
	Table: 'phpbb_blogs_users'
*/
CREATE TABLE phpbb_blogs_users (
	user_id number(8) DEFAULT '0' NOT NULL,
	perm_guest number(1) DEFAULT '1' NOT NULL,
	perm_registered number(1) DEFAULT '2' NOT NULL,
	perm_foe number(1) DEFAULT '0' NOT NULL,
	perm_friend number(1) DEFAULT '2' NOT NULL,
	title varchar2(255 char) DEFAULT '' ,
	description clob DEFAULT '' ,
	description_bbcode_bitfield varchar2(255 char) DEFAULT '' ,
	description_bbcode_uid varchar2(8 char) DEFAULT '' ,
	instant_redirect number(1) DEFAULT '1' NOT NULL,
	blog_subscription_default number(11) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blogs_users PRIMARY KEY (user_id)
)
/


/*
	Table: 'phpbb_blog_search_wordlist'
*/
CREATE TABLE phpbb_blog_search_wordlist (
	word_id number(8) NOT NULL,
	word_text varchar2(255 char) DEFAULT '' ,
	word_common number(1) DEFAULT '0' NOT NULL,
	word_count number(8) DEFAULT '0' NOT NULL,
	CONSTRAINT pk_phpbb_blog_search_wordlist PRIMARY KEY (word_id),
	CONSTRAINT u_phpbb_wrd_txt UNIQUE (word_text)
)
/

CREATE INDEX phpbb_blog_search_wordlist_wrd_cnt ON phpbb_blog_search_wordlist (word_count)
/

CREATE SEQUENCE phpbb_blog_search_wordlist_seq
/

CREATE OR REPLACE TRIGGER t_phpbb_blog_search_wordlist
BEFORE INSERT ON phpbb_blog_search_wordlist
FOR EACH ROW WHEN (
	new.word_id IS NULL OR new.word_id = 0
)
BEGIN
	SELECT phpbb_blog_search_wordlist_seq.nextval
	INTO :new.word_id
	FROM dual;
END;
/


/*
	Table: 'phpbb_blog_search_wordmatch'
*/
CREATE TABLE phpbb_blog_search_wordmatch (
	blog_id number(8) DEFAULT '0' NOT NULL,
	reply_id number(8) DEFAULT '0' NOT NULL,
	word_id number(8) DEFAULT '0' NOT NULL,
	title_match number(1) DEFAULT '0' NOT NULL,
	CONSTRAINT u_phpbb_unq_mtch UNIQUE (blog_id, reply_id, word_id, title_match)
)
/

CREATE INDEX phpbb_blog_search_wordmatch_word_id ON phpbb_blog_search_wordmatch (word_id)
/
CREATE INDEX phpbb_blog_search_wordmatch_blog_id ON phpbb_blog_search_wordmatch (blog_id)
/
CREATE INDEX phpbb_blog_search_wordmatch_reply_id ON phpbb_blog_search_wordmatch (reply_id)
/

