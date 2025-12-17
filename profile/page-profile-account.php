<?php
/**
 * Template Name: Анкета профиля
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

// Обработка формы обновления профиля
if (isset($_POST['update_profile_nonce']) && wp_verify_nonce($_POST['update_profile_nonce'], 'update_profile')) {
    $user_id = get_current_user_id();
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $display_name = sanitize_text_field($_POST['display_name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    
    $userdata = array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $display_name,
        'user_email' => $email
    );
    
    $result = wp_update_user($userdata);
    
    if (!is_wp_error($result)) {
        // Обновляем телефон и другие мета-данные
        update_user_meta($user_id, 'billing_phone', $phone);
        update_user_meta($user_id, 'billing_first_name', $first_name);
        update_user_meta($user_id, 'billing_last_name', $last_name);
        
        $success_message = 'Профиль успешно обновлен';
    } else {
        $error_message = 'Ошибка при обновлении профиля: ' . $result->get_error_message();
    }
}

// Обработка смены пароля
if (isset($_POST['change_password_nonce']) && wp_verify_nonce($_POST['change_password_nonce'], 'change_password')) {
    $user_id = get_current_user_id();
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Проверяем текущий пароль
    $user = get_user_by('id', $user_id);
    if ($user && wp_check_password($current_password, $user->data->user_pass, $user_id)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                wp_set_password($new_password, $user_id);
                $success_message = 'Пароль успешно изменен';
                
                // Автоматически логиним пользователя после смены пароля
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
            } else {
                $error_message = 'Пароль должен содержать минимум 6 символов';
            }
        } else {
            $error_message = 'Новые пароли не совпадают';
        }
    } else {
        $error_message = 'Текущий пароль указан неверно';
    }
}

$user_phone = get_user_meta($current_user->ID, 'billing_phone', true);

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
                            <li class="profile__nav-item profile__nav-item--orders">
                                <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="profile__nav-link">
                                    <span class="profile__nav-icon">📦</span>
                                    Заказы
                                </a>
                            </li>
                            <li class="profile__nav-item profile__nav-item--account profile__nav-item--active">
                                <a href="<?php echo esc_url(get_permalink()); ?>" class="profile__nav-link" aria-current="page">
                                    <span class="profile__nav-icon">👤</span>
                                    Анкета
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
                    <div class="profile__content">
                        <?php if (isset($success_message)) : ?>
                            <div class="profile-message profile-message--success">
                                <?php echo esc_html($success_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_message)) : ?>
                            <div class="profile-message profile-message--error">
                                <?php echo esc_html($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Секция информации пользователя -->
                        <section class="profile-info">
                            <h2 class="profile-info__title">Информация</h2>

                            <form method="post" class="profile-info__form">
                                <?php wp_nonce_field('update_profile', 'update_profile_nonce'); ?>
                                
                                <div class="profile-info__row">
                                    <div class="profile-info__field">
                                        <label class="profile-info__label">Имя</label>
                                        <div class="profile-info__input-wrapper">
                                            <input type="text" 
                                                   name="first_name" 
                                                   class="profile-info__input" 
                                                   value="<?php echo esc_attr($current_user->first_name); ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <div class="profile-info__field">
                                        <label class="profile-info__label">Фамилия</label>
                                        <div class="profile-info__input-wrapper">
                                            <input type="text" 
                                                   name="last_name" 
                                                   class="profile-info__input" 
                                                   value="<?php echo esc_attr($current_user->last_name); ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-info__field">
                                    <label class="profile-info__label">Отображаемое имя</label>
                                    <div class="profile-info__input-wrapper">
                                        <input type="text" 
                                               name="display_name" 
                                               class="profile-info__input" 
                                               value="<?php echo esc_attr($current_user->display_name); ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="profile-info__field">
                                    <label class="profile-info__label">Email</label>
                                    <div class="profile-info__input-wrapper">
                                        <input type="email" 
                                               name="email" 
                                               class="profile-info__input" 
                                               value="<?php echo esc_attr($current_user->user_email); ?>"
                                               required>
                                    </div>
                                </div>

                                <div class="profile-info__field">
                                    <label class="profile-info__label">Телефон</label>
                                    <div class="profile-info__input-wrapper">
                                        <input type="tel" 
                                               name="phone" 
                                               class="profile-info__input" 
                                               value="<?php echo esc_attr($user_phone); ?>">
                                    </div>
                                </div>
                            </form>
                        </section>

                        <!-- Секция смены пароля -->
                        <section class="profile-password">
                            <h2 class="profile-password__title">Смена пароля</h2>

                            <form method="post" class="profile-password__form">
                                <?php wp_nonce_field('change_password', 'change_password_nonce'); ?>
                                
                                <div class="profile-password__field">
                                    <label class="profile-password__label">Действующий пароль</label>
                                    <div class="profile-password__input-wrapper">
                                        <input type="password" 
                                               name="current_password" 
                                               class="profile-password__input" 
                                               placeholder="Введите текущий пароль"
                                               required>
                                    </div>
                                </div>

                                <div class="profile-password__field">
                                    <label class="profile-password__label">Новый пароль</label>
                                    <div class="profile-password__input-wrapper">
                                        <input type="password" 
                                               name="new_password" 
                                               class="profile-password__input" 
                                               placeholder="Введите новый пароль"
                                               minlength="6"
                                               required>
                                    </div>
                                </div>

                                <div class="profile-password__field">
                                    <label class="profile-password__label">Подтвердите новый пароль</label>
                                    <div class="profile-password__input-wrapper">
                                        <input type="password" 
                                               name="confirm_password" 
                                               class="profile-password__input" 
                                               placeholder="Подтвердите новый пароль"
                                               minlength="6"
                                               required>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <!-- Кнопка сохранения -->
                        <div class="profile__actions">
                            <button type="submit" 
                                    form="profile-info__form" 
                                    class="profile__save-button button button--black">
                                Сохранить изменения
                            </button>
                            
                            <button type="submit" 
                                    form="profile-password__form" 
                                    class="profile__save-button button button--transparent">
                                Сменить пароль
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php get_footer(); ?>