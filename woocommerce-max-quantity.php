<?php
/*
Plugin Name: WooCommerce Max Quantity
Plugin URI: http://isabelcastillo.com/downloads/woocommerce-max-quantity-plugin
Description: Set a universal limit for the max quantity, per product, that can be added to cart. Does not require customers to log in.
Version: 1.1.6
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
Text Domain: woocommerce_max_quantity
Domain Path: languages

Copyright 2013 - 2014 Isabel Castillo

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
	/*
	* Add the option to WooCommerce products tab
	*/
	function add_isa_max_quantity_options( $settings ) {
		$updated_settings = array();
		foreach ( $settings as $section ) {
			// at the bottom of the Inventory Options section
			if ( isset( $section['id'] ) && 'inventory_options' == $section['id'] &&
		       isset( $section['type'] ) && 'sectionend' == $section['type'] ) {
					$updated_settings[] = array(
					    'name'     => __( 'Maximum Quantity Limit Per Product', 'woocommerce_max_quantity' ),
					    'desc_tip'     => __( 'Set a limit for the maximum quantity, per product, that can be added to the shopping cart. Enter a number, 1 or greater.', 'woocommerce_max_quantity' ),
					    'id'       => 'isa_woocommerce_max_qty_limit',
					    'css'      => 'width:50px;',
					    'std'      => '', // WooCommerce < 2.0
					    'default'  => '', // WooCommerce >= 2.0
					    'type'     => 'text',
					    'desc' =>  __( 'Max quantity, per product, that can be added to the shopping cart. Enter a number, 1 or greater.', 'woocommerce_max_quantity' ),
				);
			}
			$updated_settings[] = $section;
		}
		return $updated_settings;
	}
	add_filter( 'woocommerce_inventory_settings', 'add_isa_max_quantity_options' );

	/**
	* For Simple products, set max input value (max number that can be added to cart per product)
	* @since 1.1.6
	*/

	function isa_woocommerce_quantity_input_args( $args, $product ) {
		$woocommerce_max_qty = get_option( 'isa_woocommerce_max_qty_limit' );
		// don't bother if limit is not entered
		if ( ! empty( $woocommerce_max_qty ) ) {
			$args['max_value'] = $woocommerce_max_qty;
		}
		return $args;
	}
	add_filter( 'woocommerce_quantity_input_args', 'isa_woocommerce_quantity_input_args', 10, 2 );

	/**
	* For products with Variations, set max quantity for total number that can be added to cart per product.
	* @since 1.1.6
	*/
	
	function isa_woocommerce_available_variation( $args ) {
		$woocommerce_max_qty = get_option( 'isa_woocommerce_max_qty_limit' );
		// don't bother if limit is not entered
		if ( ! empty( $woocommerce_max_qty ) ) {
			$args['max_value'] = $woocommerce_max_qty;
		}
		return $args;
	}
	add_filter( 'woocommerce_available_variation', 'isa_woocommerce_available_variation' );

	/**
	* Find out how many of this Simple product are already in cart
	* @param mixed $the_id of the product in question
	* @return integer
	* @since 1.1.6
	*/

	function isa_get_qty_alread_in_cart( $the_id ) {
		global $woocommerce;
		// search the cart for the product in question
		foreach($woocommerce->cart->get_cart() as $cart_item_key => $values ) {
			$qty = '';
			if ( $the_id == $values['product_id'] ) {
				// this is the product in question, get its qty
				$qty = $values['quantity'];
			}
		}
		return $qty;
	}

	/**
	* Validate product quantity when added to cart.
	* @since 1.1.6
	*/
	
	function isa_max_item_quantity_validation( $passed, $product_id, $quantity ) {
		global $woocommerce;
		$woocommerce_max_qty = get_option( 'isa_woocommerce_max_qty_limit' );
		$alread_in_cart = isa_get_qty_alread_in_cart( $product_id );
		if ( ! empty( $alread_in_cart ) ) {
			// there was already a quantity of this item in cart prior to this addition
			// Check if the total of $alread_in_cart + current addition quantity is more than our max
			$new_qty = $alread_in_cart + $quantity;
			if ( $new_qty > $woocommerce_max_qty ) {
				// oops. too much.
				$product = get_product( $product_id );
				$product_title = $product->post->post_title;
				$woocommerce->add_error( sprintf( __( "You can add a maximum of %s %s's to %s. You already have %s.", 'woocommerce_max_quantity' ), 
	$woocommerce_max_qty, $product_title, '<a href="' . $woocommerce->cart->get_cart_url() . '" title="Go to cart">' . __( 'your cart', '' ) . '</a>', $alread_in_cart ) );
				$passed = false;
			} else {
				// addition qty is okay
				$passed = true;
			}
		} else {
			// none were in cart previously, and we already have input limits in place, so no more checks are needed
			$passed = true;
		}
		return $passed;
	}
	add_action( 'woocommerce_add_to_cart_validation', 'isa_max_item_quantity_validation', 1, 3 );
}