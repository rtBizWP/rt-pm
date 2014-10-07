<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Description of Rt_Hrm_Bp_Hrm
 *
 * @author kishore
 */
if ( ! class_exists( 'RT_PM_Bp_PM' ) ) {
    
    class RT_PM_Bp_PM {
        
        function __construct() {
           
            // Define constants
            $this->define_constants();

            // Include required files
            $this->includes();

            // Init API
            $this->api = new RT_PM_Bp_PM_Loader();
			
			// Init Hooks
			$this->hooks();
        }
        
        function includes() {
            
             global $rt_pm_buddypress_pm;
             
             $rt_pm_buddypress_pm = new RT_WP_Autoload( RT_PM_BP_PM_PATH . 'bp-pm/' );
        }
        
        function define_constants() {
            
			if ( ! defined( 'RT_PM_BP_PM_PATH' ) ){
			        define( 'RT_PM_BP_PM_PATH', plugin_dir_path( __FILE__ ) );
			}
			if ( ! defined( 'RT_PM_BP_PM_SLUG' ) ){
			        define( 'RT_PM_BP_PM_SLUG', 'pm' );
			}
        }
		
		function get_component_root_url(){
			global $bp;
			foreach ( $bp->bp_nav as $nav ) {
			    
			  if ( $nav['slug'] == RT_PM_BP_PM_SLUG ){
				$link = $nav['link'];
			  }
			}
			return $link;
		}
		
		/**
		 * hooks function.
		 * Call all hooks :)
		 *
		 * @access public
		 * @return void
		 */
		public function hooks() {
			
		}
		
		
    
    }
    
}