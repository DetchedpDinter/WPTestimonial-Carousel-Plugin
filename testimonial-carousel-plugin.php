<?php
/**
 * Plugin Name:       Testimonial Carousel Plugin
 * Description:       A plugin to manage testimonials with a settings page.
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:            Sandip Mishra
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       testimonial-carousel-plugin
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function register_testimonial_blocks() {
    register_block_type(__DIR__ . '/build/testimonial-carousel',[
        'render_callback' => 'render_testimonial_carousel_block'
    ]);
    register_block_type(__DIR__ . '/build/testimonial-feedback-form',[
        'render_callback' => 'render_testimonial_form'
    ]);
}
add_action('init', 'register_testimonial_blocks');

require_once plugin_dir_path(__FILE__) . 'build/testimonial-carousel/render.php';
require_once plugin_dir_path(__FILE__) . 'build/testimonial-feedback-form/render.php';

add_action( 'wp_enqueue_scripts', function() {
    // Enqueue frontend styles
    wp_enqueue_style(
        'testimonial-plugin-style',
        plugin_dir_url( __FILE__ ) . 'build/testimonial-carousel/style-index.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/testimonial-carousel/style-index.css' )
    );
});

add_action( 'wp_enqueue_scripts', function() {
    // Enqueue editor styles
    wp_enqueue_style(
        'testimonial-carousel-style',
        plugin_dir_url( __FILE__ ) . 'build/testimonial-carousel/index.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/testimonial-carousel/index.css' )
    );
});

add_action( 'wp_enqueue_scripts', function() {
    // Enqueue frontend styles
    wp_enqueue_style(
        'testimonial-plugin-style',
        plugin_dir_url( __FILE__ ) . 'build/testimonial-feedback-form/style-index.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/testimonial-feedback-form/style-index.css' )
    );
});

add_action( 'wp_enqueue_scripts', function() {
    // Enqueue editor styles
    wp_enqueue_style(
        'testimonial-carousel-style',
        plugin_dir_url( __FILE__ ) . 'build/testimonial-feedback-form/index.css',
        [],
        filemtime( plugin_dir_path( __FILE__ ) . 'build/testimonial-feedback-form/index.css' )
    );
});

// Register the custom post type for testimonials.
function tcp_register_testimonials_post_type() {
    $labels = array(
        'name'               => __( 'Testimonials', 'tcp' ),
        'singular_name'      => __( 'Testimonial', 'tcp' ),
        'menu_name'          => __( 'Testimonials', 'tcp' ),
        'name_admin_bar'     => __( 'Testimonial', 'tcp' ),
        'add_new'            => __( 'Add New', 'tcp' ),
        'add_new_item'       => __( 'Add New Testimonial', 'tcp' ),
        'new_item'           => __( 'New Testimonial', 'tcp' ),
        'edit_item'          => __( 'Edit Testimonial', 'tcp' ),
        'view_item'          => __( 'View Testimonial', 'tcp' ),
        'all_items'          => __( 'All Testimonials', 'tcp' ),
        'search_items'       => __( 'Search Testimonials', 'tcp' ),
        'not_found'          => __( 'No testimonials found.', 'tcp' ),
        'not_found_in_trash' => __( 'No testimonials found in Trash.', 'tcp' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'testimonials' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'supports'           => array('title', 'editor','thumbnail'),
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-format-quote',
    );

    register_post_type( 'testimonial', $args );
}
add_action( 'init', 'tcp_register_testimonials_post_type' );

// Disable Gutenberg editor for the custom post type
function disable_gutenberg_for_testimonial($is_enabled, $post_type) {
    if ($post_type === 'testimonial') {
        return false;
    }
    return $is_enabled;
}
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_for_testimonial', 10, 2);

// Add the testimonial rating meta box
function add_testimonial_rating_meta_box() {
    add_meta_box(
        'testimonial_rating_meta_box',
        'Testimonial Rating',
        'render_testimonial_rating_meta_box',
        'testimonial',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_testimonial_rating_meta_box');

// Render the testimonial rating meta box
function render_testimonial_rating_meta_box( $post ) {
    $rating = get_post_meta( $post->ID, '_testimonial_rating', true );
    $rating = $rating ? $rating : 0;
    ?>
    <div id="testimonial-rating-container" data-rating="<?php echo esc_attr( $rating ); ?>" style="font-size: 20px; display: flex; cursor: pointer;">
        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
            <span class="star" data-value="<?php echo $i; ?>" style="position: relative; display: inline-block; width: 25px; height: 25px;">
                <span class="filled-star" style="position: absolute; top: 0; left: 0; width: 0%; height: 100%; overflow: hidden; color: #FFD700;">★</span>
                <span class="empty-star" style="position: absolute; top: 0; left: 0; color: #DDD;">★</span>
            </span>
        <?php endfor; ?>
        <input type="hidden" id="testimonial_rating" name="testimonial_rating" value="<?php echo esc_attr( $rating ); ?>">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('testimonial-rating-container');
            const stars = container.querySelectorAll('.star');
            const ratingInput = document.getElementById('testimonial_rating');
            let currentRating = parseInt(ratingInput.value);

            updateStars(currentRating);

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    currentRating = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = currentRating;
                    updateStars(currentRating);
                });

                star.addEventListener('mouseover', function() {
                    const hoverValue = parseInt(this.getAttribute('data-value'));
                    updateStars(hoverValue);
                });

                container.addEventListener('mouseleave', function() {
                    updateStars(currentRating);
                });
            });

            function updateStars(rating) {
                stars.forEach((s, index) => {
                    const starValue = index + 1;
                    const filledStar = s.querySelector('.filled-star');
                    const emptyStar = s.querySelector('.empty-star');
                    if (rating >= starValue) {
                        filledStar.style.width = '100%';
                        emptyStar.style.color = '#FFD700'; // Yellow color when filled
                    } else {
                        filledStar.style.width = '0';
                        emptyStar.style.color = '#DDD'; // Gray color when empty
                    }
                });
            }
        });
    </script>
    <?php
}

// Save the testimonial rating
function save_testimonial_rating( $post_id ) {
    // Check if the nonce is set and valid
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

    if ( isset( $_POST['testimonial_rating'] ) ) {
        update_post_meta( $post_id, '_testimonial_rating', sanitize_text_field( $_POST['testimonial_rating'] ) );
    }
}

add_action( 'save_post', 'save_testimonial_rating' );

// Modify the testimonial columns (show the rating)
function modify_testimonial_columns($columns) {
    // Rearrange columns: add Thumbnail, Email, and Rating in the desired order
    $new_columns = [];
    foreach ($columns as $key => $value) {
        if ($key === 'date') {
            $new_columns['thumbnail'] = 'Thumbnail'; // Add thumbnail column
            $new_columns['email'] = 'Email';         // Add email column
            $new_columns['rating'] = 'Rating';       // Add rating column
        }
        $new_columns[$key] = $value;
    }

    return $new_columns;
}

add_filter('manage_edit-testimonial_columns', 'modify_testimonial_columns');

// Display testimonial thumbnail and rating columns
function display_testimonial_custom_columns($column, $post_id) {
    if ($column === 'thumbnail') {
        if (has_post_thumbnail($post_id)) {
            $thumbnail = get_the_post_thumbnail($post_id, 'thumbnail', ['style' => 'max-width: 50px; height: auto; border-radius: 5px;']);
            echo $thumbnail;
        } else {
            echo 'No Thumbnail'; // Fallback if no thumbnail is set
        }
    }
    if ($column === 'email') {
        $email = get_post_meta($post_id, '_testimonial_email', true);
        echo $email ? esc_html($email) : 'No Email';
    }
    if ($column === 'rating') {
        $rating = get_post_meta($post_id, '_testimonial_rating', true);
        $rating = $rating ? $rating : 0;

        // Display graphical star rating
        echo '<span style="font-size: 20px; color: #FFD700; white-space: nowrap;">' . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . '</span>';
    }
}
add_action('manage_testimonial_posts_custom_column', 'display_testimonial_custom_columns', 10, 2);

// Make the Rating column sortable
function make_testimonial_columns_sortable($sortable_columns) {
    $sortable_columns['rating'] = 'rating';
    return $sortable_columns;
}
add_filter('manage_edit-testimonial_sortable_columns', 'make_testimonial_columns_sortable');

// Set custom sorting for Rating column
function sort_testimonial_by_rating($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'rating') {
        $query->set('meta_key', '_testimonial_rating'); // Meta key for rating
        $query->set('orderby', 'meta_value_num');      // Sort by numeric value
    }
}
add_action('pre_get_posts', 'sort_testimonial_by_rating');

// Add testimonial rating to the REST API response
function tcp_add_rating_to_rest_api() {
    register_rest_field( 'testimonial', 'rating', array(
        'get_callback'    => 'tcp_get_testimonial_rating',
        'update_callback' => null, // no need for an update callback in this case
        'schema'          => null,
    ) );
}

// Callback function to get testimonial rating
function tcp_get_testimonial_rating( $object ) {
    $rating = get_post_meta( $object['id'], '_testimonial_rating', true );
    return $rating ? (int) $rating : 0; // Return the rating or 0 if not set
}

add_action( 'rest_api_init', 'tcp_add_rating_to_rest_api' );

// Add the email field in the custom post type 'Testimonial'
function add_testimonial_email_meta_box() {
    add_meta_box(
        'testimonial_email_meta_box',
        'Optional',
        'render_testimonial_email_meta_box',
        'testimonial',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_testimonial_email_meta_box');

// Render the email field in the testimonial post type edit page
function render_testimonial_email_meta_box($post) {
    // Retrieve the saved email value
    $email = get_post_meta($post->ID, '_testimonial_email', true);
    ?>
    <label for="testimonial_email"><?php _e('Email', 'tcp'); ?></label>
    <input type="email" id="testimonial_email" placeholder='Please enter your email' name="testimonial_email" value="<?php echo esc_attr($email); ?>" style="width:100%;" />
    <?php
}

// Save the email when the testimonial post is saved
function save_testimonial_email($post_id) {
    // Check if the nonce is set and valid
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

    // Check if the email field is set
    if (isset($_POST['testimonial_email'])) {
        $email = sanitize_email($_POST['testimonial_email']);

        // Validate the email
        if (is_email($email)) {
            update_post_meta($post_id, '_testimonial_email', $email);
        } else {
            wp_die( 'Invalid email address. Please provide a valid email.' );
        }
    }
}
add_action('save_post', 'save_testimonial_email');

// Add the email field to the REST API response for the Testimonial post type
function tcp_add_email_to_rest_api() {
    register_rest_field(
        'testimonial', // The custom post type slug
        'email', // The field name to be added
        array(
            'get_callback'    => 'tcp_get_testimonial_email', // Callback to retrieve the value
            'update_callback' => null, // No update needed here
            'schema'          => array(
                'type' => 'string', // The type of the field
                'description' => __('The email address associated with the testimonial', 'tcp'),
                'context' => array('view', 'edit'), // Where this field is available
            ),
        )
    );
}

// Callback to retrieve the email value
function tcp_get_testimonial_email( $object ) {
    $email = get_post_meta( $object['id'], '_testimonial_email', true );
    return $email ? sanitize_email($email) : null; // Return the email if set, otherwise null
}

add_action('rest_api_init', 'tcp_add_email_to_rest_api');

// Register REST API Endpoint for Saving Form Data
add_action( 'rest_api_init', function () {
    register_rest_route( 'testimonial-form/v1', '/submit', array(
        'methods'             => 'POST',
        'callback'            => 'save_testimonial_via_meta_box',
        'permission_callback' => '__return_true',
    ) );
} );

function save_testimonial_via_meta_box( $request ) {
    // Retrieve the data from the request
    $params = $request->get_json_params();

    // Sanitize input
    $name       = sanitize_text_field( $params['name'] ?? '' );
    $email      = sanitize_email( $params['email'] ?? '' );
    $experience = sanitize_textarea_field( $params['experience'] ?? '' );
    $rating     = isset( $params['rating'] ) ? intval( $params['rating'] ) : 0;
    $image_data = $params['image_data'] ?? '';

    // Check if required fields are present
    if ( empty( $name ) || empty( $experience ) ) {
        return new WP_Error( 'missing_data', 'Required fields are missing.', array( 'status' => 400 ) );
    }

    // Insert post into CPT (Custom Post Type)
    $post_id = wp_insert_post( array(
        'post_type'   => 'testimonial',
        'post_title'  => $name,
        'post_content'=> $experience,
        'post_status' => 'publish',
    ) );

    // Check if post creation was successful
    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'post_creation_failed', 'Failed to create testimonial.', array( 'status' => 500 ) );
    }

    // Save meta fields for the testimonial
    update_post_meta( $post_id, '_testimonial_email', $email );
    update_post_meta( $post_id, '_testimonial_rating', $rating );

    // Log to confirm the experience has been stored
    $stored_experience = get_post_meta( $post_id, '_testimonial_experience', true );

    // Handle image data (Base64)
    if ( ! empty( $image_data ) ) {
        // Decode the Base64 image data
        $decoded_image = base64_decode( preg_replace( '#^data:image/\w+;base64,#i', '', $image_data ) );

        if ( $decoded_image ) {
            // Save the image to the WordPress Uploads Directory
            $upload_dir = wp_upload_dir();
            $filename = 'testimonial_image_' . time() . '.jpg';
            $file_path = $upload_dir['path'] . '/' . $filename;

            file_put_contents( $file_path, $decoded_image );

            // Insert the image into the Media Library
            $attachment_id = wp_insert_attachment( array(
                'guid'           => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => 'image/jpeg',
                'post_title'     => 'Testimonial Image',
                'post_content'   => '',
                'post_status'    => 'inherit',
            ), $file_path );

            // Generate attachment metadata
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
            wp_update_attachment_metadata( $attachment_id, $attach_data );

            // Set the image as the featured image for the testimonial post
            set_post_thumbnail( $post_id, $attachment_id );
        }
    }

    // Return a successful response
    return rest_ensure_response( array( 'success' => true, 'post_id' => $post_id ) );
}

/**
 * Helper function to upload base64 encoded image
 */
