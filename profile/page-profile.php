<?php
/**
 * Template Name: Личный кабинет
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Проверяем авторизацию пользователя
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();

get_header(); ?>

<div class="wrapper">
    <header class="header">
        <div class="header__container">
            <a href="<?php echo home_url(); ?>" class="header__logo logo">
                <?php bloginfo('name'); ?>
            </a>
            
            <div class="header__menu menu">
                <button type="button" class="menu__icon icon-menu">
                    <span></span>
                </button>
                
                <nav class="menu__body">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'menu__list',
                        'walker' => new Custom_Walker_Nav_Menu()
                    ));
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="page">
        <section class="section__title">
            <h1 class="title">Личный кабинет</h1>
        </section>

        <section class="profile">
            <div class="profile__container">
                <!-- Боковая навигация -->
                <aside class="profile__sidebar">
                    <div class="profile__avatar">
                        <?php
                        $avatar = get_avatar($current_user->ID, 80, '', '', array('class' => 'profile__avatar-image'));
                        if ($avatar) {
                            echo $avatar;
                        } else {
                            echo '<img src="' . get_template_directory_uri() . '/img/profile/avatar.jpg" alt="Аватар пользователя" class="profile__avatar-image">';
                        }
                        ?>
                        <p class="profile__avatar-name">
                            <?php 
                            $display_name = $current_user->display_name ?: $current_user->user_login;
                            echo esc_html($display_name);
                            ?>
                        </p>
                    </div>
                    
                    <nav class="profile__navigation" aria-label="Страницы аккаунта">
                        <ul class="profile__nav-list">
                            <li class="profile__nav-item profile__nav-item--orders <?php echo (is_page('profile') || !isset($_GET['tab'])) ? 'profile__nav-item--active' : ''; ?>">
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="profile__nav-link" aria-current="page">
                                    <span class="profile__nav-icon">📦</span>
                                    Заказы
                                </a>
                            </li>
                            <li class="profile__nav-item profile__nav-item--account <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'account') ? 'profile__nav-item--active' : ''; ?>">
                                <a href="<?php echo esc_url(add_query_arg('tab', 'account', get_permalink())); ?>" class="profile__nav-link">
                                    <span class="profile__nav-icon">👤</span>
                                    Анкета
                                </a>
                            </li>
                            <li class="profile__nav-item profile__nav-item--favorites <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'favorites') ? 'profile__nav-item--active' : ''; ?>">
                                <a href="<?php echo esc_url(add_query_arg('tab', 'favorites', get_permalink())); ?>" class="profile__nav-link">
                                    <span class="profile__nav-icon">❤️</span>
                                    Избранное
                                </a>
                            </li>
                            <li class="profile__nav-item profile__nav-item--logout">
                                <a href="<?php echo wp_logout_url(home_url()); ?>" class="profile__nav-link">
                                    <span class="profile__nav-icon">🚪</span>
                                    Выйти
                                </a>
                            </li>
                        </ul>
                    </nav>
                </aside>

                <!-- Основной контент -->
                <div class="profile__main">
                    <?php
                    $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
                    
                    switch ($current_tab) {
                        case 'account':
                            include 'profile-account.php';
                            break;
                            
                        case 'favorites':
                            include 'profile-favorites.php';
                            break;
                            
                        default:
                            include 'profile-orders.php';
                            break;
                    }
                    ?>
                </div>
            </div>
        </section>
    </main>

<?php get_footer(); ?>