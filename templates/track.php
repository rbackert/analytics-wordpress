<?php
/**
 * Settings
 *
 * @package analytics-wordpress
 */

?>
<script type="text/javascript">
analytics.track(
<?php
echo implode(
	',',
	array(
		"'" . esc_js( $event ) . "'",
		( empty( $properties ) ? '{}' : wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $properties ) ) ),
		( empty( $options ) ? '{}' : wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $options ) ) ),
	)
);
?>
);
<?php if ( $http_event ) : ?>
fetch(
	'<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
	{
		method: 'POST',
		body: new URLSearchParams({
			action: 'segment_unset_cookie',
			key: '<?php echo esc_js( $http_event ); ?>',
			'segment-nonce': '<?php echo esc_js( wp_create_nonce( 'segment-unset_cookie' ) ); ?>',
		}),
	}
);
<?php endif; ?>
</script>