function upload_base64_image( $base64_image, $post_id ) {
    // Decode the base64 image
    $decoded_image = base64_decode( $base64_image );
    error_log( 'Decoded image: ' . print_r( $decoded_image, true ) );

    if ( ! $decoded_image ) {
        return new WP_Error( 'image_decode_error', 'Failed to decode the image.' );
    }

    // Create a temporary file
    $upload_dir = wp_upload_dir();
    $temp_file  = tempnam( sys_get_temp_dir(), 'img_' );

    file_put_contents( $temp_file, $decoded_image );

    $filetype = wp_check_filetype( $temp_file );
    $filename = 'testimonial-image-' . $post_id . '.' . $filetype['ext'];

    // Move the temp file to the uploads directory
    $file = array(
        'name'     => $filename,
        'type'     => $filetype['type'],
        'tmp_name' => $temp_file,
        'error'    => 0,
        'size'     => filesize( $temp_file ),
    );

    // Upload the file to WordPress
    $uploaded_file_id = media_handle_sideload( $file, $post_id );

    // Cleanup the temporary file
    unlink( $temp_file );

    if ( is_wp_error( $uploaded_file_id ) ) {
        return $uploaded_file_id; // Return the error
    }

    return $uploaded_file_id; // Return the attachment ID
}

function tcp_register_hidden_carousel_taxonomy() {
    register_taxonomy(
        'carousel_group', // Taxonomy name
        'testimonial',    // Post type to associate the taxonomy with
        [
            'labels' => [
                'name'          => __('Carousel Groups', 'tcp'),
                'singular_name' => __('Carousel Group', 'tcp'),
            ],
            'public'            => false, // Hide from the front-end
            'show_ui'           => false, // Hide from the admin UI
            'show_in_menu'      => false, // Do not display in the admin menu
            'show_in_nav_menus' => false, // Do not display in navigation menus
            'show_tagcloud'     => false, // Disable tag cloud
            'show_in_rest'      => true,  // Enable REST API
            'rewrite'           => false, // Disable URL rewrites
            'hierarchical'      => false, // Non-hierarchical (like tags)
        ]
    );
}
add_action('init', 'tcp_register_hidden_carousel_taxonomy');

