<?php
/**
 * View Order - детали заказа
 * Скопировать в: theme/woocommerce/myaccount/view-order.php
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id);
if (!$order) {
    return;
}

$order_number = $order->get_order_number();
$order_date = $order->get_date_created();
$order_total = $order->get_total();
$order_status = $order->get_status();
$payment_method = $order->get_payment_method_title();
$items = $order->get_items();

// Доставка
$shipping_city = $order->get_shipping_city();
$shipping_address = $order->get_shipping_address_1();
$pvz_code = $order->get_meta('_cdek_pvz_code');
$pvz_name = $order->get_meta('_cdek_pvz_name');
$pvz_address = $order->get_meta('_cdek_pvz_address');
$is_pvz = !empty($pvz_code);

// Метод доставки
$shipping_method = '';
foreach ($order->get_shipping_methods() as $shipping) {
    $shipping_method = $shipping->get_name();
    break;
}

// Статусы
$status_labels = array(
    'pending' => array('text' => 'Ожидает оплаты', 'icon' => '⏳', 'color' => '#FF9500'),
    'processing' => array('text' => 'В обработке', 'icon' => '📦', 'color' => '#191919'),
    'on-hold' => array('text' => 'На удержании', 'icon' => '⏸️', 'color' => '#FF9500'),
    'completed' => array('text' => 'Выполнен', 'icon' => '✅', 'color' => '#34C759'),
    'cancelled' => array('text' => 'Отменён', 'icon' => '❌', 'color' => '#FF3B30'),
    'refunded' => array('text' => 'Возвращён', 'icon' => '↩️', 'color' => '#191919'),
    'failed' => array('text' => 'Не удался', 'icon' => '⚠️', 'color' => '#FF3B30'),
    'shipped' => array('text' => 'В пути', 'icon' => '🚚', 'color' => '#007AFF'),
);

$status_info = $status_labels[$order_status] ?? array(
    'text' => wc_get_order_status_name($order_status),
    'icon' => '📋',
    'color' => '#8E8E93'
);

// Трекинг СДЭК
$cdek_tracking = $order->get_meta('_cdek_tracking_number');
$cdek_uuid = $order->get_meta('_cdek_order_uuid');
?>

<div class="view-order-page">
    
    <!-- Заголовок -->
    <div class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="back-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <div class="header-info">
            <span class="header-subtitle">Заказ #<?php echo esc_html($order_number); ?></span>
            <span class="header-date">от <?php echo esc_html($order_date->date_i18n('j F Y')); ?></span>
        </div>
    </div>
    
    <!-- Статус -->
    <div class="status-card" style="--status-color: <?php echo esc_attr($status_info['color']); ?>">
        <div class="status-icon"><?php echo $status_info['icon']; ?></div>
        <div class="status-content">
            <div class="status-label">Статус заказа</div>
            <div class="status-text"><?php echo esc_html($status_info['text']); ?></div>
        </div>
    </div>
    
    <!-- Трекинг СДЭК -->
    <?php if ($cdek_tracking) : ?>
    <div class="tracking-card">
        <div class="tracking-header">
            <span class="tracking-label">Трек-номер СДЭК</span>
            <button class="copy-btn" data-copy="<?php echo esc_attr($cdek_tracking); ?>">
                Копировать
            </button>
        </div>
        <div class="tracking-number"><?php echo esc_html($cdek_tracking); ?></div>
        <a href="https://www.cdek.ru/ru/tracking?order_id=<?php echo esc_attr($cdek_tracking); ?>" 
           target="_blank" class="tracking-link">
            Отследить на сайте СДЭК →
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Доставка -->
    <div class="section-card">
        <h2 class="section-title">Доставка</h2>
        
        <div class="info-row">
            <span class="info-icon"><?php echo $is_pvz ? '📦' : '🚚'; ?></span>
            <div class="info-content">
                <div class="info-label"><?php echo $is_pvz ? 'Пункт выдачи' : 'Курьером'; ?></div>
                <div class="info-value">
                    <?php 
                    if ($is_pvz && $pvz_address) {
                        $parts = explode(', ', $pvz_address);
                        if (count($parts) >= 5) {
                            echo esc_html($parts[2] . ', ' . implode(', ', array_slice($parts, 4)));
                        } else {
                            echo esc_html($pvz_address);
                        }
                    } else {
                        echo esc_html(trim($shipping_city . ', ' . $shipping_address, ', '));
                    }
                    ?>
                </div>
                <?php if ($shipping_method) : ?>
                <div class="info-hint"><?php echo esc_html($shipping_method); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Получатель -->
    <div class="section-card">
        <h2 class="section-title">Получатель</h2>
        
        <div class="info-row">
            <span class="info-icon">👤</span>
            <div class="info-content">
                <div class="info-value">
                    <?php echo esc_html($order->get_formatted_billing_full_name()); ?>
                </div>
            </div>
        </div>
        
        <div class="info-row">
            <span class="info-icon">📱</span>
            <div class="info-content">
                <div class="info-value">
                    <?php echo esc_html($order->get_billing_phone()); ?>
                </div>
            </div>
        </div>
        
        <div class="info-row">
            <span class="info-icon">✉️</span>
            <div class="info-content">
                <div class="info-value">
                    <?php echo esc_html($order->get_billing_email()); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Товары -->
    <div class="section-card">
        <h2 class="section-title">Товары</h2>
        
        <div class="items-list">
            <?php foreach ($items as $item_id => $item) : 
                $product = $item->get_product();
                $quantity = $item->get_quantity();
                $total = $item->get_total();
            ?>
            <div class="item-row">
                <div class="item-image">
                    <?php if ($product) : ?>
                        <?php echo $product->get_image('thumbnail'); ?>
                    <?php endif; ?>
                </div>
                <div class="item-info">
                    <div class="item-name"><?php echo esc_html($item->get_name()); ?></div>
                    <div class="item-meta">
                        <?php 
                        $meta = $item->get_formatted_meta_data('_', true);
                        if ($meta) {
                            $meta_parts = array();
                            foreach ($meta as $m) {
                                $meta_parts[] = wp_strip_all_tags($m->display_value);
                            }
                            echo esc_html(implode(' • ', $meta_parts));
                        }
                        ?>
                        × <?php echo $quantity; ?>
                    </div>
                </div>
                <div class="item-price"><?php echo wc_price($total); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Оплата -->
    <div class="section-card">
        <h2 class="section-title">Оплата</h2>
        
        <div class="info-row">
            <span class="info-icon">💳</span>
            <div class="info-content">
                <div class="info-value"><?php echo esc_html($payment_method); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Итого -->
    <div class="section-card totals-card">
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
    
    <!-- Действия -->
    <?php if ($order_status === 'pending') : ?>
    <div class="order-actions">
        <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn-primary">
            Оплатить заказ
        </a>
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
    --gray-400: #AAB2BD;
    --gray-500: #86868B;
    --primary: #191919;
    --success: #34C759;
    --danger: #FF3B30;
    --radius-md: 12px;
    --radius-xl: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
}

.view-order-page {
    font-family: var(--font-family);
    max-width: 400px;
    margin: 0 auto;
    padding: 16px;
    background: var(--bg);
    min-height: 100vh;
}

/* Header */
.page-header {
    display: grid;
    grid-template-columns: 0.2fr 1fr .2fr;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding: 16px;
    background: rgba(255,255,255,0.8);
    backdrop-filter: blur(10px);
    border-radius: 0 0 var(--radius-xl) var(--radius-xl);
    margin: -16px -16px 24px;
    padding: 16px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.back-btn {
    width: 40px;
    height: 40px;
    border-radius: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-400);
    text-decoration: none;
}

