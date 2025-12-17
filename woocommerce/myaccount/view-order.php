<?php
/**
 * View Order — Единый стиль GNB
 * Показывает детали заказа в стиле сайта
 */

defined('ABSPATH') || exit;

wc_print_notices();

$current_user = wp_get_current_user();
if (!$current_user || $order->get_customer_id() !== $current_user->ID) {
    echo '<p>Доступ запрещён.</p>';
    return;
}
?>

<div class="gnb-header">
    <div class="gnb-header__top">
        <button class="gnb-btn-icon" onclick="history.back()">←</button>
        <div class="gnb-header__title">Заказ №<?php echo esc_html($order->get_order_number()); ?></div>
        <div style="width: 40px;"></div>
    </div>
</div>

<div class="gnb-container">

    <!-- Статус заказа -->
    <div class="gnb-section">
        <div class="gnb-card">
            <div class="gnb-card__title">Статус заказа</div>

            <div class="gnb-card__meta" style="margin-top: 12px;">
                <strong>Номер заказа:</strong> #<?php echo esc_html($order->get_order_number()); ?>
            </div>
            <div class="gnb-card__meta">
                <strong>Дата:</strong> <?php echo wc_format_datetime($order->get_date_created()); ?>
            </div>
            <div class="gnb-card__meta">
                <strong>Статус:</strong>
                <span style="color: #09090b; font-weight: 600; text-transform: uppercase; font-size: 14px;">
                    <?php echo esc_html(gnb_format_order_status($order->get_status())); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Состав заказа -->
    <div class="gnb-section">
        <div class="gnb-section__title">Состав заказа</div>

        <?php
        $items = $order->get_items();
        foreach ($items as $item) :
            $_product = $item->get_product();
            $thumbnail = $_product ? $_product->get_image('thumbnail', array('style' => 'width: 100%; height: 100%; object-fit: cover;')) : '';
            $quantity = $item->get_quantity();
            $subtotal = $order->get_formatted_line_subtotal($item);
            $name = $item->get_name();
            ?>
            <div class="gnb-card">
                <div class="gnb-card__header" style="align-items: stretch;">
                    <div class="gnb-card__image" style="flex: 0 0 64px; margin-right: 16px;">
                        <?php echo $thumbnail; ?>
                    </div>
                    <div style="flex: 1;">
                        <div class="gnb-card__title" style="font-size: 16px;"><?php echo esc_html($name); ?></div>
                        <div class="gnb-card__meta" style="margin-top: 8px;">
                            <?php echo sprintf('Количество: %d шт.', $quantity); ?>
                        </div>
                        <div class="gnb-card__price" style="margin-top: 8px;"><?php echo $subtotal; ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Итоги -->
        <div class="gnb-card">
            <?php foreach ($order->get_order_item_totals() as $key => $total) : ?>
                <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-color); font-size: 14px;">
                    <span style="font-weight: 600; color: var(--text-dark);"><?php echo esc_html($total['label']); ?></span>
                    <span style="color: var(--text-dark);"><?php echo wp_kses_post($total['value']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Адрес доставки / ПВЗ СДЭК -->
    <div class="gnb-section">
        <div class="gnb-section__title">Доставка</div>
        <div class="gnb-card">
            <?php
            $pvz_address = get_post_meta($order->get_id(), '_shipping_cdek_pvz_address', true);
            if (!empty($pvz_address)) :
                ?>
                <div class="gnb-card__title">Пункт выдачи СДЭК</div>
                <div class="gnb-card__meta" style="margin-top: 12px;"><?php echo esc_html($pvz_address); ?></div>
                <?php
                $pvz_info = get_post_meta($order->get_id(), '_shipping_cdek_pvz_info', true);
                if ($pvz_info) :
                    ?>
                    <div class="gnb-card__meta" style="margin-top: 8px; font-size: 13px; color: var(--text-light);">
                        <?php echo nl2br(esc_html($pvz_info)); ?>
                    </div>
                <?php endif; ?>
            <?php else :
                $shipping_address = $order->get_formatted_shipping_address();
                if ($shipping_address) :
                    ?>
                    <div class="gnb-card__title">Адрес доставки</div>
                    <address class="gnb-card__meta" style="margin-top: 12px; line-height: 1.6;">
                        <?php echo wp_kses_post($shipping_address); ?>
                    </address>
                <?php else : ?>
                    <div class="gnb-card__meta" style="color: var(--text-light);">
                        Адрес доставки не указан.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Получатель -->
    <div class="gnb-section">
        <div class="gnb-section__title">Получатель</div>
        <div class="gnb-card">
            <div class="gnb-card__meta"><strong>Имя:</strong> <?php echo esc_html($order->get_formatted_billing_full_name()); ?></div>
            <?php if ($order->get_billing_phone()) : ?>
                <div class="gnb-card__meta"><strong>Телефон:</strong> <?php echo esc_html($order->get_billing_phone()); ?></div>
            <?php endif; ?>
            <?php if ($order->get_billing_email()) : ?>
                <div class="gnb-card__meta"><strong>Email:</strong> <?php echo esc_html($order->get_billing_email()); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Кнопка "Назад" -->
    <div style="padding: 16px; text-align: center;">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="gnb-btn gnb-btn--secondary">
            ← Вернуться к заказам
        </a>
    </div>

</div>

