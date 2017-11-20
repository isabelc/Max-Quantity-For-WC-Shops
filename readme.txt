=== WooCommerce Max Quantity ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R7BHLMCQ437SS
Tags: woocommerce, max quantity, cart maximum, max purchase, cart max, order limit
Requires at least: 3.8
Tested up to: 4.9
Stable tag: 1.5.1
License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Set a limit for the max quantity of products that can be added to cart, per product, per order. Now with individual product limits.

== Description ==

= Requires WooCommerce 3.0 or above. =

WooCommerce Max Quantity is a simple extension for WooCommerce that only does one thing: it lets you set a max limit for the number of items that can be added to the cart, for each product, per order.

It's like one of those signs at the grocery store that says, "LIMIT 2 PER CUSTOMER!" This plugin will not add a sign like that, but the quantity input field (pictured above) will hit a limit.

You can simply set a universal limit to affect all products in your WooCommerce store. If you prefer, you can set a different limit for each product, individually. Each product's own limit will always override the universal limit.

This works for all products in your WooCommerce store: Simple and Variable products. 

Customers do not have to be logged in for this to work. This works for guest buyers, as well as logged-in buyers.

See the [setup instructions and documentation](https://isabelcastillo.com/docs/woocommerce-max-quantity-documentation).

**Languages**
Includes `.mo` and `.po` translations for Dutch (thanks to Martijn Heesters), French, German (thanks @tofuSCHNITZEL), Hindi, and Spanish languages. Also includes a `.pot` file to make more translations.

**Disclaimer**

I am not affiliated with WooCommerce, WooThemes, or Automattic. I provide this plugin as a free service to the WP community (originally created for my own personal use).


== Installation ==

**Install and Activate**

1. In your WordPress dashboard, go to Plugins –> Add New.
2. Search for "WooCommerce Max Quantity" to find the plugin.
3. When you see "WooCommerce Max Quantity", click "Install Now" to install the plugin.
4. Click "Activate" to activate the plugin.

**Configure**

The plugin only has 2 settings. You can use these settings in a variety of ways to accomplish the unique goals of your store. See [the documentation](https://isabelcastillo.com/docs/woocommerce-max-quantity-documentation) for specific ways to use these settings:

1. To set a universal limit to affect all products, go to WooCommerce -> Settings -> Products tab. Click "Inventory". Scroll down to "Maximum Quantity Limit Per Product". Set your desired limit there.
2. To set an individual product limit for a single product, go the product's own "Edit product" page. Scroll down to the "Product Data" box. Click on the Inventory tab. There, you’ll see the setting called, "Max Quantity Per Order." Set your desired max limit for that product, there. 

== Frequently Asked Questions ==

= Does this plugin work with products with variations (Variable products)? =

Yes, since version 1.4. 

= Can I set a different limit for different products? =

Yes, since version 1.4. 

== Screenshots ==

1. The universal max setting. It is labeled, “Maximum Quantity Limit Per Product.” It is found at WooCommerce -> Settings -> Products tab -> "Inventory" link

2. An individual product’s “Max Quantity Per Order” setting. It is found on the individual product page, in the Product Data box, on the Inventory tab.

== Changelog ==

= 1.5.1 =
* Fixed - Honor the "Sold individually" setting above the universal max limit.

= 1.5 =
* New - Added filters to the error message strings.
* Fixed - The max was not being enforced the input field for Variable products.
* Fixed - The max now works even when backorders are enabled.

= 1.4.3 =
* Fixed - The max limit also works on Variable Products, as long as backorders are not enabled.

= 1.4.2 =
* Fixed a fatal error regarding get_parent_data().

= 1.4.1 =
* New - For variable products, the stock quantity display has been restored. Previously, the stock quantity was hidden for products with variations. This only affected those who were displaying the stock quantity on the product page.
* Fixed several PHP notices. Thanks to @brettmhoffman.
* Internationalization - load_plugin_textdomain is now loaded on init rather than plugins_loaded, as it should be.

= 1.4 =
* New - Support for different limits for individual products. See the version 1.4 release post for details: https://isabelcastillo.com/woocommerce-max-quantity-1-4
* New - Support for Variable products (products with variations). Now, the plugin works with all products, Simple and Variable.
* Code refactoring - Many functions were renamed:
`isa_get_qty_alread_in_cart' was changed to 'isa_wc_max_qty_get_cart_qty`
`isa_max_item_quantity_validation' was changed to 'isa_wc_max_qty_add_to_cart_validation`
`add_isa_max_quantity_options' was changed to 'isa_wc_max_qty_options`
`isa_woo_max_qty_load_textdomain' was changed to 'isa_wc_max_qty_load_textdomain`
`isa_woo_max_qty_update_cart_validation' was changed to 'isa_wc_max_qty_update_cart_validation`
`isa_woocommerce_quantity_input_args' was changed to 'isa_wc_max_qty_input_args`
* Code refactoring - One function was removed:
`isa_woocommerce_available_variation`, which was hooked to `woocommerce_available_variation`, was removed.

= 1.3 =
* New - Added compatibility with the WooCommerce Direct Checkout plugin

= 1.2.4 =
* Fix - The setting had disappeared on last WC update.

= 1.2.3 =
* New - Added Dutch translation, thanks to Martijn Heesters.

= 1.2.2 =
* New - Added German translation, thanks @tofuSCHNITZEL.

= 1.2.1 =
* Fix - Did not calculate quantity properly if you UPDATE Quantity on Cart page. This did not let some users DECREASE or INCREASE the quantity while on the cart page.
* Maintenance - Tested and passed for WordPress 4.0 compatibility.

= 1.2.0 =
* New - Added .pot translation file.
* New - Added translations for French, Hindi, and Spanish languages.
* New - Changed textdomain to plugin slug.
* Maintenance - Tested and passed for WP 3.9 compatibility.

= 1.1.9 =
* Fix: added _update_cart_validation to avoid manual override on cart update at checkout.
* Tweak: remove passed=true in validation checks, use the passed parameter instead.
* Maintenance: replace woocommerce - add_error with wc_add_notice.

= 1.1.8 =
* Fix: now checks for manually-typed quantity because maximum limit was able to be overridden by typing in a number.
* Fix: a problem in which limit was ignored if product was previously added to cart, then added another item to cart, then re-added this item to cart.
* Maintenance: Updated description to reflect that this plugin does not yet support products with variations.

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

== Upgrade Notice ==

= 1.5.1 =
Fixed - Honor the "Sold individually" setting above the universal max limit.


= 1.5 =
Fixed variations max. New filters for error message. Now works with backorders.
