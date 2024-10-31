=== Product Display for Prestashop ===
Contributors: scottcwilson
Donate link: http://donate.thatsoftwareguy.com/
Tags: Prestashop
Requires at least: 4.3 
Tested up to: 4.8
Stable tag: 1.0 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to easily display products from your Prestashop installation 
on your WordPress blog using a shortcode.

== Description ==

Product Display for Prestashop takes a product sku, and pulls in the product name, price, image, description and link, and displays it in a post. 

== Installation ==

Note: This is a two-part install; you have to do some configuration on your Prestashop installation, then you must install code on your WordPress installation. 

In your Prestashop admin, do the following: 

1. Get the API file as described in http://doc.prestashop.com/display/PS16/Using+the+PrestaShop+Web+Service

1. In your Prestashop back office, enable the webservice and create an access key as described in http://doc.prestashop.com/display/PS16/Web+service+one-page+documentation  The key description should be blog, and the permissions should permit View (GET) on resource products.  Note your Webservice key. 

Install the WordPress part of this mod as usual (using the Install button 
on the mod page on WordPress.org).  The follow these steps: 

1. In your WordPress admin, do the following: 
- In Plugins->Installed Plugins, click the "Activate" link under Product Display for Prestashop.
- In Settings->Product Display for Prestashop, set your Prestashop URL and Webservice key. 

To show a specific product on your blog, use the shortcode 
[ps_product_display] with parameter "id" as a self closing tag.  
So showing product 41 would be done as follows: 

[ps_product_display id="41"]

The id is shown in the URL when you edit a product in your admin.

== Frequently Asked Questions ==

= I use a currency other than dollars - how do I change the price display? = 

Modify `product_display_for_prestashop.php` and change the function `ps_product_display_price`.

== Screenshots ==

1. What the product information in your post will look like. 

== Changelog ==
First version

== Upgrade Notice ==
First version

