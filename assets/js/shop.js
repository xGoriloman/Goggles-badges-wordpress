jQuery(document).ready(function ($) {
    // Переменные для управления состоянием
    let selectedCategories = [];
    let selectedBrands = [];
    let selectedSizes = [];
    let currentPage = 1;
    let isLoading = false;
    let filterTimeout;
    
    // Текущая выбранная родительская категория
    let currentParentCategory = {
        id: null,
        slug: null,
        name: null,
        level: 1 // 1, 2 или 3
    };
    
    // Инициализация объекта для выбранных элементов
    let selectedItems = {
        categories: {},
        brands: {},
        sizes: {}
    };
    
    // Инициализация из URL
    function initFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Категории из URL
        const urlCategories = urlParams.getAll('category[]') || [];
        urlCategories.forEach(slug => {
            if (!selectedCategories.includes(slug)) {
                selectedCategories.push(slug);
            }
        });
        
        // Бренды из URL
        const urlBrands = urlParams.getAll('brand[]') || [];
        urlBrands.forEach(slug => {
            if (!selectedBrands.includes(slug)) {
                selectedBrands.push(slug);
            }
        });
        
        // Размеры из URL
        const urlSizes = urlParams.getAll('size[]') || [];
        urlSizes.forEach(slug => {
            if (!selectedSizes.includes(slug)) {
                selectedSizes.push(slug);
            }
        });
        
        // Обновляем selectedItems на основе массивов
        updateSelectedItemsFromArrays();
        updateCheckboxUI();
        updateSelectedItemsDisplay();
    }
    
    // Обновление UI чекбоксов
    function updateCheckboxUI() {
        // Категории
        $('.category-checkbox').each(function() {
            const slug = $(this).val();
            const isChecked = selectedCategories.includes(slug);
            $(this).prop('checked', isChecked);
            
            // Обновляем визуальное состояние
            const $parent = $(this).closest('.sidebar__checkbox, .category-checkbox-item');
            if ($parent.length) {
                $parent.find('.checkbox-label').toggleClass('active', isChecked);
                $parent.toggleClass('checked', isChecked);
            }
        });
        
        // Бренды
        $('.brand-checkbox').each(function() {
            const slug = $(this).val();
            const isChecked = selectedBrands.includes(slug);
            $(this).prop('checked', isChecked);
            $(this).closest('.sidebar__brand-checkbox').toggleClass('checked', isChecked);
        });
        
        // Размеры
        $('.size-checkbox').each(function() {
            const slug = $(this).val();
            const isChecked = selectedSizes.includes(slug);
            $(this).prop('checked', isChecked);
            $(this).closest('.sidebar__size-checkbox').toggleClass('checked', isChecked);
        });
    }
    
    // Обновление отображения выбранных элементов
    function updateSelectedItemsDisplay() {
        const $wrapper = $('.selected-categories-wrapper');
        const $list = $('.selected-categories-list');
        
        $list.empty();
        
        // Собираем все выбранные элементы
        const allSelected = {};
        Object.assign(allSelected, selectedItems.categories, selectedItems.brands, selectedItems.sizes);
        
        if (Object.keys(allSelected).length > 0) {
            $wrapper.show();
            
            // Добавляем каждый выбранный элемент
            Object.entries(allSelected).forEach(([slug, name]) => {
                // Определяем тип
                let type = 'category';
                if (selectedItems.brands[slug]) type = 'brand';
                if (selectedItems.sizes[slug]) type = 'size';
                
                $list.append(`
                    <div class="selected-category-item selected-category-remove" data-slug="${slug}" data-type="${type}">
                        <span>${name}</span>
                    </div>
                `);
            });
        } else {
            $wrapper.hide();
        }
    }
    
    // Добавление выбранного элемента
    function addSelectedItem(type, slug, name) {
        if (type === 'category') {
            if (!selectedCategories.includes(slug)) {
                selectedCategories.push(slug);
            }
        } else if (type === 'brand') {
            if (!selectedBrands.includes(slug)) {
                selectedBrands.push(slug);
            }
        } else if (type === 'size') {
            if (!selectedSizes.includes(slug)) {
                selectedSizes.push(slug);
            }
        }
        
        // Обновляем selectedItems из массивов
        updateSelectedItemsFromArrays();
        updateSelectedItemsDisplay();
        filterProducts();
    }
    
    // Удаление выбранного элемента
    function removeSelectedItem(type, slug) {
        if (type === 'category') {
            selectedCategories = selectedCategories.filter(item => item !== slug);
        } else if (type === 'brand') {
            selectedBrands = selectedBrands.filter(item => item !== slug);
        } else if (type === 'size') {
            selectedSizes = selectedSizes.filter(item => item !== slug);
        }
        
        // Обновляем selectedItems из массивов
        updateSelectedItemsFromArrays();
        updateCheckboxUI();
        updateSelectedItemsDisplay();
        filterProducts();
    }
    
    // Очистка всех выбранных элементов
    function clearAllSelectedItems() {
        selectedCategories = [];
        selectedBrands = [];
        selectedSizes = [];
        
        // Обновляем selectedItems из массивов
        updateSelectedItemsFromArrays();
        updateCheckboxUI();
        updateSelectedItemsDisplay();
        filterProducts();
    }
    
    // Загрузка подкатегорий
    function loadSubcategories(categoryId, categoryName, level = 2) {
        currentParentCategory = {
            id: categoryId,
            name: categoryName,
            level: level
        };
        
        // Обновляем название родительской категории в шапке
        $('.parent-category-name').text(categoryName);
        
        // Показываем уровень 2-3
        $('.categories-level-1').removeClass('active');
        $('.categories-level-2-3').show();
        
        $.ajax({
            url: ajax_filter.ajax_url,
            type: 'POST',
            data: {
                action: 'pfunxtion',
                category_id: categoryId,
                level: level,
                nonce: ajax_filter.nonce
            },
            beforeSend: function() {
                if (level === 2) {
                    $('.subcategories-list').html('<div class="loading">Загрузка...</div>');
                } else if (level === 3) {
                    $('.subsubcategories-list').html('<div class="loading">Загрузка...</div>');
                }
            },
            success: function(response) {
                if (response.success) {
                    if (level === 2) {
                        displaySubcategories(response.data, categoryName);
                    } else if (level === 3) {
                        displaySubSubcategories(response.data, categoryName);
                    }
                }
            }
        });
    }
    
    // Отображение подкатегорий (уровень 2)
    function displaySubcategories(subcategories, parentName) {
        const $list = $('.subcategories-list');
        $list.empty();
                
        if (subcategories && subcategories.length > 0) {
            subcategories.forEach((subcat) => {
                const isChecked = selectedCategories.includes(subcat.slug);
                const hasChildren = subcat.has_children;
                
                if (hasChildren) {
                    // Подкатегория с под-подкатегориями (кнопка)
                    $list.append(`
                        <button type="button" 
                                class="category-item-btn has-children ${isChecked ? 'active' : ''}"
                                data-category-id="${subcat.term_id}"
                                data-category-slug="${subcat.slug}"
                                data-category-name="${subcat.name}">
                            ${subcat.name}
                        </button>
                    `);
                } else {
                    // Подкатегория без детей (чекбокс)
                    $list.append(`
                        <div class="category-checkbox-item">
                            <input type="checkbox" 
                                   class="category-checkbox subcategory-checkbox"
                                   id="subcat-${subcat.slug}"
                                   value="${subcat.slug}"
                                   data-category-name="${subcat.name}"
                                   ${isChecked ? 'checked' : ''}>
                            <label for="subcat-${subcat.slug}" class="checkbox-label">
                                ${subcat.name}
                            </label>
                        </div>
                    `);
                }
            });
        } else {
            $list.append('<div class="no-subcategories">Нет подкатегорий</div>');
        }
        
        // Показываем уровень 2
        $('.subcategories-level-2').show();
        $('.subcategories-level-3').hide();
    }
    
    // Отображение под-подкатегорий (уровень 3)
    function displaySubSubcategories(subcategories, parentName) {
        const $list = $('.subsubcategories-list');
        $list.empty();
        
        if (subcategories && subcategories.length > 0) {
            subcategories.forEach((subcat) => {
                const isChecked = selectedCategories.includes(subcat.slug);
                
                $list.append(`
                    <div class="category-checkbox-item">
                        <input type="checkbox" 
                               class="category-checkbox subsubcategory-checkbox"
                               id="subsubcat-${subcat.slug}"
                               value="${subcat.slug}"
                               data-category-name="${subcat.name}"
                               ${isChecked ? 'checked' : ''}>
                        <label for="subsubcat-${subcat.slug}" class="checkbox-label">
                            ${subcat.name}
                        </label>
                    </div>
                `);
            });
        } else {
            $list.append('<div class="no-subsubcategories">Нет дополнительных категорий</div>');
        }
        
        // Показываем уровень 3
        $('.subcategories-level-3').show();
    }
    
    // Возврат на уровень 2
    function goBackToLevel2() {
        $('.subcategories-level-3').hide();
        $('.subcategories-level-2').show();
        currentParentCategory.level = 2;
    }
    
    // Возврат к основным категориям
    function goBackToMainCategories() {
        $('.categories-level-1').addClass('active');
        $('.categories-level-2-3').hide();
        currentParentCategory = {
            id: null,
            slug: null,
            name: null,
            level: 1
        };
    }
    
    // Основная функция фильтрации
    function filterProducts() {
        if (isLoading) return;
        
        clearTimeout(filterTimeout);
        
        filterTimeout = setTimeout(function() {
            isLoading = true;
            
            const formData = new FormData();
            
            // Добавляем категории
            Object.keys(selectedItems.categories).forEach(category => {
                formData.append('category[]', category);
            });
            
            // Добавляем бренды
            Object.keys(selectedItems.brands).forEach(brand => {
                formData.append('brand[]', brand);
            });
            
            // Добавляем размеры
            Object.keys(selectedItems.sizes).forEach(size => {
                formData.append('size[]', size);
            });
            
            // Сортировка
            let orderby = 'date';
            const $sortRadio = $('#sidebar-form-sort input[name="orderby"]:checked');
            if ($sortRadio.length) {
                orderby = $sortRadio.val();
            }
            formData.set("orderby", orderby);
            
            // AJAX параметры
            formData.set("page", currentPage);
            formData.set("nonce", ajax_filter.nonce);
            formData.set("action", "filter_products");
            
            // Создаем лоадер SVG
            const loaderSVG = `
                <div class="loader-overlay" style="
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(255, 255, 255, 0.8);
                    z-index: 100;
                ">
                    <svg class="spinner" width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation: rotate 1s linear infinite;">
                        <g clip-path="url(#clip-path)">
                            <g transform="matrix(0.012 0 0 0.012 12 12)">
                                <foreignObject x="-1083.33" y="-1083.33" width="2166.67" height="2166.67">
                                    <div xmlns="http://www.w3.org/1999/xhtml" style="
                                        background: conic-gradient(from 90deg, rgba(92, 92, 92, 0) 0deg, rgba(51, 50, 50, 0) 0.036deg, rgba(25, 25, 25, 1) 360deg);
                                        height: 100%;
                                        width: 100%;
                                        opacity: 1;
                                        animation: rotate 1s linear infinite;
                                    "></div>
                                </foreignObject>
                            </g>
                        </g>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 24C18.6274 24 24 18.6274 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18.6274 5.37258 24 12 24ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" />
                        <defs>
                            <clipPath id="clip-path">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 24C18.6274 24 24 18.6274 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18.6274 5.37258 24 12 24ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" />
                            </clipPath>
                        </defs>
                    </svg>
                </div>
            `;
            
            // Добавляем стили для анимации (если их еще нет)
            if (!$('#loader-styles').length) {
                $('head').append(`
                    <style id="loader-styles">
                        @keyframes rotate {
                            from { transform: rotate(0deg); }
                            to { transform: rotate(360deg); }
                        }
                        
                        .loader-overlay {
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            width: 100% !important;
                            height: 100% !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            background: rgba(255, 255, 255, 0.9) !important;
                            z-index: 1000 !important;
                            backdrop-filter: blur(2px);
                        }
                        
                        .spinner {
                            animation: rotate 1s linear infinite !important;
                        }
                        
                        #products-container {
                            position: relative;
                            min-height: 300px;
                        }
                        
                        #products-container.loading {
                            opacity: 0.7;
                        }
                    </style>
                `);
            }
            
            // Показываем лоадер
            $("#products-container").addClass("loading");
            $("#products-container").append(loaderSVG);
            
            $.ajax({
                url: ajax_filter.ajax_url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $("#products-container").html(response.data.html);
                        
                        // Обновляем URL
                        updateURL();
                    
                        // Обновляем текст в кнопке сортировки
                        updateSortButtonText(orderby);
                        
                        // Пагинация
                        if (response.data.pagination) {
                            $('#products-pagination').html(response.data.pagination);
                        }

                        // Анимация
                        setTimeout(() => {
                            $("#products-container").find('.product').addClass('scroll-animate');
                        }, 50);
                        
                        // Закрываем фильтр на мобильных
                        if (window.innerWidth < 768) {
                            $('.sidebar-filter').removeClass('active');
                        }
                    }
                },
                complete: function() {
                    // Убираем лоадер
                    $(".loader-overlay").remove();
                    $("#products-container").removeClass("loading");
                    isLoading = false;
                    $("#products-container").css("opacity", "1");
                    
                    setTimeout(() => {
                        if (typeof reinitScrollAnimation === "function") {
                            reinitScrollAnimation();
                        }
                    }, 200);
                }
            });
        }, 300);
    }

	// Функция обновления текста в кнопке сортировки
	function updateSortButtonText(orderby) {
		let buttonText = 'По популярности'; // Текст по умолчанию

		// Мапим значения сортировки на тексты
		const sortTexts = {
			'popularity': 'По популярности',
			'rating': 'По рейтингу',
			'date': 'Новые',
			'price': 'Дешевле',
			'price-desc': 'Дороже',
			'menu_order': 'По умолчанию'
		};

		// Используем текст из мапа или значение по умолчанию
		if (sortTexts[orderby]) {
			buttonText = sortTexts[orderby];
		}

		// Обновляем текст в кнопке
		$('.sidebar__icon.icon-sidebar-sort span').text(buttonText);

		// Также обновляем select в header если он есть
		const $select = $('#sort select[name="orderby"]');
		if ($select.length) {
			$select.val(orderby);
		}
	}
    
    // Обновление URL
    function updateURL() {
        const params = new URLSearchParams();
        
        // Категории
        Object.keys(selectedItems.categories).forEach(category => {
            params.append('category[]', category);
        });
        
        // Бренды
        Object.keys(selectedItems.brands).forEach(brand => {
            params.append('brand[]', brand);
        });
        
        // Размеры
        Object.keys(selectedItems.sizes).forEach(size => {
            params.append('size[]', size);
        });
        
        // Сортировка
        const orderby = $('#sidebar-form-sort input[name="orderby"]:checked').val();
        if (orderby && orderby !== 'date') {
            params.set('orderby', orderby);
        }
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState(null, "", newURL);
    }
    
    // Сброс всех фильтров
    function resetFilters() {
        clearAllSelectedItems();
        $('#sidebar-form-sort input[name="orderby"][value="date"]').prop("checked", true);
        goBackToMainCategories();
        filterProducts();
    }

    // Обновляем объект selectedItems из массивов
    function updateSelectedItemsFromArrays() {
        selectedItems.categories = {};
        selectedItems.brands = {};
        selectedItems.sizes = {};
        
        // Категории
        selectedCategories.forEach(slug => {
            const $checkbox = $(`.category-checkbox[value="${slug}"]`);
            const name = $checkbox.length ? $checkbox.data('category-name') || slug : slug;
            selectedItems.categories[slug] = name;
        });
        
        // Бренды
        selectedBrands.forEach(slug => {
            const $checkbox = $(`.brand-checkbox[value="${slug}"]`);
            const name = $checkbox.length ? $checkbox.data('brand-name') || slug : slug;
            selectedItems.brands[slug] = name;
        });
        
        // Размеры
        selectedSizes.forEach(slug => {
            const $checkbox = $(`.size-checkbox[value="${slug}"]`);
            const name = $checkbox.length ? $checkbox.data('size-name') || slug : slug;
            selectedItems.sizes[slug] = name;
        });
    }
    
    // ========== ОБРАБОТЧИКИ СОБЫТИЙ ==========
    
    // Клик по категории с подкатегориями (уровень 1 → 2)
    $(document).on('click', '.sidebar__category-btn', function() {
        const categoryId = $(this).data('category-id');
        const categoryName = $(this).data('category-name');
        loadSubcategories(categoryId, categoryName, 2);
    });
    
    // Клик по подкатегории с под-подкатегориями (уровень 2 → 3)
    $(document).on('click', '.category-item-btn.has-children', function() {
        const categoryId = $(this).data('category-id');
        const categoryName = $(this).data('category-name');
        loadSubcategories(categoryId, categoryName, 3);
    });
    
    // Кнопка "Назад" в подкатегориях
    $(document).on('click', '.subcategories-back-btn', function() {
        if (currentParentCategory.level === 3) {
            goBackToLevel2();
        } else {
            goBackToMainCategories();
        }
    });
    
    // Изменение чекбоксов категорий
    $(document).on('change', '.category-checkbox', function() {
        const $this = $(this);
        const slug = $this.val();
        const isChecked = $this.prop('checked');
        const name = $this.data('category-name') || slug;
        const type = 'category';
        
        if (isChecked) {
            addSelectedItem(type, slug, name);
        } else {
            removeSelectedItem(type, slug);
        }
    });
    
    // Изменение чекбоксов брендов
    $(document).on('change', '.brand-checkbox', function() {
        const $this = $(this);
        const slug = $this.val();
        const isChecked = $this.prop('checked');
        const name = $this.data('brand-name') || slug;
        const type = 'brand';
        
        if (isChecked) {
            addSelectedItem(type, slug, name);
        } else {
            removeSelectedItem(type, slug);
        }
    });

    // Изменение чекбоксов размеров
    $(document).on('change', '.size-checkbox', function() {
        const $this = $(this);
        const slug = $this.val();
        const isChecked = $this.prop('checked');
        const name = $this.data('size-name') || slug;
        const type = 'size';
        
        if (isChecked) {
            addSelectedItem(type, slug, name);
        } else {
            removeSelectedItem(type, slug);
        }
    });
    
    // Кнопка удаления отдельного элемента
    $(document).on('click', '.selected-category-remove', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const slug = $(this).data('slug');
        const type = $(this).data('type');
        
        if (slug && type) {
            removeSelectedItem(type, slug);
        }
    });
    
    // Кнопка очистки всех выбранных элементов
    $(document).on('click', '.selected-categories-clear-all', function(e) {
        e.preventDefault();
        clearAllSelectedItems();
    });
    
    // Кнопка "Отмена"
    $(document).on('click', '.sidebar__cancel-btn', function(e) {
        e.preventDefault();
        $('.sidebar-filter').removeClass('active');
    });
    
    // Кнопка "Сбросить фильтры"
    $(document).on('click', '#reset-filters, .sidebar__reset-btn', function(e) {
        e.preventDefault();
        resetFilters();
    });
    
    // Сортировка
    $(document).on('click', '#apply-sort', function() {
        filterProducts();
		$('.icon-sidebar-sort').click();
    });
    
    // Фильтр
    $(document).on('click', '.sidebar__apply-btn', function() {
        filterProducts();
        document.documentElement.classList.remove("sidebar-filter-open");
        document.documentElement.classList.remove("lock");
    });
    
    // Пагинация
    $(document).on('click', '#products-pagination a', function(e) {
        e.preventDefault();
        const pageMatch = $(this).attr('href').match(/page=(\d+)/);
        if (pageMatch) {
            currentPage = parseInt(pageMatch[1]);
            filterProducts();
        }
    });
    
    // Закрытие фильтра
    $(document).on('click', '.sidebar__close', function() {
        $('.sidebar-filter').removeClass('active');
    });
    
    // Инициализация
    initFromURL();
    
    // Автоматическая фильтрация при загрузке с параметрами
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString()) {
        setTimeout(() => {
            filterProducts();
        }, 100);
    }
});