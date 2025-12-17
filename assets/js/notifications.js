// notifications.js - универсальная система уведомлений
class FlsNotifications {
  constructor() {
    this.container = null;
    this.init();
  }

  init() {
    this.createContainer();
    this.interceptWooCommerceMessages();
    this.interceptWordPressMessages();
    this.interceptFormSubmissions();
  }

  createContainer() {
    this.container = document.createElement("div");
    this.container.className = "fls-notifications-container";
    this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 320px;
        `;
    document.body.appendChild(this.container);
  }

  // Перехват сообщений WooCommerce
  interceptWooCommerceMessages() {
    // WooCommerce успешные сообщения
    const wooSuccessMessages = document.querySelectorAll(
      ".woocommerce-message, .woocommerce-success"
    );
    wooSuccessMessages.forEach((message) => {
      const text = message.textContent.trim();
      if (text) {
        this.success(text, "WooCommerce");
        message.style.display = "none";
      }
    });

    // WooCommerce ошибки
    const wooErrors = document.querySelectorAll(
      ".woocommerce-error, .wc-block-components-notice-banner.is-error"
    );
    wooErrors.forEach((error) => {
      const text = error.textContent.trim();
      if (text) {
        this.error(text, "Ошибка");
        error.style.display = "none";
      }
    });

    // WooCommerce предупреждения
    const wooInfo = document.querySelectorAll(
      ".woocommerce-info, .wc-block-components-notice-banner.is-info"
    );
    wooInfo.forEach((info) => {
      const text = info.textContent.trim();
      if (text) {
        this.info(text, "Информация");
        info.style.display = "none";
      }
    });
  }

  // Перехват сообщений WordPress
  interceptWordPressMessages() {
    // Сообщения WordPress
    const wpMessages = document.querySelectorAll(
      ".notice, .updated, .error, .warning"
    );
    wpMessages.forEach((message) => {
      const text = message.textContent.trim();
      if (text && !message.closest(".fls-notification")) {
        let type = "info";
        let title = "WordPress";

        if (
          message.classList.contains("error") ||
          message.classList.contains("notice-error")
        ) {
          type = "error";
          title = "Ошибка";
        } else if (
          message.classList.contains("warning") ||
          message.classList.contains("notice-warning")
        ) {
          type = "warning";
          title = "Внимание";
        } else if (
          message.classList.contains("success") ||
          message.classList.contains("notice-success")
        ) {
          type = "success";
          title = "Успех";
        } else if (
          message.classList.contains("updated") ||
          message.classList.contains("notice-updated")
        ) {
          type = "success";
          title = "Обновлено";
        }

        this.show({
          title: title,
          message: text,
          type: type,
        });

        message.style.display = "none";
      }
    });
  }

  // Перехват отправки форм
  interceptFormSubmissions() {
    // WooCommerce добавление в корзину
    document.addEventListener("click", (e) => {
      if (
        e.target.closest(".add_to_cart_button") ||
        e.target.closest(".single_add_to_cart_button")
      ) {
        setTimeout(() => {
          this.checkCartMessages();
        }, 1000);
      }
    });

    // WooCommerce купоны
    const couponForms = document.querySelectorAll(
      ".woocommerce-cart-form, .checkout_coupon"
    );
    couponForms.forEach((form) => {
      form.addEventListener("submit", () => {
        setTimeout(() => {
          this.checkCartMessages();
        }, 1500);
      });
    });
  }

  // Проверка сообщений корзины
  checkCartMessages() {
    setTimeout(() => {
      const messages = document.querySelectorAll(
        ".woocommerce-message, .woocommerce-error, .woocommerce-info"
      );
      messages.forEach((message) => {
        const text = message.textContent.trim();
        if (text) {
          let type = "info";
          if (message.classList.contains("woocommerce-message"))
            type = "success";
          if (message.classList.contains("woocommerce-error")) type = "error";

          this.show({
            message: text,
            type: type,
          });

          message.style.display = "none";
        }
      });
    }, 500);
  }

  // Основной метод показа уведомления
  show(options) {
    const {
      title = "",
      message = "",
      type = "info",
      duration = 4000,
      showClose = true,
    } = options;

    const notification = document.createElement("div");
    notification.className = `fls-notification ${type}`;

    // Иконки для разных типов
    const icons = {
      success: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`,
      error: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>`,
      warning: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`,
      info: `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>`,
    };

