<?php
//====================== SUPPORT WOOCOMMERCE ============================================================================================
add_action('after_setup_theme', 'your_theme_setup');
function your_theme_setup() {
    add_theme_support('woocommerce');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
}

// Создание страниц при активации темы
add_action('after_switch_theme', 'create_special_pages');
function create_special_pages() {
    // Страница акций
    $sales_page = get_page_by_path('sales');
    if (!$sales_page) {
        $page_data = array(
            'post_title'    => 'Акции',
            'post_name'     => 'sales',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'page-sales.php');
        }
    }
    
    // Страница избранного
    $favorites_page = get_page_by_path('favourites');
    if (!$favorites_page) {
        $page_data = array(
            'post_title'    => 'Избранное',
            'post_name'     => 'favourites',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'page-favourites.php');
        }
    }
}

// Убираем хлебные крошки
add_action( 'init', 'my_remove_breadcrumbs' );
function my_remove_breadcrumbs() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}

//====================== STYLE AND SCRIPT ============================================================================================
add_action('wp_enqueue_scripts', 'theme_scripts');
function theme_scripts() {
    // Основной скрипт темы
    wp_enqueue_script('fls-notifications', get_template_directory_uri() . '/assets/js/notifications.js', array('jquery'), '1.0', true);
    wp_enqueue_script('theme-main', get_template_directory_uri() . '/assets/js/app.js', array('jquery'), '1.0', true);
    wp_enqueue_script('scroll', get_template_directory_uri() . '/assets/js/scroll.js', array(), '1.0', true);
        
    // WooCommerce скрипты
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-cart-fragments');
    }
    
    wp_enqueue_style('css', get_template_directory_uri() . '/assets/css/style.css', array(), '8.0.0');
        
    // Скрипт для избранного
    wp_enqueue_script('favorites-script', get_template_directory_uri() . '/assets/js/favorites.js', array('jquery'), '1.0', true);
    
    // Передача переменных в JavaScript
    wp_localize_script('favorites-script', 'favorites_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('favorites_nonce')
    ));

    // AJAX фильтрация
    if (is_front_page() || is_shop() || is_product_category() || is_product() || is_page_template('shop/page-favourites.php') || is_page_template('shop/page-sales.php')) {
        wp_enqueue_script('ajax-filter', get_template_directory_uri() . '/assets/js/shop.js', array('jquery'), '1.0', true);
        
        wp_localize_script('ajax-filter', 'ajax_filter', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('filter_nonce')
        ));
    }

    // Для главной странцы
    if (is_front_page()) {
        
        // Скрипт главной страницы
        wp_enqueue_script('home-script', get_template_directory_uri() . '/assets/js/home.js', array('jquery'), '1.0', true);
        
        // Передача переменных
        wp_localize_script('home-script', 'home_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    // Для страницы с корзиной
    if (is_cart()) {
        wp_enqueue_script('cart', get_template_directory_uri() . '/assets/js/cart.js', array('jquery'), '1.0', true);
        
        // Передаем параметры для AJAX
        wp_localize_script('cart', 'wc_cart_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('woocommerce-cart-nonce')
        ));
    }
    
    // WooCommerce AJAX для корзины
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-cart-fragments');
    }
}

//====================== MENU ============================================================================================
add_action('init', 'your_theme_menus');
function your_theme_menus() {
    register_nav_menus(array(
        'primary' => 'Основное меню',
		'footer_menu'   => __('Footer Menu', 'gogglesnbadges'), // Меню в первой колонке футера
		'customer_menu' => __('Customer Links', 'gogglesnbadges'), // Меню во второй колонке футера
    ));
}

