=== WooCommerce Max Quantity ===
Author URI: http://isabelcastillo.com
Plugin URI: http://wordpress.org/plugins/woocommerce-max-quantity/
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=me%40isabelcastillo%2ecom
Tags: woocommerce, max quantity, cart maximum, max purchase, cart max, cart limit
Requires at least: 3.7
Tested up to: 3.8.1
Stable Tag: 1.1.7

License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Set a universal limit for the max quantity, per product, that can be added to cart. Does not require customers to log in.

== Description ==
WooCommerce Max Quantity is an extension for [WooCommerce](http://wordpress.org/plugins/woocommerce/) that lets you set a max limit for the number of items that can be added to the cart. It's a universal limit, meaning this limit affects all products in your WooCommerce store. 

**NOTE:** This plugin only works with regular ("Simple") products. **It does NOT work with products that have "variations".**

The limit is per product, not per the entire cart. 

This works for guest buyers, as well as logged-in buyers.

To set the number, go to your WordPress dashboard. Go to WooCommerce -> Settings -> Products tab. Click "Inventory". Scroll down to "Maximum Quantity Limit Per Product". Set your desired limit there.

**Known Issues**

This plugin currently does not support "products with Variations".

**Other Notes**

For more info, see the [FAQ](http://wordpress.org/plugins/woocommerce-max-quantity/faq/).

For support, please use the [Support forum](http://wordpress.org/support/plugin/woocommerce-max-quantity).

Contribute or fork it [on Github](https://github.com/isabelc/Woocommerce-Max-Quantity).

== Installation ==

1. Download the plugin file (`.zip`).
2. In your WordPress dashboard, go to "Plugins -> Add New", and click "Upload".
3. Upload the plugin file and activate it.
4. Go to WooCommerce -> Settings -> Inventory tab, and scroll down to the one setting: "Maximum Quantity Limit Per Product". Set your desired limit number there.

== Frequently Asked Questions ==

None yet.

== Screenshots ==

1. The setting at WooCommerce -> Settings -> Inventory tab

== Changelog ==

= 1.1.8 =
* Maintenance: Updated description to reflect that this plugin does not support products with variations.

= 1.1.6 =
* Fix: maximum limit was able to be overridden by adding a new instance of the item to cart.
* Fix: maximum limit was able to be overridden by updating quantity on cart page.

= 1.1.5 =
* Tested for WP 3.8 compatibility.

= 1.1.4 =
* Tested for WP 3.7.1 compatibility.

= 1.1.3 =
* bug fix related to syncing with Git.

= 1.1.2 =
* bug fix related to syncing with Git.

= 1.1.1 =
* bugfix related to syncing with Git.

= 1.1 =
* Tested for WP 3.6.1 compatibility

= 1.0 =
* Initial release.