// Hook into the admin menu to add a submenu page.
function tcp_add_testimonial_submenu() {
    // Add submenu under the "Testimonials" menu.
    add_submenu_page(
        'edit.php?post_type=testimonial', // Parent slug
        __('Create carousel', 'tcp'), // Page title
        __('Create carousel', 'tcp'), // Submenu title
        'manage_options', // Capability
        'testimonial-manage', // Submenu slug
        'tcp_render_testimonial_manage_page' // Callback function
    );
     
     // Add submenu under the "Testimonials" menu for viewing carousels.
     add_submenu_page(
        'edit.php?post_type=testimonial', // Parent slug
        __('View Carousels', 'tcp'), // Page title
        __('View Carousels', 'tcp'), // Submenu title
        'manage_options', // Capability
        'view-carousel-groups', // Submenu slug for viewing
        'tcp_render_view_carousels_page' // Callback function for viewing carousels
    );

    // Add a hidden submenu page for editing the carousel.
    add_submenu_page(
        null,                          // Parent slug is null, meaning it won't appear in the menu
        __('Edit Carousel', 'tcp'),    // Page title
        __('Edit Carousel', 'tcp'),    // Menu title
        'manage_options',              // Capability
        'edit-carousel',               // Menu slug
        'tcp_render_edit_carousel_page' // Callback function
    );
}
add_action('admin_menu', 'tcp_add_testimonial_submenu');

