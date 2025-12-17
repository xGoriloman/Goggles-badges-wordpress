<?php
/**
 * External product add to cart
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

if ($product->is_type('external')) : ?>
    <a href="<?php echo esc_url($product->get_product_url()); ?>" 
        class="product__button button-black" 
        target="_blank" 
        rel="noopener">
        <?php echo esc_html($product->get_button_text()); ?>
    </a>
<?php endif; ?>