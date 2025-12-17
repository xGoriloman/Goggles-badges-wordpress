<?php if (!defined('ABSPATH')) exit; ?>

<div class="cart-tots-fragment">
    <div class="sidebar-cart-premium">
        <h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

        <table class="sidebar-cart-premium__table">
            <tbody>
                <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--subtotal">
                    <th>Сумма товаров</th>
                    <td><?php wc_cart_totals_subtotal_html(); ?></td>
                </tr>

                <?php
                $total_sale_discount = 0;
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product = $cart_item['data'];
                    if ($_product && $_product->exists()) {
                        $regular_price = $_product->get_regular_price();
                        $sale_price = $_product->get_sale_price();
                        
                        if ($sale_price && $regular_price > $sale_price) {
                            $item_discount = ($regular_price - $sale_price) * $cart_item['quantity'];
                            $total_sale_discount += $item_discount;
                        }
                    }
                }
                
                if ($total_sale_discount > 0) : ?>
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--sale-discount">
                        <th>Скидка на товары</th>
                        <td style="color: #f80f4e; font-weight: 600;">
                            -<?php echo wc_price($total_sale_discount); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                
                <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--coupon-discount">
                        <th>Скидка по купону "<?php echo esc_html($coupon->get_code()); ?>"</th>
                        <td style="color: #f80f4e;">
                            -<?php wc_cart_totals_coupon_html($coupon); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--shipping">
                        <th>Доставка</th>
                        <td>
                            <?php woocommerce_cart_totals_shipping_html(); ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                    <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                        <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                            <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--tax">
                                <th><?php echo esc_html($tax->label); ?></th>
                                <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--tax">
                            <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>

                <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--total">
                    <th>Итого к оплате:</th>
                    <td><?php wc_cart_totals_order_total_html(); ?></td>
                </tr>
                
                <?php
                $total_savings = 0;
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product = $cart_item['data'];
                    if ($_product && $_product->exists()) {
                        $regular_price = $_product->get_regular_price();
                        $sale_price = $_product->get_sale_price();
                        $price = $_product->get_price();
                        
                        if ($sale_price && $regular_price > $sale_price) {
                            $total_savings += ($regular_price - $sale_price) * $cart_item['quantity'];
                        }
                    }
                }
                
                if ($total_savings > 0) : ?>
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--savings">
                        <td colspan="2" style="text-align: center; padding-top: 20px; color: #4CAF50; font-weight: 700; font-size: 1.1em;">
                            🎉 Вы экономите: <?php echo wc_price($total_savings); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="sidebar-cart-premium__button">
            Оформить заказ
        </a>
    </div>
</div>