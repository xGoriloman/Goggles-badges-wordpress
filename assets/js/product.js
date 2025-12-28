jQuery(document).ready(function ($) {
  // Общие функции для всех типов товаров
  function initProductQuantity() {
    $(".product-quantity-input").each(function () {
      const $input = $(this);
      const $minusBtn = $input
        .closest(".quantity")
        .find(".quantity__button-minus");
      const $plusBtn = $input
        .closest(".quantity")
        .find(".quantity__button-plus");

      let currentQuantity = parseInt($input.val()) || 1;
      const min = parseInt($input.attr("min")) || 1;
      const max = parseInt($input.attr("max")) || 9999;

      // Обработка изменения количества
      $input.on("change input", function () {
        currentQuantity = parseInt($(this).val()) || 1;

        // Валидация
        if (currentQuantity < min) currentQuantity = min;
        if (max !== -1 && currentQuantity > max) currentQuantity = max;

        $(this).val(currentQuantity);
        updateQuantityButtonsState();
      });

      // Кнопки +/-
      $minusBtn.on("click", function () {
        let newValue = currentQuantity - 1;
        if (newValue < min) newValue = min;

        $input.val(newValue);
        currentQuantity = newValue;
        updateQuantityButtonsState();
      });

      $plusBtn.on("click", function () {
        let newValue = currentQuantity + 1;
        if (max !== -1 && newValue > max) newValue = max;

        $input.val(newValue);
        currentQuantity = newValue;
        updateQuantityButtonsState();
      });

      function updateQuantityButtonsState() {
        if (currentQuantity <= min) {
          $minusBtn.prop("disabled", true).addClass("disabled");
        } else {
          $minusBtn.prop("disabled", false).removeClass("disabled");
        }

        if (max !== -1 && currentQuantity >= max) {
          $plusBtn.prop("disabled", true).addClass("disabled");
        } else {
          $plusBtn.prop("disabled", false).removeClass("disabled");
        }
      }

      updateQuantityButtonsState();
    });
  }

  // Инициализация количества
  initProductQuantity();
});

document.addEventListener('DOMContentLoaded', function() {
    
});