// Кастомное меню   
class Custom_Walker_Nav_Menu extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
        
        $output .= '<li class="menu__item ' . $class_names . '">';
        
        // Для профиля меняем URL если пользователь не авторизован
        $url = $item->url;
        if ($this->is_profile_item($item) && !is_user_logged_in()) {
            $url = get_permalink(get_page_by_path('login'));
        }
        
        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= ' href="' . esc_attr($url) . '"';
        
        // Добавляем иконки для определенных пунктов меню
        $icon = $this->get_menu_icon($item);
        
        $item_output = $args->before;
        $item_output .= '<a class="menu__link" ' . $attributes . ' title="' . esc_attr($item->title) . '">';
        $item_output .= $icon;
        $item_output .= '</a>';
        $item_output .= $args->after;
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
    
    function end_el(&$output, $item, $depth = 0, $args = array()) {
        $output .= '</li>';
    }

    private function get_menu_icon($item) {
        $icons = [
            'home' => [
                'urls' => [home_url('/'), '/'],
                'titles' => ['Главная', 'Home'],
                'svg' => '<svg width="33" height="33" viewBox="0 0 33 33" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M17.3017 3.60017C16.5453 3.09468 15.6653 3.09468 14.9088 3.60017L14.8814 3.61811L4.92984 9.97988C3.83019 10.6814 3.22105 11.892 3.22105 13.1052V26.6962C3.22105 27.9794 4.23449 28.9895 5.50463 28.9895H26.7059C27.976 28.9895 28.9895 27.9794 28.9895 26.6962V13.1052C28.9895 11.8505 28.3258 10.7258 27.2493 9.95988L17.3291 3.61811L17.3017 3.60017ZM19.0767 0.912315C17.2444 -0.304116 14.9661 -0.3041 13.1338 0.912331L3.19619 7.26518C3.19596 7.26533 3.19641 7.26504 3.19619 7.26518C1.12328 8.58792 0 10.8484 0 13.1052V26.6962C0 29.7506 2.44798 32.2105 5.50463 32.2105H26.7059C29.7625 32.2105 32.2105 29.7506 32.2105 26.6962V13.1052C32.2105 10.6106 30.8659 8.56256 29.0732 7.30462C29.0544 7.29134 29.035 7.27845 29.0156 7.266L19.0767 0.912315Z" fill="#191919" /></svg>'
            ],
            'shop' => [
                'urls' => ['/shop', 'shop'],
                'titles' => ['Магазин'],
                'svg' => '<svg width="32" height="29" viewBox="0 0 32 29" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M31.6046 4.94819L30.8065 2.71352C30.3276 1.11733 28.7314 0 27.1352 0H4.78857C3.19238 0 1.59619 1.11733 1.11733 2.71352L0.319238 4.94819C-0.319238 7.02324 -1.26854e-07 9.09829 1.27695 10.8541C1.59619 11.4926 2.23467 11.9714 2.71352 12.4503V22.6659C2.71352 26.1775 5.58667 28.8911 8.93867 28.8911H12.4503C13.408 28.8911 14.0465 28.2526 14.0465 27.2949V22.6659C14.0465 21.5486 14.8446 20.7505 15.9619 20.7505C17.0792 20.7505 17.8773 21.5486 17.8773 22.6659V27.2949C17.8773 28.2526 18.5158 28.8911 19.4735 28.8911H22.9851C26.4968 28.8911 29.2103 26.0179 29.2103 22.6659V17.5581C29.2103 16.6004 28.5718 15.9619 27.6141 15.9619C26.6564 15.9619 26.0179 16.6004 26.0179 17.5581V22.5063C26.0179 24.2621 24.5813 25.5391 22.9851 25.5391H21.0697V22.5063C21.0697 19.6331 18.8351 17.3985 15.9619 17.3985C13.0888 17.3985 10.8541 19.6331 10.8541 22.5063V25.5391H8.93867C7.18286 25.5391 5.90591 24.1025 5.90591 22.5063V13.5676C6.22514 13.5676 6.38476 13.5676 6.704 13.5676C8.45981 13.5676 10.2156 12.7695 11.333 11.4926C12.4503 12.9291 14.2061 13.7272 15.9619 13.7272C17.7177 13.7272 19.4735 12.9291 20.5909 11.6522C21.7082 12.9291 23.464 13.7272 25.2198 13.7272C27.4545 13.7272 29.3699 12.7695 30.6469 11.0137C31.9238 9.09829 32.2431 7.02324 31.6046 4.94819ZM28.093 9.09829C27.4545 10.056 26.4968 10.5349 25.3794 10.5349C23.9429 10.5349 22.6659 9.57715 22.3467 8.30019C22.1871 7.5021 21.389 7.02324 20.5909 7.02324C19.7928 7.02324 19.1543 7.5021 18.8351 8.30019C18.5158 9.57715 17.2389 10.5349 15.9619 10.5349C14.685 10.5349 13.408 9.57715 13.0888 8.30019C12.7695 7.5021 12.1311 7.02324 11.333 7.02324C10.5349 7.02324 9.73676 7.5021 9.57714 8.30019C9.25791 9.57715 7.98095 10.5349 6.704 10.5349C5.58667 10.5349 4.62895 10.056 3.83086 9.09829C3.19238 8.14057 3.03276 7.02324 3.352 5.90591L4.1501 3.67124C4.30972 3.352 4.46933 3.19238 4.78857 3.19238H27.1352C27.4545 3.19238 27.7737 3.352 27.7737 3.67124L28.5718 5.90591C28.8911 7.02324 28.7314 8.14057 28.093 9.09829Z" fill="#C0C4CB" /></svg>'
            ],
            'favourites' => [
                'urls' => ['/favourites', 'favourites', '/wishlist', 'wishlist'],
                'titles' => ['Избранное', 'Wishlist'],
                'svg' => '<svg width="36" height="31" viewBox="0 0 36 31" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M25.5895 0C22.5474 0 19.8632 1.23091 17.8947 3.51689C15.9263 1.23091 13.2421 0 10.2 0C4.65263 0 0 4.57195 0 10.0231C0 10.3748 0 10.7265 0 11.0782C0.715789 19.3429 9.66316 26.2008 14.8526 29.5418C15.7474 30.0694 16.8211 30.4211 17.8947 30.4211C18.9684 30.4211 20.0421 30.0694 20.9368 29.5418C26.1263 26.2008 35.0737 19.3429 35.7895 11.254C35.7895 10.9023 35.7895 10.5506 35.7895 10.1989C35.7895 4.57195 31.1368 0 25.5895 0ZM32.2105 10.7265C31.6737 17.7603 22.7263 24.0906 18.9684 26.3766C18.2526 26.7283 17.5368 26.7283 16.8211 26.3766C13.0632 23.9148 4.29474 17.5844 3.57895 10.5506C3.57895 10.5506 3.57895 10.1989 3.57895 10.0231C3.57895 6.50629 6.62105 3.51689 10.2 3.51689C12.8842 3.51689 15.2105 5.09949 16.2842 7.38552C16.4632 8.08878 17.1789 8.44047 17.8947 8.44047C18.6105 8.44047 19.3263 8.08878 19.5053 7.38552C20.5789 5.09949 22.9053 3.51689 25.5895 3.51689C29.1684 3.51689 32.2105 6.50629 32.2105 10.0231C32.2105 10.1989 32.2105 10.5506 32.2105 10.7265Z" fill="#C0C4CB" /></svg>'
            ],
            'cart' => [
                'urls' => ['/cart', 'cart', '/basket', 'basket'],
                'titles' => ['Корзина', 'Cart', 'Basket'],
                'svg' => '<svg width="31" height="31" viewBox="0 0 31 31" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.9262 0C25.2724 0 28.1628 2.58549 28.4672 5.93164L30.4438 23.2725C30.5958 25.0976 29.9877 26.9231 28.7709 28.292C27.5541 29.6607 25.7288 30.4209 23.9037 30.4209H6.5639C4.73885 30.4209 3.06558 29.6607 1.69672 28.292C0.479971 26.9232 -0.129084 25.0976 0.0228882 23.2725L2.15277 5.93164C2.45717 2.58552 5.19527 0 8.54144 0H21.9262ZM8.54144 3.04199C6.71619 3.04199 5.34687 4.41106 5.19476 6.23633L3.06488 23.5762C2.91278 24.4888 3.36955 25.4016 3.97797 26.1621C4.58639 26.9226 5.49921 27.2266 6.5639 27.2266H24.0561C24.9685 27.2264 25.8807 26.7703 26.6411 26.1621C27.2495 25.4016 27.5541 24.4888 27.5541 23.5762L25.2729 6.23633C25.1208 4.41106 23.7515 3.04199 21.9262 3.04199H8.54144ZM21.4702 5.01953C22.3828 5.01953 22.9916 5.62838 22.9916 6.54102C22.9915 10.7999 19.4927 14.2979 15.2338 14.2979C10.975 14.2978 7.47713 10.7998 7.47699 6.54102C7.47699 5.62845 8.08497 5.01961 8.9975 5.01953C9.91013 5.01953 10.519 5.62838 10.519 6.54102C10.5191 9.12664 12.6482 11.2558 15.2338 11.2559C17.8195 11.2559 19.9495 9.12669 19.9496 6.54102C19.9496 5.62843 20.5576 5.0196 21.4702 5.01953Z" fill="#C0C4CB" /></svg>'
            ],
            'sales' => [
                'urls' => ['/stocks', 'stocks', '/sales', 'sales', '/promo', 'promo', '/discount', 'discount'],
                'titles' => ['Акции', 'Sales', 'Promo'],
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none"><path d="M5 10C7.76142 10 10 7.76142 10 5C10 2.23858 7.76142 0 5 0C2.23858 0 0 2.23858 0 5C0 7.76142 2.23858 10 5 10Z" fill="#C0C4CB"/><path d="M26 30C28.7614 30 31 27.9853 31 25.5C31 23.0147 28.7614 21 26 21C23.2386 21 21 23.0147 21 25.5C21 27.9853 23.2386 30 26 30Z" fill="#C0C4CB"/><line x1="2" y1="29.1716" x2="29.1716" y2="2" stroke="#C0C4CB" stroke-width="4" stroke-linecap="round"/></svg>'
            ],
            'profile' => [
                'urls' => ['/profile', 'profile', '/account', 'account', '/my-account', 'my-account', '/login', 'login'],
                'titles' => ['Профиль', 'Личный кабинет', 'Account', 'Profile'],
                'svg' => '<svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 0C26.35 6.50276e-08 34 7.65 34 17C34 26.35 26.35 34 17 34C7.65 34 6.50235e-08 26.35 0 17C0 7.65 7.65 0 17 0ZM17 3.40039C9.52 3.40039 3.40039 9.52 3.40039 17C3.40039 24.48 9.52 30.5996 17 30.5996C24.48 30.5996 30.5996 24.48 30.5996 17C30.5996 9.52 24.48 3.40039 17 3.40039ZM17.001 7.64941C21.0809 7.64947 24.3105 10.88 24.3105 14.96C24.3105 16.9999 23.4605 18.6996 22.2705 20.0596C23.8005 20.7396 25.1606 21.5903 26.3506 22.7803C27.0305 23.4603 27.0306 24.4802 26.3506 25.1602C25.6706 25.8398 24.6506 25.84 23.9707 25.1602C22.1007 23.2902 19.5509 22.2696 17.001 22.2695C14.281 22.2695 11.9012 23.2902 10.0312 25.1602C9.69125 25.5002 9.35082 25.6699 8.84082 25.6699C8.50082 25.6699 7.99039 25.5001 7.65039 25.1602C6.97061 24.4803 6.97087 23.4603 7.65039 22.7803C8.84035 21.5903 10.2006 20.7396 11.7305 20.0596C10.5407 18.6997 9.69047 16.9997 9.69043 14.96C9.69043 10.88 12.921 7.64941 17.001 7.64941ZM17.001 11.0498C14.791 11.0498 13.0908 12.75 13.0908 14.96C13.0909 17.1699 14.791 18.8701 17.001 18.8701C19.2109 18.8701 20.9111 17.1699 20.9111 14.96C20.9111 12.75 19.2109 11.0499 17.001 11.0498Z" fill="#C0C4CB" /></svg>'
            ]
        ];
        
        // Сначала проверяем по заголовку (более точное совпадение)
        foreach ($icons as $icon_data) {
            foreach ($icon_data['titles'] as $title) {
                if (strtolower(trim($item->title)) === strtolower(trim($title))) {
                    return $icon_data['svg'] . '<span>' . $title . '</span>';
                }
            }
        }
        
        // Затем проверяем по URL (менее точное, но как запасной вариант)
        foreach ($icons as $icon_data) {
            foreach ($icon_data['urls'] as $url) {
                // Используем parse_url для получения пути
                $item_path = parse_url($item->url, PHP_URL_PATH);
                if ($item_path && strpos($item_path, $url) !== false) {
                    return $icon_data['svg']. '<span>' . $title . '</span>';
                }
            }
        }
        
        return '';
    }
    
    private function is_profile_item($item) {
        $profile_indicators = ['profile', 'account', 'my-account', 'Профиль', 'Личный кабинет'];
        
        foreach ($profile_indicators as $indicator) {
            if (strpos($item->url, $indicator) !== false || 
                strtolower(trim($item->title)) === strtolower(trim($indicator))) {
                return true;
            }
        }
        
        return false;
    }
}



//====================== FRONT-PAGE ============================================================================================
// AJAX загрузка товаров по категориям
add_action('wp_ajax_load_products_by_category', 'load_products_by_category');
add_action('wp_ajax_nopriv_load_products_by_category', 'load_products_by_category');
function load_products_by_category() {
    $category = sanitize_text_field($_POST['category']);
    
    switch ($category) {
        case 'new':
            $shortcode = '[products limit="8" columns="4" orderby="date" order="DESC"]';
            break;
        case 'featured':
            $shortcode = '[products limit="8" columns="4" visibility="featured"]';
            break;
        case 'men':
            $shortcode = '[products limit="8" columns="4" category="men"]';
            break;
        case 'women':
            $shortcode = '[products limit="8" columns="4" category="women"]';
            break;
        case 'accessories':
            $shortcode = '[products limit="8" columns="4" category="accessories"]';
            break;
        default:
            $shortcode = '[products limit="8" columns="4" orderby="popularity"]';
    }
    
    wp_send_json_success(do_shortcode($shortcode));
}

// Обработчик AJAX для загрузки товаров
add_action('wp_ajax_load_filtered_products', 'load_filtered_products');
add_action('wp_ajax_nopriv_load_filtered_products', 'load_filtered_products');

