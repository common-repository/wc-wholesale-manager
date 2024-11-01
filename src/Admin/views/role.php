<?php
/**
 * Display roles in a table.
 *
 * @since   1.0.0
 * @package WooCommerceWholesaleManager
 */

use WooCommerceWholesaleManager\Helper;

defined( 'ABSPATH' ) || exit;

$default_role = Helper::get_default_wholesaler_role();
?>
<h2 class="wcwm-roles-heading">
	<?php esc_html_e( 'Wholesale Role', 'wc-wholesale-manager' ); ?>
</h2>

<table class="wp-list-table widefat striped">
	<thead>
	<tr>
		<th><?php esc_html_e( 'Role', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Discount', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Product Pricing', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Category Pricing', 'wc-wholesale-manager' ); ?></th>
		<th><?php esc_html_e( 'Tax Status', 'wc-wholesale-manager' ); ?></th>
	</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<strong>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-wholesale-manager&tab=roles&edit=' . $default_role->id ) ); ?>">
						<?php echo esc_html( $default_role->name ); ?>
					</a>
				</strong>
				<div class="row-actions">
					<span class="edit">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-wholesale-manager&tab=roles&edit=' . $default_role->id ) ); ?>">
							<?php esc_html_e( 'Edit', 'wc-wholesale-manager' ); ?>
						</a>
					</span>
					|
					<span><?php esc_html_e( 'Default role', 'wc-wholesale-manager' ); ?></span>
				</div>
			</td>
			<td>
				<?php
				$discount = empty( $default_role->discount ) ? 0 : $default_role->discount;
				if ( 'fixed' === $default_role->discount_type ) {
					echo esc_html( wc_price( $discount ) );
				} else {
					printf( '%s%%', esc_html( $discount ) );
				}
				?>
			</td>
			<td>
				<?php echo 'yes' === $default_role->product_pricing ? esc_html__( 'Yes', 'wc-wholesale-manager' ) : esc_html__( 'No', 'wc-wholesale-manager' ); ?>
			</td>
			<td>
				<?php echo 'yes' === $default_role->category_pricing ? esc_html__( 'Yes', 'wc-wholesale-manager' ) : esc_html__( 'No', 'wc-wholesale-manager' ); ?>
			</td>
			<td>
				<?php echo esc_html( ucfirst( $default_role->tax_status ) ); ?>
			</td>
		</tr>
	</tbody>
</table>

<div class="wcwm-roles-promo-banner">
	<div class="wcwm-roles-promo-banner__content">
		<h2><?php esc_html_e( 'Want to Create More Wholesale Roles?', 'wc-wholesale-manager' ); ?></h2>
		<h3><?php esc_html_e( 'Available in Pro Version', 'wc-wholesale-manager' ); ?></h3>
		<a href="https://pluginever.com/plugins/wc-wholesale-manager-pro/?utm_source=import-tab&utm_medium=link&utm_campaign=upgrade&utm_id=wc-wholesale-manager" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Now', 'wc-wholesale-manager' ); ?></a>
	</div>
	<img src="<?php echo esc_url( WCWM_ASSETS_URL . 'images/roles.png' ); ?>" alt="<?php esc_attr_e( 'Registration Fields', 'wc-wholesale-manager' ); ?>"/>
</div>