.header-info {
    display: flex;
    flex-direction: column;
}

.header-subtitle {
    font-size: 16px;
    font-weight: 600;
}

.header-date {
    font-size: 12px;
    color: var(--gray-400);
}

/* Status Card */
.status-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--white);
    padding: 20px;
    border-radius: var(--radius-xl);
    margin-bottom: 16px;
    border-left: 4px solid var(--status-color);
}

.status-icon {
    font-size: 32px;
}

.status-content {
    flex: 1;
}

.status-label {
    font-size: 12px;
    color: var(--gray-400);
    text-transform: uppercase;
    margin-bottom: 4px;
}

.status-text {
    font-size: 18px;
    font-weight: 700;
    color: var(--status-color);
}

/* Tracking Card */
.tracking-card {
    background: var(--white);
    padding: 20px;
    border-radius: var(--radius-xl);
    margin-bottom: 16px;
}

.tracking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.tracking-label {
    font-size: 12px;
    color: var(--gray-400);
    text-transform: uppercase;
}

.copy-btn {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 13px;
    cursor: pointer;
    padding: 4px 8px;
}

.tracking-number {
    font-size: 20px;
    font-weight: 700;
    font-family: 'SF Mono', monospace;
    margin-bottom: 12px;
}

.tracking-link {
    display: inline-block;
    color: #007AFF;
    font-size: 14px;
    text-decoration: none;
}

/* Section Card */
.section-card {
    background: var(--white);
    padding: 20px;
    border-radius: var(--radius-xl);
    margin-bottom: 16px;
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: var(--gray-400);
    margin: 0 0 16px;
}

/* Info Row */
.info-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-200);
}

.info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-row:first-of-type {
    padding-top: 0;
}

.info-icon {
    font-size: 20px;
    width: 32px;
    text-align: center;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 12px;
    color: var(--gray-400);
    margin-bottom: 2px;
}

.info-value {
    font-size: 15px;
    font-weight: 500;
}

.info-hint {
    font-size: 13px;
    color: var(--gray-500);
    margin-top: 2px;
}

/* Items List */
.items-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.item-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item-image {
    width: 56px;
    height: 56px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 2px;
}

.item-meta {
    font-size: 13px;
    color: var(--gray-500);
}

.item-price {
    font-size: 15px;
    font-weight: 600;
}

/* Totals */
.totals-card {
    padding: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 15px;
    margin-bottom: 12px;
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
    margin-bottom: 0;
}

.total-row.total span:first-child {
    color: var(--black);
    text-transform: uppercase;
}

/* Actions */
.order-actions {
    margin-bottom: 16px;
}

.btn-primary {
    display: block;
    width: 100%;
    padding: 16px;
    background: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 100px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.btn-danger {
    display: block;
    width: 100%;
    padding: 16px;
    background: transparent;
    color: var(--danger);
    border: 1px solid var(--danger);
    border-radius: 100px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}
</style>

<script>
document.querySelectorAll('.copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var text = this.dataset.copy;
        navigator.clipboard.writeText(text).then(function() {
            btn.textContent = 'Скопировано!';
            setTimeout(function() {
                btn.textContent = 'Копировать';
            }, 2000);
        });
    });
});
</script>