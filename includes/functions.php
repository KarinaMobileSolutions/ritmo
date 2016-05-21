<?php
/**
 * Show ritmo with embed
 *
 * @param $post_id (integer) Post ID
 */
function ritmo( $post_id = false ) {

	// Set Post ID
	if( empty($post_id) ) {
		global $post;
		$post_id = $post->ID;
	}

	// Check Post ID
	if( empty($post_id) )
		return;

	// Get Ritmo URL from post meta
	$ritmo_url =  get_post_meta($post_id, 'ritmo_url', true);

	// Check Ritmo url
	if( empty($ritmo_url) )
		return;

	$ritmo_url = rtrim($ritmo_url, '/');
	return '<iframe src="'.$ritmo_url.'/embed" style="border:none" width="300" height="379"></iframe>';
}