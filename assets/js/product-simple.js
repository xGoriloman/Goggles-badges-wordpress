jQuery(document).ready(function ($) {
  // Функция для простых товаров
  function initSimpleProduct() {
    const $form = $("form.cart");
    if (!$form.length) return;

    // ... (весь ваш код для обновления цены остается без изменений) ...

    const $quantityInput = $form.find(".product-quantity-input");
    const $newPrice = $form.find(".product__new-price");
    const $oldPrice = $form.find(".product__old-price");

    if ($quantityInput.length) {
      // ... (ваш код кнопок +/- и обновления цены) ...
      // Он здесь не показан для краткости, но он должен остаться
    }

    // AJAX добавление в корзину для простых товаров
    $form.on("submit", function (e) {
      e.preventDefault();

      var $button = $(this).find(".single_add_to_cart_button");
      var product_id = $button.val();
      var quantity = $form.find("input[name=quantity]").val() || 1;

      // Формируем данные для стандартного обработчика WooCommerce
      var data = {
        action: 'woocommerce_add_to_cart', // Используем стандартное действие!
        product_id: product_id,
        quantity: quantity,
      };

      // Получаем nonce для безопасности
      var nonceField = $form.find('#woocommerce-add-to-cart-nonce');
      if (nonceField.length) {
        data[nonceField.attr('name')] = nonceField.val();
      }

      // Блокируем кнопку на время добавления
      $button.prop("disabled", true).text("Добавляем...");

      $.ajax({
        url: wc_add_to_cart_params.ajax_url,
        type: "POST",
        data: data, // Отправляем наш объект с данными
        success: function (response) {
            console.log(response);
          if (!response || response.error) {
            // Показываем ошибку
            // WooCommerce обычно возвращает html ошибки в response.data
            var errorMessage = response.data ? $(response.data).text() : 'Произошла ошибка';
            if (window.flsNotifications) {
              window.flsNotifications.error(errorMessage);
            } else {
              alert(errorMessage);
            }
          } else {
            // Обновляем фрагменты корзины (мини-корзина и т.д.)
            $(document.body).trigger("wc_fragment_refresh");
            $(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $button]); // Важный триггер!

            // Показываем уведомление об успехе
            if (window.flsNotifications) {
              window.flsNotifications.success("Товар добавлен в корзину!");
            } else {
              alert("Товар добавлен в корзину!");
            }
          }

          // Разблокируем кнопку
          $button.prop("disabled", false).text($button.data("original-text") || "В корзину");
        },
        error: function () {
          alert("Произошла ошибка при добавлении товара в корзину");
          $button.prop("disabled", false).text($button.data("original-text") || "В корзину");
        },
      });
    });

    // Сохраняем оригинальный текст кнопки
    $form.find(".single_add_to_cart_button").each(function () {
      $(this).data("original-text", $(this).text());
    });
  }

  // Инициализация при загрузке
  initSimpleProduct();
});