<?php
/*
Plugin Name: Product Display for Prestashop
Plugin URI:  https://www.thatsoftwareguy.com/wp_product_display_for_prestashop.html
Description: Shows off a product from your Prestashop based store on your blog.
Version:     1.0
Author:      That Software Guy 
Author URI:  https://www.thatsoftwareguy.com 
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: ps_product_display
Domain Path: /languages
*/

function ps_product_display_shortcode($atts = [], $content = null, $tag = '')
{
   // normalize attribute keys, lowercase
   $atts = array_change_key_case((array)$atts, CASE_LOWER);
   $id = $atts['id'];

   $pspd_settings = get_option('pspd_settings');
   $url = $pspd_settings['pspd_url'];
   $webservice_key = $pspd_settings['pspd_webservice_key'];

   //API URL to get product by ID.  Use https! 
   $requestURL = "https://" . $webservice_key . "@" . $url;
   $requestURL .= "/api/products/" . $id; 

   $response = wp_remote_get($requestURL, array(
      'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
      'body' => null,
   ));
   if (is_wp_error($response)) {
      $o = ps_product_display_get_error("Product query failure: " . $response->get_error_message());
      return $o;
   }  else if (wp_remote_retrieve_response_code( $response ) != 200) {
      $o = ps_product_display_get_error("Product query unexpected return: " . wp_remote_retrieve_response_message( $response )); 
      return $o;
   }

   // decode result
   $parse_data = simplexml_load_string(wp_remote_retrieve_body($response),'SimpleXMLElement', LIBXML_NOCDATA);

   // Initialize
   $data['name'] = ' ';
   $data['price'] = ' ';
   $data['special'] = ' ';
   $data['link'] = ' ';
   $data['image'] = ' ';
   $data['description'] = ' ';

   // Fill from response
   $data['name'] = sanitize_text_field((string)$parse_data->product->name->language);
   $data['description'] = wp_kses_post((string)$parse_data->product->description->language);
   $data['link'] = "https://" . $url . "index.php?id_product=" . $id . "&controller=product"; 
   $image_id = sanitize_text_field((string)$parse_data->product->id_default_image); 
   $image_url = $url . "api/images/products/" . $id . "/" .  $image_id; 
   $data['image'] = '<img src="https://' . $image_url . '" />';
   $data['price'] = ps_product_display_price(sanitize_text_field((string)$parse_data->product->price));
 
   // start output
   $o = '';

   // start box
   $o .= '<div class="ps_product_display-box">';

   $o .= '<div id="prod-left">' . '<a href="' . $data['link'] . '">' . $data['image'] . '</a>' . '</div>';
   $o .= '<div id="prod-right">' . '<a href="' . $data['link'] . '">' . $data['name'] . '</a>' . '<br />';
   $o .= $data['price'];
   $o .= '</div>';
   $o .= '<div class="prod-clear"></div>';
   $o .= '<div id="prod-desc">' . $data['description'] . '</div>';

   // enclosing tags
   if (!is_null($content)) {
      // secure output by executing the_content filter hook on $content
      $o .= apply_filters('the_content', $content);

      // run shortcode parser recursively
      $o .= do_shortcode($content);
   }

   // end box
   $o .= '</div>';

   // return output
   return $o;
}

function ps_product_display_price($price)
{
   setlocale(LC_MONETARY, 'en_US');
   return money_format('%.2n', $price);
}

function ps_product_display_get_error($msg)
{

   $o = '<div class="ps_product_display-box">';
   $o .= $msg;
   $o .= '</div>';
   return $o;
}

function ps_product_display_shortcodes_init()
{
   wp_register_style('ps_product_display', plugins_url('style.css', __FILE__));
   wp_enqueue_style('ps_product_display');

   add_shortcode('ps_product_display', 'ps_product_display_shortcode');
}

add_action('init', 'ps_product_display_shortcodes_init');

add_action('admin_menu', 'pspd_add_admin_menu');
add_action('admin_init', 'pspd_settings_init');


function pspd_add_admin_menu()
{

   add_options_page('Product Display for Prestashop', 'Product Display for Prestashop', 'manage_options', 'ps_product_display_', 'pspd_options_page');

}


function pspd_settings_init()
{

   register_setting('pspd_pluginPage', 'pspd_settings');

   add_settings_section(
      'pspd_pluginPage_section',
      __('Settings', 'wordpress'),
      'pspd_settings_section_callback',
      'pspd_pluginPage'
   );

   $args = array('size' => '80');
   add_settings_field(
      'pspd_url',
      __('Prestashop Domain (no http://)', 'wordpress'),
      'pspd_url_render',
      'pspd_pluginPage',
      'pspd_pluginPage_section',
      $args
   );
   add_settings_field(
      'pspd_webservice_key',
      __('Prestashop Webservice key', 'wordpress'),
      'pspd_webservice_key_render',
      'pspd_pluginPage',
      'pspd_pluginPage_section',
      $args
   );

}


function pspd_url_render($args)
{

   $options = get_option('pspd_settings');
   ?>
    <input type='text' name='pspd_settings[pspd_url]' value='<?php echo $options['pspd_url']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}

function pspd_webservice_key_render($args)
{

   $options = get_option('pspd_settings');
   ?>
    <input type='text' name='pspd_settings[pspd_webservice_key]' value='<?php echo $options['pspd_webservice_key']; ?>'
       <?php
       if (is_array($args) && sizeof($args) > 0) {
          foreach ($args as $key => $value) {
             echo $key . "=" . $value . " ";
          }
       }
       ?>>
   <?php

}


function pspd_settings_section_callback()
{

   echo __('Settings required by this plugin', 'wordpress');

}


function pspd_options_page()
{

   ?>
    <form action='options.php' method='post'>

        <h2>Product Display for Prestashop</h2>

       <?php
       settings_fields('pspd_pluginPage');
       do_settings_sections('pspd_pluginPage');
       submit_button();
       ?>

    </form>
   <?php

}
