<?php
/**
 * My Orders
 * Шаблон страницы с историей заказов
 */

get_header();

$current_user = wp_get_current_user();
?>

<div class="gnb-header">
    <div class="gnb-header__top">
        <button class="gnb-btn-icon" onclick="history.back()">←</button>
        <div class="gnb-header__title">История заказов</div>
        <div style="width: 40px;"></div>
    </div>
</div>

<div class="gnb-container">
    <?php
    $orders = gnb_get_user_orders($current_user->ID, 50);
    
    if (!empty($orders)):
    ?>
        <div class="gnb-section">
            <?php
            foreach ($orders as $order):
                $order_id = $order->get_id();
                $order_status = $order->get_status();
                $order_total = $order->get_total();
                $order_date = $order->get_date_created()->format('d F Y');
                $order_items = $order->get_items();
                $order_image = null;
                
                if (!empty($order_items)):
                    foreach ($order_items as $item):
                        $product = $item->get_product();
                        if ($product && $product->get_image()):
                            $order_image = $product->get_image();
                            break;
                        endif;
                    endforeach;
                endif;
            ?>
                <div class="gnb-card">
                    <div class="gnb-card__header">
                        <div style="flex: 1;">
                            <div class="gnb-card__subtitle">От <?php echo esc_html($order_date); ?> • <?php echo '#' . $order_id; ?></div>
                            <div class="gnb-card__status"><?php echo gnb_format_order_status($order_status); ?></div>
                            <div class="gnb-card__meta" style="margin-top: 8px; font-size: 14px;">
                                Вы получили 16 января
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="gnb-card__price"><?php echo wc_price($order_total); ?></div>
                            <?php if ($order_image): ?>
                                <div class="gnb-card__image" style="margin-top: 12px; width: 64px; height: 64px;">
                                    <?php echo $order_image; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="gnb-btn gnb-btn--secondary gnb-btn--small">
                        Подробнее
                    </a>
                </div>
            <?php
            endforeach;
            ?>
        </div>
    <?php
    else:
    ?>
        <div style="padding: 40px 20px; text-align: center;">
            <p style="color: var(--text-light); font-size: 16px;">У вас нет заказов</p>
            <a href="<?php echo esc_url(home_url('/shop')); ?>" class="gnb-btn gnb-btn--primary" style="margin-top: 20px;">
                Продолжить покупки
            </a>
        </div>
    <?php
    endif;
    ?>
</div>

<?php get_footer();