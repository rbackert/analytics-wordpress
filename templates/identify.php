<?php
/**
 * Identify
 *
 * @package analytics-wordpress
 */

?>
<script type="text/javascript">
analytics.identify(
<?php
echo '"' . esc_js( $user_id ) . '"';
if ( ! empty( $traits ) ) {
	echo ', ' . wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $traits ) );
} else {
	echo ', {}';
}
if ( ! empty( $options ) ) {
	echo ', ' . wp_json_encode( Segment_Analytics_WordPress::esc_js_deep( $options ) ); }
?>
);
</script>
