<?php

/*
	Plugin Name: Hide Category
	Plugin URI: https://brunovandekerkhove.com
	Plugin Description: Hides a given category from the activity page
	Plugin Version: 1.0
	Plugin Date: 2016-10-17
	Plugin Author: Bruno Vandekerkhove
	Plugin Author URI: http://brunovandekerkhove.com
	Plugin License: none
	Plugin Minimum Question2Answer Version: 1.6
*/

if ( !defined('QA_VERSION') ) {
	header('Location: ../../');
	exit;
}

qa_register_plugin_module('module', 'qa-hide-category-admin.php', 'qa_hide_category_admin', 'Hide Category Admin Module');
qa_register_plugin_overrides('qa-hide-category-overrides.php');