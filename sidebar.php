<search class="sidebar-filter sidebar">
    <div class="sidebar__body">
        <!-- Шапка с крестиком -->
        <div class="sidebar__header sidebar__title-block">
            <div class="sidebar__title">Фильтры</div>
            <button type="button" class="sidebar__close icon-sidebar-filter">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z" fill="#0F0F0F"></path>
                </svg>
            </button>
        </div>
        
        <!-- Блок выбранных категорий -->
        <div class="selected-categories-wrapper" style="display: none;">
            <div class="selected-categories-header">
                <button type="button" class="selected-categories-clear-all">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4L12 12M4 12L12 4" stroke="#191919" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="selected-categories-list">
                <!-- Выбранные категории добавляются динамически -->
            </div>
        </div>
        
        <!-- Основной контент фильтров -->
        <div class="filter-content">
            <!-- Все категории, подкатегории и под-подкатегории отображаются здесь -->
            <div class="categories-levels-container">
                <!-- Уровень 1: Основные категории -->
                <div class="categories-level categories-level-1 active">
                    <section class="sidebar__block">
                        <p class="sidebar__block-title">Категория</p>
                        <div class="sidebar__category-main sidebar__category-container">
                            <?php
                            $parent_categories = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => true,
                                'parent' => 0,
                            ));
                            
                            foreach ($parent_categories as $category) {
                                $child_categories = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'hide_empty' => true,
                                    'parent' => $category->term_id,
                                ));
                                
                                $has_children = !empty($child_categories);
                                $is_selected = isset($_GET['category']) && in_array($category->slug, (array)$_GET['category']);
                            ?>
                                <?php if ($has_children): ?>
                                    <!-- Категория с подкатегориями -->
                                    <button type="button" 
                                            class="sidebar__category-btn <?php echo $is_selected ? 'active' : ''; ?>"
                                            data-category-id="<?php echo $category->term_id; ?>"
                                            data-category-slug="<?php echo esc_attr($category->slug); ?>"
                                            data-category-name="<?php echo esc_attr($category->name); ?>">
                                        <span class="sidebar__category-btn-text"><?php echo esc_html($category->name); ?></span>
                                    </button>
                                <?php else: ?>
                                    <!-- Категория без подкатегорий -->
                                    <label class="sidebar__checkbox<?php echo $is_selected ? ' checked' : ''; ?>">
                                        <input type="checkbox" 
                                               name="category[]" 
                                               value="<?php echo esc_attr($category->slug); ?>" 
                                               class="sidebar__checkbox-input category-checkbox"
                                               data-category-name="<?php echo esc_attr($category->name); ?>"
                                               <?php echo $is_selected ? 'checked' : ''; ?>>
                                        <span class="sidebar__checkbox-label">
                                            <span class="sidebar__category-checkbox-text"><?php echo esc_html($category->name); ?></span>
                                        </span>
                                    </label>
                                <?php endif; ?>
                            <?php } ?>
                        </div>
                    </section>
                </div>
                
                <!-- Уровень 2 и 3: Подкатегории и под-подкатегории -->
                <div class="categories-level categories-level-2-3" style="display: none;">
                    <!-- Заголовок с названием выбранной категории -->
                    <div class="subcategories-header">
                        <button type="button" class="subcategories-back-btn">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 4L6 8L10 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            <span>Назад</span>
                        </button>
                        <div class="subcategories-parent-title">
                            <strong class="parent-category-name"></strong>
                        </div>
                    </div>
                    
                    <!-- Контейнер для всех уровней подкатегорий -->
                    <div class="all-subcategories-container">
                        <!-- Уровень 2 -->
                        <div class="subcategories-level subcategories-level-2">
                            <div class="subcategories-list">
                                <!-- Подкатегории загружаются динамически -->
                            </div>
                        </div>
                        
                        <!-- Уровень 3 -->
                        <div class="subcategories-level subcategories-level-3" style="display: none;">
                            <div class="subsubcategories-list">
                                <!-- Под-подкатегории загружаются динамически -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Бренды -->
            <section class="sidebar__block">
                <p class="sidebar__block-title">Бренды</p>
                <div class="sidebar__brands">
                    <?php
                    $brands = get_terms(array(
                        'taxonomy' => 'product_brand',
                        'hide_empty' => true,
                    ));
                    
                    if (!empty($brands) && !is_wp_error($brands)) {
                        foreach ($brands as $brand) {
                            $checked = isset($_GET['brand']) && in_array($brand->slug, (array)$_GET['brand']) ? 'checked' : '';
                    ?>
                            <label class="sidebar__brand-checkbox <?php echo $checked ? 'checked' : ''; ?>">
                                <input type="checkbox" 
                                       name="brand[]" 
                                       value="<?php echo esc_attr($brand->slug); ?>" 
                                       class="sidebar__checkbox-input brand-checkbox"
                                       data-brand-name="<?php echo esc_attr($brand->name); ?>"
                                       <?php echo $checked; ?>>
                                <span class="sidebar__brand-checkbox-text"><?php echo esc_html($brand->name); ?></span>
                            </label>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
            
            <!-- Размеры -->
            <section class="sidebar__block">
                <p class="sidebar__block-title">Размеры</p>
                <div class="sidebar__sizes">
                    <?php
                    $sizes = get_terms(array(
                        'taxonomy' => 'pa_size',
                        'hide_empty' => true,
                    ));
                    
                    if (!empty($sizes) && !is_wp_error($sizes)) {
                        foreach ($sizes as $size) {
                            $checked = isset($_GET['size']) && in_array($size->slug, (array)$_GET['size']) ? 'checked' : '';
                    ?>
                            <label class="sidebar__size-checkbox <?php echo $checked ? 'checked' : ''; ?>">
                                <input type="checkbox" 
                                       name="size[]" 
                                       value="<?php echo esc_attr($size->slug); ?>" 
                                       class="sidebar__checkbox-input size-checkbox"
                                       data-size-name="<?php echo esc_attr($size->name); ?>"
                                       <?php echo $checked; ?>>
                                <span class="sidebar__size-checkbox-text"><?php echo esc_html($size->name); ?></span>
                            </label>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
        </div>
        
        <!-- Кнопки действий -->
        <div class="sidebar__filter-actions">
            <button type="button" class="sidebar__cancel-btn icon-sidebar-filter">Отмена</button>
            <button type="button" class="sidebar__apply-btn">Применить</button>
        </div>
    </div>
</search>

<search class="sidebar-sort sidebar">
	<div class="sidebar__body">
		<div class="sidebar__title-block">
			<div class="sidebar__title">Показать сначала</div>
			<svg class="icon-sidebar-sort" xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" viewBox="0 0 24 24" fill="none">
				<path d="M6.99486 7.00636C6.60433 7.39689 6.60433 8.03005 6.99486 8.42058L10.58 12.0057L6.99486 15.5909C6.60433 15.9814 6.60433 16.6146 6.99486 17.0051C7.38538 17.3956 8.01855 17.3956 8.40907 17.0051L11.9942 13.4199L15.5794 17.0051C15.9699 17.3956 16.6031 17.3956 16.9936 17.0051C17.3841 16.6146 17.3841 15.9814 16.9936 15.5909L13.4084 12.0057L16.9936 8.42059C17.3841 8.03007 17.3841 7.3969 16.9936 7.00638C16.603 6.61585 15.9699 6.61585 15.5794 7.00638L11.9942 10.5915L8.40907 7.00636C8.01855 6.61584 7.38538 6.61584 6.99486 7.00636Z" fill="#0F0F0F"></path>
			</svg>
		</div>

		<form id="sidebar-form-sort" action="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" method="GET">
			<!-- Категории -->
			<section class="sidebar__block">
				<div class="sidebar__radio">
					<?php
					$orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'date';
					$orderby_options = array(
						'menu_order' => 'По умолчанию',
						'date'       => 'Новые',
						'price'      => 'Дешевле',
						'price-desc' => 'Дороже',
						'popularity' => 'По популярности'
					);

					foreach ($orderby_options as $value => $label) {
						$checked = ($orderby == $value) ? 'checked' : '';
						echo '
						<div class="sidebar__radio-item">
							<input type="radio" id="orderby_' . esc_attr($value) . '" name="orderby" value="' . esc_attr($value) . '" ' . $checked . '>
							<label for="orderby_' . esc_attr($value) . '">' . esc_html($label) . '</label>
						</div>';
					}
					?>
				</div>
			</section>

			<!-- Сохранение других параметров фильтрации -->
			<?php 
			// Сохраняем все GET-параметры кроме orderby и submit
			foreach ($_GET as $key => $value) {
				if ($key !== 'orderby' && $key !== 'submit') {
					if (is_array($value)) {
						foreach ($value as $val) {
							echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($val) . '">';
						}
					} else {
						echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
					}
				}
			}
			?>
		</form>

		<button type="button" id="apply-sort" class="sidebar__button sidebar__button-reset button-black">
			Применить
		</button>
	</div>
</search>