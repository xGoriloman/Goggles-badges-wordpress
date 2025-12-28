<?php
/**
 * My Account Dashboard
 * Скопировать в: theme/woocommerce/myaccount/dashboard.php
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$user_id = get_current_user_id();

// Данные пользователя
$first_name = $current_user->first_name;
$last_name = $current_user->last_name;
$display_name = $current_user->display_name;
$user_email = $current_user->user_email;
$username = $current_user->user_login;

// Полное имя
$full_name = trim($first_name . ' ' . $last_name);
if (empty($full_name)) {
    $full_name = $display_name;
}

// Аватар
$avatar_url = get_avatar_url($user_id, array('size' => 120));

// Последние заказы
$orders = wc_get_orders(array(
    'customer_id' => $user_id,
    'limit' => 5,
    'orderby' => 'date',
    'order' => 'DESC',
));

// Адреса СДЭК
$cdek_addresses = array();
if (function_exists('cdek_get_addresses')) {
    $cdek_addresses = cdek_get_addresses();
}

// Статистика
$total_orders = wc_get_customer_order_count($user_id);
$total_spent = wc_get_customer_total_spent($user_id);
?>

<div class="account-dashboard">
    
    <!-- Профиль -->
    <section class="dashboard-card profile-card">
        <div class="profile-avatar">
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($full_name); ?>">
        </div>
        <h1 class="profile-name"><?php echo esc_html($full_name); ?></h1>
        <span class="profile-username">@<?php echo esc_html($username); ?></span>
    </section>
    
    <!-- Адреса -->
    <?php if (!empty($cdek_addresses) || true) : // Всегда показываем секцию ?>
    <section class="dashboard-section">
        <h2 class="section-title">Мои адреса</h2>
        <div class="dashboard-card addresses-card">
            <?php 
            $cdek_addresses = function_exists('cdek_get_addresses') ? cdek_get_addresses() : array();
            $pvz_addresses = array_filter($cdek_addresses, function($a) { return !empty($a['pvz_code']); });
            $door_addresses = array_filter($cdek_addresses, function($a) { return empty($a['pvz_code']) && !empty($a['address']); });
            
            $all_addresses = array_merge(array_values($pvz_addresses), array_values($door_addresses));
            
            // Сортируем: сначала по умолчанию
            usort($all_addresses, function($a, $b) {
                return ($b['is_default'] ?? 0) - ($a['is_default'] ?? 0);
            });
            
            if (!empty($all_addresses)) :
                $counter = 0;
                $total = min(count($all_addresses), 3);
                
                foreach ($all_addresses as $addr) : 
                    if ($counter >= 3) break;
                    $counter++;
                    
                    $is_pvz = !empty($addr['pvz_code']);
                    $is_default = !empty($addr['is_default']);
                    
                    // Формируем отображаемый адрес
                    if ($is_pvz) {
                        $parts = explode(', ', $addr['pvz_address'] ?? '');
                        if (count($parts) >= 5) {
                            $display_address = implode(', ', array_slice($parts, 3));
                        } else {
                            $display_address =  $addr['pvz_address'] ?? $addr['pvz_name'] ?? '';
                        }
                        $type_label = 'ПВЗ';
                        $type_class = 'type-pvz';
                    } else {
                        $display_address = trim(($addr['city'] ?? '') . ', ' . ($addr['address'] ?? ''));
                        if (!empty($addr['apartment'])) {
                            $display_address .= ', кв. ' . $addr['apartment'];
                        }
                        $display_address = ltrim($display_address, ', ');
                        $type_label = 'Курьер';
                        $type_class = 'type-door';
                    }
                ?>
                <div class="address-item <?php echo $is_default ? 'is-default' : ''; ?>">
                    <div class="address-type "><svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M10.3577 18.2303C9.96191 18.5977 9.44151 18.8013 8.90149 18.8C8.36179 18.8011 7.84175 18.5975 7.44619 18.2303C3.87229 14.8634 -0.917505 11.1023 1.41799 5.642C2.68249 2.69 5.71369 0.800003 8.90239 0.800003C12.0911 0.800003 15.1232 2.69 16.3859 5.642C18.7187 11.096 13.9406 14.8751 10.3577 18.2303Z" stroke="#AAB2BD" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
  <path d="M12.0517 8.90001C12.0517 9.31368 11.9702 9.72329 11.8119 10.1055C11.6536 10.4876 11.4216 10.8349 11.1291 11.1274C10.8366 11.4199 10.4893 11.6519 10.1072 11.8102C9.72498 11.9685 9.31537 12.05 8.90171 12.05C8.48804 12.05 8.07843 11.9685 7.69626 11.8102C7.31408 11.6519 6.96683 11.4199 6.67432 11.1274C6.38182 10.8349 6.14979 10.4876 5.99149 10.1055C5.83319 9.72329 5.75171 9.31368 5.75171 8.90001C5.75171 8.06458 6.08358 7.26337 6.67432 6.67263C7.26506 6.08189 8.06628 5.75002 8.90171 5.75002C9.73714 5.75002 10.5384 6.08189 11.1291 6.67263C11.7198 7.26337 12.0517 8.06458 12.0517 8.90001Z" stroke="#AAB2BD" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
</svg></div>
                    <div class="address-info">
                        <span class="address-text"><?php echo esc_html($display_address); ?></span>
                    </div>
                    <?php if ($is_default) : ?>
                    <div class="address-default">
                        <svg width="20" height="20" viewBox="0 0 22 22" fill="none">
                            <path d="M11.001 0C17.0763 0.00020563 22.001 4.92499 22.001 11C22.001 17.075 17.0763 21.9998 11.001 22C4.92552 22 0 17.0751 0 11C0 4.92487 4.92552 0 11.001 0ZM17.209 6.27051C16.8513 5.92533 16.2807 5.93629 15.9355 6.29395L9.1543 13.4648L6.08691 10.2148C5.74177 9.85723 5.17214 9.84734 4.81445 10.1924C4.45685 10.5375 4.44606 11.1072 4.79102 11.4648L8.50586 15.3857C8.67548 15.5615 8.91004 15.6611 9.1543 15.6611C9.39841 15.661 9.63223 15.5614 9.80176 15.3857L17.2314 7.54395C17.5766 7.18629 17.5666 6.61568 17.209 6.27051Z" fill="#34C759"/>
                        </svg>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($counter < $total) : ?>
                <div class="address-divider"></div>
                <?php endif; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="address-item empty">
                    <div class="address-icon">📍</div>
                    <span class="address-text" style="color: var(--gray-400);">Добавьте адрес доставки</span>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="btn-outline">
            <?php echo !empty($all_addresses) ? 'Управление адресами' : 'Добавить адрес'; ?>
        </a>
    </section>
    <?php endif; ?>
    
    <!-- Заказы -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">Заказы</h2>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="section-link">Смотреть все</a>
        </div>
        
        <?php if (!empty($orders)) : ?>
            <?php foreach ($orders as $order) : 
                $order_id = $order->get_id();
                $order_number = $order->get_order_number();
                $order_date = $order->get_date_created();
                $order_total = $order->get_total();
                $order_status = $order->get_status();
                $items = $order->get_items();
                $first_item = reset($items);
                $product = $first_item ? $first_item->get_product() : null;
                
                // Статус на русском
                $status_labels = array(
                    'pending' => 'Ожидает оплаты',
                    'processing' => 'В обработке',
                    'on-hold' => 'На удержании',
                    'completed' => 'Уже у вас',
                    'cancelled' => 'Отменён',
                    'refunded' => 'Возвращён',
                    'failed' => 'Не удался',
                );
                $status_text = $status_labels[$order_status] ?? wc_get_order_status_name($order_status);
                
                // Дата получения (для completed)
                $completed_date = $order->get_date_completed();
            ?>
            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="dashboard-card order-card">
                <div class="order-header">
                    <span class="order-meta">От <?php echo esc_html($order_date->date_i18n('j F Y')); ?> • <?php echo esc_html($order_number); ?></span>
                    <span class="order-total"><?php echo wc_price($order_total); ?></span>
                </div>
                <div class="order-footer">
                <div class="order-footer-block">
                    <div class="order-status"><?php echo esc_html($status_text); ?></div>
                    <?php if ($order_status === 'completed' && $completed_date) : ?>
                        <div class="order-completed">Вы получили <?php echo esc_html($completed_date->date_i18n('j F')); ?></div>
                    <?php else : ?>
                        <div class="order-completed">Ожидайте доставку</div>
                    <?php endif; ?>

                </div>
                    
                    <?php if ($product) : ?>
                    <div class="order-product">
                        <div class="product-image">
                            <?php echo $product->get_image('thumbnail'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="dashboard-card empty-card">
                <div class="empty-icon">📦</div>
                <p class="empty-text">У вас пока нет заказов</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-primary-sm">
                    Перейти в каталог
                </a>
            </div>
        <?php endif; ?>
    </section>
    
</div>

<style>
:root {
    --bg: #F5F7FA;
    --white: #FFFFFF;
    --black: #1D1D1F;
    --gray-100: #F5F5F7;
    --gray-200: #E5E5E7;
    --gray-400: #AAB2BD;
    --gray-500: #86868B;
    --primary: #191919;
    --success: #34C759;
    --radius-md: 12px;
    --radius-xl: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
}

.account-dashboard {
    font-family: var(--font-family);
    max-width: 400px;
    margin: 0 auto;
    padding: 16px;
    background: var(--bg);
    min-height: 100vh;
}

/* Profile Card */
.profile-card {
    text-align: center;
    padding: 32px 24px;
    position: relative;
}

