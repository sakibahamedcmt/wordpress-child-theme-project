<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */



 add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 100 );
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
	
	wp_enqueue_style('font-awesome-4', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0');
	wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css', array(), '4.1.0');
  wp_enqueue_style('custom-css', get_stylesheet_directory_uri() . '/css/custom-style.css', array('astra-theme-css'), time(), 'all');
	

wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
  wp_enqueue_script('greed-product', get_stylesheet_directory_uri() . '/js/wpc-script.js',array(),'1.0', true);
   wp_enqueue_script('tab-greed-product', get_stylesheet_directory_uri() . '/js/wpt-script.js',array(),'1.0', true);
}



function touched_lives_slider_scripts() {
    wp_enqueue_script('touched-lives-slider', get_stylesheet_directory_uri() . '/js/touched-lives-slider.js',array(),'1.0', true);
}
add_action('wp_enqueue_scripts', 'touched_lives_slider_scripts');

remove_filter('template_redirect', 'redirect_canonical');



// Ensure Elementor is active
function register_custom_elementor_widget() {
    if ( defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base') ) {
        require_once get_stylesheet_directory() . '/elementor-widgets/custom-slider-widget.php';
    }
}
add_action('elementor/widgets/widgets_registered', 'register_custom_elementor_widget');


// ===== Featured Image উপরে দেখানোর জন্য =====
function show_featured_image_before_content( $content ) {
    if ( is_single() && in_the_loop() && is_main_query() ) {
        // যদি পোস্টে thumbnail থাকে
        if ( has_post_thumbnail() ) {
            $featured_image = '<div class="single-featured-image">';
            $featured_image .= get_the_post_thumbnail( get_the_ID(), 'large' );
            $featured_image .= '</div>';
            
            // Image উপরে, তারপর content
            $content = $featured_image . $content;
        }
    }
    return $content;
}
add_filter( 'the_content', 'show_featured_image_before_content' );



// ==== Custom Post Type: Touched Lives ==== //
function create_touched_lives_cpt() {
    $labels = array(
        'name' => 'Touched Lives',
        'singular_name' => 'Touched Life',
    );
    $args = array(
    'label' => 'Touched Lives',
    'public' => true,
    'has_archive' => true, // এটা যুক্ত করো
    'show_ui' => true,
    'show_in_menu' => true,
    'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
    'menu_icon' => 'dashicons-heart',
);
    register_post_type('touched_lives', $args);
}
add_action('init', 'create_touched_lives_cpt');


// ==== Custom Shortcode for Infinite Slider ==== //
function touched_lives_slider_shortcode() {
    ob_start();

    $args = array(
        'post_type'      => 'touched_lives',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) : ?>
        <div class="touched-lives-slider swiper">
            <div class="swiper-wrapper">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="swiper-slide">
                        <div class="slide-content">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="slide-thumbss">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="slide-info-area">
                                <a href="<?php the_permalink(); ?>" class="slide-link">
                                    <h3><?php the_title(); ?></h3>
                                    <div class="slide-excerpt"><?php the_excerpt(); ?></div>
                                </a>

                                <a href="<?php the_permalink(); ?>" class="read-more-btn">
                                    Read More <span class="arrow-icon">→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php
    endif;
    wp_reset_postdata();
    ?>

    <!-- Swiper JS Initialization -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            new Swiper('.touched-lives-slider', {
                slidesPerView: 4,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 0,
                    disableOnInteraction: false,
                },
                speed: 4000,
                allowTouchMove: true,
                breakpoints: {
                    0: { slidesPerView: 1, spaceBetween: 10 },
                    768: { slidesPerView: 2, spaceBetween: 15 },
                    1024: { slidesPerView: 3, spaceBetween: 20 },
                }
            });
        });
    </script>

<!-- Slider Area CSS -->

    
    <?php

    return ob_get_clean();
}
add_shortcode('touched_lives_slider', 'touched_lives_slider_shortcode');




// JPG, PNG to WEBP onvert

// === Convert Uploads to WebP (Only WebP, Best Speed) ===
add_filter('wp_generate_attachment_metadata', 'convert_to_webp_only', 10, 2);

function convert_to_webp_only($metadata, $attachment_id) {
    $file = get_attached_file($attachment_id);
    $image_info = getimagesize($file);

    if ($image_info && in_array($image_info['mime'], ['image/jpeg', 'image/png'])) {
        // JPG বা PNG থেকে ইমেজ লোড করা
        $image = ($image_info['mime'] === 'image/jpeg') 
            ? imagecreatefromjpeg($file) 
            : imagecreatefrompng($file);

        if ($image !== false) {
            $new_file = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file);
            imagewebp($image, $new_file, 80); // Quality 80%
            imagedestroy($image);

            // পুরাতন JPG/PNG ডিলিট
            unlink($file);

            // নতুন WebP ফাইলকে attachment বানানো
            update_attached_file($attachment_id, $new_file);
        }
    }

    return $metadata;
}

























