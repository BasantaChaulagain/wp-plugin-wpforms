<?php
/* 
In the db, form_value should be unserialized php array with 'cfdb7_status' as key and "read/unread" as value.
*/

//user input area
$_GET['fid'] = 2;
$_GET['ufid'] = 1;
$_REQUEST['action'] = "unread";
$_POST['contact_form'] = ['form_id'=>"1"];

//include fake_wp
include "fake_wp2.php";

//include target function
include "./database-for-wpforms/inc/class-form-details.php";
include "./database-for-wpforms/inc/class-sub-page.php";

//triger target function

// database-for-wp-forms.php: WPFormsDB_save

//here we want to run function:form_details_page(). 
//it is called in the class constructor, therefore we only need to create a object
$form_details = new WPFormsDB_Form_Details();

// Here, we want to do bulk action on the db contents. Action can be read, unread or delete.
// The variables $_REQUEST['action'] and $_POST['contact_form'] is used to define what action to do, and on what item.
$list_table = new WPFormsDB_List_Table();
$list_table->prepare_items();
$list_table->process_bulk_action();
