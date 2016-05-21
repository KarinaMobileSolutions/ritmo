<?php
/*
Plugin Name: Ritmo
Plugin URI: https://wordpress.org/plugins/ritmo
Description: Ritmo player for wordpress
Author: Mostafa Soufi
Version: 1.0
Author URI: http://mostafa-soufi.ir
Text Domain: ritmo
*/

define('WP_RITMO_DIR', plugin_dir_url(__FILE__));

load_plugin_textdomain('ritmo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

class Ritmo{

	/**
	 * Constructors plugin
	 *
	 * @param Not param
	 */
	public function __construct() {
		// Translate plugin name
		__('Ritmo', 'auto-slug-cleaner');
		__('Ritmo player for wordpress', 'auto-slug-cleaner');

		// Include files
        $this->includes();

        // Init setting
        $this->setting();

		add_shortcode( 'ritmo', array($this, 'shortcode') );

		// Register meta box
		if ( is_admin() ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        	add_action( 'save_post', array( $this, 'save_meta_box') );
        }
	}

	/**
	 * Includes plugin files
	 *
	 * @param  Not param
	 */
	public function includes() {
		$files = array(
            'widget',
			'includes/functions',
		);
		
		foreach($files as $file) {
			include_once dirname( __FILE__ ) . '/' . $file . '.php';
		}
	}

    /**
     * Setting plugin
     *
     * @param  Not param
     */
    public function setting() {
        require_once( 'includes/settings.php' );
        global $ritmo_settings;
        $ritmo_settings = ritmo_get_settings();
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        global $ritmo_settings;

        // Get post type
        foreach ($ritmo_settings as $field => $index) {
            if( $field = 'post_type-'.$field ) {
                $post_types[] = str_replace('post_type-', '', $field);
            }
        }
 
        if ( in_array( $post_type, $post_types ) ) {
            add_meta_box(
                'ritmo_meta_box_name',
                __( 'Ritmo', 'ritmo' ),
                array( $this, 'render_meta_box_content' ),
                $post_type,
                'advanced',
                'high'
            );
        }
    }
 
    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function save_meta_box( $post_id ) {

    	// Check if ritmo url us set
        if ( empty( $_POST['ritmo_url'] ) ) {
            return $post_id;
        }

        // Check if nonce is set.
        if ( ! isset( $_POST['ritmo_inner_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['ritmo_inner_nonce'];
 
        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'ritmo' ) ) {
            return $post_id;
        }
 
        /*
         * If this is an autosave, form has not been submitted,
         * so we don't want to do anything.
         */
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
 
        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
 
        /* OK, it's safe for us to save the data now. */
 
        // Sanitize the user input.
        $data = $_POST['ritmo_url'];
 
        // Update the meta field.
        update_post_meta( $post_id, 'ritmo_url', $data );
    }
 
 
    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {
 
        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'ritmo', 'ritmo_inner_nonce' );
 
        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, 'ritmo_url', true );
 
        // Display the form, using the current value.
        ?>
        <label for="ritmo_url">
            <?php _e( 'Enter Ritmo url', 'ritmo' ); ?>
        </label>
        <p><input type="text" id="ritmo_url" name="ritmo_url" value="<?php echo esc_attr( $value ); ?>" style="width:100%" dir="ltr" /></p>
        <?php
    }

    /**
     * Create shortcode for show embeded in post/page or text widget
     *
     * @param Not param
     */
    public function shortcode() {
		return ritmo();
    }
}

// Create object of plugin
$Ritmo = new Ritmo;