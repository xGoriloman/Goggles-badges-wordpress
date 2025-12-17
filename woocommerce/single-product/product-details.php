<?php
/**
 * Product details (tabs/accordion)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;
?>

<section class="section__details details">
    <div class="details__container">
        <div data-spollers class="details__body spollers">
            <div class="spollers__item details__item">
                <button type="button" data-spoller class="spollers__title details__title">
                    О товаре <span></span>
                </button>
                <div class="spollers__body details__text">
                    <?php
                    $weight = $product->get_weight();
                    // $dimensions = $product->get_dimensions();
                    ?>
                    
                    <?php if ($weight) : ?>
                        — Вес: <?php echo $weight; ?> кг<br />
                    <?php endif; ?>
                    
                    <?php if (false) : ?>
                        — Размеры: <?php echo $dimensions; ?><br />
                    <?php endif; ?>
                    
                    <?php
                    $attributes = $product->get_attributes();
                    foreach ($attributes as $attribute) :
                        if ($attribute->get_visible()) :
                            $attribute_name = $attribute->get_name();
                            $attribute_options = $attribute->get_options();
                            ?>
                            — <?php echo wc_attribute_label($attribute_name); ?>: <?php echo implode(', ', $attribute_options); ?><br />
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="spollers__item details__item">
                <button type="button" data-spoller class="spollers__title details__title">
                    Доставка <span></span>
                </button>
                <div class="spollers__body details__text">
                    Информация о доставке...
                </div>
            </div>
            
            <div class="spollers__item details__item">
                <button type="button" data-spoller class="spollers__title details__title">
                    Возврат <span></span>
                </button>
                <div class="spollers__body details__text">
                    Условия возврата товара...
                </div>
            </div>
        </div>
    </div>
</section>