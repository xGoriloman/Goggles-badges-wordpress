<?php
/**
 * Checkout Page
 * Шаблон страницы оформления заказа
 */

get_header();
?>

<div class="gnb-header">
    <div class="gnb-header__top">
        <button class="gnb-btn-icon" onclick="history.back()">←</button>
        <div class="gnb-header__title">Оформление заказа</div>
        <div style="width: 40px;"></div>
    </div>
</div>

<div class="gnb-container">
    <div class="woocommerce-checkout">
        <?php
        if (wc_get_page_id('checkout') === false) {
            return;
        }

        do_action('woocommerce_before_checkout_form', WC()->checkout);

        // If checkout registration is disabled and not logged in, the user cannot checkout
        if (!is_user_logged_in() && 'no' === get_option('woocommerce_enable_guest_checkout') && !is_user_logged_in()) {
            echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.')));
            return;
        }

        $checkout = WC()->checkout;
        ?>

        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

            <?php if ($checkout->get_checkout_fields()): ?>

                <div class="gnb-section">
                    <div class="gnb-section__title">Тип доставки</div>
                    
                    <div class="gnb-form-group">
                        <label class="gnb-form-group__label">Пункты выдачи (CDEK)</label>
                        <div style="padding: 12px 16px; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); background: white; display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-dark); font-weight: 500;">Пункты выдачи (CDEK)</span>
                            <span style="color: var(--text-light);">→</span>
                        </div>
                    </div>

                    <div class="gnb-form-group">
                        <label class="gnb-form-group__label">Страна</label>
                        <select class="gnb-form-group__select" name="billing_country" id="billing_country">
                            <option value="RU">Россия</option>
                        </select>
                    </div>
                </div>

                <div class="gnb-section">
                    <div class="gnb-section__title">Данные получателя</div>

                    <?php
                    $billing_fields = $checkout->get_checkout_fields('billing');
                    
                    foreach ($billing_fields as $key => $field):
                        if (in_array($key, ['billing_country', 'billing_email', 'billing_postcode', 'billing_state'])) {
                            continue;
                        }
                        
                        WC()->checkout->checkout_form_field($key, $field);
                    endforeach;
                    ?>
                </div>

                <div class="gnb-section">
                    <div class="gnb-section__title">Адрес доставки</div>

                    <div class="gnb-form-group">
                        <label class="gnb-form-group__label">Найти ближайший офис CDEK</label>
                        <div style="padding: 12px 16px; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); background: white; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                            <span style="color: var(--text-dark);">Найти ближайший офис CDEK</span>
                            <span style="color: var(--text-light);">→</span>
                        </div>
                    </div>

                    <?php
                    $shipping_fields = $checkout->get_checkout_fields('shipping');
                    
                    foreach ($shipping_fields as $key => $field):
                        WC()->checkout->checkout_form_field($key, $field);
                    endforeach;
                    ?>
                </div>

            <?php endif; ?>

            <div class="gnb-section">
                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
                
                <div class="gnb-section__title">Ваш заказ</div>

                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>

                <?php do_action('woocommerce_checkout_after_order_review'); ?>
            </div>

            <?php wp_nonce_field('woocommerce-process_checkout'); ?>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="gnb-btn gnb-btn--primary" name="woocommerce_checkout_place_order" id="place_order">
                    Оформить заказ
                </button>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="gnb-btn gnb-btn--secondary">
                    Назад в корзину
                </a>
            </div>

        </form>

        <?php do_action('woocommerce_after_checkout_form', WC()->checkout); ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CDEK Point Selection
    const cdekPointBtn = document.querySelector('[data-action="select_cdek_point"]');
    if (cdekPointBtn) {
        cdekPointBtn.addEventListener('click', function() {
            // Открыть модальное окно с выбором пункта
            showCDEKPointSelector();
        });
    }

    // Update checkout on changes
    const checkoutForm = document.querySelector('.checkout');
    if (checkoutForm) {
        checkoutForm.addEventListener('change', function() {
            jQuery(document.body).trigger('update_checkout');
        });
    }
});

function showCDEKPointSelector() {
    // Реализуется на основе плагина CDEK
    console.log('Открыть выбор пункта CDEK');
}
</script>

<?php get_footer();