// app.js

let scrollObserver = null;

// Инициализация при загрузке страницы
document.addEventListener("DOMContentLoaded", function () {
  initScrollAnimation();
  initCounters();
});

// Основная функция для анимации скролла
function initScrollAnimation() {
  // Если наблюдатель уже существует, отключаем его
  if (scrollObserver) {
    scrollObserver.disconnect();
  }

  // Создаем нового наблюдателя
  scrollObserver = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          const element = entry.target;
          
          // Проверяем, не анимирован ли уже элемент
          if (element.classList.contains("animated")) {
            scrollObserver.unobserve(element);
            return;
          }

          const delay = element.getAttribute("data-delay") || 0;

          setTimeout(function () {
            element.classList.add("animated");

            // Запускаем счетчик если есть
            if (
              element.classList.contains("stat__number") &&
              element.getAttribute("data-count") &&
              !element.classList.contains("counted")
            ) {
              element.classList.add("counted");
              animateCounter(element);
            }
          }, parseInt(delay));

          // Перестаем следить за элементом после анимации
          scrollObserver.unobserve(element);
        }
      });
    },
    {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    }
  );

  // Переподключаем наблюдение за всеми элементами
  reconnectObservers();
}

// Функция переподключения наблюдателей
function reconnectObservers() {
  const animatedElements = document.querySelectorAll(
    ".scroll-animate:not(.animated)"
  );
  
  console.log(`Reconnecting ${animatedElements.length} elements to observer`);
  
  // Начинаем наблюдать за всеми элементами
  animatedElements.forEach(function (element) {
    scrollObserver.observe(element);
  });

  // Анимируем элементы которые уже видны
  checkVisibleElements();
}

// Проверка видимых элементов
function checkVisibleElements() {
  const animatedElements = document.querySelectorAll(
    ".scroll-animate:not(.animated)"
  );
  
  animatedElements.forEach(function (element) {
    const rect = element.getBoundingClientRect();
    const isVisible = (
      rect.top < window.innerHeight - 100 && 
      rect.bottom > 0
    );
    
    if (isVisible) {
      const delay = element.getAttribute("data-delay") || 0;
      setTimeout(function () {
        if (!element.classList.contains("animated")) {
          element.classList.add("animated");
          
          if (
            element.classList.contains("stat__number") &&
            element.getAttribute("data-count") &&
            !element.classList.contains("counted")
          ) {
            element.classList.add("counted");
            animateCounter(element);
          }
        }
      }, parseInt(delay));
    }
  });
}

// Функция для анимации счетчиков
function animateCounter(element) {
  const target = parseInt(element.getAttribute("data-count"));
  const duration = parseInt(element.getAttribute("data-duration")) || 2000;
  const increment = target / (duration / 16);
  let current = 0;

  const timer = setInterval(function () {
    current += increment;
    if (current >= target) {
      element.textContent = formatNumber(target);
      clearInterval(timer);
    } else {
      element.textContent = formatNumber(Math.floor(current));
    }
  }, 16);
}

// Форматирование чисел
function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

// Инициализация счетчиков
function initCounters() {
  const counters = document.querySelectorAll(".stat__number[data-count]");
  console.log(`Found ${counters.length} counters to animate`);
}

// Функция для переинициализации анимации после AJAX
function reinitScrollAnimation() {
  console.log("Reinitializing scroll animation for new elements...");
  
  // Ждем пока DOM обновится после AJAX
  setTimeout(() => {
    initScrollAnimation();
  }, 150);
}

// Делаем функции глобальными
window.reinitScrollAnimation = reinitScrollAnimation;
window.initScrollAnimation = initScrollAnimation;