jQuery(document).ready(function ($) {
  // Функция загрузки товаров
  function loadProducts(type, category) {
    var loader = $(".products-loader");
    var productsGrid = $("#products-grid");

    loader.show();
    productsGrid.css("opacity", "0.5");

    $.ajax({
      url: "/wp-admin/admin-ajax.php",
      type: "POST",
      data: {
        action: "load_filtered_products",
        type: type,
        category: category
      },
      success: function (response) {
        productsGrid.html(response);
        
        // Добавляем класс scroll-animate к новым товарам
        setTimeout(function() {
          productsGrid.find('.product').addClass('scroll-animate');
        }, 50);
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        productsGrid.html("<p>Произошла ошибка при загрузке товаров</p>");
      },
      complete: function () {
        loader.hide();
        productsGrid.css("opacity", "1");
        
        setTimeout(function () {
          if (typeof reinitScrollAnimation === "function") {
            reinitScrollAnimation();
          }
        }, 200);
      }
    });
  }

  // Переключение категорий товаров
  $(".popular-products__category a").on("click", function (e) {
    e.preventDefault();

    var $this = $(this);
    var category = $this.data("category");
    var type = $this.data("type");

    $(".popular-products__category a").removeClass("active");
    $this.addClass("active");

    loadProducts(type, category);

    
  });

  // Загрузка при первой загрузке страницы
  var activeCategory = $(".popular-products__category a.active");
  if (activeCategory.length) {
    loadProducts(activeCategory.data("type"), activeCategory.data("category"));
  } else {
    loadProducts("new", "new");
  }


  const tabsNav = document.querySelector('.popular-products__category');
  
  // Убедимся, что наш список существует на странице
  if (!tabsNav) {
    return;
  }
  
  const navItems = tabsNav.querySelectorAll('li');

  // Функция для перемещения подчеркивания
  function moveUnderline(targetItem) {
    if (!targetItem) return;
    
    // Вычисляем позицию и ширину целевого элемента
    const newLeft = targetItem.offsetLeft;
    const newWidth = targetItem.offsetWidth;

    // Устанавливаем CSS переменные на родительском элементе ul
    tabsNav.style.setProperty('--underline-left', `${newLeft}px`);
    tabsNav.style.setProperty('--underline-width', `${newWidth}px`);
  }

  // --- Инициализация: ставим подчеркивание под активным элементом при загрузке ---
  const initialActiveItem = tabsNav.querySelector('li.active');
  moveUnderline(initialActiveItem);


  // --- Обработчики кликов ---
  navItems.forEach(item => {
    item.addEventListener('click', (event) => {
      // event.preventDefault(); // Если это ссылки, которые не должны никуда вести

      // 1. Убираем класс 'active' у всех элементов
      navItems.forEach(i => i.classList.remove('active'));

      // 2. Добавляем класс 'active' к нажатому элементу
      event.currentTarget.classList.add('active');

      // 3. Перемещаем подчеркивание
      moveUnderline(event.currentTarget);
    });
  });
});