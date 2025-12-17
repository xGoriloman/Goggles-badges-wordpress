jQuery(document).ready(function ($) {
    let isProcessing = false;
    const cartNonce = wc_cart_params.nonce;
    let updateTimeout = null;
    let updateQueue = [];

    // Обновление количества с задержкой
    function debouncedUpdateCartQuantity(cartKey, quantity) {
        // Отменяем предыдущий таймаут
        if (updateTimeout) {
            clearTimeout(updateTimeout);
        }
        
        // Сохраняем запрос в очередь
        const request = { cartKey, quantity };
        updateQueue.push(request);
        
        // Запускаем таймаут на 500ms
        updateTimeout = setTimeout(() => {
            if (updateQueue.length > 0) {
                // Берем последний запрос из очереди
                const lastRequest = updateQueue[updateQueue.length - 1];
                updateCartQuantity(lastRequest.cartKey, lastRequest.quantity);
                
                // Очищаем очередь
                updateQueue = [];
            }
        }, 500); // 500ms задержка
    }

    
    // Показываем уведомление
    function showNotification(message, type = 'success') {
        if (typeof window.flsNotifications !== 'undefined') {
            if (type === 'success') {
                window.flsNotifications.success(message);
            } else if (type === 'error') {
                window.flsNotifications.error(message);
            } else if (type === 'info') {
                window.flsNotifications.info(message);
            }
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
    
    // Показываем лоадер для строки
    function showRowLoader(cartKey) {
        const $row = $(`[data-cart-key="${cartKey}"]`).closest('.cart__row');
        if ($row.length) {
            $row.addClass('loading');
            $row.find('button, input').prop('disabled', true);
        }
        isProcessing = true;
    }
    
    // Скрываем лоадер для строки
    function hideRowLoader(cartKey) {
        const $row = $(`[data-cart-key="${cartKey}"]`).closest('.cart__row');
        if ($row.length) {
            $row.removeClass('loading');
            $row.find('button, input').prop('disabled', false);
        }
        isProcessing = false;
    }
    
    // Обновление количества товара
    function updateCartQuantity(cartKey, quantity) {
        if (isProcessing) return;
        
        showRowLoader(cartKey);
        showNotification('Обновление количества...', 'info');
        
        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_cart_quantity',
                cart_key: cartKey,
                quantity: quantity,
                nonce: cartNonce
            },
            success: function (response) {
              if (response.success) {
                  console.log('Ответ от сервера:', response);
                  
                  if (response.data.fragments) {
                      $.each(response.data.fragments, function (selector, html) {
                          console.log('Селектор:', selector, 'HTML длина:', html.length);
                          
                          const $target = $(selector);
                          console.log('Найден элемент по селектору:', $target.length);
                          
                          if ($target.length && html) {
                              // УБЕРИТЕ УСЛОВИЯ - просто заменяем всё
                              $target.replaceWith($.parseHTML(html));
                              console.log('Элемент заменен:', selector);
                          } else {
                              console.warn('Элемент не найден:', selector);
                          }
                      });
                  }
                  
                  // Обновляем шапку
                  if (response.data.cart_count !== undefined) {
                      updateHeaderCartCount(response.data.cart_count, response.data.cart_total);
                  }
                  
                  initCartRowButtons(cartKey);
                  showNotification(response.data.message, 'success');
              } else {
                  showNotification(response.data || 'Ошибка обновления', 'error');
              }
          },

            error: function (xhr, status, error) {
                console.error('AJAX ошибка:', error);
                showNotification('Ошибка соединения с сервером', 'error');
                setTimeout(() => location.reload(), 1000);
            },
            complete: function () {
                hideRowLoader(cartKey);
            }
        });
    }
    
    // Инициализация кнопок для конкретной строки
    function initCartRowButtons(cartKey) {
        const $row = $(`[data-cart-key="${cartKey}"]`).closest('.cart__row');
        if (!$row.length) return;
        
        const $input = $row.find('.quantity__field');
        if (!$input.length) return;
        
        const currentValue = parseInt($input.val()) || 1;
        const minValue = parseInt($input.data('min')) || 1;
        const maxValue = $input.data('max');
        
        // Кнопка минус
        const $minusBtn = $row.find('.quantity__button_minus');
        if (currentValue <= minValue) {
            $minusBtn.prop('disabled', true).addClass('disabled');
        } else {
            $minusBtn.prop('disabled', false).removeClass('disabled');
        }
        
        // Кнопка плюс
        const $plusBtn = $row.find('.quantity__button_plus');
        if (typeof maxValue !== 'undefined' && maxValue !== -1 && currentValue >= maxValue) {
            $plusBtn.prop('disabled', true).addClass('disabled');
        } else {
            $plusBtn.prop('disabled', false).removeClass('disabled');
        }
    }
    
    // Инициализация всех кнопок
    function initAllCartButtons() {
        $('.cart__row').each(function () {
            const $row = $(this);
            const $input = $row.find('.quantity__field');
            if (!$input.length) return;
            
            const cartKey = $input.data('cart-key');
            if (cartKey) {
                $row.attr('data-cart-key', cartKey);
                initCartRowButtons(cartKey);
            }
        });
    }
    
    // Обновление количества в шапке
    function updateHeaderCartCount(count, total) {
        // Ищем элементы с количеством товаров
        const selectors = [
            '.header-cart-count',
            '.cart-count',
            '.mini-cart-count',
            '.cart-icon-count',
            '.count'
        ];
        
        selectors.forEach(selector => {
            const $elements = $(selector);
            if ($elements.length) {
                $elements.each(function() {
                    const $el = $(this);
                    const originalText = $el.text();
                    const countMatch = originalText.match(/\d+/);
                    
                    if (countMatch) {
                        $el.text(originalText.replace(countMatch[0], count));
                    } else {
                        $el.text(count);
                    }
                });
            }
        });
        
        // Обновляем общую сумму
        const totalSelectors = [
            '.header-cart-total',
            '.cart-total',
            '.mini-cart-total'
        ];
        
        totalSelectors.forEach(selector => {
            const $elements = $(selector);
            if ($elements.length) {
                $elements.html(total);
            }
        });
        
        // Триггерим событие обновления корзины
        $(document.body).trigger('wc_update_cart');
    }

    // Функция удаления товара из корзины
    function removeCartItem(cartKey) {
        if (isProcessing) return;
        
        const $row = $(`[data-cart-key="${cartKey}"]`).closest('.cart__row');
        if (!$row.length) return;
        
        showRowLoader(cartKey);
        showNotification('Удаление товара...', 'info');
        
        // Анимация исчезновения
        $row.css({
            transition: 'all 0.3s ease',
            opacity: '0.5',
            transform: 'translateX(-20px)'
        });
        
        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'remove_cart_item',
                cart_key: cartKey,
                nonce: cartNonce
            },
            success: function (response) {
                if (response.success) {
                    // Если корзина пуста
                    if (response.data.empty) {
                        $row.fadeOut(300, function () {
                            if (response.data.fragments && response.data.fragments['.cart__container']) {
                                $('.cart__container').html(response.data.fragments['.cart__container']);
                            }
                        });
                    } else {
                        // Анимация удаления
                        $row.fadeOut(300, function () {
                            $row.remove();
                            
                            // Обновляем фрагменты
                            if (response.data.fragments) {
                                $.each(response.data.fragments, function (selector, html) {
                                    const $target = $(selector);
                                    if ($target.length && html) {
                                        $target.replaceWith(html);
                                    }
                                });
                            }
                            
                            // Обновляем шапку
                            if (response.data.cart_count !== undefined) {
                                updateHeaderCartCount(response.data.cart_count, response.data.cart_total);
                            }
                        });
                    }
                    
                    showNotification(response.data.message, 'success');
                } else {
                    // Откат анимации
                    $row.css({ opacity: '1', transform: 'translateX(0)' });
                    showNotification(response.data || 'Ошибка удаления', 'error');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX ошибка:', error);
                $row.css({ opacity: '1', transform: 'translateX(0)' });
                showNotification('Ошибка соединения с сервером', 'error');
            },
            complete: function () {
                hideRowLoader(cartKey);
                isProcessing = false;
            }
        });
    }
    
    // Кнопка уменьшения количества
    $(document).on('click', '.quantity__button_minus', function (e) {
        e.preventDefault();
        if (isProcessing) return;
        
        const cartKey = $(this).data('cart-key');
        if (!cartKey) return;
        
        const $input = $(`.quantity__field[data-cart-key="${cartKey}"]`);
        if (!$input.length) return;
        
        const currentValue = parseInt($input.val()) || 1;
        const minValue = parseInt($input.data('min')) || 1;
        
        if (currentValue > minValue) {
            const newValue = currentValue - 1;
            // Обновляем значение в поле немедленно
            $input.val(newValue);
            // Вызываем отложенное обновление
            debouncedUpdateCartQuantity(cartKey, newValue);
        } else if (currentValue === minValue && confirm('Удалить товар из корзины?')) {
            removeCartItem(cartKey);
        }
    });
    
    // Кнопка увеличения количества
    $(document).on('click', '.quantity__button_plus', function (e) {
      e.preventDefault();
      if (isProcessing) return;
      
      const cartKey = $(this).data('cart-key');
      if (!cartKey) return;
      
      const $input = $(`.quantity__field[data-cart-key="${cartKey}"]`);
      if (!$input.length) return;
      
      const currentValue = parseInt($input.val()) || 1;
      const maxValue = $input.data('max');
      
      if (typeof maxValue !== 'undefined' && maxValue !== -1 && currentValue >= maxValue) {
          showNotification(`Максимальное количество: ${maxValue}`, 'error');
          return;
      }
      
      const newValue = currentValue + 1;
      // Обновляем значение в поле немедленно
      $input.val(newValue);
      // Вызываем отложенное обновление
      debouncedUpdateCartQuantity(cartKey, newValue);
    });
    
    // Ручное изменение количества
    $(document).on('change', '.quantity__field', function () {
        if (isProcessing) return;
        
        const cartKey = $(this).data('cart-key');
        if (!cartKey) return;
        
        let newValue = parseInt($(this).val());
        
        if (isNaN(newValue) || newValue < 1) {
            newValue = 1;
            $(this).val(1);
        }
        
        const maxValue = $(this).data('max');
        if (typeof maxValue !== 'undefined' && maxValue !== -1 && newValue > maxValue) {
            newValue = maxValue;
            $(this).val(maxValue);
            showNotification(`Максимальное количество: ${maxValue}`, 'error');
        }
        
        const currentValue = parseInt($(this).attr('value')) || 1;
        if (newValue !== currentValue) {
            debouncedUpdateCartQuantity(cartKey, newValue);
        }
    });
    
    // Удаление товара
    $(document).on('click', '.cart__remove', function (e) {
        e.preventDefault();
        if (isProcessing) return;
        
        const cartKey = $(this).data('cart-key');
        if (!cartKey) return;
        
        if (confirm('Удалить товар из корзины?')) {
            removeCartItem(cartKey);
        }
    });
    
    // Инициализация при загрузке
    initAllCartButtons();
    
    // Обновляем при изменении фрагментов
    $(document.body).on('wc_fragment_refresh updated_cart_totals', function () {
        initAllCartButtons();
    });
});