<?php
/**
 * The template for displaying product content in the single-product.php template
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;
?>

<section class="section__product product">
    <div class="product__container">
        <div class="product__body">
            <!-- Галерея изображений -->
            <?php wc_get_template('single-product/product-image.php'); ?>
            
            <!-- Контент товара -->
            <div class="product__content">
                <h1 class="product__title"><?php the_title(); ?></h1>
                
                <p class="product__article">Арт <?php echo $product->get_sku() ?: get_the_ID(); ?></p>
                
                <div class="product__text">
                    <?php the_content(); ?>
                </div>
                
                <!-- Форма добавления в корзину -->
                <?php wc_get_template('single-product/add-to-cart/simple.php'); ?>
                <?php wc_get_template('single-product/add-to-cart/variable.php'); ?>
                <?php wc_get_template('single-product/add-to-cart/external.php'); ?>
            </div>
        </div>
    </div>
</section>

<!-- Детали товара -->
<?php wc_get_template('single-product/product-details.php'); ?>

<!-- Похожие товары -->
<?php wc_get_template('single-product/related.php'); ?>


<?php 

wp_enqueue_script('wc-single-product'); 
            
// Ваш базовый скрипт, от которого зависят остальные
wp_enqueue_script('product-scripts', get_template_directory_uri() . '/assets/js/product.js', array('jquery', 'wc-add-to-cart'), '1.0', true);

if($product->get_type() == 'variable'){
    // Скрипты только для вариативных товаров
    wp_enqueue_script('wc-add-to-cart-variation'); // Стандартный скрипт WC для вариаций
    wp_enqueue_script('product-variable', get_template_directory_uri() . '/assets/js/product-variable.js', array('jquery', 'wc-add-to-cart-variation', 'product-scripts'), '1.0', true);

} else if($product->get_type() == 'simple'){
    wp_enqueue_script('wc-single-product');
    wp_enqueue_script('product-simple', get_template_directory_uri() . '/assets/js/product-simple.js', array('jquery', 'product-scripts'), '1.0', true);
}

?>