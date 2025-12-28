<?php
/**
 * Single Product Image
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
?>

<div class="product__images images-product">
    <?php if ($main_image_id || $attachment_ids) : ?>
        <div class="images-product__slider swiper-product">
            <div class="images-product__wrapper swiper-wrapper">
                <?php if ($main_image_id) : ?>
                    <div class="images-product__slide swiper-slide">
                        <?php echo wp_get_attachment_image($main_image_id, 'full'); ?>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($attachment_ids as $attachment_id) : ?>
                    <div class="images-product__slide swiper-slide">
                        <?php echo wp_get_attachment_image($attachment_id, 'full'); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <span class="product__favourite card__favourite <?php echo is_user_favorite(get_the_ID()) ? 'active' : ''; ?>" 
                data-product-id="<?php echo get_the_ID(); ?>">
            <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.87 0C11.34 0 9.99 0.606935 9 1.73411C8.01 0.606935 6.66 0 5.13 0C2.34 0 0 2.25434 0 4.94218C0 5.11559 0 5.289 0 5.46241C0.36 9.53759 4.86 12.9191 7.47 14.5665C7.92 14.8266 8.46 15 9 15C9.54 15 10.08 14.8266 10.53 14.5665C13.14 12.9191 17.64 9.53759 18 5.54912C18 5.37571 18 5.20229 18 5.02888C18 2.25434 15.66 0 12.87 0ZM16.2 5.289C15.93 8.75724 11.43 11.8786 9.54 13.0058C9.18 13.1792 8.82 13.1792 8.46 13.0058C6.57 11.7919 2.16 8.67053 1.8 5.20229C1.8 5.20229 1.8 5.02888 1.8 4.94218C1.8 3.20812 3.33 1.73411 5.13 1.73411C6.48 1.73411 7.65 2.51445 8.19 3.64165C8.28 3.98841 8.64 4.16182 9 4.16182C9.36 4.16182 9.72 3.98841 9.81 3.64165C10.35 2.51445 11.52 1.73411 12.87 1.73411C14.67 1.73411 16.2 3.20812 16.2 4.94218C16.2 5.02888 16.2 5.20229 16.2 5.289Z" fill="#191919"></path>
                <path d="M12.87 0C11.34 0 9.99 0.606935 9 1.73411C8.01 0.606935 6.66 0 5.13 0C2.34 0 0 2.25434 0 4.94218C0 5.11559 0 5.289 0 5.46241C0.36 9.53759 4.86 12.9191 7.47 14.5665C7.92 14.8266 8.46 15 9 15C9.54 15 10.08 14.8266 10.53 14.5665C13.14 12.9191 17.64 9.53759 18 5.54912C18 5.37571 18 5.20229 18 5.02888C18 2.25433 15.66 0 12.87 0Z" fill="#F80F4E"></path>
            </svg>
        </span>
        
        <div class="product__pagination swiper-pagination"></div>
    <?php else : ?>
        <div class="images-product__slide">
            <img src="<?php echo wc_placeholder_img_src(); ?>" alt="<?php the_title(); ?>" />
        </div>
    <?php endif; ?>
</div>