<?php

/*							  
		Custom post fetching functions
*/

/**
 * Return SQL code that represents the constraint of a post *not* being in the category with $categoryslugs, or any of its subcategories
 */
function qa_hide_category_sql_args_negation($categoryslugs, &$arguments) {

	if (!is_array($categoryslugs)) // accept old-style string arguments for one category deep
		$categoryslugs = strlen($categoryslugs) ? array($categoryslugs) : array();

	$levels = count($categoryslugs);
	
	if (($levels > 0) && ($levels <= QA_CATEGORY_DEPTH)) {
		$arguments[] = qa_db_slugs_to_backpath($categoryslugs);
		$selection = (($levels==QA_CATEGORY_DEPTH) ? 'categoryid' : ('catidpath'.$levels));
		return '(NOT ('.$selection.'<=>(SELECT categoryid FROM ^categories WHERE backpath=$ LIMIT 1))) AND ';
	}

	return '';
}

/**
 * Custom function for selecting recent questions. The function fetches recent questions except those in the given excluded category.
 */
function qa_hide_category_fetch_questions($userid, $excluded_category_slugs) {
		
	// qa_db_qs_selectspec($userid, 'created', 0, $categoryslugs, null, false, false, qa_opt_if_loaded('page_size_activity'))
	
	$start = 0;
	$count_option = qa_opt_if_loaded('page_size_activity');
	$count = isset($count_option) ? min($count_option, QA_DB_RETRIEVE_QS_AS) : QA_DB_RETRIEVE_QS_AS;
	$type = 'Q';
	
	$selectspec = qa_db_posts_basic_selectspec($userid, false);
	$selectspec['source'] .= 	" JOIN (SELECT postid FROM ^posts WHERE ".
								qa_hide_category_sql_args_negation($excluded_category_slugs, $selectspec['arguments']).
								"type=$ ORDER BY ^posts.created DESC LIMIT #,#) y ON ^posts.postid=y.postid";
	array_push($selectspec['arguments'], $type, $start, $count);
	$selectspec['sortdesc'] = 'created';
		
	return $selectspec;
	
}

/**
 * Custom function for selecting recent questions. The function fetches recent questions except those in the given excluded category.
 */
function qa_hide_category_fetch_edited_questions($userid, $excluded_category_slugs) {
	
	// qa_db_custom_recent_edit_qs_selectspec($userid, 0, $categoryslugs),
	
	$start = 0;
	$count = QA_DB_RETRIEVE_QS_AS;
	$selectspec = qa_db_posts_basic_selectspec($userid);
	$onlyvisible = true;

	qa_db_add_selectspec_opost($selectspec, 'editposts', true, false);
	qa_db_add_selectspec_ousers($selectspec, 'editusers', 'edituserpoints');

	$selectspec['source'].=" JOIN ^posts AS parentposts ON".
		" ^posts.postid=IF(LEFT(parentposts.type, 1)='Q', parentposts.postid, parentposts.parentid)".
		" JOIN ^posts AS editposts ON parentposts.postid=IF(LEFT(editposts.type, 1)='Q', editposts.postid, editposts.parentid)".
		(QA_FINAL_EXTERNAL_USERS ? "" : " LEFT JOIN ^users AS editusers ON editposts.lastuserid=editusers.userid").
		" LEFT JOIN ^userpoints AS edituserpoints ON editposts.lastuserid=edituserpoints.userid".
		" JOIN (SELECT postid FROM ^posts WHERE ".
		qa_hide_category_sql_args_negation($excluded_category_slugs, $selectspec['arguments']).
		($onlyvisible ? "type IN ('Q', 'A', 'C')" : "1").
		" ORDER BY ^posts.updated DESC LIMIT #,#) y ON editposts.postid=y.postid".
		($onlyvisible ? " WHERE parentposts.type IN ('Q', 'A', 'C') AND ^posts.type IN ('Q', 'A', 'C')" : "");
	array_push($selectspec['arguments'], $start, $count);
	$selectspec['sortdesc']='otime';

	return $selectspec;
	
}

/**
 * Custom function for selecting recent answers. The function fetches recent answers except those whose parent questions is in the given excluded category.
 */
function qa_hide_category_fetch_answers($userid, $excluded_category_slugs) {
	
	// qa_db_custom_recent_a_qs_selectspec($userid, 0, $categoryslugs),
	
	$start = 0;
	$count = QA_DB_RETRIEVE_QS_AS;
	$type = 'A';
	$selectspec = qa_db_posts_basic_selectspec($userid);

	qa_db_add_selectspec_opost($selectspec, 'aposts', false, false);
	qa_db_add_selectspec_ousers($selectspec, 'ausers', 'auserpoints');

	$selectspec['source'].=" JOIN ^posts AS aposts ON ^posts.postid=aposts.parentid".
		(QA_FINAL_EXTERNAL_USERS ? "" : " LEFT JOIN ^users AS ausers ON aposts.userid=ausers.userid").
		" LEFT JOIN ^userpoints AS auserpoints ON aposts.userid=auserpoints.userid".
		" JOIN (SELECT postid FROM ^posts WHERE ".
		qa_hide_category_sql_args_negation($excluded_category_slugs, $selectspec['arguments']).
		(isset($createip) ? "createip=INET_ATON($) AND " : "").
		"type=$ ORDER BY ^posts.created DESC LIMIT #,#) y ON aposts.postid=y.postid";
	array_push($selectspec['arguments'], $type, $start, $count);
	$selectspec['sortdesc']='otime';

	return $selectspec;
	
}

/**
 * Custom function for selecting recent comments. The function fetches recent comments except those whose parent questions is in the given excluded category.
 */
function qa_hide_category_fetch_comments($userid, $excluded_category_slugs) {
	
	// qa_db_custom_recent_c_qs_selectspec($userid, 0, $categoryslugs),
	
	$start = 0;
	$count = QA_DB_RETRIEVE_QS_AS;
	$type = 'C';
	$selectspec = qa_db_posts_basic_selectspec($userid);

	qa_db_add_selectspec_opost($selectspec, 'cposts', false, false);
	qa_db_add_selectspec_ousers($selectspec, 'cusers', 'cuserpoints');

	$selectspec['source'].=" JOIN ^posts AS parentposts ON".
		" ^posts.postid=(CASE LEFT(parentposts.type, 1) WHEN 'A' THEN parentposts.parentid ELSE parentposts.postid END)".
		" JOIN ^posts AS cposts ON parentposts.postid=cposts.parentid".
		(QA_FINAL_EXTERNAL_USERS ? "" : " LEFT JOIN ^users AS cusers ON cposts.userid=cusers.userid").
		" LEFT JOIN ^userpoints AS cuserpoints ON cposts.userid=cuserpoints.userid".
		" JOIN (SELECT postid FROM ^posts WHERE ".
		qa_hide_category_sql_args_negation($excluded_category_slugs, $selectspec['arguments']).
		(isset($createip) ? "createip=INET_ATON($) AND " : "").
		"type=$ ORDER BY ^posts.created DESC LIMIT #,#) y ON cposts.postid=y.postid".
		($specialtype ? '' : " WHERE ^posts.type='Q' AND ((parentposts.type='Q') OR (parentposts.type='A'))");
	array_push($selectspec['arguments'], $type, $start, $count);
	$selectspec['sortdesc']='otime';

	return $selectspec;
	
}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

