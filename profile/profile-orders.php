<?php
// Получаем заказы пользователя
$customer_orders = wc_get_orders(array(
    'customer' => get_current_user_id(),
    'limit' => 10,
    'orderby' => 'date',
    'order' => 'DESC'
));
?>

<div class="profile-orders">
    <div class="profile-orders__header">
        <h2 class="profile-orders__title">Заказы</h2>
    </div>

    <div class="profile-orders__list">
        <?php if (!empty($customer_orders)) : ?>
            <?php foreach ($customer_orders as $order) : 
                $order_id = $order->get_id();
                $order_date = $order->get_date_created();
                $order_total = $order->get_total();
                $order_status = $order->get_status();
                $order_items = $order->get_items();
                
                // Получаем статус заказа на русском
                $status_labels = array(
                    'pending'    => 'Ожидает оплаты',
                    'processing' => 'В обработке',
                    'on-hold'    => 'На удержании',
                    'completed'  => 'Завершен',
                    'cancelled'  => 'Отменен',
                    'refunded'   => 'Возвращен',
                    'failed'     => 'Неудачный'
                );
                
                $status_label = isset($status_labels[$order_status]) ? $status_labels[$order_status] : $order_status;
                
                // Иконка в зависимости от статуса
                $status_icon = 'delivery-process.svg';
                $status_class = '';
                
                switch ($order_status) {
                    case 'completed':
                        $status_icon = 'delivery-check.svg';
                        $status_class = 'order-card__icon-bg--success';
                        break;
                    case 'processing':
                        $status_icon = 'delivery-truck.svg';
                        $status_class = 'order-card__icon-bg--primary';
                        break;
                    case 'pending':
                        $status_icon = 'delivery-process.svg';
                        $status_class = 'order-card__icon-bg--warning';
                        break;
                    default:
                        $status_icon = 'delivery-process.svg';
                        $status_class = 'order-card__icon-bg--secondary';
                }
                ?>
                
                <div class="profile-orders__item order-card">
                    <div class="order-card__content">
                        <div class="order-card__info">
                            <div class="order-card__date">
                                От <?php echo $order_date->format('d F Y'); ?> • #<?php echo $order_id; ?>
                            </div>
                            <h3 class="order-card__status"><?php echo esc_html($status_label); ?></h3>
                            <div class="order-card__delivery">
                                <?php
                                // Информация о доставке
                                $shipping_method = $order->get_shipping_method();
                                if ($shipping_method) {
                                    echo esc_html($shipping_method);
                                } else {
                                    echo 'Информация о доставке';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="order-card__price">
                            <div class="order-card__amount"><?php echo wc_price($order_total); ?></div>
                            <div class="order-card__icon">
                                <div class="order-card__icon-bg order-card__icon-bg--primary <?php echo $status_class; ?>"></div>
                                <div class="order-card__icon-bg order-card__icon-bg--secondary"></div>
                                <img class="order-card__icon-image" 
                                     src="<?php echo get_template_directory_uri(); ?>/assets/img/icon/<?php echo $status_icon; ?>" 
                                     alt="Статус заказа" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-card__actions">
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" 
                           class="order-card__action order-card__action--view">
                            Подробнее
                        </a>
                        
                        <?php if ($order_status === 'completed') : ?>
                            <a href="<?php echo esc_url(wc_get_endpoint_url('view-order', $order_id, wc_get_page_permalink('myaccount'))); ?>" 
                               class="order-card__action order-card__action--repeat">
                                Повторить заказ
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url($order->get_view_order_url()); ?>" 
                               class="order-card__action order-card__action--track">
                                Отследить
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="profile-orders__empty">
                <p>У вас пока нет заказов</p>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="button button-black">
                    Начать покупки
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>