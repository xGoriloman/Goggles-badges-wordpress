<?php
/**
 * The Template for displaying product archives
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); ?>

<main class="page">
    <section class="section__title">
        <div class="title__container">            
            <form id="sort" action="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" method="get" class="sort">
                <select name="orderby" class="sort" onchange="this.form.submit()">
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
                        echo '<option data-asset="/wp-content/themes/Goggles-badges-wordpress/assets/img/icon/settings.svg" value="' . esc_attr($value) . '" ' . selected($orderby, $value, false) . '>' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
                <?php wc_query_string_form_fields(null, array('orderby', 'submit')); ?>
            </form>
			
            <h1 class="title">
                <?php
                if (is_shop()) {
                    echo 'Каталог';
                } elseif (is_product_category()) {
                    single_term_title();
                } elseif (is_search()) {
                    echo 'Результаты поиска: "' . get_search_query() . '"';
                } else {
                    woocommerce_page_title();
                }
                ?>
            </h1>
			
            <div id="filter" class="filter"></div>
        </div>
    </section>

    <section class="section__catalog catalog">
        <div class="catalog__container">
            <div class="sidebar">
                <button data-da="#filter, 991.98" type="button" class="sidebar__icon icon-sidebar-filter">
                    <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 8C15.5523 8 16 8.44772 16 9V10H17.2002C17.6419 10.0001 17.9999 10.3581 18 10.7998C18 11.2416 17.6419 11.5995 17.2002 11.5996H16V13C16 13.5523 15.5523 14 15 14C14.4477 14 14 13.5523 14 13V11.5996H0.799805C0.358066 11.5995 0 11.2416 0 10.7998C0.000105527 10.3581 0.358132 10.0001 0.799805 10H14V9C14 8.44772 14.4477 8 15 8ZM4 0C4.55228 2.41411e-08 5 0.447715 5 1V2H17.2002C17.6419 2.0001 17.9999 2.35813 18 2.7998C18 3.24157 17.6419 3.5995 17.2002 3.59961H5V5C5 5.55228 4.55228 6 4 6C3.44772 6 3 5.55228 3 5V3.59961H0.799805C0.358066 3.5995 0 3.24157 0 2.7998C0.000105527 2.35813 0.358132 2.00011 0.799805 2H3V1C3 0.447715 3.44772 4.02849e-08 4 0Z" fill="#656D77" />
                    </svg>
                    <span>Фильтры</span>
                </button>
				
				<button data-da="#sort, 991.98" type="button" class="sidebar__icon icon-sidebar-sort">					
					<span>
						<?php
						// Получаем текущую сортировку
						$orderby = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : 'date';

						// Маппинг значений сортировки на читаемые названия
						$sort_labels = array(
							'menu_order' => 'По умолчанию',
							'date'       => 'Новые',
							'price'      => 'Дешевле',
							'price-desc' => 'Дороже',
							'popularity' => 'По популярности'
						);

						// Выводим соответствующее название
						echo isset($sort_labels[$orderby]) ? esc_html($sort_labels[$orderby]) : 'Сортировка';
						?>
					</span>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 28 22" fill="none">
					  <path d="M6.29648 0.404598C6.8124 -0.138719 7.62144 -0.127159 8.13735 0.404598L14.0352 6.35796C14.2814 6.60072 14.4221 6.94752 14.4221 7.25964C14.4221 7.97635 13.9179 8.46187 13.2027 8.46187C12.8626 8.46187 12.5695 8.34627 12.3467 8.11507L9.93132 5.64125L8.34841 3.84946L8.44221 6.32328V20.7732C8.44221 21.4899 7.9263 21.9985 7.21106 21.9985C6.49581 21.9985 5.9799 21.4899 5.9799 20.7732V6.32328L6.0737 3.84946L4.50251 5.64125L2.07538 8.11507C1.8526 8.34627 1.57119 8.46187 1.21943 8.46187C0.504188 8.46187 0 7.97635 0 7.25964C0 6.94752 0.152429 6.60072 0.386935 6.35796L6.29648 0.404598ZM21.7035 21.6055C21.1876 22.1373 20.3786 22.1257 19.8626 21.6055L13.9531 15.629C13.7186 15.3978 13.5779 15.051 13.5779 14.7389C13.5779 14.0222 14.0821 13.5367 14.7973 13.5367C15.1374 13.5367 15.4188 13.6523 15.6533 13.8835L18.0687 16.3457L19.6399 18.1491L19.5461 15.6753V1.22535C19.5461 0.520197 20.0737 0 20.7889 0C21.5042 0 22.0201 0.520197 22.0201 1.22535V15.6753L21.9263 18.1491L23.4975 16.3457L25.9129 13.8835C26.1474 13.6523 26.4288 13.5367 26.7806 13.5367C27.4958 13.5367 28 14.0222 28 14.7389C28 15.051 27.8476 15.3978 27.6131 15.629L21.7035 21.6055Z" fill="black"/>
					</svg>
				</button>

                <?php echo get_sidebar();?>
            </div>

            <div class="catalog__products products" id="products-container">
                <?php 
                    if (woocommerce_product_loop()) {
                        if (wc_get_loop_prop('total')) {
                            while (have_posts()) {
                                the_post();
                                wc_get_template_part('content', 'product');
                            }
                        }
                    } else {
                        do_action('woocommerce_no_products_found');
                    }
                ?>
            </div>
        </div>
    </section>
</main>
    
<?php get_footer(); ?>