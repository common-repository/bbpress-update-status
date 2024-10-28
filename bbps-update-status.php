<?php
/*
Plugin Name: BBps Update Status
Plugin URL: http://remicorson.com/bbps-update-status
Description: Update BBpress topics status to "resolved" if older than one month
Version: 0.2
Author: Remi Corson
Author URI: http://remicorson.com
Contributors: corsonr
*/

/*
|--------------------------------------------------------------------------
| MAIN CLASS
|--------------------------------------------------------------------------
*/

class rc_bbps_update_status {

	/**
	 * Constructor
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function __construct() {
	
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
		// Check if GetShopped Support Forum Plugin is activated so as BBpress plugin
		if (is_plugin_active('GetShopped_support_forums/bbps-premium-support.php') && class_exists( 'bbpress' ) ) {
			add_action('init', array( &$this,'rc_bbps_update_old_topics_status') );
		}
	
	}
	
	/**
	 * Update post status topics older than a month every 12 hours
	 *
	 * @access      public
	 * @since       1.0 
	 * @return      void
	*/
	function rc_bbps_update_old_topics_status() {
	
		// Get Transient
		$data = get_transient('rc_bbps_update_status');
		
		// Process only if transient isn't set and if current user is admin (to avoid decreasing page loading perfomance for users)
		if( false === $data && current_user_can('activate_plugins') ) {
			
			// Set Transient
			$data = 'on';
			set_transient('rc_bbps_update_status', $data, 60*60*12 ); // 12 hours
			
			// Get all old topics
			$posts_per_page = -1;
			$post_type      = bbp_get_topic_post_type();
						
			 $query = new WP_Query( array ( 
			        'fields'                 => 'ids',
			        'cache_results'          => false,
			        'update_post_term_cache' => false,
			        'update_post_meta_cache' => false,
			        'ignore_sticky_posts'    => 1,
			        'post_type'              => $post_type,
			        'posts_per_page'         => $posts_per_page,
			        'meta_query' => array(
			                        array(
			                                'type'    => 'DATETIME',
			                                'key'     => '_bbp_last_active_time',
			                                'value'   => date("Y-m-d H:i:s", strtotime("-1 month")),
			                                'compare' => '<'
			                        )
			                ) 
			        ) 
			);
			
			//Get topics count
			$post_count = $query->post_count;
			
			// Loop through topics
			if( $post_count > 0) :
			
				// Loop
				while ($query->have_posts()) : $query->the_post();
					
					// Update status
					update_post_meta( get_the_ID(), '_bbps_topic_status', 2);
					
				endwhile;
				
			endif;
			
			// Reset query to prevent conflicts
			wp_reset_query();
				
		}
	
	}

}

// instantiate plugin's class
$GLOBALS['rc_bbps_update_status'] = new rc_bbps_update_status();