function load_filtered_products() {
    // Получаем и валидируем параметры
    $type = sanitize_text_field($_POST['type'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? '');
    
    // Базовые аргументы запроса
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 8,
        'post_status' => 'publish',
    );
    
    // Обработка разных типов запросов
    switch($type) {
        case 'new':
            // Новинки - последние добавленные товары
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
            
        case 'brands':
            // Бренды - все товары у которых есть любой бренд
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_brand', // Укажите правильное название таксономии бренда
                    'operator' => 'EXISTS' // Все товары, у которых указан бренд
                )
            );
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
            
        case 'category':
            // Категории - фильтрация по конкретной категории
            if (!empty($category)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => $category,
                        'operator' => 'IN'
                    )
                );
            }
            // Для категорий можно добавить сортировку по популярности
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
            
        default:
            // По умолчанию - популярные товары
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
    }

    wc_get_logger()->info('log', $args);
    
    // Выполняем запрос
    $products_query = new WP_Query($args);
    
    // Выводим товары
    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }
        wp_reset_postdata();
    } else {
        echo '<div class="no-products">';
        echo '<p>Товары не найдены</p>';
        echo '</div>';
    }
    
    wp_die();
}

//====================== FAVORITES ============================================================================================
// Функция проверки избранного
if (!function_exists('is_user_favorite')) {
    function is_user_favorite($product_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user_id = get_current_user_id();
        $favorites = get_user_meta($user_id, 'user_favorites', true);
        
        if (empty($favorites) || !is_array($favorites)) {
            return false;
        }
        
        return in_array($product_id, $favorites);
    }
}

// Создание страницы избранного при активации темы
add_action('after_switch_theme', 'create_favorites_page');
function create_favorites_page() {
    $favorites_page = get_page_by_path('favourites');
    
    if (!$favorites_page) {
        $page_data = array(
            'post_title'    => 'Избранное',
            'post_name'     => 'favourites',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'page-favourites.php');
        }
    }
}

// AJAX добавление/удаление из избранного
add_action('wp_ajax_toggle_favorite', 'ajax_toggle_favorite');
add_action('wp_ajax_nopriv_toggle_favorite', 'ajax_require_login_favorite');

function ajax_toggle_favorite() {
    // Проверка nonce для безопасности
    if (!wp_verify_nonce($_POST['nonce'], 'favorites_nonce')) {
        wp_send_json_error('Ошибка безопасности');
        return;
    }

    // Обязательная проверка авторизации
    if (!is_user_logged_in()) {
        wp_send_json_error('Требуется авторизация');
        return;
    }

    $product_id = intval($_POST['product_id']);
    if (!$product_id) {
        wp_send_json_error('Неверный ID товара');
        return;
    }

    $user_id = get_current_user_id();
    $favorites = get_user_meta($user_id, 'user_favorites', true);
    
    if (empty($favorites) || !is_array($favorites)) {
        $favorites = array();
    }

    $favorite_index = array_search($product_id, $favorites);
    
    if ($favorite_index !== false) {
        // Удаляем из избранного
        unset($favorites[$favorite_index]);
        $is_favorite = false;
        $message = 'Товар удален из избранного';
    } else {
        // Добавляем в избранное
        $favorites[] = $product_id;
        $is_favorite = true;
        $message = 'Товар добавлен в избранное';
    }

    // Сохраняем обновленный список
    update_user_meta($user_id, 'user_favorites', $favorites);
    
    wp_send_json_success(array(
        'is_favorite' => $is_favorite,
        'favorites_count' => count($favorites),
        'message' => $message
    ));
}

function ajax_require_login_favorite() {
    wp_send_json_error('Для добавления в избранное необходимо войти в систему');
}


//====================== FILTER ============================================================================================
// Обработка пользовательских фильтров
add_action('pre_get_posts', 'custom_product_filter_query');
function custom_product_filter_query($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        
        $tax_query = array();
        
        // Фильтр по категориям
        if (!empty($_GET['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => (array)$_GET['category'],
                'operator' => 'IN'
            );
        }
        
        // Фильтр по брендам
        if (!empty($_GET['brand'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => (array)$_GET['brand'],
                'operator' => 'IN'
            );
        }
        
        // Фильтр по размерам
        if (!empty($_GET['size'])) {
            $tax_query[] = array(
                'taxonomy' => 'pa_size',
                'field'    => 'slug',
                'terms'    => (array)$_GET['size'],
                'operator' => 'IN'
            );
        }
        
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $query->set('tax_query', $tax_query);
        }
    }
}

// Обработка фильтров для страницы акций
add_action('pre_get_posts', 'sales_page_filter_query');
function sales_page_filter_query($query) {
    if (!is_admin() && is_page_template('page-sales.php') && $query->is_main_query()) {
        
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => '_sale_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC'
            )
        );
        
        $tax_query = array();
        
        // Фильтр по категориям
        if (!empty($_GET['category'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => (array)$_GET['category'],
                'operator' => 'IN'
            );
        }
        
        // Фильтр по брендам
        if (!empty($_GET['brand'])) {
            $tax_query[] = array(
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => (array)$_GET['brand'],
                'operator' => 'IN'
            );
        }
        
        // Фильтр по размерам
        if (!empty($_GET['size'])) {
            $tax_query[] = array(
                'taxonomy' => 'pa_size',
                'field'    => 'slug',
                'terms'    => (array)$_GET['size'],
                'operator' => 'IN'
            );
        }
        
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $query->set('tax_query', $tax_query);
        }
        
        $query->set('meta_query', $meta_query);
    }
}


//====================== PRODUCT ============================================================================================
// Загрузка скриптов для вариативных товаров
add_action('wp_enqueue_scripts', 'load_variation_scripts');
function load_variation_scripts() {
    if (is_product()) {
        wp_enqueue_script('wc-add-to-cart-variation');
    }
}


//====================== AJAX SHOP ============================================================================================
// AJAX фильтрация товаров
add_action('wp_ajax_filter_products', 'filter_products_callback');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_callback');

function filter_products_callback() {
    // Проверка nonce с правильным названием
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'filter_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 12,
    );
    
    // Сортировка
    if (!empty($_POST['orderby'])) {
        switch ($_POST['orderby']) {
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
            case 'popularity':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = 'total_sales';
                $args['order'] = 'DESC';
                break;
            case 'date':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'menu_order title';
                $args['order'] = 'ASC';
        }
    }
    
    // Обработка категорий
    if (!empty($_POST['category'])) {
        $categories = $_POST['category'];
        $category_slugs = array();
        
        foreach ($categories as $category) {
            $category_slugs[] = sanitize_text_field($category);
        }
        
        if (!empty($category_slugs)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category_slugs,
            );
        }
    }
    
    // Обработка брендов
    if (!empty($_POST['brand'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_brand',
            'field' => 'slug',
            'terms' => $_POST['brand'],
        );
    }
    
    // Обработка размеров
    if (!empty($_POST['size'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'pa_size',
            'field' => 'slug',
            'terms' => $_POST['size'],
        );
    }
    
    // Устанавливаем отношение AND для tax_query
    if (isset($args['tax_query']) && count($args['tax_query']) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }
    
    $products = new WP_Query($args);
    
    ob_start();
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            wc_get_template_part('content', 'product');
        }
        
        // Пагинация
        if ($products->max_num_pages > 1) {
            $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
            echo '<div id="products-pagination">';
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '?paged=%#%',
                'current' => $paged,
                'total' => $products->max_num_pages,
                'prev_text' => '«',
                'next_text' => '»',
            ));
            echo '</div>';
        }
    } else {
        echo '<p class="no-products">Товары не найдены</p>';
    }
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success(array('html' => $html));
}

// Создание nonce для AJAX
function enqueue_ajax_scripts() {
    if (is_shop()){
        wp_localize_script('ajax-filter-js', 'ajax_filter', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('filter_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_scripts');

// AJAX загрузка подкатегорий
add_action('wp_ajax_get_subcategories', 'get_subcategories_callback');
add_action('wp_ajax_nopriv_get_subcategories', 'get_subcategories_callback');

function get_subcategories_callback() {
    check_ajax_referer('filter_nonce', 'nonce');
    
    $category_id = intval($_POST['category_id']);
    $level = isset($_POST['level']) ? intval($_POST['level']) : 2;
    
    // Получаем подкатегории
    $subcategories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => $category_id,
    ));
    
    $result = array();
    
    if (!empty($subcategories) && !is_wp_error($subcategories)) {
        foreach ($subcategories as $subcat) {
            // Проверяем, есть ли у подкатегории свои подкатегории
            $has_children = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => $subcat->term_id,
                'number' => 1
            ));
            
            $result[] = array(
                'term_id' => $subcat->term_id,
                'slug' => $subcat->slug,
                'name' => $subcat->name,
                'has_children' => !empty($has_children)
            );
        }
    }
    
    wp_send_json_success($result);
}

// Кастомные хуки для кастомизации вывода
add_action('woocommerce_before_shop_loop_item', 'custom_before_shop_loop_item', 5);
function custom_before_shop_loop_item() {
    // Открываем кастомную ссылку вместо стандартной
    remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
    echo '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">';
}

add_action('woocommerce_after_shop_loop_item', 'custom_after_shop_loop_item', 5);
function custom_after_shop_loop_item() {
    // Закрываем кастомную ссылку вместо стандартной
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
    echo '</a>';
}

// Кастомный вывод цены
add_filter('woocommerce_get_price_html', 'custom_price_html', 100, 2);
function custom_price_html($price, $product) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        if ($product->is_on_sale()) {
            $regular_price = wc_get_price_to_display($product, array('price' => $product->get_regular_price()));
            $sale_price = wc_get_price_to_display($product, array('price' => $product->get_sale_price()));
            
            return '<div class="card__price card__new-price">' . wc_price($sale_price) . '</div>' .
                   '<div class="card__price card__old-price">' . wc_price($regular_price) . '</div>';
        } else {
            return '<div class="card__price card__new-price">' . wc_price($product->get_price()) . '</div>';
        }
    }
    return $price;
}

