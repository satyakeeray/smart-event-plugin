<?php
/*
Plugin Name: Smart Event - Plugin
Description: A Custom plugin for events
Version: 1.0
Author: Satyakee Ray
Text Domain: smart-event
*/

if( !defined('ABSPATH') ) exit; 


// Register post type event

add_action( 'init', 'smart_register_event_cpt' );
function smart_register_event_cpt() {
    $labels = array(
        'name'                  => __( 'Events', 'smart-event' ),
        'singular_name'         => __( 'Event', 'smart-event' ),
        'menu_name'             => __( 'Events', 'smart-event' ),
        'name_admin_bar'        => __( 'Event', 'smart-event' ),
        'add_new'               => __( 'Add New Event', 'smart-event' ),
        'add_new_item'          => __( 'Add New Event', 'smart-event' ),
        'edit_item'             => __( 'Edit Event', 'smart-event' ),
        'new_item'              => __( 'New Event', 'smart-event' ),
        'view_item'             => __( 'View Event', 'smart-event' ),
        'search_items'          => __( 'Search Events', 'smart-event' ),
        'not_found'             => __( 'No events found', 'smart-event' ),
        'not_found_in_trash'    => __( 'No events found in trash', 'smart-event' ),
    );

    $args = array(
        'label'                 => __( 'Events', 'smart-event' ),
        'labels'                => $labels,
        'public'                => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-list-view',
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'has_archive'           => true,
        'rewrite'               => array( 'slug' => 'events' ),
    );

    register_post_type( 'event', $args );
}

add_action( 'add_meta_boxes', 'smart_event_add_metaboxes' );
function smart_event_add_metaboxes() {
    add_meta_box(
        'smart_event_dates',
        __( 'Event Dates', 'smart-events' ),
        'smart_event_dates_callback',
        'event',
        'normal',
        'default'
    );
}

// Meta Box HTML
function smart_event_dates_callback( $post ) {

    // Security nonce
    wp_nonce_field( 'smart_event_save_dates', 'smart_event_dates_nonce' );

    // Get existing values
    $start_date = get_post_meta( $post->ID, 'event-start-date', true );
    $end_date   = get_post_meta( $post->ID, 'event-end-date', true );

    ?>
    <style>
        .smart-event-field {
            margin-bottom: 15px;
        }
        .smart-event-field input {
            width: 250px;
            padding: 6px;
        }
    </style>

    <p class="smart-event-field">
        <label><strong>Event Start Date:</strong></label><br>
        <input type="date" id="event-start-date" name="event_start_date" value="<?php echo esc_attr( $start_date ); ?>" class="smart-datepicker">
    </p>

    <p class="smart-event-field">
        <label><strong>Event End Date:</strong></label><br>
        <input type="date" id="event-end-date" name="event_end_date" value="<?php echo esc_attr( $end_date ); ?>" class="smart-datepicker">
    </p>
    <?php
}


// Save Meta Values
add_action( 'save_post', 'smart_event_save_dates' );
function smart_event_save_dates( $post_id ) {

    if ( ! isset( $_POST['smart_event_dates_nonce'] ) ||
        ! wp_verify_nonce( $_POST['smart_event_dates_nonce'], 'smart_event_save_dates' ) ) {
        return;
    }

    // Save start date
    if ( isset( $_POST['event_start_date'] ) ) {
        update_post_meta( $post_id, 'event_start_date', sanitize_text_field( $_POST['event_start_date'] ) );
    }

    // Save end date
    if ( isset( $_POST['event_end_date'] ) ) {
        update_post_meta( $post_id, 'event_end_date', sanitize_text_field( $_POST['event_end_date'] ) );
    }

}

// Add custom columns for Event CPT
add_filter( 'manage_event_posts_columns', 'smart_event_custom_columns' );
function smart_event_custom_columns( $columns ) {
	
    // Add before "Date" column
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        // Insert new columns just before the "Date" column
        if ( $key === 'date' ) {
            $new_columns['event_start_date'] = __( 'Start Date', 'smart-plugin' );
            $new_columns['event_end_date']   = __( 'End Date', 'smart-plugin' );
        }
		$new_columns[$key] = $label;
    }

    return $new_columns;
}

// Display value in custom column
add_action( 'manage_event_posts_custom_column', 'sp_event_custom_column_content', 10, 2 );
function sp_event_custom_column_content( $column, $post_id ) {

    if ( $column == 'event_start_date' ) {
        $event_start_date = get_post_meta( $post_id, 'event_start_date', true );
		echo date("d-m-Y", strtotime( $event_start_date) );
    }

    if ( $column == 'event_end_date' ) {
        $event_end_date = get_post_meta( $post_id, 'event_end_date', true );
		echo date("d-m-Y", strtotime( $event_end_date) );
    }
}

