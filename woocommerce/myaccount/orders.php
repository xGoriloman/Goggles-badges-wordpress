<?php
/**
 * Orders - список заказов
 * Скопировать в: theme/woocommerce/myaccount/orders.php
 */

defined('ABSPATH') || exit;

$customer_orders = wc_get_orders(array(
    'customer' => get_current_user_id(),
    'limit' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
));

// Статусы на русском
$status_labels = array(
    'pending' => 'Ожидает оплаты',
    'processing' => 'В обработке',
    'on-hold' => 'На удержании',
    'completed' => 'Уже у вас',
    'cancelled' => 'Отменён',
    'refunded' => 'Возвращён',
    'failed' => 'Не удался',
    'shipped' => 'В пути',
);
?>

<div class="orders-page">
    
    <!-- Заголовок -->
    <div class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="back-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <h1 class="page-title">История заказов</h1>
        <div></div>
    </div>
    
    <!-- Список заказов -->
    <div class="orders-list">
        <?php if (!empty($customer_orders)) : ?>
            <?php foreach ($customer_orders as $order) : 
                $order_id = $order->get_id();
                $order_number = $order->get_order_number();
                $order_date = $order->get_date_created();
                $order_total = $order->get_total();
                $order_status = $order->get_status();
                $items = $order->get_items();
                $first_item = reset($items);
                $product = $first_item ? $first_item->get_product() : null;
                
                $status_text = $status_labels[$order_status] ?? wc_get_order_status_name($order_status);
                $completed_date = $order->get_date_completed();
            ?>
            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="order-card">
                <div class="order-header">
                    <span class="order-meta">От <?php echo esc_html($order_date->date_i18n('j F Y')); ?> • <?php echo esc_html($order_number); ?>...</span>
                    <span class="order-total"><?php echo wc_price($order_total); ?></span>
                </div>
                <div class="order-status"><?php echo esc_html($status_text); ?></div>
                <?php if ($order_status === 'completed' && $completed_date) : ?>
                <div class="order-completed">Вы получили <?php echo esc_html($completed_date->date_i18n('j F')); ?></div>
                <?php elseif ($order_status === 'processing') : ?>
                <div class="order-completed">Ожидайте доставку</div>
                <?php endif; ?>
                
                <?php if ($product) : ?>
                <div class="order-product">
                    <div class="product-image">
                        <?php echo $product->get_image('thumbnail'); ?>
                    </div>
                </div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="empty-orders">
                <div class="empty-icon">📦</div>
                <h2 class="empty-title">Заказов пока нет</h2>
                <p class="empty-text">Самое время сделать первый!</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-primary">
                    Перейти в каталог
                </a>
            </div>
        <?php endif; ?>
    </div>
    
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
    --radius-md: 12px;
    --radius-xl: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --font: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'PF DinDisplay Pro', sans-serif;
}

.orders-page {
    font-family: var(--font);
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
    text-align : center;
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
    transition: background 0.2s ease;
}

.back-btn:hover {
    background: rgba(170, 178, 189, 0.3);
}

.page-title {
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    margin: 0;
}

/* Orders List */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Order Card */
.order-card {
    display: block;
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 16px;
    text-decoration: none;
    color: inherit;
    position: relative;
    min-height: 128px;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
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
    position: absolute;
    right: 16px;
    bottom: 16px;
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

/* Empty State */
.empty-orders {
    text-align: center;
    padding: 60px 24px;
    background: var(--white);
    border-radius: var(--radius-xl);
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-title {
    font-size: 20px;
    font-weight: 700;
    text-transform: uppercase;
    margin: 0 0 8px;
}

.empty-text {
    font-size: 16px;
    color: var(--gray-500);
    margin: 0 0 24px;
}

.btn-primary {
    display: inline-block;
    padding: 14px 32px;
    background: var(--primary);
    color: var(--white);
    border-radius: 100px;
    font-size: 16px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s ease;
}

.btn-primary:hover {
    background: #333;
}
</style>