// Кастомный вывод заголовка
add_action('woocommerce_shop_loop_item_title', 'custom_product_title', 10);
function custom_product_title() {
    echo '<a href="' . get_the_permalink() . '" class="card__title">' . get_the_title() . '</a>';
}

// Кастомный вывод изображения
add_action('woocommerce_before_shop_loop_item_title', 'custom_product_thumbnail', 10);
function custom_product_thumbnail() {
    global $product;
    echo $product->get_image('full');
}

// Кастомная кнопка "В корзину"
add_filter('woocommerce_loop_add_to_cart_link', 'custom_add_to_cart_button', 10, 3);
function custom_add_to_cart_button($button, $product, $args) {
    if ($product->is_in_stock()) {
        return '<button class="card__button button-black add_to_cart_button" 
                data-product_id="' . $product->get_id() . '" 
                data-product_sku="' . $product->get_sku() . '">В корзину</button>';
    }
    return $button;
}



//====================== NOTIFICATION — ИСПРАВЛЕННАЯ ВЕРСИЯ БЕЗ ОШИБОК ======================
add_action('init', 'fls_start_session_if_needed');
function fls_start_session_if_needed() {
    // Сессия нужна только на фронтенде и только если ещё не запущена
    if (!is_admin() && !session_id()) {
        session_start();
    }
}

// Добавляем уведомление
function fls_add_notification($message, $type = 'success') {
    if (!session_id()) {
        session_start();
    }
    
    $_SESSION['fls_notifications'][] = [
        'message' => $message,
        'type'    => $type
    ];
}

// Показываем уведомления (уже безопасно, потому что сессия запущена на init)
add_action('wp_footer', 'fls_display_stored_notifications');
function fls_display_stored_notifications() {
    if (!empty($_SESSION['fls_notifications'])) {
        foreach ($_SESSION['fls_notifications'] as $notification) {
            $message = esc_js($notification['message']);
            $type    = esc_js($notification['type']);
            echo "<script>window.flsNotifications && window.flsNotifications.{$type}('{$message}');</script>";
        }
        // Очищаем после вывода
        unset($_SESSION['fls_notifications']);
    }
}

//====================== CART ============================================================================================
add_action('wp_ajax_woocommerce_remove_cart_item', 'custom_woocommerce_remove_cart_item');
add_action('wp_ajax_nopriv_woocommerce_remove_cart_item', 'custom_woocommerce_remove_cart_item');
function custom_woocommerce_remove_cart_item() {
    // Проверка nonce
    if (!check_ajax_referer('woocommerce-cart', '_wpnonce', false)) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    if ($cart_item_key && WC()->cart->remove_cart_item($cart_item_key)) {
        WC()->cart->calculate_totals();
        
        // Получаем обновленные фрагменты
        ob_start();
        woocommerce_mini_cart();
        $mini_cart = ob_get_clean();
        
        // Получаем обновленную таблицу корзины
        ob_start();
        woocommerce_cart_totals();
        $cart_totals = ob_get_clean();
        
        $data = array(
            'fragments' => array(
                '.cart-totals-fragment' => '<div class="cart-totals-fragment" data-fragment="custom-cart-totals">' . $cart_totals . '</div>',
                '.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>'
            ),
            'cart_hash' => WC()->cart->get_cart_hash()
        );

        wp_send_json_success($data);
    } else {
        wp_send_json_error('Could not remove item');
    }
}


// Кастомный вывод итогов корзины
add_action('woocommerce_cart_collaterals', 'custom_woocommerce_cart_totals', 10);
function custom_woocommerce_cart_totals() {
    if (is_cart()) {
        ?>
        <table class="sidebar-cart__table shop_table shop_table_responsive">
            <tr class="sidebar-cart__row cart-subtotal">
                <th class="sidebar-cart__header"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                <td class="sidebar-cart__data" data-title="<?php esc_attr_e('Subtotal', 'woocommerce'); ?>">
                    <span class="sidebar-cart__amount"><?php wc_cart_totals_subtotal_html(); ?></span>
                </td>
            </tr>

            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                <tr class="sidebar-cart__row sidebar-cart__row--discount cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <th class="sidebar-cart__header"><?php wc_cart_totals_coupon_label($coupon); ?></th>
                    <td class="sidebar-cart__data" data-title="<?php echo esc_attr(wc_cart_totals_coupon_label($coupon, false)); ?>">
                        <span class="sidebar-cart__amount sidebar-cart__amount--discount"><?php wc_cart_totals_coupon_html($coupon); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                <tr class="sidebar-cart__row sidebar-cart__row--delivery">
                    <td class="sidebar-cart__data" colspan="2">
                        <div class="sidebar-cart__delivery">
                            <h3 class="sidebar-cart__delivery-title"><?php esc_html_e('Shipping method', 'woocommerce'); ?></h3>
                            <?php woocommerce_shipping_calculator(); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                <tr class="sidebar-cart__row fee">
                    <th class="sidebar-cart__header"><?php echo esc_html($fee->name); ?></th>
                    <td class="sidebar-cart__data" data-title="<?php echo esc_attr($fee->name); ?>">
                        <span class="sidebar-cart__amount"><?php wc_cart_totals_fee_html($fee); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                    <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                        <tr class="sidebar-cart__row tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                            <th class="sidebar-cart__header"><?php echo esc_html($tax->label); ?></th>
                            <td class="sidebar-cart__data" data-title="<?php echo esc_attr($tax->label); ?>">
                                <span class="sidebar-cart__amount"><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="sidebar-cart__row tax-total">
                        <th class="sidebar-cart__header"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                        <td class="sidebar-cart__data" data-title="<?php echo esc_attr(WC()->countries->tax_or_vat()); ?>">
                            <span class="sidebar-cart__amount"><?php wc_cart_totals_taxes_total_html(); ?></span>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <tr class="sidebar-cart__row sidebar-cart__row--total order-total">
                <th class="sidebar-cart__header"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                <td class="sidebar-cart__data" data-title="<?php esc_attr_e('Total', 'woocommerce'); ?>">
                    <strong class="sidebar-cart__total">
                        <span class="sidebar-cart__total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
                    </strong>
                </td>
            </tr>
        </table>

        <div class="sidebar-cart__checkout wc-proceed-to-checkout">
            <?php do_action('woocommerce_proceed_to_checkout'); ?>
        </div>
        <?php
    }
}

// AJAX для обновления количества товара
// AJAX для обновления количества товара
add_action('wp_ajax_update_cart_quantity', 'update_cart_quantity_handler');
add_action('wp_ajax_nopriv_update_cart_quantity', 'update_cart_quantity_handler');

