<?php
/**
 * Template Name: Страница регистрации
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
                    <h1 class="auth-page__title">Регистрация</h1>
                    
                    <?php
                    // Вывод ошибок и сообщений
                    if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
                        echo '<div class="auth-message success">Регистрация успешна! Теперь вы можете войти.</div>';
                    }
                    
                    if (isset($_GET['error'])) {
                        $errors = array(
                            'empty_fields' => 'Заполните все поля',
                            'password_mismatch' => 'Пароли не совпадают',
                            'email_exists' => 'Email уже зарегистрирован',
                            'invalid_email' => 'Неверный формат email',
                            'weak_password' => 'Пароль слишком слабый',
                            'username_exists' => 'Имя пользователя уже занято'
                        );
                        if (isset($errors[$_GET['error']])) {
                            echo '<div class="auth-message error">' . $errors[$_GET['error']] . '</div>';
                        }
                    }
                    ?>

                    <form class="auth-form" id="register-form" method="post">
                        <div class="auth-form__group">
                            <label for="reg_username" class="auth-form__label">Имя пользователя *</label>
                            <input type="text" id="reg_username" name="username" class="auth-form__input" required>
                            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                                Если имя занято, система предложит доступный вариант
                            </small>
                        </div>
                        
                        <div class="auth-form__group">
                            <label for="reg_email" class="auth-form__label">Email *</label>
                            <input type="email" id="reg_email" name="email" class="auth-form__input" required>
                        </div>
                        
                        <div class="auth-form__group">
                            <label for="reg_password" class="auth-form__label">Пароль *</label>
                            <input type="password" id="reg_password" name="password" class="auth-form__input" required>
                        </div>
                        
                        <div class="auth-form__group">
                            <label for="reg_password2" class="auth-form__label">Повторите пароль *</label>
                            <input type="password" id="reg_password2" name="password2" class="auth-form__input" required>
                        </div>
                        
                        <div class="auth-form__group">
                            <label class="auth-form__checkbox">
                                <input type="checkbox" name="agree_terms" required>
                                <span>Я согласен с <a href="<?php echo get_permalink(get_page_by_path('terms')); ?>" target="_blank">правилами сайта</a></span>
                            </label>
                        </div>
                        
                        <input type="hidden" name="action" value="custom_registration">
                        <input type="hidden" name="redirect_to" value="<?php 
                            echo isset($_GET['redirect_to']) ? esc_url($_GET['redirect_to']) : home_url();
                        ?>">
                        <?php wp_nonce_field('custom_registration_nonce', 'registration_nonce'); ?>
                        
                        <button type="submit" class="auth-form__button button-black" id="register-submit">Зарегистрироваться</button>
                        
                        <!-- Контейнер для сообщений AJAX -->
                        <div class="auth-form__message" id="register-message" style="display: none;"></div>
                    </form>
                    
                    <div class="auth-page__footer">
                        <p>Уже есть аккаунт? <a href="<?php echo get_permalink(get_page_by_path('login')); ?>" class="auth-page__link">Войти</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const registerSubmit = document.getElementById('register-submit');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Показываем лоадер
            registerSubmit.textContent = 'Регистрация...';
            registerSubmit.disabled = true;
            
            // Собираем данные формы
            const formData = new FormData(registerForm);
            
            console.log('Sending registration request...');
            
            // AJAX запрос
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Успешная регистрация
                    if (data.data.username && data.data.username !== formData.get('username')) {
                        window.flsNotifications.success(
                            'Регистрация успешна! Ваше имя пользователя: ' + data.data.username + '. Перенаправление...',
                            'Успех'
                        );
                    } else {
                        window.flsNotifications.success(
                            'Регистрация успешна! Перенаправление...',
                            'Успех'
                        );
                    }
                    
                    // Редирект после успешной регистрации
                    if (data.data.redirect) {
                        console.log('Redirecting to:', data.data.redirect);
                        setTimeout(() => {
                            window.location.href = data.data.redirect;
                        }, 2000);
                    } else {
                        console.log('Redirecting to home');
                        setTimeout(() => {
                            window.location.href = '<?php echo home_url(); ?>';
                        }, 2000);
                    }
                    
                } else {
                    // Обработка ошибок с разными форматами ответа
                    let errorMessage = 'Произошла ошибка при регистрации';
                    
                    if (data.data && data.data.message) {
                        // Формат: data.data.message
                        errorMessage = data.data.message;
                    } else if (data.message) {
                        // Формат: data.message
                        errorMessage = data.message;
                    } else if (data.data && Array.isArray(data.data)) {
                        // Формат: data.data как массив ошибок
                        errorMessage = data.data.join(', ');
                    } else if (typeof data.data === 'string') {
                        // Формат: data.data как строка
                        errorMessage = data.data;
                    }
                    
                    console.log('Error message:', errorMessage);
                    
                    // Показываем уведомление об ошибке
                    window.flsNotifications.error(
                        errorMessage,
                        'Ошибка регистрации'
                    );
                    
                    // Возвращаем кнопку в исходное состояние
                    registerSubmit.textContent = 'Зарегистрироваться';
                    registerSubmit.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.flsNotifications.error(
                    'Произошла ошибка сети. Попробуйте еще раз.',
                    'Ошибка сети'
                );
                
                registerSubmit.textContent = 'Зарегистрироваться';
                registerSubmit.disabled = false;
            });
        });
    }

    // Проверка доступности имени пользователя в реальном времени
    const usernameInput = document.getElementById('reg_username');
    if (usernameInput) {
        let checkTimeout;
        
        usernameInput.addEventListener('input', function() {
            clearTimeout(checkTimeout);
            const username = this.value.trim();
            
            if (username.length < 3) {
                return;
            }
            
            checkTimeout = setTimeout(() => {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=check_username&username=' + encodeURIComponent(username)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.available) {
                        window.flsNotifications.warning(
                            'Имя пользователя "' + username + '" уже занято. Система предложит доступный вариант при регистрации.',
                            'Имя занято'
                        );
                    }
                });
            }, 500);
        });
    }
});
</script>

<?php get_footer(); ?>