<?php
/**
 * Discounts table.
 *
 * @var array $discounts Discounts.
 * @var array $roles Roles.
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit();
$discount_types = array(
	'percentage' => __( 'Percentage', 'wc-wholesale-manager' ),
	'fixed'      => __( 'Fixed', 'wc-wholesale-manager' ),
);

if ( ! isset( $roles ) ) {
	$roles = Helper::get_wholesaler_roles();
}

if ( empty( $roles ) ) {
	return;
}
$role_keys = wp_list_pluck( $roles, 'slug' );
$values    = array();
foreach ( $roles as $role ) {
	$defaults = array(
		'role'     => $role->slug,
		'discount' => '',
		'type'     => 'percentage',
		'enabled'  => 'no',
		'name'     => $role->name,
	);
	if ( isset( $discounts[ $role->slug ] ) ) {
		$values[ $role->slug ] = wp_parse_args( $discounts[ $role->slug ], $defaults );
	} else {
		$values[ $role->slug ] = $defaults;
	}
}
?>
<table class="wcwm-role-based-discounts-table widefat striped">
	<thead>
	<tr>
		<th>
			<?php esc_html_e( 'Role', 'wc-wholesale-manager' ); ?>
		</th>
		<th><?php esc_html_e( 'Discount', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Discount Type', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Enabled', 'wc-wholesale-manager' ); ?></th>
	</tr>
	<tbody>
	<?php foreach ( $values as $key => $value ) : ?>
		<tr>
			<td>
				<strong>
					<?php echo esc_html( $value['name'] ); ?>
				</strong>
			</td>
			<td>
				<?php printf( '<input type="number" name="_wcwm_discounts[%s][discount]" value="%s" />', esc_attr( $key ), isset( $value['discount'] ) ? esc_attr( $value['discount'] ) : '' ); ?>
				<?php printf( '<input type="hidden" name="_wcwm_discounts[%s][role]" value="%s" />', esc_attr( $key ), esc_attr( isset( $value['role'] ) && in_array( $value['role'], $role_keys, true ) ? esc_attr( $value['role'] ) : esc_attr( $role->slug ) ) ); ?>
			</td>
			<td>
				<?php printf( '<select name="_wcwm_discounts[%s][type]">', esc_attr( $key ) ); ?>
				<?php foreach ( $discount_types as $type => $label ) : ?>
					<?php printf( '<option value="%s" %s>%s</option>', esc_attr( $type ), selected( isset( $value['type'] ) ? esc_attr( $value['type'] ) : 'percentage', esc_attr( $type ), false ), esc_html( $label ) ); ?>
				<?php endforeach; ?>
			</td>
			<td>
				<?php printf( '<input type="checkbox" name="_wcwm_discounts[%s][enabled]" value="yes" %s />', esc_attr( $key ), checked( isset( $value['enabled'] ) ? esc_attr( $value['enabled'] ) : 'no', 'yes', false ) ); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