function update_cart_quantity_handler() {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woocommerce-cart-nonce')) {
        wp_send_json_error('Ошибка безопасности');
        wp_die();
    }

    $cart_key = sanitize_text_field($_POST['cart_key']);
    $quantity = intval($_POST['quantity']);
    
    if (empty($cart_key) || $quantity < 1) {
        wp_send_json_error('Неверные данные');
    }

    // Получаем товар из корзины
    $cart_item = WC()->cart->get_cart_item($cart_key);
    if (!$cart_item) {
        wp_send_json_error('Товар не найден в корзине');
    }

    $_product = $cart_item['data'];
    
    // Проверяем доступное количество
    $max_quantity = $_product->get_max_purchase_quantity();
    $stock_quantity = $_product->get_stock_quantity();
    
    // Учитываем лимиты
    if ($stock_quantity !== null && $quantity > $stock_quantity) {
        $quantity = $stock_quantity;
    }
    
    if ($max_quantity !== -1 && $quantity > $max_quantity) {
        $quantity = $max_quantity;
    }

    // Обновляем количество
    $updated = WC()->cart->set_quantity($cart_key, $quantity);
    
    if ($updated) {
        WC()->cart->calculate_totals();
        WC()->cart->maybe_set_cart_cookies();

        // Получаем обновленную строку товара
        $cart_item = WC()->cart->get_cart_item($cart_key);
        if (!$cart_item) {
            wp_send_json_error('Товар не найден после обновления');
        }

        $_product = $cart_item['data'];
        $product_id = $_product->get_id();

        // Подготавливаем фрагменты для обновления
        $fragments = array();

        // 1. Фрагмент только для обновленной строки
        ob_start();
        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_key);
        $selected_size = isset($cart_item['variation']['attribute_pa_size']) ? $cart_item['variation']['attribute_pa_size'] : '';
        ?>

        <tr class="cart__row" data-cart-key="<?php echo esc_attr($cart_key); ?>">
            <td class="cart__cell cart__cell--image">
                <?php if ($product_permalink) : ?>
                    <a href="<?php echo esc_url($product_permalink); ?>" class="cart__image-link">
                <?php endif; ?>
                
                <div class="cart__image-ibg">
                    <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image('woocommerce_thumbnail'), $cart_item, $cart_key); ?>
                </div>
                
                <?php if ($product_permalink) echo '</a>'; ?>
            </td>

            <td class="cart__cell cart__cell--info">
                <div class="cart__product-info">
                    <?php
                    $brand_name = '';
                    $brand_link = '';
                    
                    // Для вариаций получаем бренд от родительского товара
                    $target_product_id = $product_id;
                    if ($_product->is_type('variation')) {
                        $target_product_id = $_product->get_parent_id();
                    }
                    
                    // Ищем бренд
                    $brands = wp_get_post_terms($target_product_id, 'product_brand');
                    if (!empty($brands) && !is_wp_error($brands)) {
                        $brand = $brands[0];
                        $brand_name = $brand->name;
                        $brand_link = get_term_link($brand);
                        
                        if (!is_wp_error($brand_link)) {
                            echo '<a class="cart__brand" href="' . esc_url($brand_link) . '">' . esc_html($brand_name) . '</a>';
                        } else {
                            echo '<span class="cart__brand">' . esc_html($brand_name) . '</span>';
                        }
                    } else {
                        // Если бренда нет, можно ничего не выводить или вывести пустой элемент
                        echo '<span class="cart__brand" style="opacity:0;">&nbsp;</span>';
                    }
                    
                    // Название товара (упрощенная версия как в вашем HTML)
                    $product_name = $_product->get_name();
                    
                    // Удаляем размер из названия если есть
                    if ($selected_size) {
                        $pattern = '/\s*-\s*' . preg_quote($selected_size, '/') . '$/i';
                        $product_name = preg_replace($pattern, '', $product_name);
                        $pattern2 = '/\s*\(' . preg_quote($selected_size, '/') . '\)$/i';
                        $product_name = preg_replace($pattern2, '', $product_name);
                        $pattern3 = '/\s*' . preg_quote($selected_size, '/') . '$/i';
                        $product_name = preg_replace($pattern3, '', $product_name);
                    }
                    
                    // Для вариаций получаем название родительского товара
                    if ($_product->is_type('variation')) {
                        $parent_product = wc_get_product($_product->get_parent_id());
                        if ($parent_product) {
                            $parent_name = $parent_product->get_name();
                            // Удаляем размер из названия родителя
                            if ($selected_size) {
                                $pattern = '/\s*-\s*' . preg_quote($selected_size, '/') . '$/i';
                                $parent_name = preg_replace($pattern, '', $parent_name);
                                $pattern2 = '/\s*\(' . preg_quote($selected_size, '/') . '\)$/i';
                                $parent_name = preg_replace($pattern2, '', $parent_name);
                                $pattern3 = '/\s*' . preg_quote($selected_size, '/') . '$/i';
                                $parent_name = preg_replace($pattern3, '', $parent_name);
                            }
                            $product_name = $parent_name;
                        }
                    }
                    
                    // Выводим название
                    if ($product_permalink) {
                        echo '<a class="cart__name" href="' . esc_url($product_permalink) . '">' . esc_html(trim($product_name)) . '</a>';
                    } else {
                        echo '<a class="cart__name">' . esc_html(trim($product_name)) . '</a>';
                    }
                    ?>
                    
                    <div class="cart__bottom">
                        <div class="cart__quantity quantity">
                            <button type="button" class="quantity__button quantity__button_minus" 
                                    data-cart-key="<?php echo esc_attr($cart_key); ?>"
                                    aria-label="Уменьшить количество">
                                <svg width="12" height="2" viewBox="0 0 12 2" fill="none">
                                    <path d="M1 1H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                            </button>
                            
                            <div class="quantity__input">
                                <input value="<?php echo esc_attr($quantity); ?>" 
                                    autocomplete="off" 
                                    type="text" 
                                    name="cart[<?php echo esc_attr($cart_key); ?>][qty]" 
                                    class="quantity__field" 
                                    data-cart-key="<?php echo esc_attr($cart_key); ?>"
                                    data-min="1"
                                    data-max="<?php 
                                        $max_qty = $_product->get_max_purchase_quantity(); 
                                        echo ($max_qty === -1 || $max_qty === null) ? -1 : esc_attr($max_qty); 
                                    ?>"
                                    aria-label="Количество товара">
                            </div>
                            
                            <button type="button" class="quantity__button quantity__button_plus"
                                    data-cart-key="<?php echo esc_attr($cart_key); ?>"
                                    aria-label="Увеличить количество">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                    <path d="M1 6H11M6 1V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <?php if ($selected_size) : ?>
                            <div class="cart__size-display">
                                <span class="cart__size-value"><?php echo esc_html($selected_size); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        // Размеры для вариативных товаров (если нужно показывать кнопки выбора размера)
                        if ($_product->is_type('variation') && false) { // false - отключено, так как в вашем HTML нет кнопок выбора
                            $parent_product = wc_get_product($_product->get_parent_id());
                            if ($parent_product) {
                                $attributes = $parent_product->get_variation_attributes();
                                if (isset($attributes['pa_size'])) {
                                    echo '<div class="cart__attributes">';
                                    foreach ($attributes['pa_size'] as $size) {
                                        $active_class = ($size === $selected_size) ? 'cart__attribute--active' : '';
                                        echo '<button type="button" class="cart__attribute ' . $active_class . '" 
                                                data-size="' . esc_attr($size) . '"
                                                data-cart-key="' . esc_attr($cart_key) . '">' . esc_html($size) . '</button>';
                                    }
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </td>

            <td class="cart__cell cart__cell--price">
                <div class="cart__prices">
                    <?php
                    // Получаем цены для одной единицы
                    $price = $_product->get_price();
                    $regular_price = $_product->get_regular_price();
                    $sale_price = $_product->get_sale_price();
                    
                    // Выводим цену как в вашем HTML
                    echo '<div class="cart__price">' . wc_price($price) . '</div>';
                    
                    // Если нужно показывать старую цену при скидке (в вашем HTML этого нет)
                    if ($sale_price && $regular_price > $sale_price) {
                        echo '<div class="cart__price cart__price--old">' . wc_price($regular_price) . '</div>';
                    }
                    ?>
                </div>

                <button class="cart__remove" 
                        aria-label="Удалить товар"
                        data-cart-key="<?php echo esc_attr($cart_key); ?>"
                        data-product-id="<?php echo esc_attr($product_id); ?>">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                </button>
            </td>
        </tr>

        <?php
        $row_html = ob_get_clean();

        // Ключ должен точно совпадать с селектором в JS
        $fragments['tr.cart__row[data-cart-key="' . $cart_key . '"]'] = $row_html;

        // Также добавляем фрагменты для обновления тоталов и счетчика
        ob_start();
        ?>
        <span class="woocommerce-Price-amount amount">
            <bdi><?php echo wc_price(WC()->cart->get_cart_total()); ?></bdi>
        </span>
        <?php
        $cart_total_html = ob_get_clean();
        $fragments['.cart-total, .header-cart-total, .mini-cart-total'] = $cart_total_html;

        // Счетчик товаров
        $cart_count = WC()->cart->get_cart_contents_count();
        $fragments['.cart-count, .header-cart-count, .mini-cart-count'] = '<span class="cart-count-number">' . $cart_count . '</span>';
        
        // 2. Фрагмент для сайдбара с итогами
        ob_start();
        ?>
        <div class="cart-tots-fragment">
            <div class="sidebar-cart-premium">
                <h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

                <table class="sidebar-cart-premium__table">
                    <tbody>
                        <!-- Сумма товаров -->
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--subtotal">
                            <th>Сумма</th>
                            <td><?php wc_cart_totals_subtotal_html(); ?></td>
                        </tr>

                        <?php
                        // Рассчитываем общую скидку на все товары
                        $total_sale_discount = 0;
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = $cart_item['data'];
                            if ($_product && $_product->exists()) {
                                $regular_price = $_product->get_regular_price();
                                $sale_price = $_product->get_sale_price();
                                
                                if ($sale_price && $regular_price > $sale_price) {
                                    $item_discount = ($regular_price - $sale_price) * $cart_item['quantity'];
                                    $total_sale_discount += $item_discount;
                                }
                            }
                        }
                        
                        if ($total_sale_discount > 0) : ?>
                            <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--sale-discount">
                                <th>Скидка</th>
                                <td style="color: #f80f4e; font-weight: 600;">
                                    -<?php echo wc_price($total_sale_discount); ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!-- ИТОГО -->
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--total">
                            <th>Итого</th>
                            <td><?php wc_cart_totals_order_total_html(); ?></td>
                        </tr>                        
                        
                    </tbody>
                </table>

                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="sidebar-cart-premium__button">
                    Оформить заказ
                </a>
            </div>
        </div>
        <?php
        $sidebar_html = ob_get_clean();
        $fragments['.sidebar-cart-premium'] = $sidebar_html;
        
        // 3. Счетчик товаров в корзине
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_total = WC()->cart->get_cart_total();
        
        wp_send_json_success(array(
            'fragments' => $fragments,
            'cart_hash' => WC()->cart->get_cart_hash(),
            'cart_count' => $cart_count,
            'cart_total' => strip_tags($cart_total),
            'message' => 'Количество обновлено'
        ));
    } else {
        wp_send_json_error('Не удалось обновить количество');
    }
}

// AJAX для удаления товара
add_action('wp_ajax_remove_cart_item', 'remove_cart_item_handler');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item_handler');

