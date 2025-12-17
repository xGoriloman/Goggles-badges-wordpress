<?php
/**
 * Template Name: Акционные товары
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main class="page">
    <section class="section__title">
        <div class="title__container">
            <div id="filter" class="filter"></div>
            <h1 class="title">Акционные товары</h1>
            
            <form action="<?php echo esc_url(get_permalink()); ?>" method="get" class="sort">
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
        </div>
    </section>

    <section class="section__catalog catalog">
        <div class="catalog__container">
            <div class="sidebar">
                <button data-da="#filter, 991.98" type="button" class="sidebar__icon icon-sidebar">
                    <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 8C15.5523 8 16 8.44772 16 9V10H17.2002C17.6419 10.0001 17.9999 10.3581 18 10.7998C18 11.2416 17.6419 11.5995 17.2002 11.5996H16V13C16 13.5523 15.5523 14 15 14C14.4477 14 14 13.5523 14 13V11.5996H0.799805C0.358066 11.5995 0 11.2416 0 10.7998C0.000105527 10.3581 0.358132 10.0001 0.799805 10H14V9C14 8.44772 14.4477 8 15 8ZM4 0C4.55228 2.41411e-08 5 0.447715 5 1V2H17.2002C17.6419 2.0001 17.9999 2.35813 18 2.7998C18 3.24157 17.6419 3.5995 17.2002 3.59961H5V5C5 5.55228 4.55228 6 4 6C3.44772 6 3 5.55228 3 5V3.59961H0.799805C0.358066 3.5995 0 3.24157 0 2.7998C0.000105527 2.35813 0.358132 2.00011 0.799805 2H3V1C3 0.447715 3.44772 4.02849e-08 4 0Z" fill="#656D77" />
                    </svg>
                    <span>Фильтры</span>
                </button>

                <?php echo get_sidebar();?>
            </div>

            <div class="catalog__products products" id="products-container">
                <?php 
                // Запрос для акционных товаров
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 12,
                    'post_status' => 'publish',
                    'meta_query' => array(
                        array(
                            'key' => '_sale_price',
                            'value' => 0,
                            'compare' => '>',
                            'type' => 'NUMERIC'
                        )
                    )
                );
                
                // Добавляем фильтрацию из URL
                if (!empty($_GET['category'])) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => (array)$_GET['category']
                    );
                }
                
                if (!empty($_GET['brand'])) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'product_brand',
                        'field' => 'slug',
                        'terms' => (array)$_GET['brand']
                    );
                }
                
                if (!empty($_GET['size'])) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'pa_size',
                        'field' => 'slug',
                        'terms' => (array)$_GET['size']
                    );
                }
                
                // Сортировка
                if (!empty($_GET['orderby'])) {
                    switch ($_GET['orderby']) {
                        case 'price':
                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = '_price';
                            $args['order'] = 'ASC';
                            break;
                        case 'price-desc':
                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = '_price';
                            $args['order'] = 'DESC';
                            break;
                        case 'date':
                            $args['orderby'] = 'date';
                            $args['order'] = 'DESC';
                            break;
                        case 'popularity':
                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = 'total_sales';
                            $args['order'] = 'DESC';
                            break;
                    }
                }
                
                $sales_products = new WP_Query($args);
                
                if ($sales_products->have_posts()) {
                    while ($sales_products->have_posts()) {
                        $sales_products->the_post();
                        wc_get_template_part('content', 'product');
                    }
                    
                    // Пагинация
                    echo '<div class="products-pagination">';
                    echo paginate_links(array(
                        'total' => $sales_products->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                    ));
                    echo '</div>';
                    
                    wp_reset_postdata();
                } else {
                    echo '<p class="no-products">Акционных товаров не найдено</p>';
                }
                ?>
            </div>
        </div>
    </section>
</main>
    
<?php get_footer(); ?>