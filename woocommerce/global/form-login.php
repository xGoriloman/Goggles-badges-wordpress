<?php
/**
 * Template Name: Страница входа
 */

if (!defined('ABSPATH')) {
    exit;
}

// Если пользователь уже авторизован, перенаправляем на главную
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header(); ?>

<main class="page">
    <div class="page__container">
        <div style="margin-top:30px;" class="back__container">
            <a href="javascript:history.back()" class="back__button">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon/arrow-black.svg" alt="Назад" />
                Назад
            </a>
        </div>

        <div class="auth-page">
            <div class="auth-page__container">
                <div class="auth-page__content">
                    <h1 class="auth-page__title">Вход в аккаунт</h1>
                    
                    <?php
                    // Вывод ошибок
                    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo '<div class="auth-message error">Неверный логин или пароль</div>';
                    }
                    
                    if (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') {
                        echo '<div class="auth-message success">Проверьте вашу почту для сброса пароля</div>';
                    }
                    ?>

                    <form class="auth-form" method="post" action="<?php echo wp_login_url(); ?>">
                        <div class="auth-form__group">
                            <label for="login_username" class="auth-form__label">Имя пользователя или Email *</label>
                            <input type="text" id="login_username" name="log" class="auth-form__input" required>
                        </div>
                        
                        <div class="auth-form__group">
                            <label for="login_password" class="auth-form__label">Пароль *</label>
                            <input type="password" id="login_password" name="pwd" class="auth-form__input" required>
                        </div>
                        
                        <div class="auth-form__group auth-form__group--row">
                            <label class="auth-form__checkbox">
                                <input type="checkbox" name="rememberme">
                                <span>Запомнить меня</span>
                            </label>
                            
                            <a href="<?php echo get_permalink(get_page_by_path('password-reset')); ?>" class="auth-form__forgot">Забыли пароль?</a>
                        </div>
                        
                        <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>">
                        
                        <button type="submit" class="auth-form__button button-black">Войти</button>
                    </form>
                    
                    <div class="auth-page__footer">
                        <p>Нет аккаунта? <a href="<?php echo get_permalink(get_page_by_path('register')); ?>" class="auth-page__link">Зарегистрироваться</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>