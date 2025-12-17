/**
 * GNB CDEK Integration JS
 * Функции для работы с CDEK и WooCommerce
 */

(function($) {
    'use strict';

    // CDEK Point Selector Modal
    const CDEKSelector = {
        init: function() {
            this.bindEvents();
            this.loadPoints();
        },

        bindEvents: function() {
            // Клик по кнопке выбора пункта CDEK
            $(document).on('click', '[data-action="select_cdek_point"]', (e) => {
                e.preventDefault();
                this.openModal();
            });

            // Выбор пункта выдачи
            $(document).on('click', '.gnb-cdek-points__item', (e) => {
                const $item = $(e.currentTarget);
                const pointId = $item.data('point-id');
                
                this.selectPoint(pointId);
            });

            // Закрытие модального окна
            $(document).on('click', '[data-action="close_cdek_modal"]', (e) => {
                e.preventDefault();
                this.closeModal();
            });
        },

        openModal: function() {
            const $modal = $('<div class="gnb-cdek-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: flex-end; z-index: 9999;"></div>');
            const $content = $('<div style="background: white; width: 100%; border-radius: 24px 24px 0 0; padding: 20px; max-height: 80vh; overflow-y: auto;"></div>');
            
            $content.html(`
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 18px; font-weight: 700; text-transform: uppercase;">Выберите пункт</h3>
                    <button type="button" data-action="close_cdek_modal" style="background: none; border: none; font-size: 24px; cursor: pointer;">✕</button>
                </div>
                <div id="cdek-points-list" class="gnb-cdek-points__list">
                    <div style="text-align: center; padding: 20px;">
                        <div class="gnb-loading"></div>
                    </div>
                </div>
            `);

            $modal.append($content);
            $('body').append($modal);

            this.loadPoints();

            // Close on background click
            $modal.on('click', function(e) {
                if (e.target === this) {
                    $(this).remove();
                }
            });
        },

        closeModal: function() {
            $('.gnb-cdek-modal').remove();
        },

        loadPoints: function() {
            const city = $('input[name="billing_city"]').val() || '2728'; // Default Moscow

            $.ajax({
                type: 'POST',
                url: GNB.ajaxUrl,
                data: {
                    action: 'gnb_get_cdek_points',
                    city: city,
                    nonce: GNB.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.renderPoints(response.data);
                    }
                },
                error: (error) => {
                    console.error('CDEK Points Error:', error);
                }
            });
        },

        renderPoints: function(points) {
            const $list = $('#cdek-points-list');
            
            if (!points || points.length === 0) {
                $list.html('<p style="text-align: center; color: #999;">Пункты не найдены</p>');
                return;
            }

            let html = '';
            
            points.forEach(point => {
                html += `
                    <div class="gnb-cdek-points__item" data-point-id="${point.code}">
                        <div class="gnb-cdek-points__address">${point.code}, ${point.city}</div>
                        <div class="gnb-cdek-points__info">
                            <span><strong>Адрес:</strong> ${point.address}</span>
                            <span><strong>Время работы:</strong> ${point.work_time}</span>
                            <span><strong>Телефон:</strong> ${point.phone}</span>
                        </div>
                    </div>
                `;
            });

            $list.html(html);
        },

        selectPoint: function(pointId) {
            $.ajax({
                type: 'POST',
                url: GNB.ajaxUrl,
                data: {
                    action: 'gnb_save_cdek_point',
                    point_id: pointId,
                    nonce: GNB.nonce
                },
                success: () => {
                    this.closeModal();
                    $(document.body).trigger('update_checkout');
                    
                    // Show success notification
                    this.showNotification('Пункт выдачи выбран', 'success');
                },
                error: () => {
                    this.showNotification('Ошибка при выборе пункта', 'error');
                }
            });
        },

        showNotification: function(message, type = 'info') {
            const bgColor = type === 'success' ? '#16a34a' : '#dc2626';
            const notification = $(`
                <div style="position: fixed; bottom: 80px; left: 16px; right: 16px; background: ${bgColor}; color: white; padding: 16px; border-radius: 12px; z-index: 10000; animation: slideUp 0.3s ease;">
                    ${message}
                </div>
            `);

            $('body').append(notification);

            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Checkout Form Handler
    const CheckoutForm = {
        init: function() {
            this.bindEvents();
            this.validateForm();
        },

        bindEvents: function() {
            // Format phone input
            $(document).on('input', '#billing_phone', function() {
                const value = $(this).val().replace(/\D/g, '');
                if (value.length > 0) {
                    $(this).val(CDEKSelector.formatPhone(value));
                }
            });

            // Update checkout on field change
            $('form.checkout').on('change', 'input, select, textarea', function() {
                $(document.body).trigger('update_checkout');
            });

            // Handle place order button
            $(document).on('click', '#place_order', function(e) {
                if (!CheckoutForm.validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        validateForm: function() {
            const firstName = $('input[name="billing_first_name"]').val().trim();
            const lastName = $('input[name="billing_last_name"]').val().trim();
            const phone = $('input[name="billing_phone"]').val().trim();
            const address = $('input[name="billing_address_1"]').val().trim();
            const city = $('input[name="billing_city"]').val().trim();

            if (!firstName || !lastName) {
                CDEKSelector.showNotification('Пожалуйста, заполните ФИО', 'error');
                return false;
            }

            if (!phone || phone.length < 11) {
                CDEKSelector.showNotification('Пожалуйста, заполните корректный номер телефона', 'error');
                return false;
            }

            if (!address || !city) {
                CDEKSelector.showNotification('Пожалуйста, заполните адрес доставки', 'error');
                return false;
            }

            return true;
        }
    };

    // Format phone number
    CDEKSelector.formatPhone = function(phone) {
        if (phone.length === 11) {
            return '+' + phone[0] + ' (' + phone.substring(1, 4) + ') ' + 
                   phone.substring(4, 7) + '-' + phone.substring(7, 9) + '-' + phone.substring(9);
        }
        return phone;
    };

    // Initialize on document ready
    $(document).ready(function() {
        CDEKSelector.init();
        CheckoutForm.init();

        // Custom WooCommerce checkout updates
        $(document.body).on('checkout_error', function() {
            $('html, body').animate({
                scrollTop: $('.woocommerce-checkout').offset().top - 100
            }, 500);
        });

        $(document.body).on('applied_coupon', function() {
            CDEKSelector.showNotification('Купон применен', 'success');
        });
    });

    // Expose to global scope
    window.GNBCDEKSelector = CDEKSelector;
    window.GNBCheckoutForm = CheckoutForm;

})(jQuery);