/**
 * WooCommerce Tabbed Product Grid — wpt-functions.php
 *
 * INSTALLATION:
 *   Upload the /wpt/ folder to /wp-content/themes/your-child-theme/wpt/
 *   Then add to your child theme's functions.php:
 *     require_once get_stylesheet_directory() . '/wpt/wpt-functions.php';
 *
 * USAGE:
 *   [woo_product_tabs
 *     title="Best Seller Products"
 *     tabs="wash-basin,bathtub,mirrors"
 *     tab_labels="Wash Basin,Bathtub,Mirrors"
 *     limit="8"
 *     columns="4"
 *     orderby="date"
 *     order="DESC"
 *   ]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─────────────────────────────────────────────────────────────
// CONFIG
// ─────────────────────────────────────────────────────────────
define( 'WPT_DIR_URL', get_stylesheet_directory_uri() . '/wpt/' );
define( 'WPT_COMPARE_PAGE_SLUG', 'yith-compare' );

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────
function wpt_currency() {
    return html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

function wpt_format_price( $amount ) {
    return wpt_currency() . number_format( floatval( $amount ), 2 );
}

function wpt_get_compare_url() {
    $page = get_page_by_path( WPT_COMPARE_PAGE_SLUG );
    return $page ? get_permalink( $page->ID ) : home_url( '/' . WPT_COMPARE_PAGE_SLUG . '/' );
}

// ─────────────────────────────────────────────────────────────
// ENQUEUE — CSS + JS only on pages using the shortcode
// ─────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'wpt_enqueue_assets' );
function wpt_enqueue_assets() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'woo_product_tabs' ) ) return;

    wp_enqueue_script( 'wc-add-to-cart' );

    wp_enqueue_style(
        'wpt-style',
        WPT_DIR_URL . 'wpt-style.css',
        array( 'woocommerce-general' ),
        '1.0'
    );

    wp_enqueue_script(
        'wpt-script',
        WPT_DIR_URL . 'wpt-script.js',
        array( 'jquery', 'wc-add-to-cart' ),
        '1.0',
        true
    );

    wp_localize_script( 'wpt-script', 'wptData', array(
        'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
        'nonce'          => wp_create_nonce( 'wpt_nonce' ),
        'comparePageUrl' => wpt_get_compare_url(),
    ) );
}

// ─────────────────────────────────────────────────────────────
// AJAX: Variation image + price swap
// ─────────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpt_variation_image',        'wpt_variation_image_cb' );
add_action( 'wp_ajax_nopriv_wpt_variation_image', 'wpt_variation_image_cb' );

function wpt_variation_image_cb() {
    check_ajax_referer( 'wpt_nonce', 'nonce' );
    $vid       = intval( $_POST['variation_id'] );
    $variation = wc_get_product( $vid );
    if ( ! $variation ) { wp_send_json_error(); }

    $img_id  = $variation->get_image_id();
    $parent  = wc_get_product( $variation->get_parent_id() );
    $img_url = $img_id
        ? wp_get_attachment_image_url( $img_id, 'woocommerce_thumbnail' )
        : ( $parent ? wp_get_attachment_image_url( $parent->get_image_id(), 'woocommerce_thumbnail' ) : '' );

    $price   = $variation->get_price();
    $regular = $variation->get_regular_price();
    $sale    = $variation->get_sale_price();

    wp_send_json_success( array(
        'image_url'    => $img_url,
        'price'        => wpt_format_price( $price ),
        'regular'      => ( $regular && $sale ) ? wpt_format_price( $regular ) : '',
        'variation_id' => $vid,
    ) );
}

// ─────────────────────────────────────────────────────────────
// AJAX: Quick View modal
// ─────────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpt_quick_view',        'wpt_quick_view_cb' );
add_action( 'wp_ajax_nopriv_wpt_quick_view', 'wpt_quick_view_cb' );

function wpt_quick_view_cb() {
    check_ajax_referer( 'wpt_nonce', 'nonce' );
    $pid     = intval( $_POST['product_id'] );
    $product = wc_get_product( $pid );
    if ( ! $product ) { wp_send_json_error( 'Not found' ); }

    $currency    = wpt_currency();
    $title       = $product->get_name();
    $desc        = wp_kses_post( $product->get_short_description() ?: $product->get_description() );
    $permalink   = get_permalink( $pid );
    $in_stock    = $product->is_in_stock();
    $is_variable = $product->is_type( 'variable' );
    $rating      = $product->get_average_rating();
    $rc          = $product->get_rating_count();

    // Gallery images
    $image_ids = array_filter( array_merge(
        array( $product->get_image_id() ),
        $product->get_gallery_image_ids()
    ) );
    $images = array();
    foreach ( $image_ids as $iid ) {
        $u = wp_get_attachment_image_url( $iid, 'woocommerce_single' );
        if ( $u ) $images[] = $u;
    }
    if ( empty( $images ) ) $images[] = wc_placeholder_img_src( 'woocommerce_single' );

    // Stars
    $stars = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $c     = $rating >= $i ? 'full' : ( $rating >= $i - 0.5 ? 'half' : '' );
        $stars .= '<span class="wpt-star ' . $c . '">&#9733;</span>';
    }

    // Variations
    $qv_var_html     = '';
    $qv_first_var_id = $pid;
    $qv_price        = $product->get_price();
    $qv_regular      = $product->get_regular_price();
    $qv_sale         = $product->get_sale_price();

    if ( $is_variable ) {
        $var_obj    = new WC_Product_Variable( $pid );
        $variations = $var_obj->get_available_variations();
        $attributes = $var_obj->get_variation_attributes();

        if ( ! empty( $variations ) ) {
            $first_v         = wc_get_product( $variations[0]['variation_id'] );
            $qv_first_var_id = $variations[0]['variation_id'];
            if ( $first_v ) {
                $qv_price   = $first_v->get_price();
                $qv_regular = $first_v->get_regular_price();
                $qv_sale    = $first_v->get_sale_price();
                $fv_img_id  = $first_v->get_image_id();
                if ( $fv_img_id ) {
                    $fv_img_url = wp_get_attachment_image_url( $fv_img_id, 'woocommerce_single' );
                    if ( $fv_img_url && $fv_img_url !== $images[0] ) array_unshift( $images, $fv_img_url );
                }
            }
        }

        foreach ( $attributes as $attr_name => $options ) {
            $label      = wc_attribute_label( $attr_name );
            $option_map = array();
            foreach ( $variations as $v ) {
                foreach ( $v['attributes'] as $key => $val ) {
                    $clean = str_replace( 'attribute_', '', $key );
                    if ( $clean === $attr_name && $val !== '' && ! isset( $option_map[$val] ) ) {
                        $option_map[$val] = $v['variation_id'];
                    }
                }
            }
            $qv_var_html .= '<div class="wpt-variant-wrap">';
            $qv_var_html .= '<select class="wpt-variant wpt-qv-variant" aria-label="' . esc_attr( $label ) . '">';
            $first = true;
            foreach ( $options as $opt ) {
                $vid          = isset( $option_map[$opt] ) ? $option_map[$opt] : '';
                $qv_var_html .= '<option value="' . esc_attr($opt) . '" data-variation-id="' . esc_attr($vid) . '"' . ($first ? ' selected' : '') . '>' . esc_html($opt) . '</option>';
                $first = false;
            }
            $qv_var_html .= '</select></div>';
        }
    }

    $price_display   = $currency . number_format( floatval($qv_price), 2 );
    $regular_display = ( $qv_sale && $qv_regular ) ? $currency . number_format( floatval($qv_regular), 2 ) : '';

    ob_start(); ?>
    <div class="wpt-qv-inner">
      <div class="wpt-qv-left">
        <div class="wpt-slider" id="wpt-qv-slider">
          <div class="wpt-slider-track">
            <?php foreach ( $images as $i => $img_url ) : ?>
              <div class="wpt-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>"/>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ( count($images) > 1 ) : ?>
            <button class="wpt-slide-prev">&#8592;</button>
            <button class="wpt-slide-next">&#8594;</button>
          <?php endif; ?>
        </div>
        <?php if ( count($images) > 1 ) : ?>
          <div class="wpt-slider-thumbs">
            <?php foreach ( $images as $i => $img_url ) : ?>
              <div class="wpt-thumb <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>">
                <img src="<?php echo esc_url($img_url); ?>" alt="thumb"/>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="wpt-qv-right">
        <h2 class="wpt-qv-title"><?php echo esc_html($title); ?></h2>
        <div class="wpt-stars" style="margin-bottom:10px">
          <?php echo $stars; ?>
          <?php if ( $rc > 0 ) echo '<span class="wpt-star-count">(' . $rc . ')</span>'; ?>
        </div>
        <div class="wpt-qv-price-wrap">
          <span class="wpt-qv-price-current"><?php echo esc_html($price_display); ?></span>
          <?php if ( $regular_display ) : ?>
            <span class="wpt-qv-price-original"><?php echo esc_html($regular_display); ?></span>
          <?php endif; ?>
        </div>
        <div class="wpt-qv-desc"><?php echo $desc ?: '<p>No description.</p>'; ?></div>
        <?php echo $qv_var_html; ?>
        <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
          <?php if ( $in_stock ) : ?>
            <button class="wpt-cart-btn add_to_cart_button ajax_add_to_cart" style="flex:1"
              data-product_id="<?php echo esc_attr($qv_first_var_id); ?>"
              data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
              data-quantity="1" rel="nofollow">
              <span class="wpt-btn-text">Add to Cart</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                <path d="M14.3085 9.49984H3.69223C3.55873 9.49984 3.45452 9.61533 3.46802 9.74808L3.92999 14.2248C4.04999 15.3723 4.49249 16.2498 6.17249 16.2498H11.8425C13.4925 16.2498 13.9725 15.3723 14.0775 14.2248L14.532 9.74734C14.5455 9.61534 14.442 9.49984 14.3085 9.49984ZM10.5 13.0623H9.5625V13.9998C9.5625 14.3103 9.3105 14.5623 9 14.5623C8.6895 14.5623 8.4375 14.3103 8.4375 13.9998V13.0623H7.5C7.1895 13.0623 6.9375 12.8103 6.9375 12.4998C6.9375 12.1893 7.1895 11.9373 7.5 11.9373H8.4375V10.9998C8.4375 10.6893 8.6895 10.4373 9 10.4373C9.3105 10.4373 9.5625 10.6893 9.5625 10.9998V11.9373H10.5C10.8105 11.9373 11.0625 12.1893 11.0625 12.4998C11.0625 12.8103 10.8105 13.0623 10.5 13.0623ZM15.75 7.24984C15.75 8.07784 15.078 8.74984 14.25 8.74984H3.75C2.922 8.74984 2.25 8.07784 2.25 7.24984C2.25 6.42184 2.922 5.74984 3.75 5.74984H5.04602L7.03271 2.45884C7.19321 2.19334 7.539 2.10709 7.8045 2.26834C8.07 2.42959 8.15628 2.77459 7.99503 3.04084L6.35925 5.74984H11.6475L10.0305 3.03784C9.87152 2.77159 9.95851 2.42584 10.2263 2.26684C10.4925 2.10859 10.8382 2.19484 10.9972 2.46259L12.9577 5.75059H14.25C15.078 5.74984 15.75 6.42184 15.75 7.24984Z" fill="white"></path>
            </svg>
            </button>
          <?php else : ?>
            <span class="wpt-out-of-stock" style="flex:1">Out of Stock</span>
          <?php endif; ?>
          <a href="<?php echo esc_url($permalink); ?>" class="wpt-cart-btn" style="flex:1;background:#444;text-decoration:none">View Product</a>
        </div>
      </div>
    </div>
    <?php
    wp_send_json_success( ob_get_clean() );
}

// ─────────────────────────────────────────────────────────────
// HELPER: render one product card
// ─────────────────────────────────────────────────────────────
function wpt_render_card( $product, $currency ) {
    $id          = $product->get_id();
    $title       = $product->get_name();
    $permalink   = get_permalink( $id );
    $on_sale     = $product->is_on_sale();
    $in_stock    = $product->is_in_stock();
    $rating      = $product->get_average_rating();
    $rating_cnt  = $product->get_rating_count();
    $is_new      = ( strtotime( get_post_field('post_date', $id) ) > strtotime('-30 days') );
    $is_variable = $product->is_type('variable');

    // Image + price — first variation if variable
    $image_url          = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
    $current            = $product->get_price();
    $regular            = $product->get_regular_price();
    $sale               = $product->get_sale_price();
    $first_variation_id = $id;
    $variation_html     = '';

    if ( $is_variable ) {
        $var_obj    = new WC_Product_Variable( $id );
        $variations = $var_obj->get_available_variations();
        $attributes = $var_obj->get_variation_attributes();

        if ( ! empty($variations) ) {
            $fvid  = $variations[0]['variation_id'];
            $fvobj = wc_get_product( $fvid );
            $first_variation_id = $fvid;
            if ( $fvobj ) {
                $fv_img_id  = $fvobj->get_image_id();
                $fv_img_url = $fv_img_id ? wp_get_attachment_image_url($fv_img_id, 'woocommerce_thumbnail') : '';
                if ( $fv_img_url ) $image_url = $fv_img_url;
                $current = $fvobj->get_price();
                $regular = $fvobj->get_regular_price();
                $sale    = $fvobj->get_sale_price();
            }
        }

        foreach ( $attributes as $attr_name => $options ) {
            $option_map = array();
            foreach ( $variations as $v ) {
                foreach ( $v['attributes'] as $key => $val ) {
                    $clean = str_replace('attribute_', '', $key);
                    if ( $clean === $attr_name && $val !== '' && ! isset($option_map[$val]) ) {
                        $option_map[$val] = $v['variation_id'];
                    }
                }
            }
            $variation_html .= '<div class="wpt-variant-wrap"><select class="wpt-variant" aria-label="' . esc_attr(wc_attribute_label($attr_name)) . '">';
            $first_opt = true;
            foreach ( $options as $opt ) {
                $vid             = isset($option_map[$opt]) ? $option_map[$opt] : '';
                $variation_html .= '<option value="' . esc_attr($opt) . '" data-variation-id="' . esc_attr($vid) . '"' . ($first_opt ? ' selected' : '') . '>' . esc_html($opt) . '</option>';
                $first_opt = false;
            }
            $variation_html .= '</select></div>';
        }
    }

    // Discount badge
    $discount_pct = '';
    if ( $sale && $regular && floatval($regular) > 0 ) {
        $discount_pct = '-' . round( (($regular - $sale) / $regular) * 100 ) . '%';
    }

    // Stars
    $stars = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $c     = $rating >= $i ? 'full' : ($rating >= $i - 0.5 ? 'half' : '');
        $stars .= '<span class="wpt-star ' . $c . '">&#9733;</span>';
    }

    $price_display   = $currency . number_format( floatval($current), 2 );
    $regular_display = ($sale && $regular && $regular != $current) ? $currency . number_format( floatval($regular), 2 ) : '';

    // Brand (product_brand or pa_brand taxonomy)
    $brand       = '';
    $brand_terms = wp_get_post_terms($id, 'product_brand', array('fields'=>'names'));
    if ( ! is_wp_error($brand_terms) && ! empty($brand_terms) ) {
        $brand = $brand_terms[0];
    } else {
        $brand_terms2 = wp_get_post_terms($id, 'pa_brand', array('fields'=>'names'));
        if ( ! is_wp_error($brand_terms2) && ! empty($brand_terms2) ) $brand = $brand_terms2[0];
    }

    ?>
    <div class="wpt-card">
      <div class="wpt-img-wrap">
        <?php if ($discount_pct) : ?><span class="wpt-badge-discount"><?php echo esc_html($discount_pct); ?></span><?php endif; ?>
        <?php if ($is_new && !$on_sale) : ?><span class="wpt-badge-new">New</span>
        <?php elseif ($on_sale && !$discount_pct) : ?><span class="wpt-badge-sale">Sale</span><?php endif; ?>

        <div class="wpt-actions">
          <button class="wpt-action-btn wpt-wishlist-btn" data-id="<?php echo $id; ?>" title="Wishlist">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.501 5.501 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
          <button class="wpt-action-btn wpt-compare-btn" data-id="<?php echo $id; ?>" data-name="<?php echo esc_attr($title); ?>" title="Compare">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
          </button>
          <button class="wpt-action-btn wpt-quickview-btn" data-id="<?php echo $id; ?>" title="Quick View">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <?php if ($image_url) : ?>
          <a href="<?php echo esc_url($permalink); ?>">
            <img class="wpt-product-img" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy"/>
          </a>
        <?php else : ?>
          <div class="wpt-img-placeholder">No image</div>
        <?php endif; ?>
      </div>

      <div class="wpt-body">
        <?php if ($brand) : ?><span class="wpt-brand"><?php echo esc_html($brand); ?></span><?php endif; ?>
        <h3 class="wpt-title"><a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a></h3>
        <div class="wpt-stars">
          <?php echo $stars; ?>
          <?php if ($rating_cnt > 0) : ?><span class="wpt-star-count">(<?php echo $rating_cnt; ?>)</span><?php endif; ?>
        </div>
        <?php echo $variation_html; ?>
        <div class="wpt-price-row">
          <span class="wpt-price"><?php echo esc_html($price_display); ?></span>
          <?php if ($regular_display) : ?><span class="wpt-price-original"><?php echo esc_html($regular_display); ?></span><?php endif; ?>
        </div>
        <?php if ($in_stock) : ?>
          <button class="wpt-cart-btn add_to_cart_button ajax_add_to_cart"
            data-product_id="<?php echo esc_attr($first_variation_id); ?>"
            data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
            data-quantity="1" rel="nofollow">
            <span class="wpt-btn-text">Add to Cart</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                <path d="M14.3085 9.49984H3.69223C3.55873 9.49984 3.45452 9.61533 3.46802 9.74808L3.92999 14.2248C4.04999 15.3723 4.49249 16.2498 6.17249 16.2498H11.8425C13.4925 16.2498 13.9725 15.3723 14.0775 14.2248L14.532 9.74734C14.5455 9.61534 14.442 9.49984 14.3085 9.49984ZM10.5 13.0623H9.5625V13.9998C9.5625 14.3103 9.3105 14.5623 9 14.5623C8.6895 14.5623 8.4375 14.3103 8.4375 13.9998V13.0623H7.5C7.1895 13.0623 6.9375 12.8103 6.9375 12.4998C6.9375 12.1893 7.1895 11.9373 7.5 11.9373H8.4375V10.9998C8.4375 10.6893 8.6895 10.4373 9 10.4373C9.3105 10.4373 9.5625 10.6893 9.5625 10.9998V11.9373H10.5C10.8105 11.9373 11.0625 12.1893 11.0625 12.4998C11.0625 12.8103 10.8105 13.0623 10.5 13.0623ZM15.75 7.24984C15.75 8.07784 15.078 8.74984 14.25 8.74984H3.75C2.922 8.74984 2.25 8.07784 2.25 7.24984C2.25 6.42184 2.922 5.74984 3.75 5.74984H5.04602L7.03271 2.45884C7.19321 2.19334 7.539 2.10709 7.8045 2.26834C8.07 2.42959 8.15628 2.77459 7.99503 3.04084L6.35925 5.74984H11.6475L10.0305 3.03784C9.87152 2.77159 9.95851 2.42584 10.2263 2.26684C10.4925 2.10859 10.8382 2.19484 10.9972 2.46259L12.9577 5.75059H14.25C15.078 5.74984 15.75 6.42184 15.75 7.24984Z" fill="white"></path>
            </svg>
          </button>
        <?php else : ?>
          <span class="wpt-out-of-stock">Out of Stock</span>
        <?php endif; ?>
      </div>
    </div>
    <?php
}

// ─────────────────────────────────────────────────────────────
// HELPER: render modal + compare bar + toast (once per page)
// ─────────────────────────────────────────────────────────────
function wpt_render_ui() {
    if ( defined('WPT_UI_RENDERED') ) return;
    define('WPT_UI_RENDERED', true);
    ?>
    <div class="wpt-modal-overlay" id="wpt-modal-overlay">
      <div class="wpt-modal">
        <button class="wpt-modal-close" id="wpt-modal-close">&times;</button>
        <div id="wpt-modal-body"><div class="wpt-modal-loading">Loading...</div></div>
      </div>
    </div>
    <div class="wpt-compare-bar" id="wpt-compare-bar">
      <span class="wpt-compare-bar-title">Compare:</span>
      <div class="wpt-compare-items" id="wpt-compare-items"></div>
      <div class="wpt-compare-actions">
        <button class="wpt-compare-go">Compare Now</button>
        <button class="wpt-compare-clear">Clear All</button>
      </div>
    </div>
    <div class="wpt-toast" id="wpt-toast">
      <img class="wpt-toast-img" id="wpt-toast-img" src="" alt=""/>
      <div class="wpt-toast-body">
        <span class="wpt-toast-label">Added to Cart</span>
        <span class="wpt-toast-name" id="wpt-toast-name"></span>
        <span class="wpt-toast-sub"  id="wpt-toast-sub"></span>
      </div>
      <div class="wpt-toast-tick">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <button class="wpt-toast-close" id="wpt-toast-close">&times;</button>
      <div class="wpt-toast-progress" id="wpt-toast-progress"></div>
    </div>
    <?php
}

// ─────────────────────────────────────────────────────────────
// HELPER: get products by category slug
// ─────────────────────────────────────────────────────────────
function wpt_get_products( $slug, $limit = 8, $orderby = 'date', $order = 'DESC' ) {
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => $orderby === 'popularity' ? 'meta_value_num' : $orderby,
        'order'          => $order,
        'tax_query'      => array( array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $slug,
        ) ),
    );
    if ( $orderby === 'popularity' ) $args['meta_key'] = 'total_sales';
    if ( $orderby === 'rating' )     { $args['meta_key'] = '_wc_average_rating'; $args['orderby'] = 'meta_value_num'; }

    $query    = new WP_Query( $args );
    $products = array();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $p = wc_get_product( get_the_ID() );
            if ( $p ) $products[] = $p;
        }
        wp_reset_postdata();
    }
    return $products;
}

// ─────────────────────────────────────────────────────────────
// SHORTCODE: [woo_product_tabs]
// ─────────────────────────────────────────────────────────────
add_shortcode( 'woo_product_tabs', 'woo_product_tabs_shortcode' );

function woo_product_tabs_shortcode( $atts ) {
    if ( ! class_exists('WooCommerce') ) return '<p style="color:red">WooCommerce not active.</p>';

    $atts = shortcode_atts( array(
        'title'      => '',
        'tabs'       => '',
        'tab_labels' => '',
        'limit'      => '8',
        'columns'    => '4',
        'orderby'    => 'date',
        'order'      => 'DESC',
    ), $atts, 'woo_product_tabs' );

    if ( empty($atts['tabs']) ) return '<p>Please set the <code>tabs</code> parameter.</p>';

    $slugs      = array_map( 'trim', explode(',', $atts['tabs']) );
    $raw_labels = $atts['tab_labels'] ? array_map('trim', explode(',', $atts['tab_labels'])) : array();
    $labels     = array();
    foreach ( $slugs as $i => $slug ) {
        $labels[$slug] = isset($raw_labels[$i]) ? $raw_labels[$i] : ucwords( str_replace(array('-','_'), ' ', $slug) );
    }

    $cols     = intval( $atts['columns'] );
    $limit    = intval( $atts['limit'] );
    $currency = wpt_currency();
    $uid      = 'wpt-' . substr( md5( serialize($atts) ), 0, 8 );

    ob_start();
    ?>
    <div class="wpt-section" id="<?php echo esc_attr($uid); ?>">

      <div class="wpt-header">
        <?php if ( $atts['title'] ) : ?>
          <h2 class="wpt-section-title"><?php echo esc_html($atts['title']); ?></h2>
        <?php endif; ?>
        <div class="wpt-tabs" role="tablist">
          <?php foreach ( $slugs as $i => $slug ) : ?>
            <button
              class="wpt-tab<?php echo $i === 0 ? ' active' : ''; ?>"
              role="tab"
              data-slug="<?php echo esc_attr($slug); ?>"
              data-uid="<?php echo esc_attr($uid); ?>"
              aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
              <?php echo esc_html($labels[$slug]); ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php foreach ( $slugs as $i => $slug ) :
        $products = wpt_get_products( $slug, $limit, $atts['orderby'], $atts['order'] );
      ?>
        <div class="wpt-panel<?php echo $i === 0 ? ' active' : ''; ?>" data-slug="<?php echo esc_attr($slug); ?>" role="tabpanel">
          <?php if ( empty($products) ) : ?>
            <p class="wpt-empty">No products found in this category.</p>
          <?php else : ?>
            <div class="wpt-grid" style="--wpt-cols:<?php echo $cols; ?>">
              <?php foreach ( $products as $product ) : ?>
                <?php wpt_render_card( $product, $currency ); ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

    </div>
    <?php
    wpt_render_ui();
    return ob_get_clean();
}
























/**
 * WooCommerce Custom Product Card — functions.php
 *
 * INSTALLATION:
 *   1. Upload the /wpc/ folder to /wp-content/themes/your-child-theme/wpc/
 *      The folder should contain:
 *        wpc-style.css
 *        wpc-script.js
 *        functions.php  ← this file
 *
 *   2. Add this line to your child theme's functions.php:
 *        require_once get_stylesheet_directory() . '/wpc/functions.php';
 *
 * SHORTCODES:
 *   [woo_product_card id="123"]
 *   [woo_product_card category="bath-tubs" limit="4" columns="4"]
 *   [woo_product_card ids="12,45,78" columns="3"]
 *
 * COMPARE TABLE:
 *   Place [wpc_compare_table] on your compare page.
 *   It also auto-overrides [yith_woocompare_table] so no page edit needed.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ─────────────────────────────────────────────────────────────
// CONFIG — adjust these to match your setup
// ─────────────────────────────────────────────────────────────
define( 'WPC_DIR_URL', get_stylesheet_directory_uri() . '/wpc/' );
define( 'WPC_COMPARE_PAGE_SLUG', 'yith-compare' );  // your compare page slug

// ─────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────
function wpc_currency() {
    return html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

function wpc_format_price( $amount ) {
    return wpc_currency() . number_format( floatval( $amount ), 2 );
}

function wpc_is_archive() {
    return function_exists( 'is_woocommerce' ) && is_woocommerce() && ! is_product();
}

function wpc_get_compare_url() {
    $page = get_page_by_path( WPC_COMPARE_PAGE_SLUG );
    return $page ? get_permalink( $page->ID ) : home_url( '/' . WPC_COMPARE_PAGE_SLUG . '/' );
}

// ─────────────────────────────────────────────────────────────
// ENQUEUE — CSS + JS (shortcode pages AND archive pages)
// ─────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'wpc_enqueue_assets', 15 );
function wpc_enqueue_assets() {
    global $post;

    $on_shortcode = is_a( $post, 'WP_Post' ) && (
        has_shortcode( $post->post_content, 'woo_product_card' ) ||
        has_shortcode( $post->post_content, 'wpc_compare_table' ) ||
        has_shortcode( $post->post_content, 'yith_woocompare_table' )
    );
    $on_archive = wpc_is_archive();

    if ( ! $on_shortcode && ! $on_archive ) return;

    // CSS
    wp_enqueue_style(
        'wpc-style',
        WPC_DIR_URL . 'wpc-style.css',
        array( 'woocommerce-general' ),
        '1.0'
    );

    // JS
    wp_enqueue_script( 'wc-add-to-cart' );
    wp_enqueue_script(
        'wpc-script',
        WPC_DIR_URL . 'wpc-script.js',
        array( 'jquery', 'wc-add-to-cart' ),
        '1.0',
        true
    );

    // Pass data to JS
    wp_localize_script( 'wpc-script', 'wpcData', array(
        'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
        'nonce'          => wp_create_nonce( 'wpc_nonce' ),
        'comparePageUrl' => wpc_get_compare_url(),
    ) );
}

// ─────────────────────────────────────────────────────────────
// SHARED: render one product card (used by shortcode + archive)
// ─────────────────────────────────────────────────────────────
function wpc_render_card( $product ) {
    if ( ! $product ) return;

    $id          = $product->get_id();
    $title       = $product->get_name();
    $desc        = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
    $permalink   = get_permalink( $id );
    $on_sale     = $product->is_on_sale();
    $in_stock    = $product->is_in_stock();
    $rating      = $product->get_average_rating();
    $rating_cnt  = $product->get_rating_count();
    $is_new      = ( strtotime( get_post_field( 'post_date', $id ) ) > strtotime( '-30 days' ) );
    $is_variable = $product->is_type( 'variable' );
    $currency    = wpc_currency();

    // Image + price — use first variation if variable
    $image_url          = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' );
    $current            = $product->get_price();
    $regular            = $product->get_regular_price();
    $sale               = $product->get_sale_price();
    $first_variation_id = $id;
    $variation_html     = '';

    if ( $is_variable ) {
        $var_obj    = new WC_Product_Variable( $id );
        $variations = $var_obj->get_available_variations();
        $attributes = $var_obj->get_variation_attributes();

        if ( ! empty( $variations ) ) {
            $fvid  = $variations[0]['variation_id'];
            $fvobj = wc_get_product( $fvid );
            $first_variation_id = $fvid;
            if ( $fvobj ) {
                $fv_img_id  = $fvobj->get_image_id();
                $fv_img_url = $fv_img_id ? wp_get_attachment_image_url( $fv_img_id, 'woocommerce_thumbnail' ) : '';
                if ( $fv_img_url ) $image_url = $fv_img_url;
                $current = $fvobj->get_price();
                $regular = $fvobj->get_regular_price();
                $sale    = $fvobj->get_sale_price();
            }
        }

        foreach ( $attributes as $attr_name => $options ) {
            $label      = wc_attribute_label( $attr_name );
            $option_map = array();
            foreach ( $variations as $v ) {
                foreach ( $v['attributes'] as $key => $val ) {
                    $clean = str_replace( 'attribute_', '', $key );
                    if ( $clean === $attr_name && $val !== '' && ! isset( $option_map[ $val ] ) ) {
                        $option_map[ $val ] = $v['variation_id'];
                    }
                }
            }
            $variation_html .= '<div class="wpc-variant-wrap"><select class="wpc-variant" aria-label="' . esc_attr( $label ) . '">';
            $first_opt = true;
            foreach ( $options as $opt ) {
                $vid             = isset( $option_map[ $opt ] ) ? $option_map[ $opt ] : '';
                $variation_html .= '<option value="' . esc_attr( $opt ) . '" data-variation-id="' . esc_attr( $vid ) . '"' . ( $first_opt ? ' selected' : '' ) . '>' . esc_html( $opt ) . '</option>';
                $first_opt = false;
            }
            $variation_html .= '</select></div>';
        }
    }

    // Discount badge
    $discount_pct = '';
    if ( $sale && $regular && floatval( $regular ) > 0 ) {
        $discount_pct = '-' . round( ( ( $regular - $sale ) / $regular ) * 100 ) . '%';
    }

    // Stars HTML
    $stars = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $c     = $rating >= $i ? 'full' : ( $rating >= $i - 0.5 ? 'half' : '' );
        $stars .= '<span class="wpc-star ' . $c . '">&#9733;</span>';
    }

    $price_display   = $currency . number_format( floatval( $current ), 2 );
    $regular_display = ( $sale && $regular && $regular != $current )
        ? $currency . number_format( floatval( $regular ), 2 ) : '';

    ?>
    <div class="wpc-card">

      <div class="wpc-img-wrap">

        <?php if ( $discount_pct ) : ?>
          <span class="wpc-discount"><?php echo esc_html( $discount_pct ); ?></span>
        <?php endif; ?>

        <?php if ( $is_new && ! $on_sale ) : ?>
          <span class="wpc-badge-new">New</span>
        <?php elseif ( $on_sale && ! $discount_pct ) : ?>
          <span class="wpc-badge-sale">Sale</span>
        <?php endif; ?>

        <div class="wpc-actions">
          <button class="wpc-action-btn wpc-wishlist-btn" data-id="<?php echo $id; ?>" data-tooltip="Wishlist" title="Wishlist">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.501 5.501 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
          </button>
          <button class="wpc-action-btn wpc-compare-btn" data-id="<?php echo $id; ?>" data-name="<?php echo esc_attr( $title ); ?>" data-tooltip="Compare" title="Compare">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
          </button>
          <button class="wpc-action-btn wpc-quickview-btn" data-id="<?php echo $id; ?>" data-tooltip="Quick View" title="Quick View">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <?php if ( $image_url ) : ?>
          <a href="<?php echo esc_url( $permalink ); ?>">
            <img class="wpc-product-img"
                 src="<?php echo esc_url( $image_url ); ?>"
                 alt="<?php echo esc_attr( $title ); ?>"
                 loading="lazy"/>
          </a>
        <?php else : ?>
          <div class="wpc-img-placeholder">No image</div>
        <?php endif; ?>

      </div><!-- .wpc-img-wrap -->

      <div class="wpc-body">

        <h3 class="wpc-title">
          <a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
        </h3>

        <?php if ( $desc ) : ?>
          <p class="wpc-desc"><?php echo esc_html( $desc ); ?></p>
        <?php endif; ?>

        <div class="wpc-stars">
          <?php echo $stars; ?>
          <?php if ( $rating_cnt > 0 ) : ?>
            <span class="wpc-star-count">(<?php echo $rating_cnt; ?>)</span>
          <?php endif; ?>
        </div>

        <?php echo $variation_html; ?>

        <div class="wpc-price-row">
          <span class="wpc-price"><?php echo esc_html( $price_display ); ?></span>
          <?php if ( $regular_display ) : ?>
            <span class="wpc-price-original"><?php echo esc_html( $regular_display ); ?></span>
          <?php endif; ?>
        </div>

        <?php if ( $in_stock ) : ?>
          <button
            class="wpc-cart-btn add_to_cart_button ajax_add_to_cart"
            data-product_id="<?php echo esc_attr( $first_variation_id ); ?>"
            data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
            data-quantity="1"
            aria-label="Add <?php echo esc_attr( $title ); ?> to cart"
            rel="nofollow">
            <span class="wpc-btn-text">Add to Cart</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                <path d="M14.3085 9.49984H3.69223C3.55873 9.49984 3.45452 9.61533 3.46802 9.74808L3.92999 14.2248C4.04999 15.3723 4.49249 16.2498 6.17249 16.2498H11.8425C13.4925 16.2498 13.9725 15.3723 14.0775 14.2248L14.532 9.74734C14.5455 9.61534 14.442 9.49984 14.3085 9.49984ZM10.5 13.0623H9.5625V13.9998C9.5625 14.3103 9.3105 14.5623 9 14.5623C8.6895 14.5623 8.4375 14.3103 8.4375 13.9998V13.0623H7.5C7.1895 13.0623 6.9375 12.8103 6.9375 12.4998C6.9375 12.1893 7.1895 11.9373 7.5 11.9373H8.4375V10.9998C8.4375 10.6893 8.6895 10.4373 9 10.4373C9.3105 10.4373 9.5625 10.6893 9.5625 10.9998V11.9373H10.5C10.8105 11.9373 11.0625 12.1893 11.0625 12.4998C11.0625 12.8103 10.8105 13.0623 10.5 13.0623ZM15.75 7.24984C15.75 8.07784 15.078 8.74984 14.25 8.74984H3.75C2.922 8.74984 2.25 8.07784 2.25 7.24984C2.25 6.42184 2.922 5.74984 3.75 5.74984H5.04602L7.03271 2.45884C7.19321 2.19334 7.539 2.10709 7.8045 2.26834C8.07 2.42959 8.15628 2.77459 7.99503 3.04084L6.35925 5.74984H11.6475L10.0305 3.03784C9.87152 2.77159 9.95851 2.42584 10.2263 2.26684C10.4925 2.10859 10.8382 2.19484 10.9972 2.46259L12.9577 5.75059H14.25C15.078 5.74984 15.75 6.42184 15.75 7.24984Z" fill="white"></path>
            </svg>
          </button>
        <?php else : ?>
          <span class="wpc-out-of-stock">Out of Stock</span>
        <?php endif; ?>

      </div><!-- .wpc-body -->

    </div><!-- .wpc-card -->
    <?php
}

// ─────────────────────────────────────────────────────────────
// SHARED: render modal + compare bar + toast HTML (once per page)
// ─────────────────────────────────────────────────────────────
function wpc_render_ui_elements() {
    if ( defined( 'WPC_UI_RENDERED' ) ) return;
    define( 'WPC_UI_RENDERED', true );
    ?>
    <!-- Quick View Modal -->
    <div class="wpc-modal-overlay" id="wpc-modal-overlay">
      <div class="wpc-modal" role="dialog" aria-modal="true">
        <button class="wpc-modal-close" id="wpc-modal-close" aria-label="Close">&times;</button>
        <div id="wpc-modal-body"><div class="wpc-modal-loading">Loading...</div></div>
      </div>
    </div>

    <!-- Compare bar -->
    <div class="wpc-compare-bar">
      <span class="wpc-compare-bar-title">Compare:</span>
      <div class="wpc-compare-items"></div>
      <div class="wpc-compare-actions">
        <button class="wpc-compare-go">Compare Now</button>
        <button class="wpc-compare-clear">Clear All</button>
      </div>
    </div>

    <!-- Toast notification -->
    <div class="wpc-toast" id="wpc-toast">
      <img class="wpc-toast-img" id="wpc-toast-img" src="" alt=""/>
      <div class="wpc-toast-body">
        <span class="wpc-toast-label">Added to Cart</span>
        <span class="wpc-toast-name" id="wpc-toast-name"></span>
        <span class="wpc-toast-sub"  id="wpc-toast-sub"></span>
      </div>
      <div class="wpc-toast-tick">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <button class="wpc-toast-close" id="wpc-toast-close">&times;</button>
      <div class="wpc-toast-progress" id="wpc-toast-progress"></div>
    </div>
    <?php
}

// ─────────────────────────────────────────────────────────────
// AJAX: Quick View modal content
// ─────────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpc_quick_view',        'wpc_quick_view_callback' );
add_action( 'wp_ajax_nopriv_wpc_quick_view', 'wpc_quick_view_callback' );

function wpc_quick_view_callback() {
    check_ajax_referer( 'wpc_nonce', 'nonce' );
    $product_id = intval( $_POST['product_id'] );
    $product    = wc_get_product( $product_id );
    if ( ! $product ) { wp_send_json_error( 'Not found' ); }

    $currency    = wpc_currency();
    $title       = $product->get_name();
    $description = wp_kses_post( $product->get_short_description() ?: $product->get_description() );
    $permalink   = get_permalink( $product_id );
    $in_stock    = $product->is_in_stock();
    $is_variable = $product->is_type( 'variable' );
    $rating      = $product->get_average_rating();
    $rc          = $product->get_rating_count();

    // Gallery images
    $image_ids = array_filter( array_merge(
        array( $product->get_image_id() ),
        $product->get_gallery_image_ids()
    ) );
    $images = array();
    foreach ( $image_ids as $img_id ) {
        $url = wp_get_attachment_image_url( $img_id, 'woocommerce_single' );
        if ( $url ) $images[] = $url;
    }
    if ( empty( $images ) ) $images[] = wc_placeholder_img_src( 'woocommerce_single' );

    // Stars
    $stars = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $c     = $rating >= $i ? 'full' : ( $rating >= $i - 0.5 ? 'half' : '' );
        $stars .= '<span class="wpc-star ' . $c . '">&#9733;</span>';
    }

    // Variations
    $qv_var_html     = '';
    $qv_first_var_id = $product_id;
    $qv_price        = $product->get_price();
    $qv_regular      = $product->get_regular_price();
    $qv_sale         = $product->get_sale_price();

    if ( $is_variable ) {
        $var_obj    = new WC_Product_Variable( $product_id );
        $variations = $var_obj->get_available_variations();
        $attributes = $var_obj->get_variation_attributes();

        if ( ! empty( $variations ) ) {
            $first_v         = wc_get_product( $variations[0]['variation_id'] );
            $qv_first_var_id = $variations[0]['variation_id'];
            if ( $first_v ) {
                $qv_price   = $first_v->get_price();
                $qv_regular = $first_v->get_regular_price();
                $qv_sale    = $first_v->get_sale_price();
                $fv_img_id  = $first_v->get_image_id();
                if ( $fv_img_id ) {
                    $fv_img_url = wp_get_attachment_image_url( $fv_img_id, 'woocommerce_single' );
                    if ( $fv_img_url && $fv_img_url !== $images[0] ) {
                        array_unshift( $images, $fv_img_url );
                    }
                }
            }
        }

        foreach ( $attributes as $attr_name => $options ) {
            $label      = wc_attribute_label( $attr_name );
            $option_map = array();
            foreach ( $variations as $v ) {
                foreach ( $v['attributes'] as $key => $val ) {
                    $clean = str_replace( 'attribute_', '', $key );
                    if ( $clean === $attr_name && $val !== '' && ! isset( $option_map[ $val ] ) ) {
                        $option_map[ $val ] = $v['variation_id'];
                    }
                }
            }
            $qv_var_html .= '<div class="wpc-variant-wrap">';
            $qv_var_html .= '<select class="wpc-variant wpc-qv-variant" aria-label="' . esc_attr( $label ) . '">';
            $first = true;
            foreach ( $options as $opt ) {
                $vid          = isset( $option_map[ $opt ] ) ? $option_map[ $opt ] : '';
                $sel          = $first ? ' selected' : '';
                $qv_var_html .= '<option value="' . esc_attr( $opt ) . '" data-variation-id="' . esc_attr( $vid ) . '"' . $sel . '>' . esc_html( $opt ) . '</option>';
                $first = false;
            }
            $qv_var_html .= '</select></div>';
        }
    }

    $price_display   = $currency . number_format( floatval( $qv_price ), 2 );
    $regular_display = ( $qv_sale && $qv_regular )
        ? $currency . number_format( floatval( $qv_regular ), 2 ) : '';

    ob_start(); ?>
    <div class="wpc-qv-inner">

      <div class="wpc-qv-left">
        <div class="wpc-slider" id="wpc-qv-slider">
          <div class="wpc-slider-track">
            <?php foreach ( $images as $i => $img_url ) : ?>
              <div class="wpc-slide <?php echo $i === 0 ? 'active' : ''; ?>">
                <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>"/>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ( count( $images ) > 1 ) : ?>
            <button class="wpc-slide-prev">&#8592;</button>
            <button class="wpc-slide-next">&#8594;</button>
          <?php endif; ?>
        </div>
        <?php if ( count( $images ) > 1 ) : ?>
          <div class="wpc-slider-thumbs">
            <?php foreach ( $images as $i => $img_url ) : ?>
              <div class="wpc-thumb <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>">
                <img src="<?php echo esc_url( $img_url ); ?>" alt="thumb"/>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="wpc-qv-right">
        <h2 class="wpc-qv-title"><?php echo esc_html( $title ); ?></h2>
        <div class="wpc-stars" style="margin-bottom:10px">
          <?php echo $stars; ?>
          <?php if ( $rc > 0 ) echo '<span class="wpc-star-count">(' . $rc . ')</span>'; ?>
        </div>
        <div class="wpc-qv-price-wrap">
          <span class="wpc-qv-price-current"><?php echo esc_html( $price_display ); ?></span>
          <?php if ( $regular_display ) : ?>
            <span class="wpc-qv-price-original"><?php echo esc_html( $regular_display ); ?></span>
          <?php endif; ?>
        </div>
        <div class="wpc-qv-desc"><?php echo $description ?: '<p>No description available.</p>'; ?></div>
        <?php echo $qv_var_html; ?>
        <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap">
          <?php if ( $in_stock ) : ?>
            <button
              class="wpc-cart-btn add_to_cart_button ajax_add_to_cart"
              style="flex:1"
              data-product_id="<?php echo esc_attr( $qv_first_var_id ); ?>"
              data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
              data-quantity="1" rel="nofollow">
              <span class="wpc-btn-text">Add to Cart</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                <path d="M14.3085 9.49984H3.69223C3.55873 9.49984 3.45452 9.61533 3.46802 9.74808L3.92999 14.2248C4.04999 15.3723 4.49249 16.2498 6.17249 16.2498H11.8425C13.4925 16.2498 13.9725 15.3723 14.0775 14.2248L14.532 9.74734C14.5455 9.61534 14.442 9.49984 14.3085 9.49984ZM10.5 13.0623H9.5625V13.9998C9.5625 14.3103 9.3105 14.5623 9 14.5623C8.6895 14.5623 8.4375 14.3103 8.4375 13.9998V13.0623H7.5C7.1895 13.0623 6.9375 12.8103 6.9375 12.4998C6.9375 12.1893 7.1895 11.9373 7.5 11.9373H8.4375V10.9998C8.4375 10.6893 8.6895 10.4373 9 10.4373C9.3105 10.4373 9.5625 10.6893 9.5625 10.9998V11.9373H10.5C10.8105 11.9373 11.0625 12.1893 11.0625 12.4998C11.0625 12.8103 10.8105 13.0623 10.5 13.0623ZM15.75 7.24984C15.75 8.07784 15.078 8.74984 14.25 8.74984H3.75C2.922 8.74984 2.25 8.07784 2.25 7.24984C2.25 6.42184 2.922 5.74984 3.75 5.74984H5.04602L7.03271 2.45884C7.19321 2.19334 7.539 2.10709 7.8045 2.26834C8.07 2.42959 8.15628 2.77459 7.99503 3.04084L6.35925 5.74984H11.6475L10.0305 3.03784C9.87152 2.77159 9.95851 2.42584 10.2263 2.26684C10.4925 2.10859 10.8382 2.19484 10.9972 2.46259L12.9577 5.75059H14.25C15.078 5.74984 15.75 6.42184 15.75 7.24984Z" fill="white"></path>
            </svg>
            </button>
          <?php else : ?>
            <span class="wpc-out-of-stock" style="flex:1">Out of Stock</span>
          <?php endif; ?>
          <a href="<?php echo esc_url( $permalink ); ?>" class="wpc-cart-btn" style="flex:1;background:#444;text-decoration:none">View Product</a>
        </div>
      </div>

    </div>
    <?php
    wp_send_json_success( ob_get_clean() );
}

// ─────────────────────────────────────────────────────────────
// AJAX: Variation image + price swap
// ─────────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpc_variation_image',        'wpc_variation_image_callback' );
add_action( 'wp_ajax_nopriv_wpc_variation_image', 'wpc_variation_image_callback' );

function wpc_variation_image_callback() {
    check_ajax_referer( 'wpc_nonce', 'nonce' );
    $variation_id = intval( $_POST['variation_id'] );
    $variation    = wc_get_product( $variation_id );
    if ( ! $variation ) { wp_send_json_error( 'Not found' ); }

    $image_id  = $variation->get_image_id();
    $parent    = wc_get_product( $variation->get_parent_id() );
    $thumb_url = $image_id
        ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' )
        : ( $parent ? wp_get_attachment_image_url( $parent->get_image_id(), 'woocommerce_thumbnail' ) : '' );
    $large_url = $image_id
        ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' )
        : ( $parent ? wp_get_attachment_image_url( $parent->get_image_id(), 'woocommerce_single' ) : '' );

    $price   = $variation->get_price();
    $regular = $variation->get_regular_price();
    $sale    = $variation->get_sale_price();

    wp_send_json_success( array(
        'image_url'       => $thumb_url,
        'image_url_large' => $large_url,
        'price'           => wpc_format_price( $price ),
        'regular'         => ( $regular && $sale ) ? wpc_format_price( $regular ) : '',
        'sale'            => $sale ? wpc_format_price( $sale ) : '',
        'variation_id'    => $variation_id,
    ) );
}

// ─────────────────────────────────────────────────────────────
// SHORTCODE: [woo_product_card]
// ─────────────────────────────────────────────────────────────
add_shortcode( 'woo_product_card', 'woo_product_card_shortcode' );

function woo_product_card_shortcode( $atts ) {
    if ( ! class_exists( 'WooCommerce' ) ) return '<p style="color:red">WooCommerce not active.</p>';

    $atts = shortcode_atts( array(
        'id'      => '',
        'ids'     => '',
        'category'=> '',
        'limit'   => '4',
        'columns' => '3',
        'orderby' => 'date',
        'order'   => 'DESC',
    ), $atts, 'woo_product_card' );

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => intval( $atts['limit'] ),
        'orderby'        => sanitize_text_field( $atts['orderby'] ),
        'order'          => sanitize_text_field( $atts['order'] ),
    );
    if ( ! empty( $atts['id'] ) )   { $args['p'] = intval( $atts['id'] ); $args['posts_per_page'] = 1; }
    elseif ( ! empty( $atts['ids'] ) ) { $args['post__in'] = array_map( 'intval', explode( ',', $atts['ids'] ) ); $args['orderby'] = 'post__in'; }
    if ( ! empty( $atts['category'] ) ) {
        $args['tax_query'] = array( array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => array_map( 'trim', explode( ',', $atts['category'] ) ),
        ) );
    }

    $query = new WP_Query( $args );
    if ( ! $query->have_posts() ) return '<p>No products found.</p>';

    $cols = intval( $atts['columns'] );
    ob_start();
    echo '<div class="wpc-grid" style="--wpc-cols:' . $cols . '">';
    while ( $query->have_posts() ) {
        $query->the_post();
        wpc_render_card( wc_get_product( get_the_ID() ) );
    }
    wp_reset_postdata();
    echo '</div>';
    wpc_render_ui_elements();
    return ob_get_clean();
}

// ─────────────────────────────────────────────────────────────
// SHORTCODE: [wpc_compare_table] + override [yith_woocompare_table]
// ─────────────────────────────────────────────────────────────
add_shortcode( 'wpc_compare_table', 'wpc_compare_table_shortcode' );
add_action( 'init', function () {
    remove_shortcode( 'yith_woocompare_table' );
    add_shortcode( 'yith_woocompare_table', 'wpc_compare_table_shortcode' );
}, 99 );

function wpc_compare_table_shortcode() {
    if ( empty( $_GET['ids'] ) ) {
        return '<p style="text-align:center;padding:40px;color:#999">No products selected. <a href="' . esc_url( home_url() ) . '">Continue Shopping</a></p>';
    }
    $ids      = array_filter( array_map( 'intval', (array) $_GET['ids'] ) );
    $currency = wpc_currency();
    $products = array();
    foreach ( $ids as $pid ) {
        $p = wc_get_product( $pid );
        if ( $p ) $products[] = $p;
    }
    if ( empty( $products ) ) return '<p style="text-align:center;padding:40px;color:#999">Products not found.</p>';

    $rows = array(
        'price'       => 'Price',
        'rating'      => 'Rating',
        'sku'         => 'SKU',
        'stock'       => 'Availability',
        'description' => 'Description',
        'categories'  => 'Category',
        'weight'      => 'Weight',
        'dimensions'  => 'Dimensions',
    );

    ob_start(); ?>
    <div class="wpc-ct-wrap">
    <table class="wpc-ct">
      <thead>
        <tr>
          <th class="wpc-ct-label" style="background:#111;border:none"></th>
          <?php foreach ( $products as $product ) :
            $img = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src();
          ?>
          <th>
            <div class="wpc-ct-head">
              <button class="wpc-ct-remove" data-id="<?php echo $product->get_id(); ?>" title="Remove">&#x2715;</button>
              <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>"/>
              <div class="wpc-ct-name">
                <a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
              </div>
            </div>
          </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $rows as $row_key => $row_label ) : ?>
        <tr>
          <td class="wpc-ct-label"><?php echo esc_html( $row_label ); ?></td>
          <?php foreach ( $products as $product ) : ?>
          <td>
            <?php
            switch ( $row_key ) {
              case 'price':
                $p = $product->get_price(); $r = $product->get_regular_price(); $s = $product->get_sale_price();
                echo '<div class="wpc-ct-price">';
                if ( $s && $r ) { echo '<del>' . $currency . number_format( floatval($r), 2 ) . '</del>' . $currency . number_format( floatval($s), 2 ); }
                else            { echo $currency . number_format( floatval($p), 2 ); }
                echo '</div>';
                if ( $product->is_in_stock() ) {
                    echo '<button class="wpc-ct-cart add_to_cart_button ajax_add_to_cart" data-product_id="' . $product->get_id() . '" data-quantity="1" rel="nofollow">';
                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19" fill="none">
                <path d="M14.3085 9.49984H3.69223C3.55873 9.49984 3.45452 9.61533 3.46802 9.74808L3.92999 14.2248C4.04999 15.3723 4.49249 16.2498 6.17249 16.2498H11.8425C13.4925 16.2498 13.9725 15.3723 14.0775 14.2248L14.532 9.74734C14.5455 9.61534 14.442 9.49984 14.3085 9.49984ZM10.5 13.0623H9.5625V13.9998C9.5625 14.3103 9.3105 14.5623 9 14.5623C8.6895 14.5623 8.4375 14.3103 8.4375 13.9998V13.0623H7.5C7.1895 13.0623 6.9375 12.8103 6.9375 12.4998C6.9375 12.1893 7.1895 11.9373 7.5 11.9373H8.4375V10.9998C8.4375 10.6893 8.6895 10.4373 9 10.4373C9.3105 10.4373 9.5625 10.6893 9.5625 10.9998V11.9373H10.5C10.8105 11.9373 11.0625 12.1893 11.0625 12.4998C11.0625 12.8103 10.8105 13.0623 10.5 13.0623ZM15.75 7.24984C15.75 8.07784 15.078 8.74984 14.25 8.74984H3.75C2.922 8.74984 2.25 8.07784 2.25 7.24984C2.25 6.42184 2.922 5.74984 3.75 5.74984H5.04602L7.03271 2.45884C7.19321 2.19334 7.539 2.10709 7.8045 2.26834C8.07 2.42959 8.15628 2.77459 7.99503 3.04084L6.35925 5.74984H11.6475L10.0305 3.03784C9.87152 2.77159 9.95851 2.42584 10.2263 2.26684C10.4925 2.10859 10.8382 2.19484 10.9972 2.46259L12.9577 5.75059H14.25C15.078 5.74984 15.75 6.42184 15.75 7.24984Z" fill="white"></path>
            </svg>';
                    echo '<span class="wpc-btn-text">Add to Cart</span></button>';
                }
                break;
              case 'rating':
                $r = $product->get_average_rating(); $c = $product->get_rating_count();
                $s = '';
                for ( $i = 1; $i <= 5; $i++ ) { $s .= $r >= $i ? '★' : ( $r >= $i - 0.5 ? '★' : '☆' ); }
                echo '<div class="wpc-ct-stars">' . $s . ' <span>(' . $c . ')</span></div>';
                break;
              case 'sku':
                $sku = $product->get_sku(); echo $sku ? esc_html($sku) : '—'; break;
              case 'stock':
                echo $product->is_in_stock() ? '<span class="in-stock">✓ In Stock</span>' : '<span class="out-of-stock">✗ Out of Stock</span>'; break;
              case 'description':
                $d = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
                echo $d ? esc_html( wp_trim_words( $d, 20 ) ) : '—'; break;
              case 'categories':
                $cats = wp_get_post_terms( $product->get_id(), 'product_cat', array('fields'=>'names') );
                echo !is_wp_error($cats) && !empty($cats) ? esc_html(implode(', ', $cats)) : '—'; break;
              case 'weight':
                $w = $product->get_weight(); echo $w ? esc_html($w) . ' ' . esc_html(get_option('woocommerce_weight_unit')) : '—'; break;
              case 'dimensions':
                $dim = wc_format_dimensions($product->get_dimensions(false)); echo $dim ? esc_html($dim) : '—'; break;
            }
            ?>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <script>
    jQuery(function($){
      // Remove product from compare
      $(document).on('click','.wpc-ct-remove',function(){
        var id=String($(this).data('id'));
        var url=new URL(window.location.href);
        var ids=url.searchParams.getAll('ids[]').filter(function(i){return i!==id;});
        url.searchParams.delete('ids[]');
        if(!ids.length){window.location.href=url.origin+url.pathname;return;}
        ids.forEach(function(i){url.searchParams.append('ids[]',i);});
        window.location.href=url.toString();
      });
      // Cart button states
      $(document).on('added_to_cart',function(e,f,h,$btn){
        if($btn&&$btn.hasClass('wpc-ct-cart')){
          $btn.addClass('added').find('.wpc-btn-text').text('✓ Added!');
          setTimeout(function(){$btn.removeClass('added').find('.wpc-btn-text').text('Add to Cart');},2500);
        }
        $(document.body).trigger('wc_fragment_refresh');
      });
    });
    </script>
    <?php
    return ob_get_clean();
}

// ─────────────────────────────────────────────────────────────
// SHOP / ARCHIVE PAGE OVERRIDE
// Uses ob_start/ob_end_clean to replace default WC card with ours
// ─────────────────────────────────────────────────────────────

// Replace grid wrapper
add_filter( 'woocommerce_product_loop_start', 'wpc_archive_loop_start', 99 );
function wpc_archive_loop_start( $html ) {
    if ( ! wpc_is_archive() ) return $html;
    return '<div class="wpc-grid" style="--wpc-cols:4">';
}

add_filter( 'woocommerce_product_loop_end', 'wpc_archive_loop_end', 99 );
function wpc_archive_loop_end( $html ) {
    if ( ! wpc_is_archive() ) return $html;
    return '</div>';
}

// Capture default WC output — discard it — render our card
add_action( 'woocommerce_before_shop_loop_item', 'wpc_archive_card_open', 1 );
function wpc_archive_card_open() {
    if ( ! wpc_is_archive() ) return;
    ob_start();
}

add_action( 'woocommerce_after_shop_loop_item', 'wpc_archive_card_close', 9999 );
function wpc_archive_card_close() {
    if ( ! wpc_is_archive() ) return;
    ob_end_clean(); // discard all default WC output
    global $product;
    wpc_render_card( $product );
}

// Append modal + compare bar + toast after loop
add_action( 'woocommerce_after_shop_loop', 'wpc_archive_append_ui', 20 );
function wpc_archive_append_ui() {
    if ( ! wpc_is_archive() ) return;
    wpc_render_ui_elements();
}












// Exit if accessed directly
if (!defined('ABSPATH')) exit;

