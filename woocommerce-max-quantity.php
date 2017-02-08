<?php
/*
Plugin Name: WooCommerce Max Quantity
Plugin URI: https://isabelcastillo.com/free-plugins/woocommerce-max-quantity
Description: Set a limit for the max quantity of products that can be added to cart. Does not require customers to log in.
Version: 1.4-alpha-3
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GPL2
Text Domain: woocommerce-max-quantity
Domain Path: languages

Copyright 2013 - 2017 Isabel Castillo

This file is part of WooCommerce Max Quantity plugin.

WooCommerce Max Quantity plugin is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

WooCommerce Max Quantity plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WooCommerce Max Quantity; if not, see <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>.
*/

// only if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	* Load plugin's textdomain
	* @since 1.2.0
	* @return void
	*/
	function isa_wc_max_qty_load_textdomain() {
		load_plugin_textdomain( 'woocommerce-max-quantity', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	add_action( 'plugins_loaded', 'isa_wc_max_qty_load_textdomain' );

	/*
	* Add the option to WooCommerce products tab
	*/
	function isa_wc_max_qty_options( $settings ) {
		$updated_settings = array();
		foreach ( $settings as $section ) {
			// at the bottom of the Inventory Options section
			if ( isset( $section['id'] ) && 'product_inventory_options' == $section['id'] &&
		       isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
					$updated_settings[] = array(
					    'name'     => __( 'Maximum Quantity Limit Per Product', 'woocommerce-max-quantity' ),
					    'desc_tip'     => __( 'Set a limit for the maximum quantity, per product, that can be added to the shopping cart. Enter a number, 1 or greater.', 'woocommerce-max-quantity' ),
					    'id'       => 'isa_woocommerce_max_qty_limit',
					    'css'      => 'width:50px;',
					    'std'      => '', // WooCommerce < 2.0
					    'default'  => '', // WooCommerce >= 2.0
					    'type'     => 'text',
					    'desc' =>  __( 'Max quantity, per product, that can be added to the shopping cart. Enter a number, 1 or greater.', 'woocommerce-max-quantity' ),
				);
			}
			$updated_settings[] = $section;
		}
		return $updated_settings;
	}
	add_filter( 'woocommerce_inventory_settings', 'isa_wc_max_qty_options' );

	/**
	 * For Simple Products, set max value for the quantity input field for add to cart forms
	 * @since 1.4
	 */
	function isa_wc_max_qty_set_input_max( $qty, $product ) {
		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		if ( empty( $max ) ) {
			return $qty;
		}

		$qty = $max;

		if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
		
			// Limit our max by the available stock, if stock is lower

			// Set to lessor of stock qty or max allowed
			$qty = min( $product->stock, $max );
		
		}

		return $qty;

	}
	add_filter( 'woocommerce_quantity_input_max', 'isa_wc_max_qty_set_input_max', 10, 2 );

	/**
	 * For Variable Products, enforce max quantity on the quantity input field on the add to cart forms 
	 * @since 1.4
	 */
	function isa_wc_max_qty_variation_input_qty_max( $qty, $variation ) {
		// Do not affect the actual variation stock quantity on the admin side
		if ( is_admin() ) {
			return $qty;
		}

		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		if ( ! empty( $max ) ) {

			$new_qty = $max;

			if ( $variation->managing_stock() && ! $variation->backorders_allowed() ) {

				// Limit our max by the available stock, if stock is lower

				// Set to lessor of stock qty or max allowed
				$new_qty = min( $qty, $max );

			}

		}
			
		return $new_qty;
	}

	/**
	 * Filter the variation stock quantity to enforce the max on the quantity input field
	 * on add to cart forms for Variable Products.
	 * @since 1.4
	 */
	add_filter( 'woocommerce_variation_get_stock_quantity', 'isa_wc_max_qty_variation_input_qty_max', 10, 2 );

	/**
	* Find out how many of this product are already in the cart
	*
	* @param mixed $product_id ID of the product in question
	* @param string $cart_item_key The cart key for this item in case of Updating cart
	*
	* @return integer $running_qty The total quantity of this item, parent item in case of variations, in cart
	* @since 1.1.6
	*/

	function isa_wc_max_qty_get_cart_qty( $product_id, $cart_item_key = '' ) {
		global $woocommerce;
		$running_qty = 0;// Keep a running total to count variations

		// search the cart for the product in question
		foreach($woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {

			if ( $product_id == $values['product_id'] ) {

				/*
				 * In case of updating the cart quantity, don't count this cart item key
				otherwise they won't be able to REDUCE the number of items in cart becuase it will think it is adding the new quantity on top of the existing quantity, when in fact it is reducing the existing quantity to the new quantity.
				 */
				
				if ( $cart_item_key == $other_cart_item_keys ) {
					continue;
				}

				// Add that quantity to our running total qty for this product
				$running_qty += (int) $values['quantity'];			

			}

		}

		return $running_qty;
	}

	/**
	* Validate product quantity when Added to cart.
	* @since 1.1.6
	*/
	function isa_wc_max_qty_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		if ( empty( $max ) ) {
			return $passed;
		}

		global $woocommerce;
		$already_in_cart = isa_wc_max_qty_get_cart_qty( $product_id );
		$product = get_product( $product_id );
		$product_title = $product->post->post_title;

		if ( ! empty( $already_in_cart ) ) {
			// there was already a quantity of this item in cart prior to this addition
			// Check if the total of already_in_cart + current addition quantity is more than our max
			if ( ( $already_in_cart + $quantity ) > $max ) {
				// oops. too much.
				$passed = false;

				// Add compatibility with WooCommerce Direct Checkout
				if ( class_exists( 'WooCommerce_Direct_Checkout' ) ) {
					$direct_checkout = get_option( 'direct_checkout_enabled' );
					$direct_checkout_url = get_option( 'direct_checkout_cart_redirect_url' );
					if ( $direct_checkout && $direct_checkout_url ) {
						// Redirect to submit page
						wp_redirect( esc_url_raw( $direct_checkout_url ) );
						exit;
					}
				}

				wc_add_notice( sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s. You already have %4$s.', 'woocommerce-max-quantity' ), 
							$max,
							$product_title,
							'<a href="' . esc_url( $woocommerce->cart->get_cart_url() ) . '" title="' . __( 'Go to cart', 'woocommerce-max-quantity' ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>',
							$already_in_cart ), 'error' );

			}
		} else {
			// none were in cart previously
			// just in case they manually type in an amount greater than we allow, check the input number here too
			if ( $quantity > $max ) {
				// oops. too much.
				wc_add_notice( sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'woocommerce-max-quantity' ),
							$max,
							$product_title,
							'<a href="' . esc_url( $woocommerce->cart->get_cart_url() ) . '" title="' . __( 'Go to cart', 'woocommerce-max-quantity' ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>'), 'error' );
				$passed = false;
			}

		}

		return $passed;
	}
	add_filter( 'woocommerce_add_to_cart_validation', 'isa_wc_max_qty_add_to_cart_validation', 1, 5 );

	/**
	* Validate product quantity when cart is UPDATED.
	*
	* Just in case they manually type in an amount greater than we allow.
	* @since 1.1.9
	*/
	function isa_wc_max_qty_update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		// don't bother if limit is not entered
		if ( empty( $max ) ) {
			return $passed;
		}

		global $woocommerce;
		$product_title = $values['data']->post->post_title;
		$already_in_cart = isa_wc_max_qty_get_cart_qty( $values['product_id'], $cart_item_key );

		if ( ( $already_in_cart + $quantity ) > $max ) {

			wc_add_notice( sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'woocommerce-max-quantity' ),
						$max,
						$product_title,
						'<a href="' . esc_url( $woocommerce->cart->get_cart_url() ) . '" title="' . __( 'Go to cart', 'woocommerce-max-quantity' ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>'), 'error' );
			$passed = false;
		}
		return $passed;
	}
	add_filter( 'woocommerce_update_cart_validation', 'isa_wc_max_qty_update_cart_validation', 1, 4 );

}
