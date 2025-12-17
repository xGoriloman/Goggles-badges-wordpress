<?php
/**
 * Order Received
 * Страница подтверждения заказа
 */

get_header();

$order = wc_get_order($order_id);
?>

<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/checkout-bg.jpg'); background-size: cover; background-position: center; z-index: -1;"></div>

<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: -1;"></div>

<div class="gnb-container" style="display: flex; align-items: flex-end; justify-content: center; min-height: 100vh; padding: 40px 16px;">
    <div style="background: white; border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0; padding: 32px 20px; width: 100%; max-width: 384px; text-align: center;">
        <div style="font-size: 40px; margin-bottom: 24px;">✓</div>
        
        <div class="gnb-section__title" style="margin-bottom: 16px;">Заказ оформлен</div>
        
        <p style="color: var(--text-dark); font-size: 16px; margin-bottom: 32px; line-height: 1.6;">
            Ваш заказ создан. Мы скоро свяжемся<br/>с вами, чтобы подтвердить информацию.
        </p>

        <?php if ($order): ?>
            <div style="background: var(--bg-light); padding: 16px; border-radius: var(--border-radius-md); margin-bottom: 24px; text-align: left;">
                <div style="font-size: 12px; color: var(--text-light); margin-bottom: 8px;">Номер заказа</div>
                <div style="font-size: 18px; font-weight: 700; color: var(--text-dark);">#<?php echo $order->get_order_number(); ?></div>
                
                <div style="font-size: 12px; color: var(--text-light); margin-top: 16px; margin-bottom: 8px;">Сумма</div>
                <div style="font-size: 18px; font-weight: 700; color: var(--text-dark);"><?php echo wc_price($order->get_total()); ?></div>
                
                <div style="font-size: 12px; color: var(--text-light); margin-top: 16px; margin-bottom: 8px;">Статус</div>
                <div style="font-size: 14px; color: var(--primary-color); font-weight: 600;">
                    <?php echo gnb_format_order_status($order->get_status()); ?>
                </div>
            </div>
        <?php endif; ?>

        <a href="<?php echo esc_url(home_url()); ?>" class="gnb-btn gnb-btn--primary">
            Продолжить
        </a>
    </div>
</div>

<?php get_footer();