// Enable sorting for the custom column
add_filter( 'manage_edit-event_sortable_columns', 'smart_event_sortable_columns' );
function smart_event_sortable_columns( $columns ) {
    $columns['event_start_date'] = 'event_start_date';
    $columns['event_end_date']   = 'event_end_date';
    return $columns;
}

// Enabling query while sorting.
add_action( 'pre_get_posts', 'smart_event_sorting_logic' );
function smart_event_sorting_logic( $query ) {
    if( ! is_admin() ) return;
    if( ! $query->is_main_query() ) return;

    $orderby = $query->get( 'orderby' );

    if ( $orderby == 'event_start_date' ) {
        $query->set( 'meta_key', 'event_start_date' );
        $query->set( 'orderby', 'meta_value' );
    }

    if ( $orderby == 'event_end_date' ) {
        $query->set( 'meta_key', 'event_end_date' );
        $query->set( 'orderby', 'meta_value' );
    }
}


add_action( 'wp_enqueue_scripts' , 'smart_evenet_frontend_enque_scripts' );
function smart_evenet_frontend_enque_scripts() {
	wp_enqueue_style(
		'smart-event-style',
		plugin_dir_url (__FILE__ ) . '/css/smart-event-front-style.css',
		array(),
		'1.0.0',
		'all'
	);
}

// Shortcode for event listing

add_shortcode( 'smart_event_list', 'upcoming_events_shortcode' );
function upcoming_events_shortcode( $atts ) {
	ob_start();
	$atts = shortcode_atts( array(
        'limit' => 5,
        'title'  => '',
		'event_type' => 'all'  // all, upcoming, past
    ), $atts );

	// Sanitize input values
	$limit      = intval( $atts['limit'] );
	$title      = sanitize_text_field( $atts['title'] );
	$event_type = sanitize_text_field( $atts['event_type'] );

	// Apply defaults
	$limit      = ( !is_numeric($limit) || $limit < 0 ) ? 5 : $limit;
	$title      = ( empty($title) ) ? '' : $title;
	$event_type = ( empty($event_type) ) ? 'all' : $event_type;

	?>
	<section class="event-listing-wrapper">
		<?php echo smart_events_html( $event_type, $title, $limit, 1 ); ?>
	</section>
	<?php
	return ob_get_clean();
}

if( !function_exists('smart_events_html') ) {
	function smart_events_html( $event_type, $title, $limit, $paged ) {
		ob_start();
		$today = date('Y-m-d');
		$meta_key = '';
		$meta_query = [];
		$order = 'DESC';
		if( $event_type == 'upcoming' ) {
			$meta_query = [
				[
					'key'     => 'event_start_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE'
				]
			];
			$order = 'ASC';
			$meta_key = 'event_start_date';
		}
		if( $event_type == 'past' ) {
			$meta_query = [
				[
					'key'     => 'event_end_date',
					'value'   => $today,
					'compare' => '<',
					'type'    => 'DATE'
				]
			];
			$order = 'DESC';
			$meta_key = 'event_end_date';
		}
		if( $event_type == 'all' ) {
			$meta_key = '';
			$meta_query = [];
			$order = 'DESC';
		}

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => $limit,
			'paged' 		 => $paged,
			'post_status'    => 'publish',
			'meta_key'       => $meta_key,
			'meta_query'     => $meta_query,
			'orderby'        => 'meta_value',
    		'order'          => $order,
		);

		$events = new WP_Query( $args );
		if( $events->have_posts() ) {
			?>
			<div class="smart-event-list">
				<?php if( !empty( $title ) ): ?>
					<h2 class="event-heading"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<div class="event-listing-grid">
					<?php 
					while( $events->have_posts() ) {
						$events->the_post();

						$start_date = get_post_meta( get_the_ID(), 'event_start_date', true );
						$end_date   = get_post_meta( get_the_ID(), 'event_end_date', true );
						?>
						<article class="event-card">
							<div class="event-thumb">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'medium' );
								}
								?>
							</div>

							<h3 class="event-title"><?php the_title(); ?></h3>

							<div class="event-dates">
								<?php echo esc_html( date("d-m-Y", strtotime($start_date)) ); ?>
								-
								<?php echo esc_html( date("d-m-Y", strtotime($end_date)) ); ?>
							</div>

							<p class="event-excerpt">
								<?php echo get_the_excerpt(); ?>
							</p>

							<a href="<?php the_permalink(); ?>" class="event-btn">View Event</a>
						</article>
						<?php
					}
					?>
				</div>

				<nav class="event-pagination">
					<?php
					echo paginate_links([
						'total' => $events->max_num_pages
					]);
					?>
				</nav>
			</div>
			<?php
			
		} else {
			echo 'no post';
		}

		wp_reset_postdata();
		return ob_get_clean();
	}
}