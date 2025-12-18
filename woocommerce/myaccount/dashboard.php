<div class="gnb-header">
    <div class="gnb-header__top">
        <button class="gnb-btn-icon" onclick="history.back()">←</button>
        <div class="gnb-header__title">Профиль</div>
        <div style="width: 40px;"></div>
    </div>
</div>

<div class="gnb-container">
    <!-- Profile Card -->
    <div class="gnb-profile">
        <?php
        $avatar_url = get_avatar_url($current_user->ID);
        if (!$avatar_url) {
            $avatar_url = get_template_directory_uri() . '/assets/images/placeholder.png';
        }
        ?>
        <div class="gnb-profile__avatar">
            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" loading="lazy">
        </div>
        <div class="gnb-profile__name"><?php echo esc_html($current_user->first_name . ' ' . $current_user->last_name); ?></div>
        <div class="gnb-profile__username">@<?php echo esc_html($current_user->user_login); ?></div>
    </div>

    <!-- Check which page is being displayed -->
    <?php
    $current_endpoint = '';
    foreach (wc_get_account_menu_items() as $endpoint => $label) {
        if (is_wc_endpoint_url($endpoint)) {
            $current_endpoint = $endpoint;
            break;
        }
    }
    ?>

    <?php if (is_wc_endpoint_url('orders') || is_wc_endpoint_url()): ?>
        <!-- ORDERS PAGE -->
        <div class="gnb-section">
            <div class="gnb-section__title">Мои заказы</div>
            
            <?php
            $current_user = wp_get_current_user();
            $orders = gnb_get_user_orders($current_user->ID, 50);
            
            if (!empty($orders)):
                foreach ($orders as $order):
                    $order_id = $order->get_id();
                    $order_status = $order->get_status();
                    $order_total = $order->get_total();
                    $order_date = $order->get_date_created()->format('d.m.Y');
                    $order_items = $order->get_items();
                    $order_image = null;
                    
                    // Get first product image
                    if (!empty($order_items)):
                        foreach ($order_items as $item):
                            $product = $item->get_product();
                            if ($product):
                                $thumbnail_id = $product->get_image_id();
                                if ($thumbnail_id):
                                    $order_image = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
                                endif;
                                break;
                            endif;
                        endforeach;
                    endif;
            ?>
                    <div class="gnb-card">
                        <div class="gnb-card__header">
                            <div style="flex: 1;">
                                <div class="gnb-card__subtitle">От <?php echo esc_html($order_date); ?> • #<?php echo esc_html($order_id); ?></div>
                                <div class="gnb-card__status"><?php echo esc_html(gnb_format_order_status($order_status)); ?></div>
                                <div class="gnb-card__meta" style="margin-top: 8px; font-size: 14px;">
                                    <?php
                                    if ('completed' === $order_status) {
                                        echo 'Вы получили ' . esc_html($order->get_date_completed()->format('d.m.Y'));
                                    } else {
                                        echo 'В процессе доставки';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="gnb-card__price"><?php echo wp_kses_post(wc_price($order_total)); ?></div>
                                <?php if ($order_image): ?>
                                    <div class="gnb-card__image" style="margin-top: 12px; width: 64px; height: 64px;">
                                        <img src="<?php echo esc_url($order_image[0]); ?>" alt="Product" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="gnb-btn gnb-btn--secondary gnb-btn--small" style="display: inline-block;">
                            Подробнее
                        </a>
                    </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="gnb-card">
                    <p style="text-align: center; color: var(--text-light);">У вас нет заказов</p>
                </div>
            <?php
            endif;
            ?>
        </div>

    <?php elseif (is_wc_endpoint_url('edit-address')): ?>
        <!-- EDIT ADDRESS PAGE -->
        <div class="gnb-section">
            <div class="gnb-section__title">Мои адреса</div>
            
            <div class="gnb-card">
                <p style="text-align: center; color: var(--text-light);">Адреса доставки</p>
            </div>
        </div>

    <?php elseif (is_wc_endpoint_url('edit-account')): ?>
        <!-- EDIT ACCOUNT PAGE -->
        <div class="gnb-section">
            <div class="gnb-section__title">Мой аккаунт</div>
            
            <div class="gnb-card">
                <p style="text-align: center; color: var(--text-light);">Редактирование аккаунта</p>
            </div>
        </div>

    <?php else: ?>
        <!-- DASHBOARD PAGE (DEFAULT) -->
        <!-- Recent Orders -->
        <div class="gnb-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div class="gnb-section__title" style="margin-bottom: 0;">Мои заказы</div>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" style="color: var(--text-light); font-size: 12px; text-decoration: none;">
                    Смотреть все →
                </a>
            </div>
            
            <?php
            $orders = gnb_get_user_orders($current_user->ID, 2);
            
            if (!empty($orders)):
                foreach ($orders as $order):
                    $order_id = $order->get_id();
                    $order_status = $order->get_status();
                    $order_total = $order->get_total();
                    $order_date = $order->get_date_created()->format('d.m.Y');
                    $order_items = $order->get_items();
                    $order_image = null;
                    
                    if (!empty($order_items)):
                        foreach ($order_items as $item):
                            $product = $item->get_product();
                            if ($product):
                                $thumbnail_id = $product->get_image_id();
                                if ($thumbnail_id):
                                    $order_image = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
                                endif;
                                break;
                            endif;
                        endforeach;
                    endif;
            ?>
                    <div class="gnb-card">
                        <div class="gnb-card__header">
                            <div style="flex: 1;">
                                <div class="gnb-card__subtitle">От <?php echo esc_html($order_date); ?> • #<?php echo esc_html($order_id); ?></div>
                                <div class="gnb-card__status"><?php echo esc_html(gnb_format_order_status($order_status)); ?></div>
                                <div class="gnb-card__meta" style="margin-top: 8px; font-size: 14px;">
                                    <?php
                                    if ('completed' === $order_status) {
                                        echo 'Вы получили ' . esc_html($order->get_date_completed()->format('d.m.Y'));
                                    } else {
                                        echo 'В процессе доставки';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="gnb-card__price"><?php echo wp_kses_post(wc_price($order_total)); ?></div>
                                <?php if ($order_image): ?>
                                    <div class="gnb-card__image" style="margin-top: 12px; width: 64px; height: 64px;">
                                        <img src="<?php echo esc_url($order_image[0]); ?>" alt="Product" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- CDEK Points -->
        <div class="gnb-section">
            <div class="gnb-section__title">Пункты выдачи СДЭК</div>
            
            <?php
            $cdek_points = get_user_meta($current_user->ID, 'cdek_points', true);
            
            if (!empty($cdek_points) && is_array($cdek_points)):
                foreach ($cdek_points as $point):
            ?>
                    <div class="gnb-card">
                        <div class="gnb-card__title"><?php echo esc_html($point['code'] ?? 'CDEK'); ?></div>
                        <div class="gnb-card__meta" style="font-size: 14px; margin-top: 8px;">
                            <strong>Адрес:</strong> <?php echo esc_html($point['address'] ?? 'Нет данных'); ?>
                        </div>
                        <?php if (!empty($point['phone'])): ?>
                            <div class="gnb-card__meta" style="font-size: 14px; margin-top: 4px;">
                                <strong>Телефон:</strong> <?php echo esc_html($point['phone']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="gnb-card">
                    <p style="text-align: center; color: var(--text-light);">Нет сохраненных пунктов выдачи</p>
                </div>
            <?php
            endif;
            ?>

            <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="gnb-btn gnb-btn--primary" style="margin-top: 12px;">
                Управлять пунктами
            </a>
        </div>

    <?php endif; ?>

</div>

<style>
    .woocommerce-MyAccount-navigation {
        background: white;
        border-radius: var(--border-radius-lg);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .woocommerce-MyAccount-navigation ul {
        list-style: none;
        display: flex;
        flex-direction: column;
    }

    .woocommerce-MyAccount-navigation li {
        border-bottom: 1px solid var(--border-color);
    }

    .woocommerce-MyAccount-navigation li:last-child {
        border-bottom: none;
    }

    .woocommerce-MyAccount-navigation a {
        display: block;
        padding: 14px 16px;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s ease;
    }

    .woocommerce-MyAccount-navigation a:hover,
    .woocommerce-MyAccount-navigation a.is-active {
        background: var(--bg-light);
        color: var(--primary-color);
    }
</style>