<?php

/*							  
		Overriden functions
*/

/**
 * Overriden function that fetches the content for the current request.
 * The new function has custom behaviour when the activity page is requested and no category is selected.
 */
function qa_get_request_content() {
	
	$requestparts = qa_request_parts();
	$categoryslugs=qa_request_parts(1);
	$excluded_slugs = qa_opt('qa_hide_category_slug');
	
	if (strtolower($requestparts[0]) === 'activity'
		&& qa_opt('qa_hide_category_enabled') 
		&& (!isset($categoryslugs) || empty($categoryslugs)) 
		&& (isset($excluded_slugs) && !empty($excluded_slugs))) {
		
		require_once QA_INCLUDE_DIR.'db/selects.php';
		require_once QA_INCLUDE_DIR.'app/format.php';
		require_once QA_INCLUDE_DIR.'app/q-list.php';
		require_once QA_PLUGIN_DIR.'q2a-hide-category/qa-hide-category-functions.php';
	
		$sometitle = qa_lang_html('main/recent_activity_title');
		$nonetitle = qa_lang_html('main/no_questions_found');
		$userid = qa_get_logged_in_userid();
		
		// Fetch question list
		list($questions1, $questions2, $questions3, $questions4, $categories) = qa_db_select_with_pending(
			qa_hide_category_fetch_questions($userid, $excluded_slugs),
			qa_hide_category_fetch_edited_questions($userid, $excluded_slugs),
			qa_hide_category_fetch_answers($userid, $excluded_slugs),
			qa_hide_category_fetch_comments($userid, $excluded_slugs),
			qa_db_category_nav_selectspec($categoryslugs, false, false, true)
		);
	
		// Prepare and return content for theme
		return qa_q_list_page_content(
			qa_any_sort_and_dedupe(array_merge($questions1, $questions2, $questions3, $questions4)), // questions
			qa_opt('page_size_activity'), // questions per page
			0, // start offset
			null, // total count (null to hide page links)
			$sometitle, // title if some questions
			$nonetitle, // title if no questions
			$categories, // categories for navigation
			null, // selected category id
			true, // show question counts in category navigation
			'activity/', // prefix for links in category navigation
			qa_opt('feed_for_activity') ? 'activity' : null, // prefix for RSS feed paths (null to hide)
			qa_html_suggest_qs_tags(qa_using_tags(), qa_category_path_request($categories, null)), // suggest what to do next
			null, // page link params
			null // category nav params
		);
		
	}
	
	return qa_get_request_content_base();
	
}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

