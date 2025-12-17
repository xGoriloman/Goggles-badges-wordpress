jQuery(document).ready(function ($) {
  // Функция для вариативных товаров
  function initVariableProduct() {
    const $form = $(".variations_form");
    if (!$form.length) return;

    let currentVariation = null;
    let currentQuantity = 1;

    // --- Весь ваш код для обновления интерфейса остается здесь без изменений ---
    // (Я его скопировал из вашего примера)

    function initVariableQuantity() {
      const $quantityInput = $form.find(".product-quantity-input");
      if ($quantityInput.length) {
        currentQuantity = parseInt($quantityInput.val()) || 1;
        $quantityInput.on("change input", function () {
          currentQuantity = parseInt($(this).val()) || 1;
          updateTotalPrice();
        });
        $form.find(".quantity__button").on("click", function () {
          setTimeout(() => {
            currentQuantity = parseInt($quantityInput.val()) || 1;
            updateTotalPrice();
          }, 10);
        });
      }
    }

    $(".variation-input").on("change", function () {
      const attributeName = $(this).data("attribute");
      const value = $(this).val();
      $form.find('select[name="attribute_' + attributeName + '"]').val(value).trigger("change");
    });

    $form.on("found_variation", function (event, variation) {
      currentVariation = variation;
      $(".single_add_to_cart_button").prop("disabled", false);
      const $quantityInput = $form.find('.product-quantity-input');
      if ($quantityInput.length) {
        $quantityInput.attr('min', variation.min_qty).attr('max', variation.max_qty);
        if (parseInt($quantityInput.val()) < variation.min_qty) {
          $quantityInput.val(variation.min_qty).trigger('change');
        }
      }
      updateTotalPrice();
    });

    $form.on("hide_variation", function () {
      currentVariation = null;
      $(".single_add_to_cart_button").prop("disabled", true);
      $(".variation-price-container .product__new-price").text("Выберите параметры");
      $(".variation-price-container .product__old-price").empty();
    });

    function updateTotalPrice() {
        if (!currentVariation) return;
        const $priceContainer = $(".variation-price-container");
        const $newPrice = $priceContainer.find(".product__new-price");
        const $oldPrice = $priceContainer.find(".product__old-price");
        const singlePrice = currentVariation.display_price;
        const singleRegularPrice = currentVariation.display_regular_price;
        const totalPrice = singlePrice * currentQuantity;
        const totalRegularPrice = singleRegularPrice * currentQuantity;

        if (singlePrice !== singleRegularPrice) {
            $newPrice.text(formatPrice(totalPrice) + " ₽");
            $oldPrice.text(formatPrice(totalRegularPrice) + " ₽");
            if (currentQuantity > 1) {
                $newPrice.attr("title", formatPrice(singlePrice) + " ₽ за шт.");
                $oldPrice.attr("title", formatPrice(singleRegularPrice) + " ₽ за шт.");
            } else {
                $newPrice.removeAttr("title");
                $oldPrice.removeAttr("title");
            }
        } else {
            $newPrice.text(formatPrice(totalPrice) + " ₽");
            $oldPrice.empty();
            if (currentQuantity > 1) {
                $newPrice.attr("title", formatPrice(singlePrice) + " ₽ за шт.");
            } else {
                $newPrice.removeAttr("title");
            }
        }
    }

    function formatPrice(price) {
      return new Intl.NumberFormat("ru-RU").format(price);
    }
    
    // --- Конец вашего кода ---

    // Инициализация
    initVariableQuantity();

    // ================================================================
    // ===== AJAX ДОБАВЛЕНИЕ В КОРЗИНУ ДЛЯ ВАРИАТИВНЫХ ТОВАРОВ =====
    // ================================================================
    $form.on("submit", function (e) {
      e.preventDefault();

      const $button = $(this).find(".single_add_to_cart_button");
      const variations = $form.data("product_variations");

      // 1. Собираем атрибуты НАПРЯМУЮ из ВЫБРАННЫХ РАДИО-КНОПОК. Это надежнее.
      const selectedAttributes = {};
      let attributesSelectedCount = 0;
      // Используем скрытые select'ы только чтобы посчитать, сколько всего атрибутов должно быть выбрано
      const totalAttributes = $form.find('.variations select').length;
      
      // Проходимся по каждому скрытому select'у, чтобы узнать имя атрибута
      $form.find('.variations select').each(function() {
        const attributeName = $(this).attr('name'); // Например, "attribute_pa_size"
        
        // Теперь ищем ВЫБРАННУЮ радио-кнопку с таким же именем
        const $checkedRadio = $form.find('input[name="' + attributeName + '"]:checked');
        
        // Если радио-кнопка для этого атрибута выбрана
        if ($checkedRadio.length) {
          const attributeValue = $checkedRadio.val();
          selectedAttributes[attributeName] = attributeValue; // Добавляем в наш объект
          attributesSelectedCount++;
        }
      });
      
      // Проверяем, все ли атрибуты выбраны
      if (attributesSelectedCount < totalAttributes) {
          if (window.flsNotifications) {
              window.flsNotifications.error("Пожалуйста, выберите все опции товара.");
          } else {
              alert("Пожалуйста, выберите все опции товара.");
          }
          return;
      }

      // 2. Функция для поиска подходящей вариации (остается без изменений)
      function findMatchingVariation(variations, attributes) {
        for (let i = 0; i < variations.length; i++) {
          const variation = variations[i];
          let match = true;
          for (const attr_name in variation.attributes) {
            const variation_attr_value = variation.attributes[attr_name];
            const selected_attr_value = attributes[attr_name];
            if (variation_attr_value !== '' && variation_attr_value !== selected_attr_value) {
              match = false;
              break;
            }
          }
          if (match) return variation;
        }
        return null;
      }

      // 3. Находим нашу вариацию
      const matchedVariation = findMatchingVariation(variations, selectedAttributes);

      if (!matchedVariation) {
        if (window.flsNotifications) {
          window.flsNotifications.error("Выбранная комбинация недоступна.");
        } else {
          alert("Выбранная комбинация недоступна.");
        }
        return;
      }

      // 4. Собираем все данные для отправки на сервер
      const data = {
        action: 'woocommerce_ajax_add_to_cart',
        'add-to-cart': $form.find('input[name="add-to-cart"]').val(),
        product_id: $form.find('input[name="product_id"]').val(),
        quantity: $form.find('input[name="quantity"]').val() || 1,
        variation_id: matchedVariation.variation_id,
        'woocommerce-add-to-cart-nonce': $form.find('#woocommerce-add-to-cart-nonce').val()
      };
      for (const key in selectedAttributes) {
        data[key] = selectedAttributes[key];
      }

      $button.prop("disabled", true).text("Добавляем...");

      // 5. Отправляем AJAX запрос
      $.ajax({
        url: wc_add_to_cart_params.ajax_url,
        type: "POST",
        data: data,
        success: function (response) {
          if (!response || response.error) {
            var errorMessage = response.data && response.data.notice ? $(response.data.notice).text() : "Не удалось добавить товар.";
            if (window.flsNotifications) {
              window.flsNotifications.error(errorMessage);
            } else {
              alert(errorMessage);
            }
          } else {
            $(document.body).trigger("wc_fragment_refresh");
            $(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $button]);
            if (window.flsNotifications) {
              window.flsNotifications.success("Товар добавлен в корзину!");
            } else {
              alert("Товар добавлен в корзину!");
            }
          }
          $button.prop("disabled", false).text($button.data("original-text") || "В корзину");
        },
        error: function () {
          alert("Произошла ошибка при добавлении товара в корзину.");
          $button.prop("disabled", false).text($button.data("original-text") || "В корзину");
        }
      });
    });

    // Сохраняем оригинальный текст кнопки
    $form.find(".single_add_to_cart_button").each(function () {
      $(this).data("original-text", $(this).text());
    });
  }

  // Инициализация при загрузке
  initVariableProduct();
});