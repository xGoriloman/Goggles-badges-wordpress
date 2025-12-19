<?php

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
                <!-- ПВЗ будут загружены через JS -->
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
    let cdekOfficesData = [];
    let selectedCdekOffice = null;
    
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
        loadCdekOffices();
        // Интеграция CDEK должна быть вызвана после parseShippingMethods
        integrateCdekPlugin(); 
    }
    
    // НОВАЯ ФУНКЦИЯ: Интеграция с плагином CDEK
    function integrateCdekPlugin() {
        // Находим все кнопки CDEK от плагина
        // Ищем в скрытом контейнере, куда мы переместили элементы
        const cdekButtons = $('#hiddenShippingMethods').find('.open-pvz-btn, .cdek-pvz-btn, [class*="cdek"]button, .cdek-widget-button');
        
        console.log('Found CDEK buttons:', cdekButtons.length);
        
        if (cdekButtons.length) {
            // Перехватываем клик на нашей кнопке ПВЗ
            // Сначала удаляем старый обработчик, чтобы избежать дублирования
            $(document).off('click', '#cdekPvzSelector');
            
            $(document).on('click', '#cdekPvzSelector', function(e) {
                e.preventDefault();
                console.log('Opening CDEK widget from plugin');
                
                // Кликаем по оригинальной кнопке плагина, которая находится в скрытом контейнере
                cdekButtons.first().click();
            });
            
            // Слушаем изменения в скрытых полях от плагина
            $(document.body).on('change', 'input[name="office_code"], .cdek-office-code', function() {
                const code = $(this).val();
                console.log('CDEK office selected:', code);
                
                if (code) {
                    // Ищем информацию об офисе
                    const officeName = findOfficeNameByCode(code);
                    if (officeName) {
                        $('#cdekPvzText').text(officeName);
                        selectedCdekOffice = {
                            code: code,
                            name: officeName
                        };
                    }
                }
            });
        }
        
        // Отслеживаем события от плагина CDEK
        $(document.body).on('cdek_office_selected', function(e, data) {
            console.log('CDEK office selected via event:', data);
            if (data && data.code) {
                $('#cdekPvzText').text(data.name || 'Офис CDEK #' + data.code);
            }
        });
    }
    
    // Поиск названия офиса по коду
    function findOfficeNameByCode(code) {
        if (cdekOfficesData.length > 0) {
            const office = cdekOfficesData.find(o => o.code === code);
            if (office) {
                return office.name;
            }
        }
        // Возвращаем просто код, если имя не найдено
        return 'Офис CDEK #' + code;
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
                if (value[0] === '7' || value[0] === '8') value = value.substring(1);
                
                let formatted = '+7';
                if (value.length > 0) formatted += ' (' + value.substring(0, 3);
                if (value.length >= 4) formatted += ') ' + value.substring(3, 6);
                if (value.length >= 7) formatted += '-' + value.substring(6, 8);
                if (value.length >= 9) formatted += '-' + value.substring(8, 10);
                
                e.target.value = formatted;
            }
        });
    }
    
    // Загрузка данных ПВЗ CDEK из JSON на странице
    function loadCdekOffices() {
        const cdekScript = $('script[type="application/cdek-offices"]');
        if (cdekScript.length) {
            try {
                cdekOfficesData = JSON.parse(cdekScript.html());
                console.log('Loaded ' + cdekOfficesData.length + ' CDEK offices');
            } catch(e) {
                console.error('Error parsing CDEK offices:', e);
            }
        }
    }
    
    // Перемещение методов доставки
    function moveShippingMethods() {
        const shippingMethods = $('#shipping_method');
        if (shippingMethods.length) {
            $('#hiddenShippingMethods').append(shippingMethods);
        }
        
        // Перемещаем ВСЕ элементы, которые могут быть связаны с расчетом доставки (например, поля CDEK)
        // Они обычно находятся в order_review
        const orderReview = $('#order_review');
        if (orderReview.length) {
            const wcCdekElements = orderReview.find('.open-pvz-btn, .cdek-pvz-btn, [class*="cdek"], input[name="office_code"], .cdek-pvz-info, .cdek-widget-button').closest('li, div, p');
            if (wcCdekElements.length) {
                 // Клонируем, чтобы оригинальные элементы остались для работы плагина
                $('#hiddenShippingMethods').append(wcCdekElements.clone(true, true).css('display', 'none')); 
            }
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
        // Ищем радиокнопки внутри скрытого контейнера
        const methods = $('#hiddenShippingMethods').find('input[name="shipping_method[0]"]');
        const deliveryTypes = {};
        
        methods.each(function() {
            const $this = $(this);
            const value = $this.val();
            // Находим соответствующий label, который содержит текст метода
            const label = $this.closest('li, div').find('label').clone(); 
            label.find('input').remove();
            const text = label.text().trim();
            
            // Логика группировки CDEK
            if (value.includes('official_cdek') || value.includes('cdek')) {
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
        
        // 1. CDEK (Если есть методы CDEK)
        if (types['cdek'] && types['cdek'].methods.length > 0) {
            html += `<div class="ios-option" onclick="selectDeliveryType('cdek', 'Доставка CDEK')">
                <div class="option-icon">📦</div>
                <div class="option-text">Доставка CDEK</div>
                <div class="option-arrow">›</div>
            </div>`;
        }
        
        // 2. Самовывоз
        if (types['pickup']) {
            html += `<div class="ios-option" onclick="selectDeliveryType('pickup', 'Самовывоз', '${types['pickup'].value}')">
                <div class="option-icon">🏪</div>
                <div class="option-text">Самовывоз</div>
                <div class="option-arrow">›</div>
            </div>`;
        }
        
        // 3. Другие методы
        for (const key in types) {
            if (key !== 'cdek' && key !== 'pickup') {
                html += `<div class="ios-option" onclick="selectDeliveryType('standard', '${types[key].name}', '${types[key].value}')">
                    <div class="option-icon">🚚</div>
                    <div class="option-text">${types[key].name}</div>
                    <div class="option-arrow">›</div>
                </div>`;
            }
        }
        
        $('#deliveryTypeOptions').html(html);
    }
    
    // Выбор типа доставки
    window.selectDeliveryType = function(type, name, value = null) {
        currentDeliveryType = type;
        selectedShippingMethod = value; // Сбрасываем или устанавливаем для не-CDEK
        
        $('#selectedDeliveryType .select-text').text(name);
        closeModal('deliveryTypeModal');
        
        // Скрываем все CDEK-специфичные элементы по умолчанию
        $('#cdekMethodSelector').hide();
        $('#cdekPvzSelector').hide();
        
        if (type === 'cdek') {
            $('#cdekMethodSelector').show();
            $('#selectedCdekMethod .select-text').text('Выберите метод доставки');
            $('#cdekPvzText').text('Выбрать пункт выдачи CDEK');
            renderCdekMethods();
        } else if (value) {
            // Для Самовывоза или других стандартных методов
            $('input[name="shipping_method[0]"][value="' + value + '"]').prop('checked', true).trigger('change');
        }
    };
    
    // Отрисовка методов CDEK
    function renderCdekMethods() {
        let html = '';
        
        cdekMethods.forEach(function(method) {
            // Пытаемся разделить название и описание
            const regex = /(.*?)(?:\s+-\s+(.*))?$/;
            const match = method.text.match(regex);
            const title = match[1] ? match[1].trim() : method.text;
            const desc = match[2] ? match[2].trim() : '';

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
        
        // 1. Отмечаем радиокнопку
        const $radio = $('input[name="shipping_method[0]"][value="' + value + '"]');
        $radio.prop('checked', true);
        
        // 2. Триггерим изменение для обновления checkout
        $radio.trigger('change');
        
        closeModal('cdekMethodModal');
        
        // 3. Показываем выбор ПВЗ если это склад/ПВЗ
        const methodName = name.toLowerCase();
        
        // Ждем небольшую паузу после обновления чекаута, чтобы плагин CDEK успел вставить виджет
        setTimeout(function() {
            if (methodName.includes('склад') || methodName.includes('пвз') || methodName.includes('посылка')) {
                $('#cdekPvzSelector').show();
                // Повторно связываем плагин
                integrateCdekPlugin();
            } else {
                $('#cdekPvzSelector').hide();
            }
        }, 800); 
    };
    
    // Отрисовка методов оплаты
    function renderPaymentMethods() {
        // Ищем методы оплаты в скрытом контейнере
        const methods = $('#hiddenPaymentMethods').find('.wc_payment_methods .wc_payment_method');
        let html = '';
        
        methods.each(function() {
            const $this = $(this);
            const input = $this.find('input[type="radio"]');
            const label = $this.find('label').clone();
            label.find('input').remove();
            const text = label.text().trim();
            const value = input.val();
            // Ищем иконку внутри элемента
            const icon = $this.find('.payment_box img').attr('src') || $this.find('label img').attr('src');
            
            html += `<div class="ios-option payment-option" onclick="selectPaymentMethod('${value}', '${escapeHtml(text)}')">
                <div class="option-text">
                    ${icon ? '<img src="' + icon + '" style="height: 20px; margin-right: 8px;">' : ''}
                    ${text}
                </div>
                <div class="option-arrow">›</div>
            </div>`;
        });
        
        $('#paymentMethodOptions').html(html);
        
        // Выбираем первый метод по умолчанию, если ничего не выбрано
        const firstMethod = methods.first().find('input[type="radio"]');
        if (firstMethod.length && !selectedPaymentMethod) {
            const firstLabel = methods.first().find('label').text().trim();
            selectPaymentMethod(firstMethod.val(), firstLabel);
        }
    }
    
    // Выбор метода оплаты
    window.selectPaymentMethod = function(value, name) {
        selectedPaymentMethod = value;
        $('#selectedPaymentMethod .select-text').text(name);
        // Отмечаем и триггерим
        $('input[name="payment_method"][value="' + value + '"]').prop('checked', true).trigger('change');
        closeModal('paymentMethodModal');
    };
    
    // Event listeners
    function setupEventListeners() {
        $('#deliveryTypeSelector').on('click', function() {
            parseShippingMethods(); // Перепарсинг на случай, если методы изменились
            openModal('deliveryTypeModal');
        });
        
        $('#cdekMethodSelector').on('click', function() {
            openModal('cdekMethodModal');
        });
        
        $('#paymentMethodSelector').on('click', function() {
            renderPaymentMethods(); // Отрисовка каждый раз, так как WC может менять доступность методов
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
        // ... (Валидация остается без изменений, она выглядит адекватной) ...
        
        // Валидация
        if (!currentDeliveryType) {
            alert('Пожалуйста, выберите способ доставки');
            return;
        }
        
        if (currentDeliveryType === 'cdek' && !selectedShippingMethod) {
            alert('Пожалуйста, выберите метод доставки CDEK (Курьер или ПВЗ)');
            return;
        }
        
        // Проверка заполнения полей
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
        
        // Проверяем выбор ПВЗ для методов склад
        const methodName = $('#selectedCdekMethod .select-text').text().toLowerCase();
        if (currentDeliveryType === 'cdek' && (methodName.includes('склад') || methodName.includes('пвз') || methodName.includes('посылка'))) {
            const officeCode = $('input[name="office_code"]').val();
            if (!officeCode) {
                alert('Пожалуйста, выберите пункт выдачи CDEK');
                return;
            }
        }
        
        if (!selectedPaymentMethod) {
            alert('Пожалуйста, выберите способ оплаты');
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
    
    // Обработка успешного заказа
    $(document.body).on('checkout_place_order_success', function(e, result) {
        console.log('Checkout result:', result);
        
        if (result && result.result === 'success') {
            if (result.redirect) {
                // Если есть редирект (например, на ЮKassa или Thank You page)
                $('#iosLoading').addClass('active');
                window.location.href = result.redirect;
                return false;
            }
        }
        
        if (result && result.result === 'failure') {
            $('#iosLoading').removeClass('active');
        }
        
        return true;
    });
    
    // Вспомогательная функция
    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
    
    // Закрыть модальное окно успеха
    window.closeSuccessModal = function() {
        $('#iosSuccessModal').removeClass('active');
    };
    
    // Обновление корзины при изменении доставки
    $(document.body).on('change', 'input[name="shipping_method[0]"]', function() {
        // Убедимся, что при смене метода доставки вызывается обновление чекаута
        $(document.body).trigger('update_checkout');
        
        // Повторно интегрируем CDEK после обновления, так как плагин мог перерисовать виджет
        setTimeout(function() {
            integrateCdekPlugin();
        }, 500);
    });
    
    // Обновление при изменении метода оплаты
    $(document.body).on('change', 'input[name="payment_method"]', function() {
        $(document.body).trigger('update_checkout');
    });
    
    // После обновления checkout
    $(document.body).on('updated_checkout', function() {
        console.log('Checkout updated');
        // Обновляем список методов на случай, если цены/доступность изменились
        parseShippingMethods(); 
        // Повторно связываем CDEK
        integrateCdekPlugin();
    });
});
</script>