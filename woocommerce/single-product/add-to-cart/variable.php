<?php
/**
 * Variable product add to cart
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $product;

if ($product->is_type('variable')) : ?>
    <form class="variations_form cart" method="post" enctype='multipart/form-data' 
        data-product_id="<?php echo absint($product->get_id()); ?>" 
        data-product_variations="<?php echo htmlspecialchars(wp_json_encode($product->get_available_variations())); ?>">
        
        <?php do_action('woocommerce_before_variations_form'); ?>   

        <div class="variations">
            <?php 
            $attributes = $product->get_variation_attributes();
            $default_attributes = $product->get_default_attributes();

            foreach ($attributes as $attribute_name => $options) {
                $attribute_key = sanitize_title($attribute_name);
                
                // Получаем значение по умолчанию
                $default_value = $product->get_variation_default_attribute($attribute_name);
                if (!$default_value && !empty($default_attributes[$attribute_key])) {
                    $default_value = $default_attributes[$attribute_key];
                }
                // Если всё равно нет значения по умолчанию, берем первый вариант
                if (!$default_value && !empty($options)) {
                    $default_value = current($options);
                    $default_value = sanitize_title($default_value);
                }
                
                $attribute_label = wc_attribute_label($attribute_name);
                ?>
                <div class="variation product__atributes">
                    <div class="variation__label">
                        <strong><?php echo $attribute_label; ?>:</strong>
                    </div>
                    <div class="sidebar__checkboxs variation-select">
                        <div style="display:none">
                            <?php 
                            wc_dropdown_variation_attribute_options(array(
                                'options'   => $options,
                                'attribute' => $attribute_name,
                                'product'   => $product,
                                'selected'  => $default_value,
                                'class'     => 'variation-select-hidden',
                            ));
                            ?>
                        </div>
                        
                        <?php 
                        foreach ($options as $option) {
                            $slug = sanitize_title($option);
                            $is_selected = ($default_value === $slug);
                            $input_id = sanitize_title($attribute_name . '_' . $slug);
                            ?>
                            <div class="sidebar__checkbox variation-option">
                                <input id="<?php echo $input_id; ?>" 
                                    type="radio" 
                                    name="attribute_<?php echo esc_attr($attribute_name); ?>"
                                    value="<?php echo esc_attr($slug); ?>" 
                                    <?php checked($is_selected); ?> 
                                    class="sidebar__checkbox-input variation-input" 
                                    data-attribute="<?php echo esc_attr($attribute_name); ?>" />
                                <label for="<?php echo $input_id; ?>" class="sidebar__checkbox-label">
                                    <span class="sidebar__checkbox-text"><?php echo esc_html($option); ?></span>
                                </label>
                            </div>
                            <?php 
                        } 
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        
        <div class="single_variation_wrap">                                        
            <div class="woocommerce-variation-add-to-cart variations_button">
                <!-- Кастомный input количества -->
                <?php //wc_get_template('single-product/add-to-cart/quantity.php'); ?>
                
                <div class="product__line"></div>
                
                <!-- Контейнер для динамической цены -->
                <div class="product__prices variation-price-container">
                    <div class="product__price product__new-price">Выберите параметры</div>
                    <div class="product__price product__old-price"></div>
                </div>
                
                <button type="submit" class="product__button button-black single_add_to_cart_button" disabled>
                    <?php echo esc_html($product->single_add_to_cart_text()); ?>
                </button>
                
                <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
                <input type="hidden" name="variation_id" class="variation_id" value="0" />
            </div>
        </div>
        
        <?php do_action('woocommerce_after_variations_form'); ?>
    </form>
<?php endif; ?>