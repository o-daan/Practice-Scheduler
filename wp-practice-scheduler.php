<?php
/*
 Plugin Name: Practice Scheduler
 Plugin URI: http://www.qrafts.nl
 Version: 0.9
 Author: Daniel Oosterhuis - Atane Software
 Description: A plugin to facilitate making a doctor's appointment from within a WP site, using Google Calendar as a planning backend.
 */
require_once('PracticeSchedulerController.php');

// add the backend menu
add_action('admin_menu', 'wp_practice_scheduler_menu');
function wp_practice_scheduler_menu() {
    add_options_page(_('Practice Scheduler Configuration'), _('Practice Scheduler'), 'manage_options', 'wp_practice_scheduler', 'wp_practice_scheduler_options');
}

// admin form
function wp_practice_scheduler_options() {
    require_once('PracticeSchedulerAdminController.php');
    $adminPageController = new PracticeSchedulerAdminController();
    $adminPageController->execute();
}

function jQueryLoader() {
    wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js');
    wp_enqueue_script( 'jquery' );
}

add_action('wp_enqueue_scripts', 'jQueryLoader');

// instantiate the plugin
$practiceSchedulerController = new PracticeSchedulerController();


