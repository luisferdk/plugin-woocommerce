<?php

/**
 * Plugin Name: Woo Luis
 * Plugin URI: https://woocommerce.com/
 * Description: An eCommerce toolkit that helps you sell anything. Beautifully.
 * Version: 5.5.2
 * Author: Automattic
 * Author URI: https://woocommerce.com
 * Text Domain: woocommerce
 * Domain Path: /i18n/languages/
 * Requires at least: 5.5
 * Requires PHP: 7.0
 *
 * @package Woo Luis
 */

defined('ABSPATH') || exit;

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function cfwc_create_custom_field()
  {
    $args = array(
      'id' => 'gender',
      'label' => __('Gender', 'cfwc'),
      'class' => 'cfwc-custom-field',
      'desc_tip' => true,
      'description' => __('Select Movies Gender', 'ctwc'),
      'options'     => array(
        ''        => __('Select one', 'woocommerce'),
        '28'    => __('Action', 'woocommerce'), //28
        '12' => __('Adventure', 'woocommerce'), // 12
        '35' => __('Comedy', 'woocommerce'), // 35
        '18' => __('Drama', 'woocommerce'), // 18
      )
    );
    woocommerce_wp_select($args);
  }
  add_action('woocommerce_product_options_general_product_data', 'cfwc_create_custom_field');


  function cfwc_save_custom_field($post_id)
  {
    $product = wc_get_product($post_id);
    $title = isset($_POST['gender']) ? $_POST['gender'] : '';
    $product->update_meta_data('gender', sanitize_text_field($title));
    $product->save();
  }

  add_action('woocommerce_process_product_meta', 'cfwc_save_custom_field');


  add_filter('woocommerce_short_description', 'ts_add_text_short_descr');
  function ts_add_text_short_descr($description)
  {
    $text = "<br />Offer: 40% OFF at Checkout on all products across the store.";
    return $description . $text;
  }

  add_action('woocommerce_after_single_product_summary', 'after_single_product', 11);
  function after_single_product()
  {
    global $product;
    $gender = get_post_meta($product->get_id(), 'gender', true);
    if ($gender) {
      echo "
      <div class='movies'>
        <h1>Movies</h1>
        <div class='products-movies'></div>
      </div>";
    }
  }

  function woocommerce_ajax_add_to_cart_js()
  {
    if (function_exists('is_product') && is_product()) {
      wp_enqueue_script('woocommerce-ajax-add-to-cart', plugin_dir_url(__FILE__) . 'assets/plugin.js', array('jquery'), '', true);
      wp_register_style('custom_wp_admin_css', plugin_dir_url(__FILE__) . 'assets/plugin.css', false, '1.0.0');
      wp_enqueue_style('custom_wp_admin_css');
    }
  }
  add_action('wp_enqueue_scripts', 'woocommerce_ajax_add_to_cart_js', 99);




  add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
  add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');

  function woocommerce_ajax_add_to_cart()
  {

    // echo wp_send_json(var_dump($_POST));
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $variation_id = absint($_POST['variation_id']);
    // This is where you extra meta-data goes in
    $cart_item_data = $_POST['movie'];
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    // Remember to add $cart_item_data to WC->cart->add_to_cart
    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $cart_item_data) && 'publish' === $product_status) {

      do_action('woocommerce_ajax_added_to_cart', $product_id);

      if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
        wc_add_to_cart_message(array($product_id => $quantity), true);
      }

      WC_AJAX::get_refreshed_fragments();
    } else {

      $data = array(
        'error' => true,
        'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
      );

      echo wp_send_json($data);
    }
    wp_die();
  }







  /**
   * Display custom field on the front end
   */
  function my_before_add_to_cart_button()
  {
    global $product;
    $gender = get_post_meta($product->get_id(), 'gender', true);
    $gender = $gender ? $gender : "";
    echo "
    <input id='gender' name='gender' value='$gender' type='hidden' />
    <input id='movie_id' name='movie_id' value='' type='hidden' />
    <input id='movie_title' name='movie_title' value='' type='hidden' />
    <input id='movie_image' name='movie_image' value='' type='hidden' />
    ";
  }
  add_action('woocommerce_before_add_to_cart_button', 'my_before_add_to_cart_button');


  function my_add_to_cart_validation($passed, $product_id, $quantity)
  {
    if (empty($_POST['gender'])) {
      $passed = true;
    } else if (empty($_POST['movie']['movie_id'])) {
      // Fails validation
      $passed = false;
      wc_add_notice(__('Please select a movie', 'woocommerce'), 'error');
    }

    return $passed;
  }
  add_filter('woocommerce_add_to_cart_validation', 'my_add_to_cart_validation', 10, 3);
}