function remove_cart_item_handler() {
    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woocommerce-cart-nonce')) {
        wp_send_json_error('Ошибка безопасности');
        wp_die();
    }

    $cart_key = sanitize_text_field($_POST['cart_key']);
    
    if (empty($cart_key)) {
        wp_send_json_error('Неверные данные');
    }
    
    $removed = WC()->cart->remove_cart_item($cart_key);
    
    if ($removed) {
        WC()->cart->calculate_totals();
        WC()->cart->maybe_set_cart_cookies();
        
        // Получаем обновленные данные
        $is_empty = WC()->cart->is_empty();
        $cart_count = WC()->cart->get_cart_contents_count();
        $cart_total = WC()->cart->get_cart_total();
        
        $fragments = array();
        
        if ($is_empty) {
            // Если корзина пуста
            ob_start();
            ?>
            <section class="empty-cart" style="font-weight: 700;font-size: 1.25rem;letter-spacing: 0.02em;text-transform: uppercase;text-align: center;color: #000;display: flex;flex-direction: column;height: calc(100vw - 52px);align-items: center;justify-content: center;">
                <h3>В корзине пока пусто</h3>
            </section>
            <?php
            $fragments['.cart__container'] = ob_get_clean();
        } else {
            // Если в корзине еще есть товары
            ob_start();
            woocommerce_cart_totals();
            $fragments['.cart-tots-fragment'] = ob_get_clean();
        }
        
        wp_send_json_success(array(
            'fragments' => $fragments,
            'cart_hash' => WC()->cart->get_cart_hash(),
            'cart_count' => $cart_count,
            'cart_total' => strip_tags($cart_total),
            'empty' => $is_empty,
            'message' => 'Товар удалён из корзины'
        ));
    } else {
        wp_send_json_error('Не удалось удалить товар');
    }
}

//====================== PROFILE ============================================================================================
// Обработка AJAX регистрации
add_action('wp_ajax_nopriv_custom_registration', 'custom_registration_handler');
add_action('wp_ajax_custom_registration', 'custom_registration_handler');
function custom_registration_handler() {
    // Проверка nonce
    if (!wp_verify_nonce($_POST['registration_nonce'], 'custom_registration_nonce')) {
        wp_send_json_error(array('message' => 'Ошибка безопасности'));
        return;
    }
    
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $redirect_to = sanitize_text_field($_POST['redirect_to']);
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'Пожалуйста, заполните все обязательные поля'));
        return;
    }
    
    if (strlen($username) < 3) {
        wp_send_json_error(array('message' => 'Имя пользователя должно содержать не менее 3 символов'));
        return;
    }
    
    if ($password !== $password2) {
        wp_send_json_error(array('message' => 'Введенные пароли не совпадают'));
        return;
    }
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Пожалуйста, введите корректный email адрес'));
        return;
    }
    
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Этот email адрес уже зарегистрирован в системе'));
        return;
    }

    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'Заполните все поля'));
        return;
    }
    
    if (strlen($username) < 3) {
        wp_send_json_error(array('message' => 'Имя пользователя должно быть не менее 3 символов'));
        return;
    }
    
    if ($password !== $password2) {
        wp_send_json_error(array('message' => 'Пароли не совпадают'));
        return;
    }
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Неверный формат email'));
        return;
    }
    
    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Email уже зарегистрирован'));
        return;
    }
    
    // Если имя пользователя занято, предлагаем варианты
    $original_username = $username;
    $counter = 1;
    
    while (username_exists($username)) {
        $username = $original_username . $counter;
        $counter++;
        
        if ($counter > 10) {
            $username = 'user_' . uniqid();
            break;
        }
    }
    
    // Создание пользователя
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => 'Ошибка регистрации: ' . $user_id->get_error_message()));
        return;
    }
    
    // Устанавливаем роль "customer" для WooCommerce
    $user = new WP_User($user_id);
    $user->set_role('customer');
    
    // Сохраняем оригинальное имя для отображения
    if ($username !== $original_username) {
        update_user_meta($user_id, 'original_username', $original_username);
    }
    
    // Автоматический логин после регистрации
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
    
    // Успешный ответ
    $message = 'Регистрация прошла успешно! Добро пожаловать!';
    if ($username !== $original_username) {
        $message = 'Регистрация прошла успешно! Ваше имя пользователя: ' . $username;
    }
    
    wp_send_json_success(array(
        'message' => $message,
        'redirect' => $redirect_to ?: home_url(),
        'username' => $username
    ));
}

// Проверка доступности имени пользователя
add_action('wp_ajax_nopriv_check_username', 'check_username_availability');
add_action('wp_ajax_check_username', 'check_username_availability');
function check_username_availability() {
    $username = sanitize_user($_POST['username']);
    
    if (username_exists($username)) {
        wp_send_json_success(array('available' => false));
    } else {
        wp_send_json_success(array('available' => true));
    }
}

// Запрет доступа в админку для всех, кроме администраторов
add_action('admin_init', 'restrict_admin_access');
function restrict_admin_access() {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    
    if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
        wp_redirect(home_url());
        exit;
    }
}

// Скрываем админбар для всех, кроме администраторов
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('manage_options') && !is_admin()) {
        show_admin_bar(false);
    }
}

// Перенаправление с wp-login.php на кастомную страницу входа
add_action('init', 'redirect_login_page');
function redirect_login_page() {
    $login_page = home_url('/login/');
    $page_viewed = basename($_SERVER['REQUEST_URI']);
    
    if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
}

// Обработка ошибок входа
add_action('wp_login_failed', 'login_failed');
function login_failed() {
    $login_page = home_url('/login/');
    wp_redirect($login_page . '?login=failed');
    exit;
}

// Выход из системы
add_action('wp_logout', 'logout_redirect');
function logout_redirect() {
    wp_redirect(home_url('/login/'));
    exit;
}


//====================== RESET PASSWORD ============================================================================================
// AJAX обработчик запроса сброса пароля
add_action('wp_ajax_nopriv_password_reset_request', 'password_reset_request_handler');
add_action('wp_ajax_password_reset_request', 'password_reset_request_handler');
function password_reset_request_handler() {
    // Проверка nonce
    if (!wp_verify_nonce($_POST['password_reset_nonce'], 'password_reset_nonce')) {
        wp_send_json_error(array('message' => 'Ошибка безопасности'));
        return;
    }
    
    $user_email = sanitize_email($_POST['user_email']);
    
    // Валидация
    if (empty($user_email)) {
        wp_send_json_error(array('message' => 'Пожалуйста, введите email адрес'));
        return;
    }
    
    if (!is_email($user_email)) {
        wp_send_json_error(array('message' => 'Пожалуйста, введите корректный email адрес'));
        return;
    }
    
    // Проверяем существует ли пользователь
    $user = get_user_by('email', $user_email);
    if (!$user) {
        wp_send_json_error(array('message' => 'Пользователь с таким email не найден'));
        return;
    }
    
    // Отправляем письмо сброса пароля через WordPress
    $result = retrieve_password($user->user_login);
    
    if (is_wp_error($result)) {
        $error_message = $result->get_error_message();
        wp_send_json_error(array('message' => $error_message ?: 'Не удалось отправить письмо сброса пароля'));
        return;
    }
    
    wp_send_json_success(array(
        'message' => 'Ссылка для сброса пароля отправлена на ваш email. Проверьте почту.'
    ));
}

// Обработчик сброса пароля (для страницы сброса от WordPress)
add_action('wp_ajax_nopriv_password_reset', 'password_reset_handler');
add_action('wp_ajax_password_reset', 'password_reset_handler');
function password_reset_handler() {
    // Проверка nonce
    if (!wp_verify_nonce($_POST['password_reset_nonce'], 'password_reset_nonce')) {
        wp_send_json_error(array('message' => 'Ошибка безопасности'));
        return;
    }
    
    $rp_key = sanitize_text_field($_POST['rp_key']);
    $rp_login = sanitize_text_field($_POST['rp_login']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Валидация
    if (empty($new_password) || empty($confirm_password)) {
        wp_send_json_error(array('message' => 'Заполните все поля пароля'));
        return;
    }
    
    if ($new_password !== $confirm_password) {
        wp_send_json_error(array('message' => 'Пароли не совпадают'));
        return;
    }
    
    if (strlen($new_password) < 6) {
        wp_send_json_error(array('message' => 'Пароль должен содержать не менее 6 символов'));
        return;
    }
    
    // Проверяем ключ сброса
    $user = check_password_reset_key($rp_key, $rp_login);
    
    if (is_wp_error($user)) {
        wp_send_json_error(array('message' => 'Недействительная или устаревшая ссылка сброса пароля'));
        return;
    }
    
    // Сбрасываем пароль
    reset_password($user, $new_password);
    
    wp_send_json_success(array(
        'message' => 'Пароль успешно изменен! Теперь вы можете войти с новым паролем.',
        'redirect' => get_permalink(get_page_by_path('login'))
    ));
}



//====================== AJAX PRODUCT ============================================================================================
// AJAX добавление в корзину для простых товаров
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');

function woocommerce_ajax_add_to_cart() {
    // Проверка nonce
    if (!isset($_POST['woocommerce-add-to-cart-nonce']) || !wp_verify_nonce($_POST['woocommerce-add-to-cart-nonce'], 'woocommerce-add-to-cart')) {
        wp_send_json_error(array('message' => 'Ошибка безопасности'));
        wp_die();
    }

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['add-to-cart']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    
    // Проверяем существование товара
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Товар не найден'));
        wp_die();
    }
    
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        wp_send_json_success(array(
            'message' => 'Товар добавлен в корзину'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Не удалось добавить товар в корзину'
        ));
    }
    
    wp_die();
}


