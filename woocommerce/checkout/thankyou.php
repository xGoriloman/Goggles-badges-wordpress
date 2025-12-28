<?php
/**
 * Thankyou page - страница благодарности
 * Скопировать в: theme/woocommerce/checkout/thankyou.php
 */

defined('ABSPATH') || exit;

$order = false;
if ($order_id = absint(get_query_var('order-received'))) {
    $order = wc_get_order($order_id);
}

// Данные заказа
$order_number = $order ? $order->get_order_number() : '';
$order_date = $order ? wc_format_datetime($order->get_date_created()) : '';
$order_total = $order ? $order->get_total() : 0;
$order_status = $order ? $order->get_status() : '';
$payment_method = $order ? $order->get_payment_method_title() : '';
$shipping_method = '';

// Получаем метод доставки
if ($order) {
    foreach ($order->get_shipping_methods() as $shipping) {
        $shipping_method = $shipping->get_name();
        break;
    }
}

// Данные доставки
$shipping_city = $order ? $order->get_shipping_city() : '';
$shipping_address = $order ? $order->get_shipping_address_1() : '';
$pvz_code = $order ? $order->get_meta('_cdek_pvz_code') : '';
$pvz_name = $order ? $order->get_meta('_cdek_pvz_name') : '';
$pvz_address = $order ? $order->get_meta('_cdek_pvz_address') : '';

// Определяем тип доставки
$is_pvz = !empty($pvz_code);
?>

<div class="thankyou-page">
    
    <?php if ($order) : ?>
    
    <!-- Успешный заказ -->
    <div class="thankyou-container">
        
        <!-- Иконка успеха -->
        <div class="thankyou-header">
            <div class="success-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="32" fill="#34C759"/>
                    <path d="M20 32L28 40L44 24" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1 class="thankyou-title">Заказ оформлен!</h1>
            <p class="thankyou-subtitle">Спасибо за покупку. Мы уже начали собирать ваш заказ.</p>
        </div>
        
        <!-- Номер заказа -->
        <div class="order-number-card">
            <span class="order-number-label">Номер заказа</span>
            <span class="order-number-value">#<?php echo esc_html($order_number); ?></span>
        </div>
        
        <!-- Информация о заказе -->
        <div class="thankyou-cards">
            
            <!-- Доставка -->
            <div class="thankyou-card">
                <div class="card-icon">📦</div>
                <div class="card-content">
                    <div class="card-label"><?php echo $is_pvz ? 'Пункт выдачи' : 'Доставка курьером'; ?></div>
                    <div class="card-value">
                        <?php 
                        if ($is_pvz && $pvz_address) {
                            $parts = explode(', ', $pvz_address);
                            if (count($parts) >= 5) {
                                echo esc_html($parts[2] . ', ' . implode(', ', array_slice($parts, 4)));
                            } else {
                                echo esc_html($pvz_address);
                            }
                        } else {
                            echo esc_html($shipping_city . ', ' . $shipping_address);
                        }
                        ?>
                    </div>
                    <?php if ($shipping_method) : ?>
                    <div class="card-hint"><?php echo esc_html($shipping_method); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Оплата -->
            <div class="thankyou-card">
                <div class="card-icon">💳</div>
                <div class="card-content">
                    <div class="card-label">Способ оплаты</div>
                    <div class="card-value"><?php echo esc_html($payment_method); ?></div>
                </div>
            </div>
            
            <!-- Статус -->
            <div class="thankyou-card">
                <div class="card-icon">⏱️</div>
                <div class="card-content">
                    <div class="card-label">Статус</div>
                    <div class="card-value">
                        <span class="status-badge status-<?php echo esc_attr($order_status); ?>">
                            <?php echo esc_html(wc_get_order_status_name($order_status)); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Товары -->
        <div class="thankyou-section">
            <h2 class="section-title">Товары в заказе</h2>
            <div class="order-items">
                <?php foreach ($order->get_items() as $item_id => $item) : 
                    $product = $item->get_product();
                    $quantity = $item->get_quantity();
                    $total = $item->get_total();
                ?>
                <div class="order-item">
                    <div class="item-image">
                        <?php if ($product) : ?>
                            <?php echo $product->get_image(); ?>
                        <?php endif; ?>
                    </div>
                    <div class="item-info">
                        <div class="item-name"><?php echo esc_html($item->get_name()); ?></div>
                        <div class="item-meta">
                            <?php 
                            $meta = $item->get_formatted_meta_data('_', true);
                            if ($meta) {
                                foreach ($meta as $m) {
                                    echo '<span>' . esc_html($m->display_key) . ': ' . esc_html(wp_strip_all_tags($m->display_value)) . '</span>';
                                }
                            }
                            ?>
                            <span>× <?php echo $quantity; ?></span>
                        </div>
                    </div>
                    <div class="item-price"><?php echo wc_price($total); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Итого -->
        <div class="thankyou-section">
            <div class="order-totals">
                <div class="total-row">
                    <span>Сумма товаров</span>
                    <span><?php echo wc_price($order->get_subtotal()); ?></span>
                </div>
                
                <?php if ($order->get_total_discount() > 0) : ?>
                <div class="total-row discount">
                    <span>Скидка</span>
                    <span>−<?php echo wc_price($order->get_total_discount()); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="total-row">
                    <span>Доставка</span>
                    <span><?php echo $order->get_shipping_total() > 0 ? wc_price($order->get_shipping_total()) : 'Бесплатно'; ?></span>
                </div>
                
                <div class="total-row total">
                    <span>Итого</span>
                    <span><?php echo wc_price($order_total); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Действия -->
        <div class="thankyou-actions">
            <?php if (is_user_logged_in()) : ?>
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="btn-primary">
                Мои заказы
            </a>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-secondary">
                На главную
            </a>
        </div>
        
        <!-- Контакты -->
        <div class="thankyou-footer">
            <p>Письмо с подтверждением отправлено на <strong><?php echo esc_html($order->get_billing_email()); ?></strong></p>
            <p>Есть вопросы? <a href="mailto:support@example.com">Напишите нам</a></p>
        </div>
        
    </div>
    
    <?php else : ?>
    
    <!-- Заказ не найден -->
    <div class="thankyou-container">
        <div class="thankyou-header">
            <div class="error-icon">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="32" fill="#FF3B30"/>
                    <path d="M24 24L40 40M40 24L24 40" stroke="white" stroke-width="4" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="thankyou-title">Заказ не найден</h1>
            <p class="thankyou-subtitle">К сожалению, мы не смогли найти информацию о заказе.</p>
        </div>
        
        <div class="thankyou-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">На главную</a>
        </div>
    </div>
    
    <?php endif; ?>
    
