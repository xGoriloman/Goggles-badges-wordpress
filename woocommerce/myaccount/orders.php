<?php
/**
 * Orders — Единый стиль GNB (как в view-order.php)
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();

// Получаем заказы
$customer_orders = wc_get_orders(array(
    'customer_id' => $current_user->ID,
    'numberposts' => 10,
    'paginate'    => false,
    'status'      => array_keys(wc_get_order_statuses())
));

$has_orders = !empty($customer_orders);
?>

<div class="gnb-header">
    <div class="gnb-header__top">
        <button class="gnb-btn-icon" onclick="history.back()">←</button>
        <div class="gnb-header__title">Мои заказы</div>
        <div style="width: 40px;"></div>
    </div>
</div>

<div class="gnb-container">

    <?php do_action('woocommerce_before_account_orders', $has_orders); ?>

    <div class="gnb-section">

        <?php if ($has_orders) : ?>
            <?php foreach ($customer_orders as $order) :
                $items = $order->get_items();
                $product = reset($items);
                $thumb_url = $product ? wp_get_attachment_image_url($product->get_product()->get_image_id(), 'thumbnail') : '';
                $is_delivered = $order->get_status() === 'completed';
                $date_received = $order->get_date_completed() ? date_i18n('j F', $order->get_date_completed()->getTimestamp()) : 'Ожидается';
                ?>

                <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="gnb-card">
                    <div class="gnb-card__header">
                        <div style="flex: 1;">
                            <div class="gnb-card__subtitle">
                                От <?php echo wc_format_datetime($order->get_date_created(), 'j F Y'); ?> • #<?php echo substr($order->get_order_number(), 0, 9); ?>...
                            </div>
                            <div class="gnb-card__status">
                                <?php echo $is_delivered ? 'Уже у вас' : esc_html(gnb_format_order_status($order->get_status())); ?>
                            </div>
                            <div class="gnb-card__meta" style="margin-top: 8px; font-size: 14px; color: #a1a5aa;">
                                <?php echo $is_delivered ? "Вы получили {$date_received}" : "Текущий статус"; ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="gnb-card__price"><?php echo $order->get_formatted_order_total(); ?></div>
                            <?php if ($thumb_url) : ?>
                                <div class="gnb-card__image" style="margin-top: 12px; width: 64px; height: 64px;">
                                    <img src="<?php echo esc_url($thumb_url); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>

            <?php endforeach; ?>
        <?php else : ?>

            <div style="padding: 40px 20px; text-align: center;">
                <p style="color: var(--text-light); font-size: 16px;">У вас нет заказов</p>
                <a href="<?php echo esc_url(home_url('/shop')); ?>" class="gnb-btn gnb-btn--primary" style="margin-top: 20px;">
                    Продолжить покупки
                </a>
            </div>

        <?php endif; ?>

    </div>

    <?php do_action('woocommerce_after_account_orders', $has_orders); ?>

</div>
