<?php

/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Rt_PM_Time_Entries_Model
 *
 * @author udit
 */
if ( ! class_exists( 'Rt_PM_Time_Entries_Model' ) ) {

	class Rt_PM_Time_Entries_Model extends RT_DB_Model {

		public function __construct() {
			parent::__construct( 'wp_pm_time_entries' );
		}

	}

}
