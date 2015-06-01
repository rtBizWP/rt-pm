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

        function add_timeentry( $data ) {
            return parent::insert( $data );
        }

        function update_timeentry( $data, $where ) {
            return parent::update( $data, $where );
        }

        function delete_timeentry( $where ) {
            return parent::delete( $where );
        }

        function get_timeentry( $timeentry_Id='' ) {
            $args   = array();
            if ( ! empty( $timeentry_Id ) ){
                $tTimeEntries = parent::get( $args );
                foreach ( $tTimeEntries as $tTimeEntry ) {
                    if ( $tTimeEntry->id == $timeentry_Id ){
                        return $tTimeEntry;
                    }
                }
            }
            return null;
        }

        /**
         * Get total billed hours on task
         * @param $args
         *
         * @return mixed
         */
        public function rtpm_get_billed_hours( $args ) {
            global $wpdb;

            $select_statement = "SELECT COALESCE( SUM(time_duration), 0 ) FROM {$this->table_name}";

            $where_columns   =   $this->rtpm_prepare_query_where_clause( $args );

            $where_columns[]  =   " post_status <> 'trash' ";
            $where_clause   =   $this->rtpm_generate_where_clause_string( $where_columns );

            $query = $select_statement.$where_clause;

            return $wpdb->get_var( $query );
        }


        /**
         * Prepare where clause for resources query
         * @param $args
         *
         * @return string
         */
        public function rtpm_prepare_query_where_clause( $args ) {

            if( ! is_array( $args ) )
                return false;

            $where_columns = array();

            if( isset( $args['author'] ) )
                $where_columns[] = " author = {$args['author']} ";

            if( isset( $args['task_id'] ) )
                $where_columns[] = " task_id = {$args['task_id']} ";

            if( isset( $args['project_id'] ) )
                $where_columns[] = " project_id = {$args['project_id']} ";

            if( isset( $args['timestamp'] ) )
                $where_columns[] = " DATE(timestamp) = '{$args['timestamp']}'";

            if( isset( $args['task__in'] )  && is_array( $args['task__in'] ) )
                $where_columns[] =  ' task_id IN( '.implode(', ', $args['task__in'] ) .') ';

            if( isset( $args['project__in'] )  && is_array( $args['project__in'] ) )
                $where_columns[] =  ' project_id IN( '.implode(', ', $args['project__in'] ) .') ';

            if( isset( $args['author__in'] )  && is_array( $args['author__in'] ) )
                $where_columns[] =  ' author IN( '.implode(', ', $args['author__in'] ) .') ';

            return $where_columns;
        }

        /**
         * Join all where clause column with am AND
         * @param $where_columns
         *
         * @return string
         */
        public function rtpm_generate_where_clause_string( $where_columns ) {

            if( empty( $where_columns ) )
                return '';

            $where_clause = ' WHERE ';
            $where_clause .= implode( ' AND ', $where_columns );
            return $where_clause;
        }

	}

}
