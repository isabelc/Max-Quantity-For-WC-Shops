<?php
/*
Plugin Name: WooCommerce Max Quantity
Plugin URI: http://isabelcastillo.com/downloads/woocommerce-max-quantity-plugin
Description: Set a universal limit for the max quantity, per product, that can be added to cart. Does not require customers to log in.
Version: 1.0
Author: Isabel Castillo
Author URI: http://isabelcastillo.com
License: GPL2
Text Domain: woocommerce_max_quantity
Domain Path: languages

Copyright 2013 Isabel Castillo

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

// Is WooCommerce active

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


	add_filter( 'woocommerce_inventory_settings', 'add_isa_max_quantity_options' );
	
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
		
			} // end if ( isset( $section['id']
	
			$updated_settings[] = $section;
	
		} // end foreach
	
		return $updated_settings;
	}
	
	
	/**
	* Set max quantity for total number that can be added to cart per product
	*
	* @param mixed $cart_item
	* @return void
	*/
	function isa_quantity_input_max( $cart_item ) {
	
		$woocommerce_max_qty = get_option( 'isa_woocommerce_max_qty_limit' );
	
		global $woocommerce;
	
		$max = ! empty( $woocommerce_max_qty) ? $woocommerce_max_qty: '';
	
		return $max;
	}
	
	add_filter( 'woocommerce_quantity_input_max', 'isa_quantity_input_max');
}