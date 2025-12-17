<?php
/**
 * Related products
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

$current_product_id = $product->get_id();
$original_product = $product;
$related_products = wc_get_related_products($current_product_id, 8, []);
$related_products = array_unique($related_products);
$related_products = array_diff($related_products, array($current_product_id));
$related_products = array_slice($related_products, 0, 4);

if (!empty($related_products)) : ?>
    <section class="section__related-products related-products">
        <div class="related-products__container">
            <h2 class="related-products__title title">Похожие товары</h2>
            <div class="related-products products">
                <?php
                $displayed_count = 0;
                foreach ($related_products as $related_product_id) :
                    if ($displayed_count >= 4) break;
                    
                    $related_product = wc_get_product($related_product_id);
                    if ($related_product && $related_product->is_visible()) :
                        $GLOBALS['product'] = $related_product;
                        $displayed_count++;
                        ?>
                        <div class="product-item">
                            <?php wc_get_template_part('content', 'product'); ?>
                        </div>
                    <?php
                    endif;
                endforeach;
                
                $GLOBALS['product'] = $original_product;
                ?>
            </div>
        </div>
    </section>
<?php endif; ?>