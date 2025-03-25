<?php
/**
 * Plugin Name: Product Publish Date
 * Description: نمایش زمان باقی‌مانده تا انتشار محصول و حذف خودکار محصول پس از انتشار.
 * Version: 1.4
 * Author: Sajjad Ataei
 */

class Product_Publish_Date_Manager {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'add_publish_date_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_publish_date_field'));
        add_shortcode('product_publish_date', array($this, 'publish_date_shortcode'));
        add_action('woocommerce_single_product_summary', array($this, 'display_on_product_page'), 25);
        add_action('wp_head', array($this, 'add_custom_styles'));
        add_action('wp', array($this, 'schedule_daily_check'));
        add_action('update_publish_date_event', array($this, 'handle_expired_products'));
    }

    public function enqueue_assets() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    }

    public function add_publish_date_field() {
        global $post;
        
        $publish_date = get_post_meta($post->ID, '_publish_date', true);
        
        echo '<div class="options_group">';
        echo '<p class="form-field">';
        echo '<label for="publish_date">' . __('تاریخ انتشار', 'textdomain') . '</label>';
        echo '<input type="date" class="short" name="publish_date" id="publish_date" value="' . esc_attr($publish_date) . '" />';
        echo '<span class="description">' . __('تاریخ انتشار محصول را وارد کنید.', 'textdomain') . '</span>';
        echo '</p>';
        echo '</div>';
    }

    public function save_publish_date_field($product_id) {
        if (isset($_POST['publish_date'])) {
            update_post_meta($product_id, '_publish_date', sanitize_text_field($_POST['publish_date']));
        }
    }

    public function publish_date_shortcode() {
        global $product;

        if (!$product) return '';

        $product_id = $product->get_id();
        $publish_date = get_post_meta($product_id, '_publish_date', true);

        if (!$publish_date) return '';

        $current_date = current_time('Y-m-d');
        $days_remaining = floor((strtotime($publish_date) - strtotime($current_date)) / DAY_IN_SECONDS);

        if ($days_remaining > 0) {
            $clock_icon_url = plugin_dir_url(__FILE__) . 'Vector (1).png';
            
            return '<div class="product-publish-date" style="text-align:center;border: 2px solid #ccc; padding: 10px; background-color: #e2e2e2; border-radius: 17px; height: 54px; width: 209px; display: flex; align-items: center; justify-content: center;">
                    <img src="' . esc_url($clock_icon_url) . '" alt="Clock Icon" style="width: 20px; height: 20px; margin-left: 10px;" />
                    <span><span class="publish_days_int">' . esc_html($days_remaining) . '</span> روز تا انتشار</span>
                </div>';
        }
        
        return '';
    }

    public function display_on_product_page() {
        echo do_shortcode('[product_publish_date]');
    }

    public function add_custom_styles() {
        echo '<style>
            .product-publish-date {
                font-size: 16px;
                font-weight: bold;
                color: #333;
                margin: 10px 0;
            }
            .product-publish-date img {
                margin-right: 10px;
            }
        </style>';
    }

    public function schedule_daily_check() {
        if (!wp_next_scheduled('update_publish_date_event')) {
            wp_schedule_event(time(), 'daily', 'update_publish_date_event');
        }
    }

    public function handle_expired_products() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_key' => '_publish_date',
            'meta_compare' => 'EXISTS',
            'post_status' => 'publish'
        );
        
        $products = new WP_Query($args);
        
        if ($products->have_posts()) {
            while ($products->have_posts()) {
                $products->the_post();
                $product_id = get_the_ID();
                $publish_date = get_post_meta($product_id, '_publish_date', true);
                $current_date = current_time('Y-m-d');
                
                if (strtotime($current_date) >= strtotime($publish_date)) {
                    wp_update_post(array(
                        'ID' => $product_id,
                        'post_status' => 'draft'
                    ));
                }
            }
        }
        wp_reset_postdata();
    }
}

new Product_Publish_Date_Manager();