// Callback function to render the page.
function tcp_render_testimonial_manage_page() {
    // Handle form submission.
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['testimonial-title']) && isset($_POST['tcp_create_carousel_nonce']) && wp_verify_nonce($_POST['tcp_create_carousel_nonce'], 'tcp_create_carousel')) {

        // Sanitize and retrieve the form input
        //$carousel_title = sanitize_text_field($_POST['testimonial-title']);

        // Check if the title is empty
        if (empty($carousel_title)) {
            // Set error message
            add_settings_error('tcp_create_carousel', 'empty-title', __('Please provide a carousel title.', 'tcp'), 'error');
        } else {
            // Create the carousel (term in the taxonomy)
            //$term = wp_insert_term($carousel_title, 'carousel_group');  // Replace 'carousel_group' with your custom taxonomy

            if (is_wp_error($term)) {
                // Handle the error if term creation fails
                add_settings_error('tcp_create_carousel', 'term-error', __('There was an issue creating the carousel group.', 'tcp'), 'error');
            } else {
                // Set success message
                add_settings_error('tcp_create_carousel', 'success', __('Carousel group created successfully!', 'tcp'), 'updated');
            }
        }
    }

    ?>
    <div class="wrap">
        <h1><?php _e('Create carousel', 'tcp'); ?></h1>

        <!-- Display success or error messages -->
        <?php
        settings_errors('tcp_create_carousel');
        ?>

        <!-- Add a title field -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="text" name="testimonial-title" id="testimonial-title" style="width: 300px;" placeholder="Enter Carousel Name">
            <button type="submit" class="button button-primary"><?php _e('Create', 'tcp'); ?></button>

            <?php wp_nonce_field('tcp_create_carousel', 'tcp_create_carousel_nonce'); ?>
            
            <!-- Render the testimonials table -->
            <?php
                $testimonials_table = new TCP_Testimonials_Table();
                $testimonials_table->prepare_items();
                $testimonials_table->display();
            ?>
            <input type="hidden" name="action" value="tcp_create_carousel">
        </form>
    </div>

    <script type="text/javascript">
    // JavaScript to limit the number of selections to 5
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('input[name="testimonials[]"]');
        const maxSelections = 5;

        // Update the state of checkboxes based on the current selection
        function updateCheckboxState() {
            let selectedCount = document.querySelectorAll('input[name="testimonials[]"]:checked').length;

            checkboxes.forEach(function (checkbox) {
                if (selectedCount >= maxSelections) {
                    if (!checkbox.checked) {
                        checkbox.disabled = true; // Disable unchecked boxes when limit is reached
                    }
                } else {
                    checkbox.disabled = false; // Enable all checkboxes if limit isn't reached
                }
            });
        }

        // Initial state check
        updateCheckboxState();

        // Add event listener to each checkbox to monitor changes
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateCheckboxState();
            });
        });
    });
    </script>
    <?php
}

// Extend WP_List_Table to create a custom table for testimonials.
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class TCP_Testimonials_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct([
            'singular' => __('Testimonial', 'tcp'),
            'plural'   => __('Testimonials', 'tcp'),
            'ajax'     => false, // We won't use Ajax for this table
        ]);
    }

    // Define table columns.
    public function get_columns() {
        return [
            'cb'        => '', // Remove the 'Select All' checkbox column by leaving it empty
            'title'     => __('Title', 'tcp'),
            'email'     => __('Email', 'tcp'), // Add Email column here
            'rating'    => __('Rating', 'tcp'), // Rating column comes after Email
            'date'      => __('Date', 'tcp'),
        ];
    }        

    public function get_sortable_columns() {
        return [
            'rating' => ['rating', true], // Make rating sortable
            'date'   => ['date', true],   // Make date sortable
        ];
    }            

    // Fetch the data for the table.
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = []; // No hidden columns
        $sortable = $this->get_sortable_columns(); // Use sortable columns for sorting
        $this->_column_headers = [$columns, $hidden, $sortable];
    
        // Set default sorting parameters
        $orderby = isset($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'date';
        $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'desc';
    
        // Fetch testimonials (adjust query as needed).
        $args = [
            'post_type'      => 'testimonial',
            'posts_per_page' => -1, // Fetch all testimonials
            'orderby'        => $orderby,
            'order'          => $order,
        ];
    
        // If sorting by rating, modify the query to order by post meta (rating).
        if ($orderby === 'rating') {
            $args['meta_key'] = '_testimonial_rating';
            $args['orderby'] = 'meta_value_num'; // Sort by numerical value of the rating
        }
    
        // Fetch testimonials
        $testimonials = get_posts($args);
    
        $items = [];
        foreach ($testimonials as $testimonial) {
            $rating = get_post_meta($testimonial->ID, '_testimonial_rating', true); // Fetch the rating
            $rating = $rating ? $rating : 0; // Default to 0 if no rating is found
            $email = get_post_meta($testimonial->ID, '_testimonial_email', true); // Fetch the email
    
            $items[] = [
                'ID'       => $testimonial->ID,
                'title'    => $testimonial->post_title,
                'rating'   => $rating, // Add rating here
                'email'    => $email, // Add email here
                'date'     => $testimonial->post_date,
            ];
        }
    
        $this->items = $items;
    }            

    // Define default column behavior.
    protected function column_default($item, $column_name) {
        switch ($column_name) {
            case 'title':
            case 'date':
                return $item[$column_name];
            case 'email':
                return $item[$column_name]; // Display email
            case 'rating':
                // Display stars for the rating, make them yellow and set a wider width
                $rating = $item['rating'];
                return '<span style="color: #FFD700; font-size: 20px; width: 150px; display: inline-block;">' . 
                    str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . 
                    '</span>';
            default:
                return '';
        }
    }     

    // Add checkboxes to the first column (individual row checkboxes)
    protected function column_cb($item) {
        return sprintf('<input type="checkbox" name="testimonials[]" value="%s" />', $item['ID']);
    }
}

function tcp_handle_create_carousel() {
    error_log('FLAG 1'); // Confirm the function is running

    if (isset($_POST['testimonial-title']) && isset($_POST['testimonials']) && isset($_POST['tcp_create_carousel_nonce']) && wp_verify_nonce($_POST['tcp_create_carousel_nonce'], 'tcp_create_carousel')) {
        $carousel_title = sanitize_text_field($_POST['testimonial-title']);
        $selected_testimonials = array_map('intval', $_POST['testimonials']);

        error_log('FLAG 2'); // Confirm valid data received

        // Create the carousel (taxonomy term)
        $term = wp_insert_term($carousel_title, 'carousel_group'); // Assuming 'carousel_group' is your taxonomy

        if (!is_wp_error($term)) {
            $carousel_group_id = $term['term_id'];
            error_log('FLAG 3'); // Confirm term creation

            foreach ($selected_testimonials as $testimonial_id) {
                wp_set_post_terms($testimonial_id, [$carousel_group_id], 'carousel_group', true);
                error_log('Linked Testimonial ID: ' . $testimonial_id); // Debugging linkage
            }

            // Clear the cache to reflect the updates immediately
            wp_cache_flush();

            wp_redirect(admin_url('edit.php?post_type=testimonial&page=testimonial-manage&message=success'));
            exit;
        } else {
            error_log('Error Creating Term: ' . $term->get_error_message());
            wp_redirect(admin_url('edit.php?post_type=testimonial&page=testimonial-manage&message=error'));
            exit;
        }
    } else {
        error_log('Missing or Invalid Data'); // Debugging failed conditions
        wp_redirect(admin_url('edit.php?post_type=testimonial&page=testimonial-manage&message=error'));
        exit;
    }
}
add_action('admin_post_tcp_create_carousel', 'tcp_handle_create_carousel');

