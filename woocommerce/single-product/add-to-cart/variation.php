<?php
/**
 * Single variation display
 * This template is used by JavaScript to display variation data
 */

defined('ABSPATH') || exit;

?>
<script type="text/template" id="tmpl-variation-template">
    <div class="woocommerce-variation single_variation">
        <div class="woocommerce-variation-description">{{{ data.variation.variation_description }}}</div>
        
        <div class="product__prices variation-price-container">
            <div class="product__price product__new-price">
                {{{ data.variation.price_html }}}
            </div>
        </div>
        
        <div class="woocommerce-variation-availability">{{{ data.variation.availability_html }}}</div>
    </div>

    <div class="woocommerce-variation-add-to-cart variations_button">
        <div class="quantity-wrapper">
            <div class="cart__quantity quantity">
                <button type="button" class="quantity__button quantity__button-minus">-</button>
                <div class="quantity__input">
                    <input 
                        type="number" 
                        class="quantity__field qty" 
                        value="{{ data.variation.min_qty }}"
                        min="{{ data.variation.min_qty }}"
                        max="{{ data.variation.max_qty }}"
                        step="1"
                    >
                </div>
                <button type="button" class="quantity__button quantity__button-plus">+</button>
            </div>
        </div>
        
        <div class="product__line"></div>
        
        <button type="submit" class="product__button button-black single_add_to_cart_button">
            <?php echo esc_html__('Add to cart', 'woocommerce'); ?>
        </button>
        
        <input type="hidden" name="variation_id" class="variation_id" value="{{ data.variation.variation_id }}" />
    </div>
</script>

<script type="text/template" id="tmpl-unavailable-variation-template">
    <div class="woocommerce-variation single_variation">
        <p class="stock out-of-stock"><?php esc_html_e('Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce'); ?></p>
    </div>
    
    <div class="woocommerce-variation-add-to-cart variations_button">
        <button type="submit" class="product__button button-black single_add_to_cart_button disabled" disabled>
            <?php echo esc_html__('Add to cart', 'woocommerce'); ?>
        </button>
    </div>
</script>