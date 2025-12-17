<?php
/**
 * Grouped product add to cart
 */

defined('ABSPATH') || exit;

global $product, $grouped_product, $grouped_products;

do_action('woocommerce_before_add_to_cart_form');
?>

<form class="cart grouped_form product__atributes" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
    <table cellspacing="0" class="woocommerce-grouped-product-list group_table">
        <tbody>
            <?php
            foreach ($grouped_products as $grouped_product) {
                $post_object        = get_post($grouped_product->get_id());
                $quantites_required = $quantites_required || $grouped_product->is_purchasable();
                setup_postdata($GLOBALS['post'] = &$post_object); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
                ?>
                <tr>
                    <td class="label">
                        <label for="product-<?php echo esc_attr($grouped_product->get_id()); ?>">
                            <?php echo $grouped_product->is_visible() ? '<a href="' . esc_url(apply_filters('woocommerce_grouped_product_list_link', get_permalink($grouped_product->get_id()), $grouped_product->get_id())) . '">' . $grouped_product->get_name() . '</a>' : $grouped_product->get_name(); ?>
                        </label>
                    </td>
                    <td class="price">
                        <?php echo $grouped_product->get_price_html(); ?>
                    </td>
                    <td class="quantity">
                        <?php if ($grouped_product->is_purchasable() && $grouped_product->is_in_stock()) : ?>
                            <div class="cart__quantity quantity">
                                <?php woocommerce_quantity_input(
                                    array(
                                        'input_name'  => 'quantity[' . $grouped_product->get_id() . ']',
                                        'input_value' => isset($_POST['quantity'][$grouped_product->get_id()]) ? wc_stock_amount(wp_unslash($_POST['quantity'][$grouped_product->get_id()])) : 0,
                                        'min_value'   => apply_filters('woocommerce_quantity_input_min', 0, $grouped_product),
                                        'max_value'   => apply_filters('woocommerce_quantity_input_max', $grouped_product->get_max_purchase_quantity(), $grouped_product),
                                    )
                                ); ?>
                            </div>
                        <?php else : ?>
                            <span class="out-of-stock">Нет в наличии</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            }
            wp_reset_postdata();
            ?>
        </tbody>
    </table>

    <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />

    <?php if ($quantites_required) : ?>
        <div class="product__line"></div>
        <button type="submit" class="product__button button-black single_add_to_cart_button"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
    <?php endif; ?>
    
    <?php wp_nonce_field('woocommerce-add-to-cart', 'woocommerce-add-to-cart-nonce'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>