<?php
/**
 * Display role edit form.
 *
 * @param string $role Role name.
 *
 * @package WooCommerceWholesaleManager
 */

defined( 'ABSPATH' ) || exit;
$shipping_methods = WC()->shipping->load_shipping_methods();
$shipping_methods = array_map(
	function ( $method ) {
		return $method->get_method_title();
	},
	$shipping_methods
);
?>
	<h2>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-wholesale-manager&tab=roles' ) ); ?>"><?php esc_html_e( 'Wholesale User Roles', 'wc-wholesale-manager' ); ?></a> &gt;
		<span class="wcwm-role-name"><?php esc_html_e( 'Role', 'wc-wholesale-manager' ); ?></span>
	</h2>

	<form method="post">
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-name"><?php esc_html_e( 'Role Name', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<input type="text" name="name" id="role-name" class="regular-text" value="<?php echo esc_attr( $role->name ?? '' ); ?>"/>
					<p class="description"><?php esc_html_e( 'The name of the role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-discount"><?php esc_html_e( 'Discount', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<input type="number" name="discount" id="role-discount" class="regular-text" value="<?php echo esc_attr( $role->discount ?? '' ); ?>" min="0" max="100" step="any"/>
					<p class="description"><?php esc_html_e( 'The discount for the role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-discount-type"><?php esc_html_e( 'Discount Type', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="discount_type" id="role-discount-type" class="regular-text">
						<option value="percentage" <?php selected( $role->discount_type ?? '', 'percentage' ); ?>><?php esc_html_e( 'Percentage', 'wc-wholesale-manager' ); ?></option>
						<option value="fixed" <?php selected( $role->discount_type ?? '', 'fixed' ); ?>><?php esc_html_e( 'Fixed', 'wc-wholesale-manager' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The discount type for the role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-tax-status"><?php esc_html_e( 'Tax Status', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="tax_status" id="role-tax-status" class="regular-text">
						<option value="inherited" <?php selected( $role->tax_status, 'inherited' ); ?>><?php esc_html_e( 'Inherited', 'wc-wholesale-manager' ); ?></option>
						<option value="enable" <?php selected( $role->tax_status, 'enable' ); ?>><?php esc_html_e( 'Enable', 'wc-wholesale-manager' ); ?></option>
						<option value="disable" <?php selected( $role->tax_status, 'disable' ); ?>><?php esc_html_e( 'Disable', 'wc-wholesale-manager' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'The tax status for the role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-tax-display"><?php esc_html_e( 'Tax Display', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="tax_display" id="role-tax-display" class="regular-text">
						<option value="inherited" <?php selected( $role->tax_display, 'inherited' ); ?>><?php esc_html_e( 'Inherited', 'wc-wholesale-manager' ); ?></option>
						<option value="excl" <?php selected( $role->tax_display, 'excl' ); ?>><?php esc_html_e( 'Including tax', 'wc-wholesale-manager' ); ?></option>
						<option value="incl" <?php selected( $role->tax_display, 'incl' ); ?>><?php esc_html_e( 'Excluding tax', 'wc-wholesale-manager' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'How to display the price for the role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-disabled-gateways"><?php esc_html_e( 'Disabled Payment Gateways', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="disabled_gateways[]" id="role-disabled-gateways" class="regular-text wc-enhanced-select" multiple="multiple">
						<?php
						$gateways = WC()->payment_gateways->payment_gateways();
						foreach ( $gateways as $gateway ) {
							?>
							<option value="<?php echo esc_attr( $gateway->id ); ?>" <?php selected( in_array( $gateway->id, $role->disabled_gateways, true ), true ); ?>><?php echo esc_html( $gateway->title ); ?></option>
							<?php
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'The payment gateways that will be disabled for this role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-disabled-shipping-methods"><?php esc_html_e( 'Disabled Shipping Methods', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<select name="disabled_shipping_methods[]" id="role-disabled-shipping-methods" class="regular-text wc-enhanced-select" multiple="multiple">
						<?php
						foreach ( $shipping_methods as $shipping_method => $shipping_method_name ) {
							?>
							<option value="<?php echo esc_attr( $shipping_method ); ?>" <?php selected( in_array( $shipping_method, $role->disabled_shipping_methods, true ), true ); ?>><?php echo esc_html( $shipping_method_name ); ?></option>
							<?php
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'The shipping methods that will be disabled for this role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-product-pricing"><?php esc_html_e( 'Product Pricing', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<input type="checkbox" name="product_pricing" id="role-product-pricing" class="regular-text" value="yes" <?php checked( $role->product_pricing, 'yes' ); ?> />
					<?php esc_html_e( 'Enable product specific discounts for this role.', 'wc-wholesale-manager' ); ?>
					<p class="description"><?php esc_html_e( 'If enabled, you will be able to set discounts for each product for this role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="role-category-pricing"><?php esc_html_e( 'Category Pricing', 'wc-wholesale-manager' ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<input type="checkbox" name="category_pricing" id="role-category-pricing" class="regular-text" value="yes" <?php checked( $role->category_pricing, 'yes' ); ?> />
					<?php esc_html_e( 'Enable category specific discounts for this role.', 'wc-wholesale-manager' ); ?>
					<p class="description"><?php esc_html_e( 'If enabled, you will be able to set discounts for each product category for this role.', 'wc-wholesale-manager' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" name="id" value="<?php echo esc_attr( $role->id ); ?>"/>
		<?php wp_nonce_field( 'wcwm_save_role' ); ?>
		<?php submit_button( __( 'Save changes', 'wc-wholesale-manager' ) ); ?>
	</form>
<?php