    notification.innerHTML = `
            <div class="fls-notification__icon ${type}">
                ${icons[type] || icons.info}
            </div>
            <div class="fls-notification__content">
                ${
                  title
                    ? `<div class="fls-notification__title">${title}</div>`
                    : ""
                }
                <div class="fls-notification__message">${message}</div>
            </div>
            ${
              showClose
                ? `
                <button class="fls-notification__close" type="button">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            `
                : ""
            }
        `;

    this.container.appendChild(notification);

    // Анимация появления
    setTimeout(() => {
      notification.classList.add("show");
    }, 10);

    // Закрытие по кнопке
    const closeBtn = notification.querySelector(".fls-notification__close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        this.hide(notification);
      });
    }

    // Автозакрытие
    if (duration > 0) {
      setTimeout(() => {
        this.hide(notification);
      }, duration);
    }

    return notification;
  }

  hide(notification) {
    notification.classList.remove("show");
    notification.classList.add("hide");

    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 400);
  }

  // Быстрые методы
  success(message, title = "Успех") {
    return this.show({ title, message, type: "success" });
  }

  error(message, title = "Ошибка") {
    return this.show({ title, message, type: "error" });
  }

  warning(message, title = "Внимание") {
    return this.show({ title, message, type: "warning" });
  }

  info(message, title = "Информация") {
    return this.show({ title, message, type: "info" });
  }

  // Очистка всех уведомлений
  clearAll() {
    const notifications = this.container.querySelectorAll(".fls-notification");
    notifications.forEach((notification) => {
      this.hide(notification);
    });
  }

  interceptWordPressForms() {
    // Контактные формы
    const contactForms = document.querySelectorAll(
      'form[action*="wp-admin/admin-ajax.php"]'
    );
    contactForms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        const formData = new FormData(form);
        const action = formData.get("action");

        // Отслеживаем отправку форм
        if (
          (action && action.includes("contact")) ||
          action.includes("submit")
        ) {
          setTimeout(() => {
            this.checkFormResponse(form);
          }, 1000);
        }
      });
    });
  }

  checkFormResponse(form) {
    // Проверяем ответы от форм
    const responseMessages = form.querySelectorAll(
      ".wpcf7-response-output, .form-response"
    );
    responseMessages.forEach((message) => {
      const text = message.textContent.trim();
      if (text) {
        let type = "info";
        if (
          message.classList.contains("wpcf7-validation-errors") ||
          message.classList.contains("error")
        ) {
          type = "error";
        } else if (
          message.classList.contains("wpcf7-mail-sent-ok") ||
          message.classList.contains("success")
        ) {
          type = "success";
        }

        this.show({
          message: text,
          type: type,
        });

        message.style.display = "none";
      }
    });
  }
}

// Создаем глобальный экземпляр
window.flsNotifications = new FlsNotifications();

// Интеграция с WooCommerce AJAX
jQuery(document).ready(function ($) {
  // Перехват AJAX событий WooCommerce
  $(document).on(
    "added_to_cart",
    function (event, fragments, cart_hash, $button) {
      window.flsNotifications.success("Товар добавлен в корзину");
    }
  );

  $(document).on(
    "removed_from_cart",
    function (event, fragments, cart_hash, $button) {
      window.flsNotifications.success("Товар удален из корзины");
    }
  );

  // Обработка ошибок WooCommerce
  $(document).ajaxError(function (event, xhr, settings, error) {
    if (
      settings.url.includes("admin-ajax.php") &&
      settings.data.includes("action=add_to_cart")
    ) {
      window.flsNotifications.error("Не удалось добавить товар в корзину");
    }
  });
});
