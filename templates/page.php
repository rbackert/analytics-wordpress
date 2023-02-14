<?php
/**
 * Page
 *
 * @package analytics-wordpress
 */

?>
<script type="text/javascript">
analytics.page(
<?php
echo '"' . esc_js( $category ) . '"';
if ( ! empty( $name ) ) {
	echo ', "' . esc_js( $name ) . '"';
}
echo ', ' . (
	empty( $properties )
		? '{}'
		: wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $properties ) )
);
if ( ! empty( $options ) ) {
	echo ', ' . wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $options ) );
}
?>
);
<?php if ( $http_event ) : ?>
analytics.ajaxurl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"

jQuery( document ).ready( function( $ ) {
	var data = {
		action : 'segment_unset_cookie',
		key    : '<?php echo wp_json_encode( $http_event ); ?>',
		'segment-nonce': <?php echo wp_json_encode( wp_create_nonce( 'segment-unset_cookie' ) ); ?>,
	},
	success = function( response ) {
		console.log( response );
	};

	$.post( analytics.ajaxurl, data, success );
});
<?php endif; ?>
</script>
