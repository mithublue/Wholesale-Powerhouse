=== Wholesale Powerhouse ===
Contributors: yourname
Tags: woocommerce, wholesale, pricing, b2b, bulk pricing
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful, flexible wholesale pricing and user role system for WooCommerce. No custom tables - uses WP/WC meta exclusively.

== Description ==

**Wholesale Powerhouse** is a comprehensive wholesale pricing solution for WooCommerce that provides everything you need to manage wholesale customers with flexible pricing, user roles, and advanced controls.

= Key Features =

* **3 Wholesale Roles**: Bronze, Silver, and Gold wholesale customer roles
* **Dual Pricing System**: Set fixed prices per role OR use global discount percentages
* **Tiered Pricing Lite**: Quantity-based discount tiers
* **Private Store Mode**: Hide prices and purchase buttons from guests
* **Wholesale-Only Products**: Restrict products to wholesale customers only
* **Minimum Order Value**: Set minimum cart value for wholesale customers
* **Coupon Controls**: Optionally disable coupons for wholesale customers
* **Registration Management**: Auto-approve or manual approval for new wholesale registrations
* **No Custom Tables**: All data stored in standard WP/WC meta tables

= Perfect For =

* B2B Stores
* Wholesale Distributors
* Bulk Sellers
* Manufacturers
* Multi-tier Pricing Businesses

= How It Works =

1. **Install & Activate**: The plugin automatically creates 3 wholesale roles on activation
2. **Configure Settings**: Go to WooCommerce > Settings > Wholesale to configure global settings
3. **Set Product Pricing**: Edit products and use the "Wholesale" tab to set role-specific pricing
4. **Manage Users**: Assign wholesale roles to customers via Users > All Users
5. **Registration Form**: Use the [wholesale_registration_form] shortcode for customer registration

= Pricing Options =

**Fixed Prices**: Set specific prices for each wholesale role on a per-product basis
**Global Discounts**: Set percentage discounts that apply to all products for each role
**Tiered Pricing**: Add quantity-based discounts (e.g., 15% off when buying 10+ units)

= Store Controls =

* **Private Store**: Hide all prices from non-logged-in users
* **Product Visibility**: Hide specific products from retail customers
* **Cart Minimum**: Enforce minimum order values for wholesale customers
* **Coupon Restrictions**: Disable coupon usage for wholesale customers

= Developer Friendly =

* Clean, modular, class-based architecture
* Follows WordPress coding standards
* Translation ready
* Hooks and filters for customization
* Well-documented code

== Installation ==

= Automatic Installation =

1. Log in to your WordPress dashboard
2. Navigate to Plugins > Add New
3. Search for "Wholesale Powerhouse"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Extract the files to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

= After Activation =

1. Ensure WooCommerce is installed and active
2. Go to WooCommerce > Settings > Wholesale
3. Configure your wholesale roles and settings
4. Start assigning wholesale roles to users
5. Set wholesale pricing on your products

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes, WooCommerce must be installed and active for this plugin to work.

= Does this plugin create custom database tables? =

No! All data is stored in standard WordPress/WooCommerce meta tables (wp_options and wp_postmeta).

= How do I add a registration form? =

Use the shortcode [wholesale_registration_form] on any page or post.

= Can I customize the wholesale role names? =

Yes, you can customize role labels in WooCommerce > Settings > Wholesale.

= Can I have more than 3 wholesale roles? =

The plugin comes with 3 roles (Bronze, Silver, Gold) by default. Additional roles would require custom development.

= How does the pricing hierarchy work? =

1. Product-specific fixed price (highest priority)
2. Global role discount percentage (if no fixed price)
3. Regular product price (if user is not wholesale)

= Can wholesale customers use coupons? =

Yes, by default. You can disable coupons for wholesale customers in the settings if needed.

= Is the plugin translation ready? =

Yes! The plugin is fully translation ready with .pot file included.

= Does it work with variable products? =

Yes! The plugin supports both simple and variable products.

== Screenshots ==

1. Wholesale settings page in WooCommerce
2. Product edit page - Wholesale tab with pricing options
3. User management with wholesale role column
4. Wholesale registration form (frontend)
5. Tiered pricing display on product pages

== Changelog ==

= 1.0.0 =
* Initial release
* 3 wholesale user roles (Bronze, Silver, Gold)
* Dual pricing system (fixed prices or global discounts)
* Tiered pricing lite feature
* Private store mode
* Wholesale-only product visibility
* Minimum order value controls
* Coupon restrictions
* Registration form with shortcode
* Manual or auto-approval for registrations
* Complete admin interface

== Upgrade Notice ==

= 1.0.0 =
Initial release of Wholesale Powerhouse.

== Support ==

For support, please visit our website or contact us through the WordPress.org support forums.

== Credits ==

Developed with ❤️ for the WooCommerce community.
