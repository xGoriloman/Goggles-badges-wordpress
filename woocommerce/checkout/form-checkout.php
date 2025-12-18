<?php
/**
 * Checkout Form - iOS Style
 * 
 * Путь: /wp-content/themes/ваша-тема/woocommerce/checkout/form-checkout.php
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! WC()->cart->get_cart_contents_count() ) {
    return;
}
?>

<!-- iOS Header -->
<div class="ios-checkout-header">
    <div class="ios-status-bar">
        <div class="ios-time" id="iosTime">9:41</div>
        <div class="ios-indicators">
            <div class="ios-indicator"></div>
            <div class="ios-indicator"></div>
            <div class="ios-indicator"></div>
        </div>
    </div>
    <div class="ios-nav-buttons">
        <button type="button" class="ios-back-btn" onclick="window.history.back()">
            <span class="back-arrow">‹</span>
            Close
        </button>
    </div>
    <div class="ios-header-subtitle"><?php bloginfo( 'name' ); ?></div>
    <div class="ios-header-title">Оформление заказа</div>
</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout ios-checkout-form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

    <div class="ios-checkout-content">

        <?php if ( $checkout->get_checkout_fields() ) : ?>

            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="ios-form-section" id="customer_details">

                <!-- Выбор способа доставки -->
                <div class="ios-delivery-selector" id="deliveryTypeSelector">
                    <label class="ios-form-label">Способ доставки</label>
                    <div class="ios-select-display" id="selectedDeliveryType">
                        <span class="select-text">Выберите способ доставки</span>
                        <span class="select-arrow">›</span>
                    </div>
                </div>

                <!-- Выбор метода CDEK (скрыт по умолчанию) -->
                <div class="ios-cdek-method-selector" id="cdekMethodSelector" style="display: none;">
                    <label class="ios-form-label">Метод доставки CDEK</label>
                    <div class="ios-select-display" id="selectedCdekMethod">
                        <span class="select-text">Выберите метод доставки</span>
                        <span class="select-arrow">›</span>
                    </div>
                </div>

                <!-- Блок выбора ПВЗ CDEK (скрыт по умолчанию) -->
                <div class="ios-cdek-pvz-selector" id="cdekPvzSelector" style="display: none;">
                    <div class="cdek-icon">📍</div>
                    <div class="cdek-text" id="cdekPvzText">Выбрать пункт выдачи CDEK</div>
                    <div class="cdek-arrow">›</div>
                </div>

                <!-- Контейнер для скрытых shipping methods -->
                <div style="display: none;" id="hiddenShippingMethods"></div>

                <?php do_action( 'woocommerce_checkout_billing' ); ?>

                <?php do_action( 'woocommerce_checkout_shipping' ); ?>

                <!-- Способ оплаты -->
                <div class="ios-payment-selector" id="paymentMethodSelector">
                    <label class="ios-form-label">Способ оплаты</label>
                    <div class="ios-select-display" id="selectedPaymentMethod">
                        <span class="select-text">Выберите способ оплаты</span>
                        <span class="select-arrow">›</span>
                    </div>
                </div>

                <!-- Контейнер для скрытых payment methods -->
                <div style="display: none;" id="hiddenPaymentMethods"></div>

            </div>

            <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

        <?php endif; ?>

        <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
        <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

        <div id="order_review" class="woocommerce-checkout-review-order" style="display: none;">
            <?php do_action( 'woocommerce_checkout_order_review' ); ?>
        </div>

        <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

    </div>

</form>

<!-- Action Buttons -->
<div class="ios-action-buttons">
    <button type="button" class="ios-btn-primary" id="iosSubmitOrder">Оформить заказ</button>
    <button type="button" class="ios-btn-secondary" onclick="window.history.back()">Отмена</button>
</div>

<!-- Success Modal -->
<div class="ios-success-modal" id="iosSuccessModal">
    <div class="ios-modal-content">
        <button type="button" class="ios-modal-close" onclick="closeSuccessModal()">✕</button>
        <div class="ios-modal-title">Заказ оформлен</div>
        <div class="ios-modal-text">
            Ваш заказ создан. Мы скоро свяжемся<br>
            с вами, чтобы подтвердить информацию.
        </div>
        <button type="button" class="ios-btn-primary" onclick="window.location.href='<?php echo esc_url( home_url() ); ?>'">Продолжить</button>
    </div>
</div>

<!-- Delivery Type Modal -->
<div class="ios-modal" id="deliveryTypeModal">
    <div class="ios-modal-content-center">
        <div class="ios-modal-header">
            <div class="ios-modal-title">Способ доставки</div>
            <button type="button" class="ios-modal-close" onclick="closeModal('deliveryTypeModal')">✕</button>
        </div>
        <div class="ios-modal-body" id="deliveryTypeOptions"></div>
    </div>
</div>

<!-- CDEK Method Modal -->
<div class="ios-modal" id="cdekMethodModal">
    <div class="ios-modal-content-center">
        <div class="ios-modal-header">
            <div class="ios-modal-title">Метод доставки CDEK</div>
            <button type="button" class="ios-modal-close" onclick="closeModal('cdekMethodModal')">✕</button>
        </div>
        <div class="ios-modal-body" id="cdekMethodOptions"></div>
    </div>
</div>

<!-- CDEK PVZ Modal -->
<div class="ios-modal" id="cdekPvzModal">
    <div class="ios-modal-content-center">
        <div class="ios-modal-header">
            <div class="ios-modal-title">Пункт выдачи CDEK</div>
            <button type="button" class="ios-modal-close" onclick="closeModal('cdekPvzModal')">✕</button>
        </div>
        <div class="ios-modal-body">
            <div class="cdek-pvz-list" id="cdekPvzList">
                <div class="cdek-pvz-item" onclick="selectPvz('CDEK, ул. Ленина 25, Иваново')">
                    <div class="pvz-icon">📍</div>
                    <div class="pvz-info">
                        <div class="pvz-title">CDEK, ул. Ленина 25</div>
                        <div class="pvz-desc">Иваново, работает Пн-Пт 9:00-18:00</div>
                    </div>
                    <div class="pvz-arrow">›</div>
                </div>
                <div class="cdek-pvz-item" onclick="selectPvz('CDEK, пр. Текстильщиков 48, Иваново')">
                    <div class="pvz-icon">📍</div>
                    <div class="pvz-info">
                        <div class="pvz-title">CDEK, пр. Текстильщиков 48</div>
                        <div class="pvz-desc">Иваново, работает Пн-Сб 9:00-19:00</div>
                    </div>
                    <div class="pvz-arrow">›</div>
                </div>
                <div class="cdek-pvz-item" onclick="selectPvz('CDEK, ул. Советская 12, Иваново')">
                    <div class="pvz-icon">📍</div>
                    <div class="pvz-info">
                        <div class="pvz-title">CDEK, ул. Советская 12</div>
                        <div class="pvz-desc">Иваново, работает Пн-Вс 10:00-20:00</div>
                    </div>
                    <div class="pvz-arrow">›</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div class="ios-modal" id="paymentMethodModal">
    <div class="ios-modal-content-center">
        <div class="ios-modal-header">
            <div class="ios-modal-title">Способ оплаты</div>
            <button type="button" class="ios-modal-close" onclick="closeModal('paymentMethodModal')">✕</button>
        </div>
        <div class="ios-modal-body" id="paymentMethodOptions"></div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="ios-loading" id="iosLoading">
    <div class="ios-spinner"></div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
    jQuery(document).ready(function($) {
        let selectedShippingMethod = null;
        let selectedPaymentMethod = null;
        let cdekMethods = [];
        let currentDeliveryType = null;
        
        // Инициализация
        init();
        
        function init() {
            updateTime();
            setInterval(updateTime, 60000);
            setupPhoneMask();
            moveShippingMethods();
            movePaymentMethods();
            setupEventListeners();
            parseShippingMethods();
        }
        
        // Обновление времени
        function updateTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            $('#iosTime').text(hours + ':' + minutes);
        }
        
        // Маска телефона
        function setupPhoneMask() {
            $('#billing_phone').on('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value[0] === '7') value = value.substring(1);
                    if (value[0] === '8') value = value.substring(1);
                    
                    let formatted = '+7';
                    if (value.length > 0) formatted += ' (' + value.substring(0, 3);
                    if (value.length >= 4) formatted += ') ' + value.substring(3, 6);
                    if (value.length >= 7) formatted += '-' + value.substring(6, 8);
                    if (value.length >= 9) formatted += '-' + value.substring(8, 10);
                    
                    e.target.value = formatted;
                }
            });
        }
        
        // Перемещение методов доставки
        function moveShippingMethods() {
            const shippingMethods = $('#shipping_method');
            if (shippingMethods.length) {
                $('#hiddenShippingMethods').append(shippingMethods);
            }
        }
        
        // Перемещение методов оплаты
        function movePaymentMethods() {
            const paymentMethods = $('#payment');
            if (paymentMethods.length) {
                $('#hiddenPaymentMethods').append(paymentMethods);
            }
        }
        
        // Парсинг методов доставки
        function parseShippingMethods() {
            const methods = $('input[name="shipping_method[0]"]');
            const deliveryTypes = {};
            
            methods.each(function() {
                const $this = $(this);
                const value = $this.val();
                const label = $this.next('label').clone();
                label.find('input').remove();
                const text = label.text().trim();
                
                if (value.includes('official_cdek')) {
                    if (!deliveryTypes['cdek']) {
                        deliveryTypes['cdek'] = {
                            name: 'CDEK',
                            methods: []
                        };
                    }
                    deliveryTypes['cdek'].methods.push({
                        value: value,
                        text: text,
                        element: $this
                    });
                } else if (value.includes('local_pickup')) {
                    deliveryTypes['pickup'] = {
                        name: 'Самовывоз',
                        value: value,
                        element: $this
                    };
                } else {
                    deliveryTypes[value] = {
                        name: text,
                        value: value,
                        element: $this
                    };
                }
            });
            
            renderDeliveryTypes(deliveryTypes);
            cdekMethods = deliveryTypes['cdek'] ? deliveryTypes['cdek'].methods : [];
        }
        
        // Отрисовка типов доставки
        function renderDeliveryTypes(types) {
            let html = '';
            
            if (types['cdek']) {
                html += `<div class="ios-option" onclick="selectDeliveryType('cdek', 'Доставка CDEK')">
                    <div class="option-icon">📦</div>
                    <div class="option-text">Доставка CDEK</div>
                    <div class="option-arrow">›</div>
                </div>`;
            }
            
            if (types['pickup']) {
                html += `<div class="ios-option" onclick="selectDeliveryType('pickup', 'Самовывоз', '${types['pickup'].value}')">
                    <div class="option-icon">🏪</div>
                    <div class="option-text">Самовывоз</div>
                    <div class="option-arrow">›</div>
                </div>`;
            }
            
            $('#deliveryTypeOptions').html(html);
        }
        
        // Выбор типа доставки
        window.selectDeliveryType = function(type, name, value) {
            currentDeliveryType = type;
            $('#selectedDeliveryType .select-text').text(name);
            closeModal('deliveryTypeModal');
            
            if (type === 'cdek') {
                $('#cdekMethodSelector').show();
                $('#cdekPvzSelector').hide();
                $('#selectedCdekMethod .select-text').text('Выберите метод доставки');
                renderCdekMethods();
            } else if (type === 'pickup') {
                $('#cdekMethodSelector').hide();
                $('#cdekPvzSelector').hide();
                $('input[name="shipping_method[0]"][value="' + value + '"]').prop('checked', true).trigger('change');
            }
        };
        
        // Отрисовка методов CDEK
        function renderCdekMethods() {
            let html = '';
            
            cdekMethods.forEach(function(method) {
                const parts = method.text.split(':');
                const title = parts[0] ? parts[0].trim() : method.text;
                const desc = parts[1] ? parts[1].trim() : '';
                
                html += `<div class="ios-option" onclick="selectCdekMethod('${method.value}', '${escapeHtml(title)}')">
                    <div class="option-text">
                        <div class="option-title">${title}</div>
                        ${desc ? '<div class="option-desc">' + desc + '</div>' : ''}
                    </div>
                    <div class="option-arrow">›</div>
                </div>`;
            });
            
            $('#cdekMethodOptions').html(html);
        }
        
        // Выбор метода CDEK
        window.selectCdekMethod = function(value, name) {
            selectedShippingMethod = value;
            $('#selectedCdekMethod .select-text').text(name);
            $('input[name="shipping_method[0]"][value="' + value + '"]').prop('checked', true).trigger('change');
            closeModal('cdekMethodModal');
            
            // Показываем выбор ПВЗ если это склад
            if (name.includes('склад') || name.includes('Посылка')) {
                $('#cdekPvzSelector').show();
            } else {
                $('#cdekPvzSelector').hide();
            }
        };
        
        // Выбор ПВЗ
        window.selectPvz = function(address) {
            $('#cdekPvzText').text(address);
            closeModal('cdekPvzModal');
        };
        
        // Отрисовка методов оплаты
        function renderPaymentMethods() {
            const methods = $('.wc_payment_methods .wc_payment_method');
            let html = '';
            
            methods.each(function() {
                const $this = $(this);
                const input = $this.find('input[type="radio"]');
                const label = $this.find('label').clone();
                label.find('input').remove();
                const text = label.text().trim();
                const value = input.val();
                const icon = $this.find('img').attr('src');
                
                html += `<div class="ios-option payment-option" onclick="selectPaymentMethod('${value}', '${escapeHtml(text)}')">
                    <div class="option-text">
                        ${icon ? '<img src="' + icon + '" style="height: 20px; margin-right: 8px;">' : ''}
                        ${text}
                    </div>
                    <div class="option-arrow">›</div>
                </div>`;
            });
            
            $('#paymentMethodOptions').html(html);
            
            // Выбираем первый метод по умолчанию
            const firstMethod = methods.first().find('input[type="radio"]');
            if (firstMethod.length) {
                const firstLabel = methods.first().find('label').text().trim();
                selectPaymentMethod(firstMethod.val(), firstLabel);
            }
        }
        
        // Выбор метода оплаты
        window.selectPaymentMethod = function(value, name) {
            selectedPaymentMethod = value;
            $('#selectedPaymentMethod .select-text').text(name);
            $('input[name="payment_method"][value="' + value + '"]').prop('checked', true);
            closeModal('paymentMethodModal');
        };
        
        // Event listeners
        function setupEventListeners() {
            $('#deliveryTypeSelector').on('click', function() {
                openModal('deliveryTypeModal');
            });
            
            $('#cdekMethodSelector').on('click', function() {
                openModal('cdekMethodModal');
            });
            
            $('#cdekPvzSelector').on('click', function() {
                openModal('cdekPvzModal');
            });
            
            $('#paymentMethodSelector').on('click', function() {
                renderPaymentMethods();
                openModal('paymentMethodModal');
            });
            
            $('#iosSubmitOrder').on('click', function() {
                submitOrder();
            });
        }
        
        // Открыть модальное окно
        function openModal(modalId) {
            $('#' + modalId).addClass('active');
        }
        
        // Закрыть модальное окно
        window.closeModal = function(modalId) {
            $('#' + modalId).removeClass('active');
        };
        
        // Отправка заказа
        function submitOrder() {
            // Валидация
            if (!currentDeliveryType) {
                alert('Пожалуйста, выберите способ доставки');
                return;
            }
            
            if (currentDeliveryType === 'cdek' && !selectedShippingMethod) {
                alert('Пожалуйста, выберите метод доставки CDEK');
                return;
            }
            
            if (!selectedPaymentMethod) {
                alert('Пожалуйста, выберите способ оплаты');
                return;
            }
            
            // Валидация обязательных полей
            let isValid = true;
            $('.validate-required').each(function() {
                const $input = $(this).find('input, select, textarea');
                if ($input.val() === '' || $input.val() === null) {
                    $(this).addClass('ios-field-error');
                    isValid = false;
                } else {
                    $(this).removeClass('ios-field-error');
                }
            });
            
            if (!isValid) {
                alert('Пожалуйста, заполните все обязательные поля');
                return;
            }
            
            // Показываем загрузку
            $('#iosLoading').addClass('active');
            
            // Отправляем форму
            $('form.checkout').submit();
        }
        
        // Обработка ошибок
        $(document.body).on('checkout_error', function() {
            $('#iosLoading').removeClass('active');
        });
        
        // Вспомогательная функция
        function escapeHtml(text) {
            return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
        }
        
        // Закрыть модальное окно успеха
        window.closeSuccessModal = function() {
            $('#iosSuccessModal').removeClass('active');
        };
        
        // Обновление корзины при изменении доставки
        $(document.body).on('change', 'input[name="shipping_method[0]"]', function() {
            $(document.body).trigger('update_checkout');
        });
    });
</script>