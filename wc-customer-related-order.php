<?php
/*
Plugin Name: WooCommerce Customer Related Order
Plugin URI: http://wptarzan.com/
Description: Helps to show related customer order in WooCommerce order details page.
Version: 1.0.0
Author: Sabbir Ahmed
Author URI: https://github.com/sabbir1991
License: GPL2
*/

/**
 * Copyright (c) YEAR Sabbir Ahmed (email: sabbir.081070@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC_Customer_Related_Order class
 *
 * @class WC_Customer_Related_Order The class that holds the entire WC_Customer_Related_Order plugin
 */
class WC_Customer_Related_Order {

     /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Constructor for the WC_Customer_Related_Order class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {

        // Define all constant
        $this->define_constant();

        //includes file
        $this->includes();

        // init actions and filter
        $this->init_filters();
        $this->init_actions();

        // initialize classes
        $this->init_classes();

        do_action( 'wc_customer_related_order_loaded', $this );
    }

    /**
     * Initializes the WC_Customer_Related_Order() class
     *
     * Checks for an existing WC_Customer_Related_Order() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WC_Customer_Related_Order();
        }

        return $instance;
    }

    /**
    * Defined constant
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function define_constant() {
        define( 'WCCRO_VERSION', $this->version );
        define( 'WCCRO_FILE', __FILE__ );
        define( 'WCCRO_PATH', dirname( WCCRO_FILE ) );
        define( 'WCCRO_ASSETS', plugins_url( '/assets', WCCRO_FILE ) );
    }

    /**
    * Includes all files
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function includes() {
        // Includes all files in your plugins
    }

    /**
    * Init all filters
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function init_filters() {
        // Load all filters
    }

    /**
    * Init all actions
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function init_actions() {
        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // Loads frontend scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_related_order_metabox' ) );
        add_action( 'admin_footer', array( $this, 'order_preview_template' ) );
    }

    /**
    * Inistantiate all classes
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function init_classes() {
        // Create instnace for all class
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wc-customer-related-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
    * Load admin scripts
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function admin_enqueue_scripts( $hooks ) {
        global $post_type;

        if ( 'post.php' === $hooks && 'shop_order' === $post_type ) {
            wp_enqueue_style( 'wccor-styles', WCCRO_ASSETS . '/css/wccro-admin.css', false, date( 'Ymd' ) );
            wp_enqueue_script( 'wc-orders', WC()->plugin_url() . '/assets/js/admin/wc-orders.js', array( 'jquery', 'wp-util', 'underscore', 'backbone', 'jquery-blockui' ), WC_VERSION );
        }
    }

    /**
     * Show meta box in order details page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_related_order_metabox() {
        add_meta_box( 'wc_related_order_box', __('Customer Other Orders','wc-customer-related-order' ), array( $this, 'wc_customer_related_order_cb' ), 'shop_order', 'advanced', 'high' );
    }

    /**
     * Meta box content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function wc_customer_related_order_cb() {
        global $post;
        $order = wc_get_order( $post->ID );

        $customer_orders = get_posts( array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => $order->get_customer_id(),
            'post_type'   => wc_get_order_types( 'view-orders' ),
            'post_status' => array_keys( wc_get_order_statuses() ),
            'post_parent' => 0,
            'fields'      => 'ids',
            'exclude'     => array( $post->ID )
        ) );
        ?>
        <table class="widefat fixed wc-related-order-table">
            <thead>
                <tr>
                    <th><?php _e( 'Order', 'wc-customer-related-order' ); ?></th>
                    <th><?php _e( 'Total', 'wc-customer-related-order' ); ?></th>
                    <th><?php _e( 'Status', 'wc-customer-related-order' ); ?></th>
                    <th><?php _e( 'Payment Method', 'wc-customer-related-order' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( $customer_orders ): ?>
                <?php foreach ( $customer_orders as $order_id ): ?>
                    <?php
                        $other_order = wc_get_order( $order_id );
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $other_order->get_edit_order_url(); ?>">
                                <?php echo _x( '#', 'hash before order number', 'wc-customer-related-order' ) . $other_order->get_order_number(); ?>
                            </a>
                        </td>
                        <td><?php echo $other_order->get_formatted_order_total(); ?></td>
                        <td><?php echo wc_get_order_status_name( $other_order->get_status() ); ?></td>
                        <td><?php echo $other_order->get_payment_method_title(); ?></td>
                        <td><a href="#" class="button wc-action-button order-preview" data-order-id="<?php echo $other_order->get_id(); ?>"><?php _e( 'View Items', 'wc-customer-related-order' ) ?></a></td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>

                <tr>
                    <td colspan="4"><?php _e( 'No orders found', 'wc-customer-related-order' ); ?></td>
                </tr>

            <?php endif ?>
            </tbody>
        </table>
        <?php
    }

        /**
     * Template for order preview.
     *
     * @since 3.3.0
     */
    public function order_preview_template() {
        $screen = get_current_screen();

        if ( 'post' === $screen->base && 'shop_order' === $screen->post_type ) {
        ?>
            <script type="text/template" id="tmpl-wc-modal-view-order">
                <div class="wc-backbone-modal wc-order-preview">
                    <div class="wc-backbone-modal-content">
                        <section class="wc-backbone-modal-main" role="main">
                            <header class="wc-backbone-modal-header">
                                <mark class="order-status status-{{ data.status }}"><span>{{ data.status_name }}</span></mark>
                                <?php /* translators: %s: order ID */ ?>
                                <h1><?php echo esc_html( sprintf( __( 'Order #%s', 'wc-customer-related-order' ), '{{ data.order_number }}' ) ); ?></h1>
                                <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                    <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'wc-customer-related-order' ); ?></span>
                                </button>
                            </header>
                            <article>
                                <?php do_action( 'woocommerce_admin_order_preview_start' ); ?>

                                <div class="wc-order-preview-addresses">
                                    <div class="wc-order-preview-address">
                                        <# if ( data.data.billing.email ) { #>
                                            <strong><?php esc_html_e( 'Email', 'wc-customer-related-order' ); ?></strong>
                                            <a href="mailto:{{ data.data.billing.email }}">{{ data.data.billing.email }}</a>
                                        <# } #>

                                        <# if ( data.data.billing.phone ) { #>
                                            <strong><?php esc_html_e( 'Phone', 'wc-customer-related-order' ); ?></strong>
                                            <a href="tel:{{ data.data.billing.phone }}">{{ data.data.billing.phone }}</a>
                                        <# } #>

                                        <# if ( data.payment_via ) { #>
                                            <strong><?php esc_html_e( 'Payment via', 'wc-customer-related-order' ); ?></strong>
                                            {{{ data.payment_via }}}
                                        <# } #>
                                    </div>
                                    <# if ( data.needs_shipping ) { #>
                                        <div class="wc-order-preview-address">
                                            <# if ( data.shipping_via ) { #>
                                                <strong><?php esc_html_e( 'Shipping method', 'wc-customer-related-order' ); ?></strong>
                                                {{ data.shipping_via }}
                                            <# } #>
                                        </div>
                                    <# } #>

                                    <# if ( data.data.customer_note ) { #>
                                        <div class="wc-order-preview-note">
                                            <strong><?php esc_html_e( 'Note', 'wc-customer-related-order' ); ?></strong>
                                            {{ data.data.customer_note }}
                                        </div>
                                    <# } #>
                                </div>

                                {{{ data.item_html }}}

                                <?php do_action( 'woocommerce_admin_order_preview_end' ); ?>
                            </article>
                            <footer>
                                <div class="inner">
                                    {{{ data.actions_html }}}

                                    <a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'wc-customer-related-order' ); ?>" href="<?php echo esc_url( admin_url( 'post.php?action=edit' ) ); ?>&post={{ data.data.id }}"><?php esc_html_e( 'Edit', 'wc-customer-related-order' ); ?></a>
                                </div>
                            </footer>
                        </section>
                    </div>
                </div>
                <div class="wc-backbone-modal-backdrop modal-close"></div>
            </script>
        <?php
        }
    }

} // WC_Customer_Related_Order

$wcro = WC_Customer_Related_Order::init();