function tcp_admin_notices() {
    if (isset($_GET['message'])) {
        if ($_GET['message'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Carousel created successfully!', 'tcp') . '</p></div>';
        } elseif ($_GET['message'] === 'error') {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to create the carousel. Please try again.', 'tcp') . '</p></div>';
        }
    }
}
add_action('admin_notices', 'tcp_admin_notices');

function tcp_render_view_carousels_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('View Carousels', 'tcp'); ?></h1>

        <!-- Render any messages if any -->
        <?php settings_errors('tcp_create_carousel'); ?>

        <h2><?php _e('All Carousel Groups', 'tcp'); ?></h2>

        <?php
        // Fetch all carousel groups (terms in the custom taxonomy)
        $carousels = get_terms([
            'taxonomy'   => 'carousel_group',  // Custom taxonomy name
            'hide_empty' => false,  // Show even empty terms
        ]);

        // Check if there are any carousel groups created
        if (!empty($carousels) && !is_wp_error($carousels)) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" id="name" class="manage-column column-name column-primary">
                            <?php _e('Carousel Name', 'tcp'); ?>
                        </th>
                        <th scope="col" id="actions" class="manage-column column-actions">
                            <?php _e('Actions', 'tcp'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody id="carousel-list">
                    <?php foreach ($carousels as $carousel) { ?>
                        <tr id="carousel-<?php echo esc_attr($carousel->term_id); ?>">
                            <td class="column-primary" data-colname="<?php _e('Carousel Name', 'tcp'); ?>">
                                <strong><?php echo esc_html($carousel->name); ?></strong>
                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text"><?php _e('Show more details', 'tcp'); ?></span>
                                </button>
                            </td>
                            <td data-colname="<?php _e('Actions', 'tcp'); ?>">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=edit-carousel&carousel_id=' . $carousel->term_id)); ?>" class="button button-secondary">
                                    <?php _e('Edit', 'tcp'); ?>
                                </a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_carousel" value="1">
                                    <input type="hidden" name="carousel_id" value="<?php echo esc_attr($carousel->term_id); ?>">
                                    <?php wp_nonce_field('tcp_delete_carousel_nonce'); ?>
                                    <button type="submit" class="button button-secondary" style="background-color: #d9534f; color: white;" title="<?php _e('Delete this carousel', 'tcp'); ?>">
                                        <?php _e('Delete', 'tcp'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="col" class="manage-column column-name">
                            <?php _e('Carousel Name', 'tcp'); ?>
                        </th>
                        <th scope="col" class="manage-column column-actions">
                            <?php _e('Actions', 'tcp'); ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
            <?php
        } else {
            echo '<p>' . __('No carousel groups found.', 'tcp') . '</p>';
        }
        ?>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Handle delete button click via AJAX
            $('.delete-carousel').on('click', function() {
                var carouselId = $(this).data('carousel-id');

                if (confirm('<?php _e("Are you sure you want to delete this carousel?", "tcp"); ?>')) {
                    var data = {
                        action: 'tcp_delete_carousel',
                        carousel_id: carouselId,
                        _wpnonce: '<?php echo wp_create_nonce('tcp_delete_carousel_nonce'); ?>'
                    };

                    $.post(ajaxurl, data, function(response) {
                        if (response.success) {
                            // Remove the deleted carousel row
                            $('#carousel-' + carouselId).remove();
                        } else {
                            alert('<?php _e("Error deleting carousel. Please try again.", "tcp"); ?>');
                        }
                    });
                }
            });
        });
    </script>
    <?php
}

// Handle the AJAX request for deleting a carousel
function tcp_delete_carousel_from_view() {
    if (!isset($_POST['carousel_id']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tcp_delete_carousel_nonce')) {
        wp_send_json_error(['message' => __('Invalid request.', 'tcp')]);
    }

    $carousel_id = intval($_POST['carousel_id']);

    // Delete the carousel (term in the custom taxonomy)
    $deleted = wp_delete_term($carousel_id, 'carousel_group');

    if ($deleted) {
        wp_send_json_success(['message' => __('Carousel deleted successfully.', 'tcp')]);
    } else {
        wp_send_json_error(['message' => __('Error deleting carousel. Please try again.', 'tcp')]);
    }

    wp_die();
}

add_action('wp_ajax_tcp_delete_carousel', 'tcp_delete_carousel_from_view');

// Handle the deletion of the carousel when the button is clicked
function tcp_handle_carousel_deletion() {
    if (isset($_POST['delete_carousel']) && isset($_GET['carousel_id'])) {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tcp_delete_carousel_nonce')) {
            wp_die('Nonce verification failed');
        }

        $carousel_id = intval($_GET['carousel_id']);

        // Delete the carousel (term in the 'carousel_group' taxonomy)
        $deleted = wp_delete_term($carousel_id, 'carousel_group');

        if ($deleted) {
            // Success - redirect to view carousels page
            wp_redirect(admin_url('admin.php?page=view-carousel-groups'));
            exit;  // Important to call exit after redirect
        } else {
            // Failure - show an error message
            wp_die(__('Failed to delete the carousel. Please try again.', 'tcp'));
        }
    }
}
add_action('admin_init', 'tcp_handle_carousel_deletion');

