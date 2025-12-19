<?php
/**
 * Thankyou page
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0 // Обновлено до версии ядра
 */

defined( 'ABSPATH' ) || exit;

// Используем обертку, чтобы стилизовать страницу как iOS-приложение
?>

<div class="ios-checkout-header">
    <div class="ios-status-bar">
        <div class="ios-time" id="iosTime"></div>
        <div class="ios-indicators">
            <div class="ios-indicator"></div>
            <div class="ios-indicator"></div>
            <div class="ios-indicator"></div>
        </div>
    </div>
    <div class="ios-header-title">Заказ оформлен</div>
</div>

<div class="woocommerce-order ios-checkout-content">

    <?php
    if ( $order ) :

        do_action( 'woocommerce_before_thankyou', $order->get_id() );
        ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>
            
            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
                <?php endif; ?>
            </p>

        <?php else : 
            // Мы убираем стандартный заголовок "Thank you. Your order has been received.", 
            // так как эту роль выполняет модальное окно ниже.
        ?>

            <h2 class="ios-section-title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>
            
            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details ios-order-overview">

                <li class="woocommerce-order-overview__order order">
                    <span class="ios-detail-label"><?php esc_html_e( 'Order number:', 'woocommerce' ); ?></span>
                    <span class="ios-detail-value"><strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></span>
                </li>

                <li class="woocommerce-order-overview__date date">
                    <span class="ios-detail-label"><?php esc_html_e( 'Date:', 'woocommerce' ); ?></span>
                    <span class="ios-detail-value"><strong><?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></span>
                </li>

                <?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
                    <li class="woocommerce-order-overview__email email">
                        <span class="ios-detail-label"><?php esc_html_e( 'Email:', 'woocommerce' ); ?></span>
                        <span class="ios-detail-value"><strong><?php echo esc_html( $order->get_billing_email() ); ?></strong></span>
                    </li>
                <?php endif; ?>

                <li class="woocommerce-order-overview__total total">
                    <span class="ios-detail-label"><?php esc_html_e( 'Total:', 'woocommerce' ); ?></span>
                    <span class="ios-detail-value"><strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></span>
                </li>

                <?php if ( $order->get_payment_method_title() ) : ?>
                    <li class="woocommerce-order-overview__payment-method method">
                        <span class="ios-detail-label"><?php esc_html_e( 'Payment method:', 'woocommerce' ); ?></span>
                        <span class="ios-detail-value"><strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong></span>
                    </li>
                <?php endif; ?>

            </ul>
            
            <!-- Вывод информации о товарах в заказе и адреса -->
            <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
            <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

        <?php endif; ?>

    <?php else : ?>

        <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

    <?php endif; ?>

</div>

<div class="ios-action-buttons">
    <button type="button" class="ios-btn-primary" onclick="window.location.href='<?php echo esc_url( home_url() ); ?>'">Вернуться в магазин</button>
</div>

<!-- Success Modal (Должна быть в самом конце body или footer) -->
<div class="ios-success-modal" id="iosSuccessModal">
    <div class="ios-modal-content">
        <div class="ios-modal-icon">✓</div>
        <div class="ios-modal-title">Заказ оформлен успешно!</div>
        <div class="ios-modal-text">
            Спасибо за ваш заказ. Номер вашего заказа: 
            <strong><?php echo $order ? $order->get_order_number() : ''; ?></strong><br><br>
            Мы свяжемся с вами в ближайшее время для подтверждения заказа.
        </div>
        <button type="button" class="ios-btn-primary" onclick="window.location.href='<?php echo esc_url( home_url() ); ?>'">Продолжить покупки</button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    // Обновляем время
    function updateTime() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        $('#iosTime').text(hours + ':' + minutes);
    }
    updateTime();
    setInterval(updateTime, 60000);

    // Показываем модалку успеха сразу
    // Удалили таймаут 500 мс
    $('#iosSuccessModal').addClass('active');
    
    // В случае, если модалка закрывается, она больше не должна открываться
    $(document).on('click', '.ios-modal-close', function() {
        $('#iosSuccessModal').removeClass('active');
    });
});
</script>