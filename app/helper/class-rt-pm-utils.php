<?php
/**
 * Don't load this file directly!
 */
if (!defined('ABSPATH'))
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rt_CRM_Utils
 *
 * @author Dipesh
 */
if( !class_exists( 'Rt_PM_Utils' ) ) {
	class Rt_PM_Utils {
		static public function forceUFT8($tmpStr){
			return preg_replace('/[^(\x20-\x7F)]*/','', $tmpStr);
		}

		public static function get_pm_rtcamp_user() {
			$users = rt_biz_get_module_users( RT_PM_TEXT_DOMAIN );
			return $users;
		}

        public static function get_pm_client_user() {
            $users = rt_biz_get_clients( );
            return $users;
        }

	}
}