function tcp_render_edit_carousel_page() {
    // Check if carousel_id is set in the URL
    if (!isset($_GET['carousel_id']) || empty($_GET['carousel_id'])) {
        wp_die(__('Invalid carousel ID.', 'tcp'));
    }

    $carousel_id = intval($_GET['carousel_id']);
    $carousel = get_term($carousel_id, 'carousel_group');

    // Check if the carousel exists
    if (is_wp_error($carousel) || !$carousel) {
        wp_die(__('Carousel not found.', 'tcp'));
    }

    // Fetch testimonials assigned to this carousel (linked testimonials)
    $testimonials_in_carousel = get_posts([
        'post_type'      => 'testimonial',
        'numberposts'    => -1,
        'tax_query'      => [
            [
                'taxonomy' => 'carousel_group',
                'field'    => 'term_id',
                'terms'    => $carousel_id,
            ],
        ],
    ]);

    // Fetch testimonials NOT assigned to this carousel (not linked testimonials)
    $all_testimonials = get_posts([
        'post_type'      => 'testimonial',
        'numberposts'    => -1,
        'exclude'        => wp_list_pluck($testimonials_in_carousel, 'ID'), // Exclude linked testimonials
    ]);

    ?>
    <div class="wrap">
        <a href="<?php echo esc_url($_SERVER['HTTP_REFERER']); ?>" class="button"><?php _e('Back', 'tcp'); ?></a>

        <div id="carousel-name" style="display: flex; align-items: center;">
            <h1 style="margin: 0; display: flex; align-items: center;">
                <?php _e('Carousel:', 'tcp'); ?>
                <span id="carousel-name-display" style="margin-left: 10px;"><?php echo esc_html($carousel->name); ?></span>
                <a href="javascript:void(0);" id="edit-carousel-name" style="margin-left: 10px; font-size: 16px; color: #0073aa;">
                    <span class="dashicons dashicons-edit"></span>
                </a>
            </h1>
            <div id="carousel-name-edit" style="display: none; margin-left: 10px;">
                <input type="text" id="carousel-name-input" value="<?php echo esc_html($carousel->name); ?>" style="font-size: 1em; padding: 4px; width: auto;">
                <button id="save-carousel-name" class="button-primary" style="margin-left: 5px;"><?php _e('Save', 'tcp'); ?></button>
            </div>
        </div>

        <script type="text/javascript">
            document.getElementById('edit-carousel-name').addEventListener('click', function () {
                document.getElementById('carousel-name-display').style.display = 'none';
                document.getElementById('edit-carousel-name').style.display = 'none';
                document.getElementById('carousel-name-edit').style.display = 'flex';
            });

            document.getElementById('save-carousel-name').addEventListener('click', function () {
                var newName = document.getElementById('carousel-name-input').value;
                var carouselId = <?php echo $carousel_id; ?>;

                if (newName !== '') {
                    var data = {
                        action: 'tcp_save_carousel_name',
                        carousel_id: carouselId,
                        new_name: newName,
                        _wpnonce: '<?php echo wp_create_nonce('tcp_save_carousel_name_nonce'); ?>'
                    };

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                document.getElementById('carousel-name-display').textContent = newName;
                                document.getElementById('carousel-name-display').style.display = 'inline';
                                document.getElementById('edit-carousel-name').style.display = 'inline';
                                document.getElementById('carousel-name-edit').style.display = 'none';
                            } else {
                                alert('Error: ' + response.message);
                            }
                        } else {
                            alert('Request failed. Please try again.');
                        }
                    };
                    xhr.send('action=' + data.action + '&carousel_id=' + data.carousel_id + '&new_name=' + encodeURIComponent(data.new_name) + '&_wpnonce=' + data._wpnonce);
                }
            });
        </script>

        <div style="text-align: right; margin-bottom: 20px;">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_carousel" value="1">
                <input type="hidden" name="carousel_id" value="<?php echo esc_attr($carousel->term_id); ?>">
                <?php wp_nonce_field('tcp_delete_carousel_nonce'); ?>
                <button type="submit" class="button button-secondary" style="background-color: #d9534f; color: white;" title="<?php _e('Delete this carousel', 'tcp'); ?>">
                    <?php _e('Delete Carousel', 'tcp'); ?>
                </button>
            </form>
        </div>

        <h2><?php _e('Testimonials in Carousel', 'tcp'); ?></h2>
        <form id="removeTestimonialForm">
            <table class="wp-list-table widefat fixed striped" id="firstTable">
                <thead>
                    <tr>
                        <th><?php _e('Select', 'tcp'); ?></th>
                        <th><?php _e('Title', 'tcp'); ?></th>
                        <th><?php _e('Date', 'tcp'); ?></th>
                        <th><?php _e('Rating', 'tcp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($testimonials_in_carousel)) : ?>
                        <?php foreach ($testimonials_in_carousel as $testimonial) { 
                            $rating = get_post_meta($testimonial->ID, '_testimonial_rating', true);
                            $rating_display = $rating 
                                ? '<span style="color: #FFD700; font-size: 16px;">' . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . '</span>' 
                                : __('No rating', 'tcp');
                        ?>
                            <tr>
                                <td><input type="checkbox" name="testimonials_in_carousel[]" value="<?php echo esc_attr($testimonial->ID); ?>"></td>
                                <td><?php echo esc_html($testimonial->post_title); ?></td>
                                <td><?php echo esc_html($testimonial->post_date); ?></td>
                                <td><?php echo $rating_display; ?></td>
                            </tr>
                        <?php } ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4"><?php _e('No testimonials in this carousel.', 'tcp'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p>
            <button type="button" id="removeTestimonialBtn" class="button-primary"><?php _e('Remove', 'tcp'); ?></button>
            </p>
        </form>

        <h2><?php _e('Available Testimonials', 'tcp'); ?></h2>
        <form id="addTestimonialForm">
            <table class="wp-list-table widefat fixed striped" id="secondTable">
                <thead>
                    <tr>
                        <th><?php _e('Select', 'tcp'); ?></th>
                        <th><?php _e('Title', 'tcp'); ?></th>
                        <th><?php _e('Date', 'tcp'); ?></th>
                        <th><?php _e('Rating', 'tcp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_testimonials)) : ?>
                        <?php foreach ($all_testimonials as $testimonial) { 
                            $rating = get_post_meta($testimonial->ID, '_testimonial_rating', true);
                            $rating_display = $rating 
                                ? '<span style="color: #FFD700; font-size: 16px;">' . str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) . '</span>' 
                                : __('No rating', 'tcp');
                        ?>
                            <tr>
                                <td><input type="checkbox" name="testimonials[]" value="<?php echo esc_attr($testimonial->ID); ?>"></td>
                                <td><?php echo esc_html($testimonial->post_title); ?></td>
                                <td><?php echo esc_html($testimonial->post_date); ?></td>
                                <td><?php echo $rating_display; ?></td>
                            </tr>
                        <?php } ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4"><?php _e('No available testimonials.', 'tcp'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p>
            <button type="button" id="addTestimonialBtn" class="button-primary"><?php _e('Add', 'tcp'); ?></button>
            </p>
        </form>
    </div>

    <!-- Inline JS to handle edit/save carousel name functionality -->
    <script type="text/javascript">
        document.getElementById('edit-carousel-name').addEventListener('click', function() {
            // Hide current carousel name and show the input field
            document.getElementById('carousel-name-display').style.display = 'none';
            document.getElementById('edit-carousel-name').style.display = 'none';
            document.getElementById('carousel-name-edit').style.display = 'block';
        });

        document.getElementById('save-carousel-name').addEventListener('click', function() {
            var newName = document.getElementById('carousel-name-input').value;
            var carouselId = <?php echo $carousel_id; ?>;

            if (newName !== '') {
                var data = {
                    action: 'tcp_save_carousel_name',
                    carousel_id: carouselId,
                    new_name: newName,
                    _wpnonce: '<?php echo wp_create_nonce('tcp_save_carousel_name_nonce'); ?>'
                };

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        // On success, update the carousel name on the page and hide the input field
                        var response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            // Update carousel name
                            document.getElementById('carousel-name-display').textContent = newName;

                            // Show the saved name and hide the input field and save button
                            document.getElementById('carousel-name-display').style.display = 'inline';
                            document.getElementById('edit-carousel-name').style.display = 'inline';
                            document.getElementById('carousel-name-edit').style.display = 'none';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } else {
                        alert('Request failed. Please try again.');
                    }
                };
                xhr.send('action=' + data.action + '&carousel_id=' + data.carousel_id + '&new_name=' + encodeURIComponent(data.new_name) + '&_wpnonce=' + data._wpnonce);
            }
        });
    </script>

    <!-- Inline JS to handle add/remove without page reload -->
    <script type="text/javascript">
        document.getElementById('addTestimonialBtn').addEventListener('click', function() {
            var selectedTestimonials = [];
            var checkboxes = document.querySelectorAll('input[name="testimonials[]"]:checked');
            checkboxes.forEach(function(checkbox) {
                selectedTestimonials.push(checkbox.value);
            });

            if (selectedTestimonials.length > 0) {
                var data = {
                    action: 'tcp_add_to_carousel',
                    carousel_id: <?php echo $carousel_id; ?>,
                    testimonials: selectedTestimonials,
                    _wpnonce: '<?php echo wp_create_nonce('tcp_create_carousel'); ?>'
                };

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload(); // Refresh the page after adding testimonials
                    }
                };
                xhr.send('action=' + data.action + '&carousel_id=' + data.carousel_id + '&testimonials[]=' + data.testimonials.join('&testimonials[]=') + '&_wpnonce=' + data._wpnonce);
            }
        });

        document.getElementById('removeTestimonialBtn').addEventListener('click', function() {
            var selectedTestimonials = [];
            var checkboxes = document.querySelectorAll('input[name="testimonials_in_carousel[]"]:checked');
            checkboxes.forEach(function(checkbox) {
                selectedTestimonials.push(checkbox.value);
            });

            if (selectedTestimonials.length > 0) {
                var data = {
                    action: 'tcp_remove_from_carousel',
                    carousel_id: <?php echo $carousel_id; ?>,
                    testimonials: selectedTestimonials,
                    _wpnonce: '<?php echo wp_create_nonce('tcp_create_carousel'); ?>'
                };

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload(); // Refresh the page after removing testimonials
                    }
                };
                xhr.send('action=' + data.action + '&carousel_id=' + data.carousel_id + '&testimonials[]=' + data.testimonials.join('&testimonials[]=') + '&_wpnonce=' + data._wpnonce);
            }
        });
