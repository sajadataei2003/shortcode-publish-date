<?php
/**
 * Plugin Name: Product Publish Date
 * Description: نمایش زمان باقی‌مانده تا انتشار محصول.
 * Version: 1.2
 * Author: Sajjad Ataei
 */


function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');


function add_publish_date_custom_field() {
    global $post;

    if ('product' === $post->post_type) {
        $days_to_publish = get_post_meta($post->ID, '_days_to_publish', true);

        ?>
        <div class="options_group">
            <p class="form-field">
                <label for="days_to_publish"><?php _e('روزهای تا انتشار', 'textdomain'); ?></label>
                <input type="number" class="short" name="days_to_publish" id="days_to_publish" value="<?php echo esc_attr($days_to_publish); ?>" min="0" />
                <span class="description"><?php _e('تعداد روزهای باقی‌مانده تا انتشار محصول را وارد کنید.', 'textdomain'); ?></span>
            </p>
        </div>
        <?php
    }
}
add_action('woocommerce_product_options_general_product_data', 'add_publish_date_custom_field');


function save_publish_date_custom_field($post_id) {
    if (isset($_POST['days_to_publish']) && is_numeric($_POST['days_to_publish'])) {
        update_post_meta($post_id, '_days_to_publish', sanitize_text_field($_POST['days_to_publish']));
    }
}
add_action('woocommerce_process_product_meta', 'save_publish_date_custom_field');


function product_publish_date_shortcode() {
    global $product;

    $days_to_publish = get_post_meta($product->get_id(), '_days_to_publish', true);

    if ($days_to_publish) {
        
        $clock_icon_url = plugin_dir_url(__FILE__) . 'Vector (1).png';

        return '<div class="product-publish-date" style="text-align:center;border: 2px solid #ccc; padding: 10px; background-color: #e2e2e2; border-radius: 5px;border-radius: 17px;height: 54px;width: 209px; display: flex; align-items: center; justify-content: center;">
                    <img src="' . esc_url($clock_icon_url) . '" alt="Clock Icon" style="width: 20px; height: 20px; margin-left: 10px;" />
                    <span>' . esc_html($days_to_publish) . ' روز تا انتشار</span>
                </div>';
    }

    return '';
}
add_shortcode('product_publish_date', 'product_publish_date_shortcode');


function add_publish_date_to_product() {
    echo do_shortcode('[product_publish_date]');
}
add_action('woocommerce_single_product_summary', 'add_publish_date_to_product', 25);


function add_publish_date_styles() {
    ?>
    <style>
        .product-publish-date {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .product-publish-date img {
            margin-right: 10px;
        }
    </style>
    <?php
}
add_action('wp_head', 'add_publish_date_styles');


server {
    listen 81;
    listen [::]:81;

    server_name example.ubuntu.com;

    root /var/www/tutorial;
    index index.html;

    location / {
            try_files $uri $uri/ =404;
    }
}