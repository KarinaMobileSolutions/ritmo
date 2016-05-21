<?php
/**
 * Adds settings part to plugin
 * Originally, wrote by Pippin Williamson
 *
 * @author          Pippin Williamson
 */
if ( ! defined( 'ABSPATH' ) ) exit; // No direct access allowed ;)

add_action( 'admin_menu', 'ritmo_add_settings_menu', 11 );

/**
 * Add admin page settings
 */
function ritmo_add_settings_menu() {
	global $ritmo_settings;
	
	add_submenu_page(
		'options-general.php',
		__( 'Ritmo', 'ritmo' ),
		__( 'Ritmo', 'ritmo' ),
		'manage_options',
		'ritmo',
		'ritmo_render_settings'
	);
}

/**
 * Gets saved settings from WP core
 *
 * @since           2.0
 * @return          array Settings
 */
function ritmo_get_settings() {
	$settings = get_option( 'ritmo_settings' );
	
	if ( empty( $settings ) ) {
		update_option( 'ritmo_settings', array(
			'post_type'	=>  '',
		) );
	}
	
	return apply_filters( 'ritmo_get_settings', $settings );
}

/**
 * Registers settings in WP core
 *
 * @since           2.0
 * @return          void
 */
function ritmo_register_settings() {
	if ( false == get_option( 'ritmo_settings' ) )
		add_option( 'ritmo_settings' );

	foreach( ritmo_get_registered_settings() as $tab => $settings ) {
		add_settings_section(
			'ritmo_settings_' . $tab,
			__return_null(),
			'__return_false',
			'ritmo_settings_' . $tab
		);

		foreach( $settings as $option ) {
			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'ritmo_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'ritmo_' . $option['type'] . '_callback' ) ? 'ritmo_' . $option['type'] . '_callback' : 'ritmo_missing_callback',
				'ritmo_settings_' . $tab,
				'ritmo_settings_' . $tab,
				array(
					'id'      => isset( $option['id'] ) ? $option['id'] : null,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : ''
				)
			);

			register_setting( 'ritmo_settings', 'ritmo_settings', 'ritmo_settings_sanitize' );
		}
	}
}
add_action( 'admin_init', 'ritmo_register_settings' );

/**
 * Gets settings tabs
 *
 * @since               2.0
 * @return              array Tabs list
 */
function ritmo_get_tabs() {
    $tabs = array(
        'content'	=>  sprintf( __( '%s Content', 'ritmo' ), '<span class="dashicons dashicons-download"></span>' ),
    );
    return $tabs;
}

/**
 * Sanitizes and saves settings after submit
 *
 * @since               2.0
 * @param               array $input Settings input
 * @return              array New settings
 */
function ritmo_settings_sanitize( $input = array() ) {

	global $ritmo_settings;

	if( empty( $_POST['_wp_http_referer'] ) )
		return $input;

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings  	= ritmo_get_registered_settings();
	$tab       	= isset( $referrer['tab'] ) ? $referrer['tab'] : 'content';

	$input 		= $input ? $input : array();
	$input 		= apply_filters( 'ritmo_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $key ][ 'type' ] ) ? $settings[ $tab ][ $key ][ 'type' ] : false;

		if( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'ritmo_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[ $key ] = apply_filters( 'ritmo_settings_sanitize', $value, $key );
	}


	// Loop through the whitelist and unset any that are empty for the tab being saved
	if( ! empty( $settings[ $tab ] ) ) {
		foreach( $settings[ $tab ] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if( empty( $input[ $key ] ) ) {
				unset( $ritmo_settings[ $key ] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $ritmo_settings, $input );

	add_settings_error( 'wpp-notices', '', __( 'Settings updated', 'ritmo' ), 'updated' );

	return $output;

}

/**
 * Get settings fields
 *
 * @since           2.0
 * @return          array Fields
 */
function ritmo_get_registered_settings() {
	// Get post types
	$get_post_types = get_post_types();

	// Clean post type
	unset($get_post_types['attachment']);
	unset($get_post_types['revision']);
	unset($get_post_types['nav_menu_item']);
	unset($get_post_types['wp-types-group']);
	unset($get_post_types['wp-types-user-group']);

	// Set post type name
	foreach ($get_post_types as $value) {
		$post_type = get_post_type_object($value);

		$post_types[$value] = $post_type->labels->singular_name;
	}

    $settings = apply_filters( 'ritmo_registered_settings', array(
        'content'             =>  apply_filters( 'ritmo_content_settings', array(
			'post_type'      =>  array(
				'id'            =>  'post_type',
				'name'          =>  __( 'Post type', 'ritmo' ),
				'type'          =>  'multicheck',
				'options'		=>	$post_types,
			),
        ) ),
    ) );
    return $settings;
}


/* Form Callbacks Made by EDD Development Team */
function ritmo_header_callback( $args ) {
	echo '<hr/>';
}

function ritmo_checkbox_callback( $args ) {
	global $ritmo_settings;

	$checked = isset($ritmo_settings[$args['id']]) ? checked(1, $ritmo_settings[$args['id']], false) : '';
	$html = '<input type="checkbox" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_multicheck_callback( $args ) {
    global $ritmo_settings;

    $html = '';
    foreach( $args['options'] as $key => $value ) {
        $option_name = $args['id'] . '-' . $key;
        ritmo_checkbox_callback( array(
            'id'        =>  $option_name,
            'desc'      =>  $value
        ) );
        echo '<br>';
    }

    echo $html;
}

function ritmo_radio_callback( $args ) {
	global $ritmo_settings;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $ritmo_settings[ $args['id'] ] ) && $ritmo_settings[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $ritmo_settings[ $args['id'] ] ) )
			$checked = false;

		echo '<input name="ritmo_settings[' . $args['id'] . ']"" id="ritmo_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>';
		echo '<label for="ritmo_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label>&nbsp;&nbsp;';
	endforeach;

	echo '<p class="description">' . $args['desc'] . '</p>';
}

function ritmo_text_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_number_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_textarea_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<textarea class="large-text" cols="50" rows="5" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_password_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_missing_callback($args) {
	echo '&ndash;';
	return false;
}


function ritmo_select_callback($args) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_color_select_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_rich_editor_callback( $args ) {
	global $ritmo_settings, $wp_version;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		$html = wp_editor( stripslashes( $value ), 'ritmo_settings[' . $args['id'] . ']', array( 'textarea_name' => 'ritmo_settings[' . $args['id'] . ']' ) );
	} else {
		$html = '<textarea class="large-text" rows="10" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_upload_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text ritmo_upload_field" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="ritmo_settings_upload_button button-secondary" value="' . __( 'Upload File', 'ritmo' ) . '"/></span>';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_color_callback( $args ) {
	global $ritmo_settings;

	if ( isset( $ritmo_settings[ $args['id'] ] ) )
		$value = $ritmo_settings[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="wpp-color-picker" id="ritmo_settings[' . $args['id'] . ']" name="ritmo_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="ritmo_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

function ritmo_render_settings() {
	global $ritmo_settings;
	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], ritmo_get_tabs() ) ? $_GET[ 'tab' ] : 'content';

	ob_start();
	?>
	<div class="wrap wpp-settings-wrap">
		<h2><?php _e('Settings','ritmo') ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( ritmo_get_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo $tab_name;
				echo '</a>';
			}
			?>
		</h2>
		<?php echo settings_errors( 'wpp-notices' ); ?>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'ritmo_settings' );
				do_settings_fields( 'ritmo_settings_' . $active_tab, 'ritmo_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}