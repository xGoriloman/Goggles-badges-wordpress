<?php
/**
 * Simple product add to cart
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

if (!$product->is_purchasable()) {
    return;
}

if ($product->is_type('simple')) : ?>
    <form class="cart product__atributes" method="post" enctype='multipart/form-data'>
        <!-- Кастомный input количества -->
        <div class="quantity-wrapper">
            <div class="cart__quantity quantity">
                <?php
                if ($product->is_sold_individually()) {
                    $min_quantity = 1;
                    $max_quantity = 1;
                } else {
                    $min_quantity = 1;
                    $max_quantity = $product->get_max_purchase_quantity();
                }
                ?>
                
                <button type="button" class="quantity__button quantity__button-minus" 
                        aria-label="Уменьшить количество">
                    <svg width="12" height="2" viewBox="0 0 12 2" fill="none">
                        <path d="M1 1H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                
                <div class="quantity__input">
                    <input value="1" 
                        autocomplete="off" 
                        type="number" 
                        name="quantity" 
                        class="quantity__field product-quantity-input" 
                        data-min="<?php echo esc_attr($min_quantity); ?>"
                        data-max="<?php echo esc_attr($max_quantity); ?>"
                        step="1"
                        aria-label="Количество товара <?php echo esc_attr($product->get_name()); ?>">
                </div>
                
                <button type="button" class="quantity__button quantity__button-plus"
                        aria-label="Увеличить количество">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M1 6H11M6 1V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="product__line"></div>
        
        <!-- Цена -->
        <div class="product__prices simple-price-container">
            <?php 
            $single_price = $product->get_price();
            if ($product->is_on_sale()) : 
                $single_sale_price = $product->get_sale_price();
                $single_regular_price = $product->get_regular_price();
                ?>
                <div class="product__price product__new-price" data-single-price="<?php echo $single_sale_price; ?>">
                    <?php echo wc_price($single_sale_price); ?>
                </div>
                <div class="product__price product__old-price" data-single-price="<?php echo $single_regular_price; ?>">
                    <?php echo wc_price($single_regular_price); ?>
                </div>
            <?php else : ?>
                <div class="product__price product__new-price" data-single-price="<?php echo $single_price; ?>">
                    <?php echo wc_price($single_price); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="product__button button-black single_add_to_cart_button">
            <?php echo esc_html($product->single_add_to_cart_text()); ?>
        </button>
        
        <?php wp_nonce_field('woocommerce-add-to-cart', 'woocommerce-add-to-cart-nonce'); ?>
    </form>
<?php endif; ?>