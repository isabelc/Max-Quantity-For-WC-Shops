<?php
/*
Plugin Name: WooCommerce Max Quantity
Plugin URI: https://isabelcastillo.com/free-plugins/woocommerce-max-quantity
Description: Set a limit for the max quantity of products that can be added to cart, per product. Now with individual product limits.
Version: 1.5.1
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GPL2
Text Domain: woocommerce-max-quantity
Domain Path: languages

Copyright 2013-2017 Isabel Castillo

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
	add_action( 'init', 'isa_wc_max_qty_load_textdomain' );

	/**
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
					    'desc_tip'     => __( 'This is the universal max quantity limit that can be added to the cart, per each product. To override this for a product, edit the product, see the Inventory tab in the Product Data box, and enter a number for "Max Quantity Per Order"', 'woocommerce-max-quantity' ),
					    'id'       => 'isa_woocommerce_max_qty_limit',
					    'css'      => 'width:50px;',
					    'std'      => '', // WooCommerce < 2.0
					    'default'  => '', // WooCommerce >= 2.0
					    'type'     => 'text',
					    'desc' =>  __( 'Max quantity that can be added to the cart, per product. This is universal, meaning that it will affect all products. Enter a number, 1 or greater. (Products can override this from their inventory tab.)', 'woocommerce-max-quantity' ),
				);
			}
			$updated_settings[] = $section;
		}
		return $updated_settings;
	}
	add_filter( 'woocommerce_inventory_settings', 'isa_wc_max_qty_options' );

	/**
	 * Get the individual product max limit
	 * @param int $product_id
	 * @return int|bool $limit The max limit number for this product, if set, otherwise false
	 * @since 1.4
	 */
	function isa_wc_get_product_max_limit( $product_id ) {
		$qty = get_post_meta( $product_id, '_isa_wc_max_qty_product_max', true );
		if ( empty( $qty ) ) {
			// honor the Sold individually setting
			$product = wc_get_product( $product_id );
			$limit = $product->is_sold_individually() ? 1 : false;
		} else {
			$limit = (int) $qty;
		}
		return $limit;
	}

	/**
	 * Set the max attribute value for the quantity input field for Add to cart forms.
	 * This applies to Simple product Add To Cart forms, and ALL (simple and variable) products on the Cart page quantity field.
	 * @return array $args
	 * @since 1.1.6
	 */
	function isa_wc_max_qty_input_args( $args, $product ) {
		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$product_max = isa_wc_get_product_max_limit( $product_id );

		// Allow individual product max limit, if set, to override universal max

		if ( ! empty( $max ) ) {
			$args['max_value'] = ( false !== $product_max ) ? $product_max : $max;
		} else {
			// max is empty
			if ( false !== $product_max ) {
				$args['max_value'] = $product_max;
			} else {
				// neither max is set, so get out
				return $args;
			}
		}

		if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
			$stock = $product->get_stock_quantity();

			// Limit our max by the available stock, if stock is lower

			// Set to lessor of stock qty or max allowed
			$args['max_value'] = min( $stock, $args['max_value'] );
		
		}

		return $args;
	}
	add_filter( 'woocommerce_quantity_input_args', 'isa_wc_max_qty_input_args', 10, 2 );

	/**
	 * Filter the available variation to enforce the max on the quantity input field
	 * on Add to cart forms for Variable Products. 
	 */	
	add_filter( 'woocommerce_available_variation', 'isa_wc_max_qty_variation_input_qty_max', 10, 3 );
	function isa_wc_max_qty_variation_input_qty_max( $args, $product, $variation ) {
		if ( is_admin() ) {
			return $args;
		}

		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );
		$product_max = isa_wc_get_product_max_limit( $variation->get_parent_id() );

		// Allow individual product max limit, if set, to override universal max

		if ( ! empty( $max ) ) {
			$args['max_qty'] = ( false !== $product_max ) ? $product_max : $max;
		} else {
			// max is empty
			if ( false !== $product_max ) {
				$args['max_qty'] = $product_max;
			} else {
				// neither max is set, so get out
				return $args;
			}
		}

		if ( $variation->managing_stock() && ! $variation->backorders_allowed() ) {

			// Limit our max by the available stock, if stock is lower

			// Set to lessor of stock qty or max allowed
			$args['max_qty'] = min( $qty, $args['max_qty'] );

		}

		return $args;
	}

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

		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );// universal max
		$product_max = isa_wc_get_product_max_limit( $product_id );

		// Allow individual product max limit, if set, to override universal max

		if ( ! empty( $max ) ) {
			$new_max = ( false !== $product_max ) ? $product_max : $max;
		} else {
			// max is empty
			if ( false !== $product_max ) {
				$new_max = $product_max;
			} else {
				// neither max is set, so get out
				return $passed;
			}
		}

		$already_in_cart = isa_wc_max_qty_get_cart_qty( $product_id );
		$product = wc_get_product( $product_id );
		$product_title = $product->get_title();
		
		if ( ! empty( $already_in_cart ) ) {
			// There was already a quantity of this item in cart prior to this addition.
			// Check if the total of already_in_cart + current addition quantity is more than our max.
			if ( ( $already_in_cart + $quantity ) > $new_max ) {
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

				wc_add_notice( apply_filters( 'isa_wc_max_qty_error_message_already_had', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s. You already have %4$s.', 'woocommerce-max-quantity' ), 
							$new_max,
							$product_title,
							'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>',
							$already_in_cart ),
						$new_max,
						$already_in_cart ),
				'error' );

			}
		} else {
			// none were in cart previously
			// just in case they manually type in an amount greater than we allow, check the input number here too
			if ( $quantity > $new_max ) {
				// oops. too much.
				wc_add_notice( apply_filters( 'isa_wc_max_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'woocommerce-max-quantity' ),
							$new_max,
							$product_title,
							'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>'),
					$new_max ),
				'error' );
				$passed = false;
			}

		}

		return $passed;
	}
	add_filter( 'woocommerce_add_to_cart_validation', 'isa_wc_max_qty_add_to_cart_validation', 1, 5 );

	/**
	* Validate product quantity when cart is UPDATED.
	*
	* Just in case they manually type in an amount greater than we allow and the HTML5 Constraint validation doesn't work.
	* @since 1.1.9
	*/
	function isa_wc_max_qty_update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
		$max = (int) get_option( 'isa_woocommerce_max_qty_limit' );// universal max
		$product_max = isa_wc_get_product_max_limit( $values['product_id'] );

		// Allow individual product max limit, if set, to override universal max

		if ( ! empty( $max ) ) {
			$new_max = ( false !== $product_max ) ? $product_max : $max;
		} else {
			// max is empty
			if ( false !== $product_max ) {
				$new_max = $product_max;
			} else {
				// neither max is set, so get out
				return $passed;
			}
		}

		$product = wc_get_product( $values['product_id'] );
		$already_in_cart = isa_wc_max_qty_get_cart_qty( $values['product_id'], $cart_item_key );

		if ( ( $already_in_cart + $quantity ) > $new_max ) {
			wc_add_notice( apply_filters( 'isa_wc_max_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'woocommerce-max-quantity' ),
						$new_max,
						$product->get_name(),
						'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'woocommerce-max-quantity' ) . '</a>'),
					$new_max ),
			'error' );
			$passed = false;
		}
		return $passed;
	}
	add_filter( 'woocommerce_update_cart_validation', 'isa_wc_max_qty_update_cart_validation', 1, 4 );

	/**
	 * Display the product's "Max Quantity Per Perchase" field in the Product Data metabox
	 * @since 1.4
	 */
	function isa_wc_max_qty_add_product_field() {
		echo '<div class="options_group">';
		woocommerce_wp_text_input( 
			array( 
				'id'          => '_isa_wc_max_qty_product_max', 
				'label'       => __( 'Max Quantity Per Order', 'woocommerce-max-quantity' ), 
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Optional. Set a maximum quantity limit allowed per order. Enter a number, 1 or greater.', 'woocommerce-max-quantity' ) 
			)
		);
		echo '</div>';
	}
	add_action( 'woocommerce_product_options_inventory_product_data', 'isa_wc_max_qty_add_product_field' );

	/**
	 * Save product's Max Quantity field
	 * @since 1.4
	 */
	function isa_wc_max_qty_save_product_field( $post_id ) {
		$val = trim( get_post_meta( $post_id, '_isa_wc_max_qty_product_max', true ) );
		$new = sanitize_text_field( $_POST['_isa_wc_max_qty_product_max'] );
		if ( $val != $new ) {
			update_post_meta( $post_id, '_isa_wc_max_qty_product_max', $new );
		}
	}
	add_action( 'woocommerce_process_product_meta', 'isa_wc_max_qty_save_product_field' );	

}
