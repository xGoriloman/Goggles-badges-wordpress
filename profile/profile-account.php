<?php
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
        // Обновляем телефон
        update_user_meta($user_id, 'billing_phone', $phone);
        
        echo '<div class="profile-message profile-message--success">Профиль успешно обновлен</div>';
    } else {
        echo '<div class="profile-message profile-message--error">Ошибка при обновлении профиля</div>';
    }
}

$current_user = wp_get_current_user();
$user_phone = get_user_meta($current_user->ID, 'billing_phone', true);
?>

<div class="profile-account">
    <div class="profile-account__header">
        <h2 class="profile-account__title">Анкета</h2>
    </div>

    <form method="post" class="profile-account__form">
        <?php wp_nonce_field('update_profile', 'update_profile_nonce'); ?>
        
        <div class="profile-account__fields">
            <div class="profile-account__field">
                <label for="first_name" class="profile-account__label">Имя</label>
                <input type="text" 
                       id="first_name" 
                       name="first_name" 
                       value="<?php echo esc_attr($current_user->first_name); ?>" 
                       class="profile-account__input" 
                       required>
            </div>
            
            <div class="profile-account__field">
                <label for="last_name" class="profile-account__label">Фамилия</label>
                <input type="text" 
                       id="last_name" 
                       name="last_name" 
                       value="<?php echo esc_attr($current_user->last_name); ?>" 
                       class="profile-account__input" 
                       required>
            </div>
            
            <div class="profile-account__field">
                <label for="display_name" class="profile-account__label">Отображаемое имя</label>
                <input type="text" 
                       id="display_name" 
                       name="display_name" 
                       value="<?php echo esc_attr($current_user->display_name); ?>" 
                       class="profile-account__input" 
                       required>
            </div>
            
            <div class="profile-account__field">
                <label for="email" class="profile-account__label">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?php echo esc_attr($current_user->user_email); ?>" 
                       class="profile-account__input" 
                       required>
            </div>
            
            <div class="profile-account__field">
                <label for="phone" class="profile-account__label">Телефон</label>
                <input type="tel" 
                       id="phone" 
                       name="phone" 
                       value="<?php echo esc_attr($user_phone); ?>" 
                       class="profile-account__input">
            </div>
        </div>
        
        <button type="submit" class="profile-account__submit button-black">
            Сохранить изменения
        </button>
    </form>
</div>