//====================== SIDEBAR PRODUCR ============================================================================================
// Кастомная боковая панель корзины
// add_action('woocommerce_after_cart_table', 'custom_cart_sidebar');
function custom_cart_sidebar() {
    if (!is_cart()) return; // Убираем вывод на чекауте
    ?>
    <div class="cart__sidebar sidebar-cart">
    <div class="cart-totals-fragment">
        <div class="sidebar-cart-premium">
            <h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

            <table class="sidebar-cart-premium__table">
                <tbody>
                    <!-- Сумма товаров -->
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--subtotal">
                        <th>Сумма товаров</th>
                        <td><?php wc_cart_totals_subtotal_html(); ?></td>
                    </tr>

                    <?php
                    // Рассчитываем общую скидку на все товары
                    $total_sale_discount = 0;
                    
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        
                        if ( $_product && $_product->exists() ) {
                            $regular_price = $_product->get_regular_price();
                            $sale_price = $_product->get_sale_price();
                            
                            // Если есть скидка на товар
                            if ( $sale_price && $regular_price > $sale_price ) {
                                $item_discount = ( $regular_price - $sale_price ) * $cart_item['quantity'];
                                $total_sale_discount += $item_discount;
                            }
                        }
                    }
                    
                    // Если общая скидка больше 0, показываем её
                    if ( $total_sale_discount > 0 ) : ?>
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--sale-discount">
                            <th>Скидка на товары</th>
                            <td style="color: #f80f4e; font-weight: 600;">
                                -<?php echo wc_price( $total_sale_discount ); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php
                    // Отдельно показываем купоны если они есть
                    foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--coupon-discount">
                            <th>Скидка по купону "<?php echo esc_html( $coupon->get_code() ); ?>"</th>
                            <td style="color: #f80f4e;">
                                -<?php wc_cart_totals_coupon_html( $coupon ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Доставка -->
                    <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--shipping">
                            <th>Доставка</th>
                            <td>
                                <?php woocommerce_cart_totals_shipping_html(); ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- Налоги -->
                    <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                        <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                            <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                                <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--tax">
                                    <th><?php echo esc_html( $tax->label ); ?></th>
                                    <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--tax">
                                <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                                <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- ИТОГО -->
                    <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--total">
                        <th>Итого к оплате:</th>
                        <td><?php wc_cart_totals_order_total_html(); ?></td>
                    </tr>
                    
                    <?php
                    // Показываем сколько покупатель сэкономил
                    $total_regular_price = 0;
                    $total_sale_price = 0;
                    
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        
                        if ( $_product && $_product->exists() ) {
                            $regular_price = $_product->get_regular_price();
                            $sale_price = $_product->get_sale_price();
                            $price = $_product->get_price();
                            
                            if ( $sale_price && $regular_price > $sale_price ) {
                                $total_regular_price += $regular_price * $cart_item['quantity'];
                                $total_sale_price += $sale_price * $cart_item['quantity'];
                            } else {
                                $total_regular_price += $price * $cart_item['quantity'];
                                $total_sale_price += $price * $cart_item['quantity'];
                            }
                        }
                    }
                    
                    $total_savings = $total_regular_price - $total_sale_price;
                    
                    if ( $total_savings > 0 ) : ?>
                        <tr class="sidebar-cart-premium__row sidebar-cart-premium__row--savings">
                            <td colspan="2" style="text-align: center; padding-top: 20px; color: #4CAF50; font-weight: 700; font-size: 1.1em;">
                                🎉 Вы экономите: <?php echo wc_price( $total_savings ); ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>

            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="sidebar-cart-premium__button">
                Оформить заказ
            </a>
        </div>
    </div>
</div>
    <?php
}

// AJAX обновление методов доставки
add_action('wp_ajax_update_shipping_method', 'ajax_update_shipping_method');
add_action('wp_ajax_nopriv_update_shipping_method', 'ajax_update_shipping_method');
function ajax_update_shipping_method() {
    if (!wp_verify_nonce($_POST['nonce'], 'woocommerce-cart')) {
        wp_send_json_error('Ошибка безопасности');
    }

    $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
    $new_shipping_method = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : '';
    
    if ($new_shipping_method) {
        $chosen_shipping_methods[0] = $new_shipping_method;
        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        
        // Пересчитываем корзину
        WC()->cart->calculate_totals();
        
        wp_send_json_success(array(
            'subtotal' => WC()->cart->get_subtotal(),
            'shipping_total' => WC()->cart->get_shipping_total(),
            'total' => WC()->cart->get_total()
        ));
    }
    
    wp_send_json_error('Не удалось обновить способ доставки');
}

// JavaScript для обновления доставки
add_action('wp_footer', 'cart_sidebar_scripts');
function cart_sidebar_scripts() {
    if (is_cart()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Обновление способа доставки
            $('.sidebar-cart__shipping-radio').on('change', function() {
                var shippingMethod = $(this).val();
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'update_shipping_method',
                        shipping_method: shippingMethod,
                        nonce: '<?php echo wp_create_nonce('woocommerce-cart'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Обновляем фрагменты корзины
                            $(document.body).trigger('wc_fragment_refresh');
                            
                            // Показываем уведомление
                            if (window.flsNotifications) {
                                window.flsNotifications.success('Способ доставки обновлен');
                            }
                        }
                    }
                });
            });
            
            // Обновление итогов при изменении корзины
            $(document.body).on('updated_cart_totals', function() {
                // Корзина уже обновлена через WooCommerce
                // Можно добавить дополнительную логику если нужно
            });
        });
        </script>
        <?php
    }
}

// ============================================================================
// ФУНКЦИИ РАБОТЫ С АДРЕСАМИ
// ============================================================================

/**
 * Получить сохранённые адреса пользователя
 */
function ios_get_saved_addresses($user_id) {
    $addresses = get_user_meta($user_id, '_ios_saved_addresses', true);
    return is_array($addresses) ? $addresses : array();
}

/**
 * Сохранить адрес
 */
function ios_save_address($user_id, $data) {
    $addresses = ios_get_saved_addresses($user_id);
    
    // Проверяем дубликаты
    $address_key = md5($data['city'] . $data['address']);
    
    foreach ($addresses as $key => $addr) {
        if (md5($addr['city'] . $addr['address']) === $address_key) {
            // Обновляем существующий
            $addresses[$key] = array_merge($addr, $data);
            update_user_meta($user_id, '_ios_saved_addresses', $addresses);
            return $addresses[$key];
        }
    }
    
    // Новый адрес
    $new_address = array(
        'id' => uniqid('addr_'),
        'city' => sanitize_text_field($data['city']),
        'address' => sanitize_text_field($data['address']),
        'state' => sanitize_text_field($data['state'] ?? ''),
        'postcode' => sanitize_text_field($data['postcode'] ?? ''),
        'is_default' => empty($addresses) ? '1' : '0',
        'created_at' => current_time('mysql'),
    );
    
    // Лимит 10 адресов
    if (count($addresses) >= 10) {
        array_shift($addresses);
    }
    
    $addresses[] = $new_address;
    update_user_meta($user_id, '_ios_saved_addresses', $addresses);
    
    return $new_address;
}

/**
 * Удалить адрес
 */
function ios_delete_address($user_id, $address_id) {
    $addresses = ios_get_saved_addresses($user_id);
    
    foreach ($addresses as $key => $addr) {
        if ($addr['id'] === $address_id) {
            unset($addresses[$key]);
            break;
        }
    }
    
    $addresses = array_values($addresses);
    update_user_meta($user_id, '_ios_saved_addresses', $addresses);
    
    return true;
}

/**
 * Установить адрес по умолчанию
 */
function ios_set_default_address($user_id, $address_id) {
    $addresses = ios_get_saved_addresses($user_id);
    
    foreach ($addresses as $key => $addr) {
        $addresses[$key]['is_default'] = ($addr['id'] === $address_id) ? '1' : '0';
    }
    
    update_user_meta($user_id, '_ios_saved_addresses', $addresses);
    
    return true;
}

// ============================================================================
// AJAX HANDLERS
// ============================================================================

add_action('wp_ajax_ios_save_address', 'ios_ajax_save_address');
function ios_ajax_save_address() {
    check_ajax_referer('ios_checkout', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Необходима авторизация'));
    }
    
    $data = array(
        'city' => sanitize_text_field($_POST['city'] ?? ''),
        'address' => sanitize_text_field($_POST['address'] ?? ''),
        'state' => sanitize_text_field($_POST['state'] ?? ''),
        'postcode' => sanitize_text_field($_POST['postcode'] ?? ''),
    );
    
    if (empty($data['city']) || empty($data['address'])) {
        wp_send_json_error(array('message' => 'Заполните город и адрес'));
    }
    
    $result = ios_save_address($user_id, $data);
    
    wp_send_json_success(array(
        'message' => 'Адрес сохранён',
        'address' => $result,
    ));
}

add_action('wp_ajax_ios_delete_address', 'ios_ajax_delete_address');
function ios_ajax_delete_address() {
    check_ajax_referer('ios_checkout', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Необходима авторизация'));
    }
    
    $address_id = sanitize_text_field($_POST['id'] ?? '');
    
    if (empty($address_id)) {
        wp_send_json_error(array('message' => 'ID адреса не указан'));
    }
    
    ios_delete_address($user_id, $address_id);
    
    wp_send_json_success(array('message' => 'Адрес удалён'));
}

add_action('wp_ajax_ios_set_default_address', 'ios_ajax_set_default_address');
function ios_ajax_set_default_address() {
    check_ajax_referer('ios_checkout', 'nonce');
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(array('message' => 'Необходима авторизация'));
    }
    
    $address_id = sanitize_text_field($_POST['id'] ?? '');
    
    ios_set_default_address($user_id, $address_id);
    
    wp_send_json_success(array('message' => 'Установлен по умолчанию'));
}

