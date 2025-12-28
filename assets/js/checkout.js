/**
 * WooCommerce Checkout - iOS Style JavaScript
 * Обновление checkout при изменении данных + сохранение адресов
 */

(function($) {
    'use strict';

    const IOSCheckout = {
        
        // Debounce таймер
        updateTimer: null,
        
        /**
         * Init
         */
        init: function() {
            console.log('[iOS Checkout] Initializing...');
            
            this.bindEvents();
            this.initSavedAddresses();
            this.syncShipping();
            
            console.log('[iOS Checkout] Initialized successfully');
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;

            // ========================================
            // ОБНОВЛЕНИЕ CHECKOUT ПРИ ИЗМЕНЕНИИ ПОЛЕЙ
            // ========================================
            
            // Поля, которые триггерят обновление доставки
            const addressFields = '#billing_city, #billing_postcode, #billing_state, #billing_address_1, #billing_country';
            
            // При изменении адресных полей - обновляем checkout
            $(document).on('change', addressFields, function() {
                console.log('[iOS Checkout] Address field changed:', this.id);
                self.syncShipping();
                self.triggerUpdateCheckout();
            });
            
            // При вводе в адресные поля (с debounce)
            $(document).on('input', '#billing_city, #billing_postcode', function() {
                self.syncShipping();
                self.debouncedUpdate();
            });
            
            // При потере фокуса - сразу обновляем
            $(document).on('blur', addressFields, function() {
                self.syncShipping();
                self.triggerUpdateCheckout();
            });

            // ========================================
            // МЕТОДЫ ДОСТАВКИ
            // ========================================
            
            // Выбор метода доставки
            $(document).on('change', 'input[name^="shipping_method"]', function() {
                console.log('[iOS Checkout] Shipping method changed:', $(this).val());
                self.triggerUpdateCheckout();
            });

            // ========================================
            // МЕТОДЫ ОПЛАТЫ
            // ========================================
            
            $(document).on('change', '.ios-payment-method__input, input[name="payment_method"]', function() {
                console.log('[iOS Checkout] Payment method changed');
                $('.ios-payment-method').removeClass('ios-payment-method--active');
                $(this).closest('.ios-payment-method').addClass('ios-payment-method--active');
            });

            // ========================================
            // СОХРАНЁННЫЕ АДРЕСА
            // ========================================
            
            // Клик по сохранённому адресу
            $(document).on('click', '.ios-address-item', function(e) {
                if ($(e.target).closest('.ios-address-item__btn').length) return;
                
                console.log('[iOS Checkout] Saved address selected');
                
                $('.ios-address-item').removeClass('ios-address-item--active');
                $(this).addClass('ios-address-item--active');

                // Заполняем форму
                $('#billing_city').val($(this).data('city') || '');
                $('#billing_address_1').val($(this).data('address') || '');
                $('#billing_state').val($(this).data('state') || '');
                $('#billing_postcode').val($(this).data('postcode') || '');
                
                // Синхронизируем и обновляем
                self.syncShipping();
                self.triggerUpdateCheckout();
            });

            // Кнопка "Новый адрес"
            $(document).on('click', '#ios-new-address-btn, .ios-add-new-btn', function() {
                console.log('[iOS Checkout] New address clicked');
                
                $('.ios-address-item').removeClass('ios-address-item--active');
                $('#billing_city').val('').focus();
                $('#billing_address_1').val('');
                $('#billing_state').val('');
                $('#billing_postcode').val('');
                
                self.syncShipping();
            });

            // Удаление адреса
            $(document).on('click', '.ios-address-item__btn--delete', function(e) {
                e.stopPropagation();
                const id = $(this).data('id');
                const $item = $(this).closest('.ios-address-item');
                
                if (confirm('Удалить этот адрес?')) {
                    self.deleteAddress(id, $item);
                }
            });

            // ========================================
            // СОБЫТИЯ WOOCOMMERCE
            // ========================================
            
            // После обновления checkout
            $(document.body).on('updated_checkout', function() {
                console.log('[iOS Checkout] Checkout updated');
                self.updateTotalsDisplay();
            });
            
            // Ошибка обновления
            $(document.body).on('checkout_error', function() {
                console.log('[iOS Checkout] Checkout error');
            });

            // ========================================
            // АВТОСОХРАНЕНИЕ АДРЕСА
            // ========================================
            
            // Для авторизованных пользователей
            if (typeof iosCheckoutData !== 'undefined' && iosCheckoutData.isLoggedIn) {
                let saveTimeout;
                $('#billing_address_1, #billing_city').on('blur', function() {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(function() {
                        self.autoSaveAddress();
                    }, 2000);
                });
            }
        },

        /**
         * Trigger WooCommerce update_checkout
         */
        triggerUpdateCheckout: function() {
            console.log('[iOS Checkout] Triggering update_checkout...');
            $(document.body).trigger('update_checkout');
        },

        /**
         * Debounced update (для полей ввода)
         */
        debouncedUpdate: function() {
            const self = this;
            
            clearTimeout(this.updateTimer);
            this.updateTimer = setTimeout(function() {
                self.triggerUpdateCheckout();
            }, 800);
        },

        /**
         * Sync billing to shipping
         */
        syncShipping: function() {
            $('#shipping_first_name').val($('#billing_first_name').val());
            $('#shipping_last_name').val($('#billing_last_name').val());
            $('#shipping_address_1').val($('#billing_address_1').val());
            $('#shipping_city').val($('#billing_city').val());
            $('#shipping_state').val($('#billing_state').val());
            $('#shipping_postcode').val($('#billing_postcode').val());
            $('#shipping_country').val($('#billing_country').val() || 'RU');
        },

        /**
         * Update totals display after AJAX
         */
        updateTotalsDisplay: function() {
            // Обновляем итого в футере
            const $footerPrice = $('.ios-checkout-footer__price');
            const $orderTotal = $('.ios-order-total-row--total .ios-order-total-row__value');
            
            if ($orderTotal.length && $footerPrice.length) {
                $footerPrice.html($orderTotal.html());
            }
            
            // Обновляем стоимость доставки
            const $shippingCost = $('#ios-shipping-cost');
            const shippingTotal = $('.woocommerce-shipping-totals td').text().trim();
            
            if (shippingTotal && $shippingCost.length) {
                // Парсим цену из методов доставки
                const $checkedMethod = $('input[name^="shipping_method"]:checked');
                if ($checkedMethod.length) {
                    const $label = $('label[for="' + $checkedMethod.attr('id') + '"]');
                    const priceMatch = $label.find('.woocommerce-Price-amount').clone();
                    if (priceMatch.length) {
                        $shippingCost.html(priceMatch);
                    }
                }
            }
        },

        /**
         * Initialize saved addresses
         */
        initSavedAddresses: function() {
            // Проверяем наличие данных
            if (typeof iosCheckoutData === 'undefined') {
                console.log('[iOS Checkout] iosCheckoutData not defined, skipping saved addresses');
                return;
            }
            
            if (!iosCheckoutData.savedAddresses || !iosCheckoutData.savedAddresses.length) {
                console.log('[iOS Checkout] No saved addresses');
                return;
            }

            console.log('[iOS Checkout] Found', iosCheckoutData.savedAddresses.length, 'saved addresses');

            // Находим адрес по умолчанию и заполняем форму
            const defaultAddr = iosCheckoutData.savedAddresses.find(function(a) {
                return a.is_default === '1' || a.is_default === true;
            });
            
            if (defaultAddr) {
                console.log('[iOS Checkout] Filling default address:', defaultAddr.city);
                this.fillFormWithAddress(defaultAddr);
            }
        },

        /**
         * Fill form with address
         */
        fillFormWithAddress: function(addr) {
            $('#billing_city').val(addr.city || '');
            $('#billing_address_1').val(addr.address || '');
            $('#billing_state').val(addr.state || '');
            $('#billing_postcode').val(addr.postcode || '');
            
            this.syncShipping();
        },

        /**
         * Auto save address
         */
        autoSaveAddress: function() {
            if (typeof iosCheckoutData === 'undefined') return;
            
            const address = $('#billing_address_1').val();
            const city = $('#billing_city').val();
            
            if (!address || !city) return;
            
            console.log('[iOS Checkout] Auto-saving address...');

            $.post(iosCheckoutData.ajaxUrl, {
                action: 'ios_save_address',
                nonce: iosCheckoutData.nonce,
                address: address,
                city: city,
                state: $('#billing_state').val(),
                postcode: $('#billing_postcode').val()
            }, function(response) {
                if (response.success) {
                    console.log('[iOS Checkout] Address saved');
                }
            });
        },

        /**
         * Delete address
         */
        deleteAddress: function(id, $element) {
            if (typeof iosCheckoutData === 'undefined') return;
            
            $.post(iosCheckoutData.ajaxUrl, {
                action: 'ios_delete_address',
                nonce: iosCheckoutData.nonce,
                id: id
            }, function(response) {
                if (response.success) {
                    $element.fadeOut(200, function() {
                        $(this).remove();
                    });
                }
            });
        }
    };

    // ========================================
    // INIT
    // ========================================
    
    $(function() {
        if ($('.woocommerce-checkout').length) {
            IOSCheckout.init();
        }
    });

    // Export
    window.IOSCheckout = IOSCheckout;

})(jQuery);