//here
function editFunction() {
    let currentSum = 0; // Initialize the count variable

    // Select the first and second tables
    const firstTable = document.getElementById('firstTable');
    const secondTable = document.getElementById('secondTable');
    const checkboxes = secondTable.querySelectorAll('input[type="checkbox"]');

    // Function to update the current sum and disable/enable checkboxes
    function updateCurrentSum() {
        // Count only valid rows in the first table (excluding "No testimony" message)
        const tbodyRows = firstTable.querySelectorAll('tbody tr');
        currentSum = 0; // Reset currentSum before counting

        tbodyRows.forEach(row => {
            // Check if the row has content (not the "No testimony" message)
            if (row.querySelector('td') && row.querySelector('td').textContent !== 'No testimonials in this carousel.') {
                currentSum += 1;
            }
        });

        console.log('Rows in first table:', currentSum);

        // Count selected checkboxes in the second table
        const selectedCheckboxes = secondTable.querySelectorAll('input[type="checkbox"]:checked').length;
        console.log('Selected checkboxes in second table:', selectedCheckboxes);

        // Update the current sum
        currentSum += selectedCheckboxes;
        console.log('Current Sum:', currentSum);

        // Check if the sum is greater than or equal to 5
        if (currentSum >= 5) {
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = true; // Disable unselected checkboxes
                }
            });
        } else {
            checkboxes.forEach(cb => {
                cb.disabled = false; // Enable checkboxes
            });
        }
    }

    // Add event listeners to checkboxes in the second table
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updateCurrentSum();
        });
    });

    // Monitor changes in the first table (e.g., adding or removing rows)
    const firstTableObserver = new MutationObserver(() => {
        updateCurrentSum();
    });

    // Observe the first table for changes in its rows
    firstTableObserver.observe(firstTable, { childList: true, subtree: true });

    // Initial call to set up the state
    updateCurrentSum();
}

// Call the edit function
editFunction();
</script>

<?php }

