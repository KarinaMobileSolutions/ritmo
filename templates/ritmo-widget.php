<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'ritmo' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'Ritmo URL', 'ritmo' ); ?></label> 
	<input class="widefat" dir="ltr" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>">
	<p class="description"><?php _e( 'You should enter ritmo url (album, track or playlist', 'ritmo' ); ?></p>
</p>