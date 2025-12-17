<?php
/**
 * Template Name: Восстановление пароля
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
        <div class="back__container">
            <a href="javascript:history.back()" class="back__button">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon/arrow-black.svg" alt="Назад" />
                Назад
            </a>
        </div>

        <div class="auth-page">
            <div class="auth-page__container">
                <div class="auth-page__content">
                    <h1 class="auth-page__title">Восстановление пароля</h1>
                    
                    <?php
                    // Показываем сообщения WordPress если есть
                    if (isset($_GET['checkemail']) && $_GET['checkemail'] == 'confirm') {
                        echo '<div class="auth-message success">Проверьте вашу почту для получения ссылки восстановления пароля.</div>';
                    }
                    
                    if (isset($_GET['error'])) {
                        $errors = array(
                            'invalid_email' => 'Пользователь с таким email не найден',
                            'empty_email' => 'Введите email адрес'
                        );
                        if (isset($errors[$_GET['error']])) {
                            echo '<div class="auth-message error">' . $errors[$_GET['error']] . '</div>';
                        }
                    }
                    ?>

                    <!-- Форма запроса сброса пароля -->
                    <form class="auth-form" id="password-reset-form" method="post">
                        <div class="auth-form__group">
                            <label for="user_email" class="auth-form__label">Email адрес *</label>
                            <input type="email" id="user_email" name="user_email" class="auth-form__input" required>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                На этот email будет отправлена ссылка для сброса пароля
                            </small>
                        </div>
                        
                        <input type="hidden" name="action" value="password_reset_request">
                        <?php wp_nonce_field('password_reset_nonce', 'password_reset_nonce'); ?>
                        
                        <button type="submit" class="auth-form__button button-black" id="reset-submit">Отправить ссылку</button>
                    </form>
                    
                    <div class="auth-page__footer">
                        <p>Вспомнили пароль? <a href="<?php echo get_permalink(get_page_by_path('login')); ?>" class="auth-page__link">Войти</a></p>
                        <p>Нет аккаунта? <a href="<?php echo get_permalink(get_page_by_path('register')); ?>" class="auth-page__link">Зарегистрироваться</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetForm = document.getElementById('password-reset-form');
    const resetSubmit = document.getElementById('reset-submit');
    
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Показываем лоадер
            resetSubmit.textContent = 'Отправка...';
            resetSubmit.disabled = true;
            
            // Собираем данные формы
            const formData = new FormData(resetForm);
            
            // AJAX запрос
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Password reset response:', data);
                
                if (data.success) {
                    // Успешный запрос
                    window.flsNotifications.success(
                        data.data.message || 'Ссылка для сброса пароля отправлена на ваш email',
                        'Проверьте почту'
                    );
                    
                    // Очищаем форму
                    resetForm.reset();
                    
                    // Перенаправляем на страницу входа через 3 секунды
                    setTimeout(() => {
                        window.location.href = '<?php echo get_permalink(get_page_by_path('login')); ?>';
                    }, 3000);
                    
                } else {
                    // Ошибка
                    window.flsNotifications.error(
                        data.data.message || 'Произошла ошибка при отправке запроса',
                        'Ошибка'
                    );
                }
                
                resetSubmit.textContent = 'Отправить ссылку';
                resetSubmit.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                window.flsNotifications.error(
                    'Произошла ошибка сети. Попробуйте еще раз.',
                    'Ошибка сети'
                );
                
                resetSubmit.textContent = 'Отправить ссылку';
                resetSubmit.disabled = false;
            });
        });
    }
});
</script>

<?php get_footer(); ?>