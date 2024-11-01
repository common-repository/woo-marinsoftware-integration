<?php
/**
 * Plugin Name: Woo Marinsoftware Integration
 * Plugin URI: https://github.com/phpgladiator
 * Description: Insert javascript to every product page of the website to conform with Marinsoftware. This plugin will dynamically insert the ProductID in the product page. Then when a completed checkout happens, it will include OrderID and Revenue (cart total) dynamically on the script.
 * Author: Azharul Lincoln
 * Author URI: https://github.com/phpgladiator
 * Version:     1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MarinSoftwareWooCommerceIntegration
{

    function __construct()
    {
        add_action('wp_footer', array($this, 'insert_script'));
    }

    /**
     * @return bool
     */
    function is_user_accounts_order_view_page(){
       return is_wc_endpoint_url('view-order');
    }


    /**
     *
     * @return bool|int
     */
    function get_order_id_from_url(){
       $result =  preg_match("/\/(\d+)\/?$/",$_SERVER['REQUEST_URI'], $matches);
        if($result){
            return (int)$matches[1];
        }
        return false;
    }


    /**
     *
     * Insert script into footer if it is product or order view page
     */
    function insert_script()
    {
        if (is_product() || is_order_received_page() || $this->is_user_accounts_order_view_page()) {
            $this->script();
        }
    }


    /**
     *
     * Script that will get inserted on product and order page
     */
    function script()
    {
        $order_id = $this->get_order_id();
        $order_total = $this->get_order_total();
        $product_id = is_product() ?  $this->get_product_id() : '';
        ?>
        <!--    Genareted from Perfect Pixel Script Plugin    -->
        <script type="text/javascript">
            (function () {
                window._pa = window._pa || {};
                _pa.orderId = <?php  echo empty($order_id) ? "''" : $order_id; ?>; // OPTIONAL: attach unique conversion identifier to conversions
                _pa.revenue = <?php echo empty($order_total) ? "''" : $order_total; ?>; // OPTIONAL: attach dynamic purchase values to conversions
                _pa.productId = <?php echo empty($product_id) ? "''" : $product_id; ?>; // OPTIONAL: Include product ID for use with dynamic ads
                var pa = document.createElement('script');
                pa.type = 'text/javascript';
                pa.async = true;
                pa.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + "//tag.marinsm.com/serve/577d6f54f732774c39000022.js";
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(pa, s);
            })();
        </script>
        <!--    Pixel Script Plugin End    -->

        <?php
    }



    /**
     *
     * Return order id
     * @return string
     */
    function get_order_id()
    {
        global $wp;
        $order_id = '';
        if (is_order_received_page()) {
            $order_id  = $wp->query_vars['order-received'];
        }elseif($this->is_user_accounts_order_view_page()){
            $order_id = $this->get_order_id_from_url();
        }
        return $order_id;
    }


    /**
     *
     * Return order total
     * @return float|string
     */
    function get_order_total()
    {
        $order_total = '';
        $order_id = $this->get_order_id();
        if (!empty($order_id)) {
            $order = wc_get_order($order_id);
            $order_total = $order->get_total();
        }
        return $order_total;
    }



    /**
     *
     * Return product id
     * @return int
     */
    function get_product_id()
    {
        global $product;
        return $product->id;
    }

}

new MarinSoftwareWooCommerceIntegration();