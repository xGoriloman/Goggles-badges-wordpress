<?php
/**
 * The template for displaying product content in favorites
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$product_id = $product->get_id();
$is_on_sale = $product->is_on_sale();
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
?>

<div class="card card--favorite" data-product-id="<?php echo $product_id; ?>">
    <?php if ($is_on_sale && $regular_price && $sale_price) : 
        $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
    ?>
        <span class="card__discount">-<?php echo $discount; ?>%</span>
    <?php endif; ?>
    
    <span class="card__favourite active" data-product-id="<?php echo $product_id; ?>">
        <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.87 0C11.34 0 9.99 0.606935 9 1.73411C8.01 0.606935 6.66 0 5.13 0C2.34 0 0 2.25434 0 4.94218C0 5.11559 0 5.289 0 5.46241C0.36 9.53759 4.86 12.9191 7.47 14.5665C7.92 14.8266 8.46 15 9 15C9.54 15 10.08 14.8266 10.53 14.5665C13.14 12.9191 17.64 9.53759 18 5.54912C18 5.37571 18 5.20229 18 5.02888C18 2.25434 15.66 0 12.87 0ZM16.2 5.289C15.93 8.75724 11.43 11.8786 9.54 13.0058C9.18 13.1792 8.82 13.1792 8.46 13.0058C6.57 11.7919 2.16 8.67053 1.8 5.20229C1.8 5.20229 1.8 5.02888 1.8 4.94218C1.8 3.20812 3.33 1.73411 5.13 1.73411C6.48 1.73411 7.65 2.51445 8.19 3.64165C8.28 3.98841 8.64 4.16182 9 4.16182C9.36 4.16182 9.72 3.98841 9.81 3.64165C10.35 2.51445 11.52 1.73411 12.87 1.73411C14.67 1.73411 16.2 3.20812 16.2 4.94218C16.2 5.02888 16.2 5.20229 16.2 5.289Z" fill="#191919" />
            <path d="M12.87 0C11.34 0 9.99 0.606935 9 1.73411C8.01 0.606935 6.66 0 5.13 0C2.34 0 0 2.25434 0 4.94218C0 5.11559 0 5.289 0 5.46241C0.36 9.53759 4.86 12.9191 7.47 14.5665C7.92 14.8266 8.46 15 9 15C9.54 15 10.08 14.8266 10.53 14.5665C13.14 12.9191 17.64 9.53759 18 5.54912C18 5.37571 18 5.20229 18 5.02888C18 2.25433 15.66 0 12.87 0Z" fill="#F80F4E" />
        </svg>
    </span>
    
    <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="card__image-ibg">
        <?php 
        echo $product->get_image('woocommerce_thumbnail', array(
            'class' => 'card__image',
            'loading' => 'lazy',
            'alt' => $product->get_name()
        )); 
        ?>
    </a>
    
    <div class="card__conten">
        <p class="card__article">
            Арт <?php echo $product->get_sku() ? esc_html($product->get_sku()) : $product_id; ?>
        </p>
        
        <?php
        $brands = wp_get_post_terms($product_id, 'product_brand');
        if ($brands && !is_wp_error($brands)) : ?>
            <a href="<?php echo esc_url(get_term_link($brands[0])); ?>" class="card__attribute">
                <?php echo esc_html($brands[0]->name); ?>
            </a>
        <?php endif; ?>
        
        <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="card__title">
            <?php echo esc_html($product->get_name()); ?>
        </a>
        
        <div class="card__prices">
            <?php if ($is_on_sale && $sale_price) : ?>
                <div class="card__price card__new-price"><?php echo wc_price($sale_price); ?></div>
                <?php if ($regular_price) : ?>
                    <div class="card__price card__old-price"><?php echo wc_price($regular_price); ?></div>
                <?php endif; ?>
            <?php else : ?>
                <div class="card__price card__new-price"><?php echo $product->get_price_html(); ?></div>
            <?php endif; ?>
        </div>
        
        <?php if ($product->is_in_stock()) : ?>
            <?php if ($product->is_type('simple')) : ?>
                <button class="card__button button-black add_to_cart_button ajax_add_to_cart" 
                        data-product_id="<?php echo $product_id; ?>" 
                        data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                        aria-label="Добавить <?php echo esc_attr($product->get_name()); ?> в корзину">
                    В корзину
                </button>
            <?php else : ?>
                <a href="<?php echo esc_url(get_permalink($product_id)); ?>" 
                   class="card__button button-black"
                   aria-label="Подробнее о <?php echo esc_attr($product->get_name()); ?>">
                    Подробнее
                </a>
            <?php endif; ?>
        <?php else : ?>
            <div class="card__button button-black disabled" aria-label="Товар недоступен">
                Нет в наличии
            </div>
        <?php endif; ?>
        
        <!-- Кнопка удаления из избранного -->
        <button class="card__remove-favorite button button--transparent" 
                data-product-id="<?php echo $product_id; ?>"
                aria-label="Удалить из избранного">
            Удалить
        </button>
    </div>
</div>