.profile-avatar {
    width: 112px;
    height: 112px;
    margin: 0 auto 16px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--white);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-name {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin: 0 0 4px;
}

.profile-username {
    font-size: 12px;
    color: var(--gray-400);
}

/* Dashboard Cards */
.dashboard-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Section */
.dashboard-section {
    margin-top: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin: 0 0 16px;
}

.section-header .section-title {
    margin: 0;
}

.section-link {
    font-size: 12px;
    color: var(--black);
    text-decoration: none;
}

/* Addresses Card */
.addresses-card {
    padding: 16px;
    margin-bottom: 16px;
}

.address-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    justify-content: space-between;
}

.address-info{
    flex: 1 1 auto;
}

.address-icon {
    flex-shrink: 0;
}

.address-text {
    font-size: 16px;
    font-weight: 500;
}

.address-divider {
    height: 1px;
    background: var(--gray-200);
}

/* Button Outline */
.btn-outline {
    display: block;
    width: 100%;
    padding: 16px;
    background: transparent;
    border: 1px solid var(--gray-200);
    border-radius: 100px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    color: var(--black);
    transition: all 0.2s ease;
}

.btn-outline:hover {
    background: var(--white);
}

/* Order Card */
.order-card {
    padding: 16px;
    margin-bottom: 16px;
    position: relative;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.order-footer{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
}

.order-footer-block{

}

.order-meta {
    font-size: 12px;
    color: var(--gray-400);
}

.order-total {
    font-size: 16px;
    font-weight: 700;
}

.order-status {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.order-completed {
    font-size: 16px;
    font-weight: 500;
    color: var(--black);
    margin-top: 4px;
}

.order-product {
}

.product-image {
    width: 64px;
    height: 64px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Empty Card */
.empty-card {
    text-align: center;
    padding: 40px 24px;
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.empty-text {
    font-size: 16px;
    color: var(--gray-500);
    margin: 0 0 20px;
}

.btn-primary-sm {
    display: inline-block;
    padding: 12px 24px;
    background: var(--primary);
    color: var(--white);
    border-radius: 100px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

/* Quick Actions */
.quick-actions {
    background: var(--white);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    text-decoration: none;
    color: var(--black);
    border-bottom: 1px solid var(--gray-200);
    transition: background 0.2s ease;
}

.action-item:last-child {
    border-bottom: none;
}

.action-item:hover {
    background: var(--gray-100);
}

.action-icon {
    font-size: 20px;
}

.action-text {
    flex: 1;
    font-size: 16px;
    font-weight: 500;
}

.action-arrow {
    color: var(--gray-400);
}

.action-logout {
    color: #FF3B30;
}
</style>