// ============================================================================
// СОХРАНЕНИЕ АДРЕСА ПРИ ОФОРМЛЕНИИ ЗАКАЗА
// ============================================================================

add_action('woocommerce_checkout_order_processed', 'ios_save_order_address', 10, 3);
function ios_save_order_address($order_id, $posted_data, $order) {
    $user_id = get_current_user_id();
    if (!$user_id) return;
    
    $city = $order->get_billing_city();
    $address = $order->get_billing_address_1();
    
    if ($city && $address) {
        ios_save_address($user_id, array(
            'city' => $city,
            'address' => $address,
            'state' => $order->get_billing_state(),
            'postcode' => $order->get_billing_postcode(),
        ));
    }
}

//====================== CHECKOUT ============================================================================================

remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );



// ========== НАСТРОЙКА ПОЛЕЙ CHECKOUT ==========
// add_filter('woocommerce_checkout_fields', 'ios_checkout_customize_fields_final', 999);
function ios_checkout_customize_fields_final($fields) {
    
    if (isset($fields['billing'])) {
        // Плейсхолдеры
        $fields['billing']['billing_first_name']['placeholder'] = 'Иван';
        $fields['billing']['billing_last_name']['placeholder'] = 'Иванов';
        $fields['billing']['billing_phone']['placeholder'] = '+7 (999) 999-99-99';
        $fields['billing']['billing_email']['placeholder'] = 'example@mail.com';
        $fields['billing']['billing_address_1']['placeholder'] = 'Улица, дом';
        $fields['billing']['billing_address_2']['placeholder'] = 'Квартира, корпус';
        $fields['billing']['billing_city']['placeholder'] = 'Город';
        $fields['billing']['billing_state']['placeholder'] = 'Область';
        $fields['billing']['billing_postcode']['placeholder'] = '123456';
        
        // Лейблы
        $fields['billing']['billing_first_name']['label'] = 'Имя';
        $fields['billing']['billing_last_name']['label'] = 'Фамилия';
        $fields['billing']['billing_phone']['label'] = 'Номер телефона';
        $fields['billing']['billing_address_1']['label'] = 'Адрес';
        $fields['billing']['billing_address_2']['label'] = 'Квартира, корпус';
        $fields['billing']['billing_city']['label'] = 'Населенный пункт';
        $fields['billing']['billing_country']['label'] = 'Страна';
        $fields['billing']['billing_state']['label'] = 'Область / район';
        $fields['billing']['billing_postcode']['label'] = 'Почтовый индекс';
        
        // Убираем компанию
        unset($fields['billing']['billing_company']);
        
        // Порядок полей
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_last_name']['priority'] = 20;
        $fields['billing']['billing_phone']['priority'] = 30;
        $fields['billing']['billing_address_1']['priority'] = 40;
        $fields['billing']['billing_address_2']['priority'] = 50;
        $fields['billing']['billing_city']['priority'] = 60;
        $fields['billing']['billing_country']['priority'] = 70;
        $fields['billing']['billing_state']['priority'] = 80;
        $fields['billing']['billing_postcode']['priority'] = 90;
        $fields['billing']['billing_email']['priority'] = 100;
    }
    
    return $fields;
}

// ========== ДОБАВЛЕНИЕ КЛАССОВ К ПОЛЯМ ==========
// add_filter('woocommerce_form_field_args', 'ios_checkout_field_args_final', 999, 3);
function ios_checkout_field_args_final($args, $key, $value) {
    if (is_checkout()) {
        $args['class'][] = 'ios-form-field';
        $args['input_class'][] = 'ios-form-input';
        $args['label_class'][] = 'ios-form-label';
    }
    return $args;
}

// ========== BODY CLASS ==========
// add_filter('body_class', 'ios_checkout_body_class_final', 999);
function ios_checkout_body_class_final($classes) {
    if (is_checkout()) {
        $classes[] = 'ios-checkout-page';
    }
    return $classes;
}

// ========== ТЕКСТ КНОПКИ ==========
// add_filter('woocommerce_order_button_text', 'ios_checkout_button_text_final');
function ios_checkout_button_text_final() {
    return 'Оформить заказ';
}

// ========== ВАЛИДАЦИЯ ТЕЛЕФОНА ==========
// add_action('woocommerce_checkout_process', 'ios_checkout_phone_validation_final');
function ios_checkout_phone_validation_final() {
    $phone = isset($_POST['billing_phone']) ? $_POST['billing_phone'] : '';
    $phone_digits = preg_replace('/[^0-9]/', '', $phone);
    
    if (strlen($phone_digits) < 11) {
        wc_add_notice('Пожалуйста, введите корректный номер телефона', 'error');
    }
}

// ========== СОХРАНЕНИЕ ДАННЫХ CDEK ==========
// add_action('woocommerce_checkout_update_order_meta', 'ios_checkout_save_cdek_data_final');
function ios_checkout_save_cdek_data_final($order_id) {
    if (isset($_POST['cdek_office_code'])) {
        update_post_meta($order_id, '_cdek_office_code', sanitize_text_field($_POST['cdek_office_code']));
    }
    
    if (isset($_POST['cdek_office_address'])) {
        update_post_meta($order_id, '_cdek_office_address', sanitize_text_field($_POST['cdek_office_address']));
    }
}

// ========== ОТОБРАЖЕНИЕ В АДМИНКЕ ==========
add_action('woocommerce_admin_order_data_after_shipping_address', 'ios_checkout_display_cdek_final');
function ios_checkout_display_cdek_final($order) {
    $order_id = is_numeric($order) ? $order : (is_object($order) && method_exists($order, 'get_id') ? $order->get_id() : 0);
    
    if (!$order_id) return;
    
    $cdek_office_code = get_post_meta($order_id, '_cdek_office_code', true);
    $cdek_office_address = get_post_meta($order_id, '_cdek_office_address', true);
    
    if ($cdek_office_code || $cdek_office_address) {
        echo '<div style="margin-top: 15px; padding: 10px; background: #f0f0f1; border-radius: 4px;">';
        echo '<h4 style="margin: 0 0 10px 0;">📍 Пункт выдачи CDEK</h4>';
        
        if ($cdek_office_code) {
            echo '<p style="margin: 5px 0;"><strong>Код:</strong> ' . esc_html($cdek_office_code) . '</p>';
        }
        
        if ($cdek_office_address) {
            echo '<p style="margin: 5px 0;"><strong>Адрес:</strong> ' . esc_html($cdek_office_address) . '</p>';
        }
        
        echo '</div>';
    }
}

// ========== EMAIL УВЕДОМЛЕНИЯ ==========
add_filter('woocommerce_email_order_meta_fields', 'ios_checkout_email_meta_final', 999, 3);
function ios_checkout_email_meta_final($fields, $sent_to_admin, $order) {
    $order_id = is_numeric($order) ? $order : (is_object($order) && method_exists($order, 'get_id') ? $order->get_id() : 0);
    
    if (!$order_id) return $fields;
    
    $cdek_office_address = get_post_meta($order_id, '_cdek_office_address', true);
    $cdek_office_code = get_post_meta($order_id, '_cdek_office_code', true);
    
    if ($cdek_office_address) {
        $fields['cdek_office'] = array(
            'label' => 'Пункт выдачи CDEK',
            'value' => $cdek_office_address . ($cdek_office_code ? ' (Код: ' . $cdek_office_code . ')' : '')
        );
    }
    
    return $fields;
}

// Сохраняем выбранный офис CDEK
// add_action('woocommerce_checkout_update_order_meta', 'save_cdek_office_from_plugin', 10, 1);
function save_cdek_office_from_plugin($order_id) {
    // Сохраняем код офиса
    if (isset($_POST['office_code']) && !empty($_POST['office_code'])) {
        update_post_meta($order_id, '_cdek_office_code', sanitize_text_field($_POST['office_code']));
    }
}

// Логируем для отладки
// add_action('wp_footer', 'cdek_debug_info');
function cdek_debug_info() {
    if (is_checkout() && current_user_can('manage_options')) {
        ?>
        <script>
        console.log('%c=== CDEK Integration Debug ===', 'color: #00ff00; font-weight: bold;');
        
        // Проверяем наличие элементов CDEK
        jQuery(document).ready(function($) {
            var cdekButtons = $('.open-pvz-btn, .cdek-pvz-btn, .cdek-widget-button');
            console.log('CDEK buttons found:', cdekButtons.length);
            
            var cdekScripts = $('script[type="application/cdek-offices"]');
            console.log('CDEK offices data found:', cdekScripts.length);
            
            var officeCodeInput = $('input[name="office_code"]');
            console.log('Office code input found:', officeCodeInput.length);
            
            if (cdekButtons.length) {
                console.log('CDEK button element:', cdekButtons[0]);
            }
        });
        </script>
        <?php
    }
}




// ====================== КОРЗИНА — AJAX ОБНОВЛЕНИЕ СУММЫ ======================
add_filter('woocommerce_add_to_cart_fragments', 'goggles_cart_totals_fragment');
add_filter('woocommerce_update_order_review_fragments', 'goggles_cart_totals_fragment', 10, 1);
function goggles_cart_totals_fragment($fragments) {
    // Удаляем стандартный фрагмент для обновления итогов
    unset($fragments['.cart_totals']);
    unset($fragments['.cart-totals-fragment']);
    
    return $fragments;
}





 ?>