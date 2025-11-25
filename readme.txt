=== Wholesale Powerhouse ===
Contributors: mithublue, cybercraftit
Tags: woocommerce, wholesale, pricing, b2b, tiered-pricing
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wholesale Powerhouse adds fast wholesale pricing, roles, tiered discounts, and private storefront controls to WooCommerce—no custom tables.

== Description ==

Wholesale Powerhouse provides wholesale pricing, role management, tiered discounts, and private storefront controls for WooCommerce. The plugin is designed to help store owners manage wholesale operations efficiently using WordPress and WooCommerce's native meta APIs.

The plugin includes three pre-configured wholesale user roles (Bronze, Silver, and Gold) with customizable discount levels. Store owners can choose between product-level fixed prices or global percentage discounts per role. The plugin supports quantity-based tiered pricing to encourage bulk purchases.

Key capabilities include hiding prices from non-logged-in visitors, restricting specific products to wholesale customers, setting minimum order values, and managing wholesale customer registrations through a frontend form. The plugin is compatible with WooCommerce High-Performance Order Storage (HPOS).

== Features ==

* __Three Wholesale Roles__: Includes Bronze, Silver, and Gold customer roles, each with configurable discount percentages that can be customized to match your business needs.
* __Flexible Pricing Options__: Choose between setting fixed wholesale prices for individual products or applying automatic percentage discounts globally based on customer role.
* __Quantity-Based Discounts__: Configure tiered pricing to offer additional discounts when customers purchase in larger quantities, helping to incentivize bulk orders.
* __Private Store Mode__: Option to hide product prices and purchase buttons from visitors who are not logged in, creating a members-only shopping experience.
* __Product Visibility Control__: Mark specific products as wholesale-only, making them visible exclusively to wholesale customers while hiding them from retail shoppers.
* __Order Management__: Set minimum order values for wholesale purchases and choose whether to allow or disable coupon usage for wholesale customers.
* __Customer Registration__: Includes a frontend registration form that can be added to any page via shortcode, with options for manual approval of new wholesale customers.
* __WooCommerce Integration__: Settings are integrated into the WooCommerce settings panel, and product pricing options appear in the product edit screen.
* __Lightweight Architecture__: Built using WordPress and WooCommerce's native meta storage, avoiding the need for custom database tables.

== Additional Capabilities ==

* __Automatic Page Creation__: Creates a wholesale registration page automatically upon activation, which can be customized as needed.
* __HPOS Support__: Fully compatible with WooCommerce's High-Performance Order Storage system for improved scalability.
* __Bulk User Management__: Assign or change wholesale roles for multiple users simultaneously from the WordPress users screen.
* __Pricing Transparency__: Displays tiered pricing tables on product pages so wholesale customers can see available discounts at different quantity levels.
* __Extensible Design__: Built with an object-oriented architecture that includes hooks and filters, allowing developers to extend functionality as needed.

== Installation ==

1. Ensure WooCommerce is installed and active.
2. Upload the plugin files to `/wp-content/plugins/wholesale-powerhouse/` or install through the WordPress plugins screen.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Navigate to WooCommerce > Settings > Wholesale to configure roles and discounts.
5. Edit products to set wholesale pricing in the "Wholesale" tab.
6. Use the `[wholesale_registration_form]` shortcode to display the registration form.

== What This Plugin Helps You Achieve ==

This plugin is designed to help WooCommerce store owners:

* Manage multiple wholesale customer tiers with different pricing levels
* Offer quantity-based discounts to encourage larger orders
* Control store visibility and access for different customer types
* Streamline wholesale customer registration and approval processes
* Maintain pricing flexibility with both fixed and percentage-based discounts
* Operate without additional database tables, keeping the site structure clean

The plugin uses WordPress and WooCommerce's built-in systems, which can help with site maintenance and compatibility with other plugins.

== Frequently Asked Questions ==

= Do I need WooCommerce? =
Yes. This plugin requires WooCommerce to be installed and active.

= Does the plugin modify the database structure? =
No. The plugin uses WordPress options and post meta tables without creating custom tables.

= Can I rename the Bronze, Silver, and Gold roles? =
Yes. Navigate to WooCommerce > Settings > Wholesale to customize role labels and discounts.

= How does pricing priority work? =
1. Product-specific fixed price
2. Tiered discount (if quantity threshold is met)
3. Global role discount
4. Regular/sale price fallback

= Can wholesale customers use coupons? =
Coupon usage can be enabled or disabled per role in the settings.

= Is it translation ready? =
Yes. A `.pot` file is included in the `languages/` directory.

= Does it work with variable and simple products? =
Yes. Both product types and variations support wholesale pricing.

== Screenshots ==

1. WooCommerce Wholesale settings tab
2. Product edit screen with fixed and tiered pricing controls
3. Wholesale role management inside user profiles
4. Frontend registration form ready for instant signups
5. Stylish tiered discount table beneath the Add to Cart area

== Changelog ==

= 1.0.0 =
* Initial release by CyberCraft
* Bronze, Silver, Gold role presets
* Dual pricing engine (fixed + global)
* Tiered pricing lite with frontend table display
* Private store controls and wholesale-only products
* Minimum cart value enforcement and coupon toggle
* Registration shortcode with manual approval workflow
* Admin settings, product tabs, and user management enhancements
* HPOS compatibility declaration

== Upgrade Notice ==

= 1.0.0 =
Initial release with wholesale pricing, role management, and tiered discounts.