</div>

<style>
:root {
    --bg: #F5F7FA;
    --white: #FFFFFF;
    --black: #1D1D1F;
    --gray-100: #F5F5F7;
    --gray-200: #E5E5E7;
    --gray-400: #A1A1A6;
    --gray-500: #86868B;
    --primary: #191919;
    --success: #34C759;
    --danger: #FF3B30;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --font: -apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif;
}

.thankyou-page {
    font-family: var(--font);
    background: var(--bg);
    min-height: 100vh;
    padding: 40px 16px;
    color: var(--black);
}

.thankyou-container {
    max-width: 560px;
    margin: 0 auto;
}

/* Header */
.thankyou-header {
    text-align: center;
    margin-bottom: 32px;
}

.success-icon, .error-icon {
    margin-bottom: 24px;
}

.thankyou-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 12px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.thankyou-subtitle {
    font-size: 16px;
    color: var(--gray-500);
    margin: 0;
}

/* Order Number */
.order-number-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--white);
    padding: 20px 24px;
    border-radius: var(--radius-xl);
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}

.order-number-label {
    font-size: 14px;
    color: var(--gray-500);
}

.order-number-value {
    font-size: 18px;
    font-weight: 700;
}

/* Cards */
.thankyou-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}

.thankyou-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: var(--white);
    padding: 20px 24px;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
}

.card-icon {
    font-size: 24px;
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-content {
    flex: 1;
}

.card-label {
    font-size: 12px;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin-bottom: 4px;
}

.card-value {
    font-size: 15px;
    font-weight: 500;
}

.card-hint {
    font-size: 13px;
    color: var(--gray-500);
    margin-top: 4px;
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.status-pending, .status-on-hold { background: #FFF3CD; color: #856404; }
.status-processing { background: #CCE5FF; color: #004085; }
.status-completed { background: #D4EDDA; color: #155724; }
.status-cancelled, .status-failed { background: #F8D7DA; color: #721C24; }

/* Sections */
.thankyou-section {
    background: var(--white);
    padding: 24px;
    border-radius: var(--radius-xl);
    margin-bottom: 16px;
    box-shadow: var(--shadow-sm);
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin: 0 0 20px;
}

/* Order Items */
.order-items {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--gray-200);
}

.order-item:last-child {
    padding-bottom: 0;
    border-bottom: none;
}

.item-image {
    width: 64px;
    height: 64px;
    background: var(--gray-100);
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 4px;
}

.item-meta {
    font-size: 13px;
    color: var(--gray-500);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.item-price {
    font-size: 15px;
    font-weight: 600;
    white-space: nowrap;
}

/* Totals */
.order-totals {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 15px;
}

.total-row span:first-child {
    color: var(--gray-500);
}

.total-row.discount span:last-child {
    color: var(--danger);
}

.total-row.total {
    font-size: 18px;
    font-weight: 700;
    padding-top: 12px;
    border-top: 1px solid var(--gray-200);
    margin-top: 4px;
}

.total-row.total span:first-child {
    color: var(--black);
    text-transform: uppercase;
}

/* Actions */
.thankyou-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 32px;
}

.btn-primary, .btn-secondary {
    flex: 1;
    padding: 16px 24px;
    border-radius: 100px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--primary);
    color: var(--white);
}

.btn-primary:hover {
    background: #333;
}

.btn-secondary {
    background: var(--white);
    color: var(--black);
    border: 1px solid var(--gray-200);
}

.btn-secondary:hover {
    background: var(--gray-100);
}

/* Footer */
.thankyou-footer {
    text-align: center;
    font-size: 14px;
    color: var(--gray-500);
}

.thankyou-footer p {
    margin: 0 0 8px;
}

.thankyou-footer a {
    color: var(--primary);
    text-decoration: underline;
}

@media (max-width: 480px) {
    .thankyou-actions {
        flex-direction: column;
    }
}
</style>