function tcp_save_carousel_name() {
    // Verify nonce
    if ( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tcp_save_carousel_name_nonce') ) {
        wp_send_json_error(['message' => __('Nonce verification failed.', 'tcp')]);
    }

    // Check if carousel ID and new name are provided
    if ( isset($_POST['carousel_id']) && isset($_POST['new_name']) ) {
        $carousel_id = intval($_POST['carousel_id']);
        $new_name = sanitize_text_field($_POST['new_name']);

        // Update the carousel name in the database
        $term = get_term($carousel_id, 'carousel_group');
        if ($term && !is_wp_error($term)) {
            // Update the term name
            wp_update_term($carousel_id, 'carousel_group', [
                'name' => $new_name,
            ]);
            wp_send_json_success(); // Respond with success
        }
    }

    // If we reach here, something went wrong
    wp_send_json_error(['message' => __('Error saving carousel name.', 'tcp')]);
}
add_action('wp_ajax_tcp_save_carousel_name', 'tcp_save_carousel_name');

// Add AJAX actions for adding, removing, and deleting testimonials
add_action('wp_ajax_tcp_add_to_carousel', 'tcp_add_to_carousel');
add_action('wp_ajax_tcp_remove_from_carousel', 'tcp_remove_from_carousel');

function tcp_add_to_carousel() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tcp_create_carousel')) {
        die('Permission denied');
    }

    $carousel_id = intval($_POST['carousel_id']);
    $testimonials = array_map('intval', $_POST['testimonials']);

    if (!empty($testimonials)) {
        foreach ($testimonials as $testimonial_id) {
            wp_set_post_terms($testimonial_id, [$carousel_id], 'carousel_group', true);
        }
    }

    echo 'success';
    wp_die();
}

function tcp_remove_from_carousel() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'tcp_create_carousel')) {
        die('Permission denied');
    }

    $carousel_id = intval($_POST['carousel_id']);
    $testimonials = array_map('intval', $_POST['testimonials']);

    if (!empty($testimonials)) {
        foreach ($testimonials as $testimonial_id) {
            wp_remove_object_terms($testimonial_id, $carousel_id, 'carousel_group');
        }
    }

    echo 'success';
    wp_die();
}

add_action('admin_enqueue_scripts', 'tcp_enqueue_admin_scripts');
function tcp_enqueue_admin_scripts($hook) {
    // Only load on the edit carousel page
    if ($hook !== 'admin.php?page=testimonial-edit-carousel') {
        return;
    }

    wp_enqueue_script('jquery'); // Ensure jQuery is loaded
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/testimonials', [
        'methods' => 'GET',
        'callback' => 'get_testimonials',
    ]);
});

function get_testimonials(WP_REST_Request $request) {
    // Get carousel group taxonomy term
    $carousel_group = $request->get_param('carousel_group');
    
    // Query testimonials based on taxonomy (carousel_group)
    $args = [
        'post_type' => 'testimonial', // Assuming 'testimonial' is the post type
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'carousel_group', // The taxonomy name
                'field' => 'id', // or 'slug' depending on how you pass the value
                'terms' => $carousel_group,
            ]
        ]
    ];

    $testimonials_query = new WP_Query($args);
    $testimonials = [];

    while ($testimonials_query->have_posts()) {
        $testimonials_query->the_post();
        
        // Get custom fields (rating and email)
        $rating = get_post_meta(get_the_ID(), '_testimonial_rating', true); // Assuming '_testimonial_rating' is the custom field key
        $rating = $rating ? $rating : 0; // Default to 0 if no rating
        
        // Get the custom email field
        $email = get_post_meta(get_the_ID(), '_testimonial_email', true); // Assuming '_testimonial_email' is the custom field key for email
        
        // Prepare testimonial data
        $testimonial_data = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'content' => get_the_content(),
            'featured_image' => get_the_post_thumbnail_url(),
            'rating' => $rating,  // Include the rating field
            'email' => $email,    // Include the email field
        ];
        
        $testimonials[] = $testimonial_data;
    }

    wp_reset_postdata();

    return rest_ensure_response($testimonials);
}

add_action( 'wp_ajax_submit_testimonial', 'handle_frontend_testimonial_submission' );

function handle_frontend_testimonial_submission() {
    // Retrieve the data from the request
    $params = $_POST;

    // Sanitize input
    $name       = sanitize_text_field( $params['name'] ?? '' );
    $email      = sanitize_email( $params['email'] ?? '' );
    $experience = sanitize_textarea_field( $params['experience'] ?? '' );
    $rating     = isset( $params['rating'] ) ? intval( $params['rating'] ) : 0;
    $image_data = $params['image_data'] ?? '';

    // Check if required fields are present
    if ( empty( $name ) || empty( $experience ) ) {
        wp_send_json_error( array( 'message' => 'Required fields are missing.' ) );
        return;
    }

    // Insert post into CPT (Custom Post Type)
    $post_id = wp_insert_post( array(
        'post_type'   => 'testimonial',
        'post_title'  => $name,
        'post_content'=> $experience,
        'post_status' => 'publish',
    ) );

    // Check if post creation was successful
    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => 'Failed to create testimonial.' ) );
        return;
    }

    // Save meta fields for the testimonial
    update_post_meta( $post_id, '_testimonial_email', $email );
    update_post_meta( $post_id, '_testimonial_rating', $rating );

    // Handle image data (Base64)
    if ( ! empty( $image_data ) ) {
        // Decode the Base64 image data
        $decoded_image = base64_decode( preg_replace( '#^data:image/\w+;base64,#i', '', $image_data ) );

        if ( $decoded_image ) {
            // Save the image to the WordPress Uploads Directory
            $upload_dir = wp_upload_dir();
            $filename = 'testimonial_image_' . time() . '.jpg';
            $file_path = $upload_dir['path'] . '/' . $filename;

            file_put_contents( $file_path, $decoded_image );

            // Insert the image into the Media Library
            $attachment_id = wp_insert_attachment( array(
                'guid'           => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => 'image/jpeg',
                'post_title'     => 'Testimonial Image',
                'post_content'   => '',
                'post_status'    => 'inherit',
            ), $file_path );

            // Generate attachment metadata
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
            wp_update_attachment_metadata( $attachment_id, $attach_data );

            // Set the image as the featured image for the testimonial post
            set_post_thumbnail( $post_id, $attachment_id );
        }
    }

    // Return a successful response
    wp_send_json_success( array( 'post_id' => $post_id ) );
}
