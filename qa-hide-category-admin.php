<?php

/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Hide Category Plugin by Bruno Vandekerkhove © 2015
	
*/

class qa_hide_category_admin {

	// Default admin options
	function option_default($option) {
	    switch($option) {
			case 'qa_hide_category_enabled':
				return false;
			case 'qa_hide_category_slug':
				return 'mycategory';
			default:
			    return null;				
	    }
	}
       
    // Allow template
	function allow_template($template) {
		return ($template != 'admin');
	}       
		
	// Create admin form
	function admin_form(&$qa_content) {                       
						
		// Process form input
		$ok = null;

		if (qa_clicked('qa_hide_category_save')) {
		
			qa_opt('qa_hide_category_slug',qa_post_text('qa_hide_category_slug'));
			qa_opt('qa_hide_category_enabled',(bool)qa_post_text('qa_hide_category_enabled'));
			$ok = qa_lang('admin/options_saved');
			
		}
		else if (qa_clicked('qa_hide_category_reset')) {
			
			// Reset options
			qa_opt('qa_hide_category_enabled',$this->option_default('qa_hide_category_enabled'));
			qa_opt('qa_hide_category_slug',$this->option_default('qa_hide_category_slug'));
			$ok = qa_lang('admin/options_reset');
			
		}
		
		// Create the form for display
		$fields = array();
		$fields[] = array('label' => 'Hide category', 'tags' => 'NAME="qa_hide_category_enabled"', 'value' => qa_opt('qa_hide_category_enabled'), 'type' => 'checkbox',);
		$fields[] = array('label' => 'Category slug', 'type' => 'text', 'value' => qa_opt('qa_hide_category_slug'), 'tags' => 'NAME="qa_hide_category_slug"',); 

		return array(           
			'ok' => ($ok && !isset($error)) ? $ok : null,
			'fields' => $fields,
			'buttons' => array(
				array('label' => 'Save', 'tags' => 'NAME="qa_hide_category_save"',),
				array('label' => 'Reset to defaults', 'tags' => 'NAME="qa_hide_category_reset"',)
			),
		);
		
	}
}
