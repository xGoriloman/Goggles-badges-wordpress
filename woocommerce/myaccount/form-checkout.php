<?php
/**
 * Checkout Form
 * Форма оформления заказа
 * 
 * @package WooCommerce\Templates
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', WC()->checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout
if (!is_user_logged_in() && 'no' === get_option('woocommerce_enable_guest_checkout') && !is_user_logged_in()) {
    echo wp_kses_post(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

$checkout = WC()->checkout;
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <?php if ($checkout->get_checkout_fields()): ?>

        <div class="gnb-section">
            <h3 class="gnb-section__title">Тип доставки</h3>
            
            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Способ доставки</label>
                <select class="gnb-form-group__select" name="shipping_method" id="shipping_method">
                    <option value="cdek">Пункты выдачи CDEK</option>
                    <option value="courier">Курьером</option>
                </select>
            </div>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Страна</label>
                <select class="gnb-form-group__select" name="billing_country" id="billing_country">
                    <option value="RU" selected>Россия</option>
                </select>
            </div>
        </div>

        <div class="gnb-section">
            <h3 class="gnb-section__title">Данные получателя</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="gnb-form-group">
                    <label class="gnb-form-group__label">Имя *</label>
                    <input type="text" class="gnb-form-group__input" name="billing_first_name" id="billing_first_name" placeholder="Иван" required>
                </div>

                <div class="gnb-form-group">
                    <label class="gnb-form-group__label">Фамилия *</label>
                    <input type="text" class="gnb-form-group__input" name="billing_last_name" id="billing_last_name" placeholder="Иванов" required>
                </div>
            </div>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Email *</label>
                <input type="email" class="gnb-form-group__input" name="billing_email" id="billing_email" placeholder="email@example.com" required>
            </div>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Телефон *</label>
                <input type="tel" class="gnb-form-group__input" name="billing_phone" id="billing_phone" placeholder="+7 (999) 999-99-99" required>
            </div>
        </div>

        <div class="gnb-section" id="cdek-section">
            <h3 class="gnb-section__title">Пункт выдачи CDEK</h3>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Найти пункт выдачи</label>
                <div style="display: flex; gap: 12px;">
                    <input type="text" class="gnb-form-group__input" id="cdek_city_search" placeholder="Введите город" style="flex: 1;">
                    <button type="button" class="gnb-btn gnb-btn--primary" style="flex: 0 0 auto; padding: 12px 20px;" onclick="searchCDEKCities()">Поиск</button>
                </div>
            </div>

            <div id="cdek-points-container" style="display: none;">
                <label class="gnb-form-group__label" style="margin-top: 16px;">Доступные пункты:</label>
                <div id="cdek-points-list" class="gnb-cdek-points__list">
                    <div style="text-align: center; padding: 20px;">
                        <div class="gnb-loading"></div>
                    </div>
                </div>
                <input type="hidden" name="cdek_point_id" id="cdek_point_id">
            </div>
        </div>

        <div class="gnb-section">
            <h3 class="gnb-section__title">Адрес доставки</h3>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Населенный пункт *</label>
                <input type="text" class="gnb-form-group__input" name="billing_city" id="billing_city" placeholder="Москва" required>
            </div>

            <div class="gnb-form-group">
                <label class="gnb-form-group__label">Улица, дом *</label>
                <input type="text" class="gnb-form-group__input" name="billing_address_1" id="billing_address_1" placeholder="ул. Пушкина, дом 10" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="gnb-form-group">
                    <label class="gnb-form-group__label">Квартира</label>
                    <input type="text" class="gnb-form-group__input" name="billing_address_2" id="billing_address_2" placeholder="123">
                </div>

                <div class="gnb-form-group">
                    <label class="gnb-form-group__label">Почтовый индекс</label>
                    <input type="text" class="gnb-form-group__input" name="billing_postcode" id="billing_postcode" placeholder="123456">
                </div>
            </div>
        </div>

    <?php endif; ?>

    <div class="gnb-section">
        <h3 class="gnb-section__title">Ваш заказ</h3>
        <?php do_action('woocommerce_checkout_order_review'); ?>
    </div>

    <?php wp_nonce_field('woocommerce-process_checkout'); ?>

    <div style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 80px;">
        <button type="submit" class="gnb-btn gnb-btn--primary" name="woocommerce_checkout_place_order" id="place_order">
            Оформить заказ
        </button>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="gnb-btn gnb-btn--secondary">
            Назад в корзину
        </a>
    </div>

</form>

<?php do_action('woocommerce_after_checkout_form', WC()->checkout); ?>

<script>
function searchCDEKCities() {
    const city = document.getElementById('cdek_city_search').value.trim();
    
    if (city.length < 2) {
        alert('Введите минимум 2 символа');
        return;
    }
    
    jQuery.ajax({
        type: 'POST',
        url: GNB.ajaxUrl,
        data: {
            action: 'gnb_get_cdek_points',
            city: city,
            nonce: GNB.nonce
        },
        success: function(response) {
            if (response.success && response.data) {
                renderCDEKPoints(response.data);
                document.getElementById('cdek-points-container').style.display = 'block';
            }
        }
    });
}

function renderCDEKPoints(points) {
    const container = document.getElementById('cdek-points-list');
    
    if (!points || points.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #999;">Пункты не найдены</p>';
        return;
    }
    
    let html = '';
    
    points.forEach(point => {
        html += `
            <div class="gnb-cdek-points__item" onclick="selectCDEKPoint('${point.code}')">
                <div class="gnb-cdek-points__address">${point.code}, ${point.city}</div>
                <div class="gnb-cdek-points__info">
                    <span><strong>Адрес:</strong> ${point.address}</span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function selectCDEKPoint(pointId) {
    document.getElementById('cdek_point_id').value = pointId;
    
    jQuery.ajax({
        type: 'POST',
        url: GNB.ajaxUrl,
        data: {
            action: 'gnb_save_cdek_point',
            point_id: pointId,
            nonce: GNB.nonce
        },
        success: function() {
            jQuery('[data-point-id="' + pointId + '"]').addClass('active');
        }
    });
}

// Форматирование телефона
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('billing_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length === 11) {
                    this.value = '+' + value[0] + ' (' + value.substring(1, 4) + ') ' +
                                value.substring(4, 7) + '-' + value.substring(7, 9) + '-' + value.substring(9);
                }
            }
        });
    }
});
</script>

<style>
.gnb-cdek-points__item {
    padding: 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-md);
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.gnb-cdek-points__item:hover {
    border-color: var(--primary-color);
    background: var(--bg-light);
}

.gnb-cdek-points__item.active {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
}

.gnb-cdek-points__item.active .gnb-cdek-points__address,
.gnb-cdek-points__item.active .gnb-cdek-points__info {
    color: white;
}

.gnb-cdek-points__address {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    color: var(--text-dark);
}

.gnb-cdek-points__info {
    font-size: 12px;
    color: var(--text-light);
    line-height: 1.6;
}

.gnb-cdek-points__info span {
    display: block;
    margin-bottom: 4px;
}
</style>