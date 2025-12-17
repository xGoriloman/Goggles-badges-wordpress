<?php
/**
 * Product quantity inputs
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

if ($product->is_sold_individually()) {
    $min_quantity = 1;
    $max_quantity = 1;
} else {
    $min_quantity = 1;
    $max_quantity = $product->get_max_purchase_quantity();
}
?>

<div class="quantity-wrapper">
    <div class="cart__quantity quantity">
        <?php
        if ($product->is_sold_individually()) {
            $min_quantity = 1;
            $max_quantity = 1;
        } else {
            $min_quantity = 0;
            $max_quantity = $product->get_max_purchase_quantity();
        }
        ?>
        
        <button type="button" class="quantity__button quantity__button-minus" 
                data-min="<?php echo esc_attr($min_quantity); ?>"
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
                min="<?php echo esc_attr($min_quantity); ?>"
                max="<?php echo esc_attr($max_quantity); ?>"
                step="1"
                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                aria-label="Количество товара <?php echo esc_attr($product->get_name()); ?>">
        </div>
        
        <button type="button" class="quantity__button quantity__button-plus"
                data-max="<?php echo esc_attr($max_quantity); ?>"
                aria-label="Увеличить количество">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path d="M1 6H11M6 1V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    </div>