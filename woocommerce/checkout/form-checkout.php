<?php
/**
 * Checkout Form - с работающим сохранением адресов
 * Скопировать в: theme/woocommerce/checkout/form-checkout.php
 */

defined('ABSPATH') || exit;

$checkout = WC()->checkout();
$cart = WC()->cart;

// Данные пользователя
$is_logged_in = is_user_logged_in();
$has_cdek = function_exists('cdek_has_addresses');

if ($has_cdek && $is_logged_in) {
    $has_addresses = cdek_has_addresses();
    $addresses = $has_addresses ? cdek_get_addresses() : array();
    $default_address = $has_addresses ? cdek_get_default_address() : null;
} else {
    $has_addresses = false;
    $addresses = array();
    $default_address = null;
}

// Тип доставки по умолчанию
$default_delivery_type = 'pvz';
if ($default_address) {
    $default_delivery_type = !empty($default_address['pvz_code']) ? 'pvz' : 'door';
}
$default_cdek_code = $default_address['city_code'] ?? '';

?>

<div class="checkout-page">
    <form name="checkout" method="post" class="checkout woocommerce-checkout" 
          action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
        
        <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
        
        <div class="checkout-grid">
            
            <!-- ЛЕВАЯ КОЛОНКА -->
            <div class="checkout-main">
                
                <!-- АДРЕС ДОСТАВКИ -->
                <section class="checkout-card" id="address-section">
                    <h2 class="card-title">Адрес доставки</h2>
                    
                    <!-- Выбранный адрес -->
                    <div class="selected-address <?php echo !$default_address ? 'hidden' : ''; ?>" id="selected-address">
                        <div class="selected-address__icon"><svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M10.3577 18.2303C9.96191 18.5977 9.44151 18.8013 8.90149 18.8C8.36179 18.8011 7.84175 18.5975 7.44619 18.2303C3.87229 14.8634 -0.917505 11.1023 1.41799 5.642C2.68249 2.69 5.71369 0.800003 8.90239 0.800003C12.0911 0.800003 15.1232 2.69 16.3859 5.642C18.7187 11.096 13.9406 14.8751 10.3577 18.2303Z" stroke="#AAB2BD" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
  <path d="M12.0517 8.90001C12.0517 9.31368 11.9702 9.72329 11.8119 10.1055C11.6536 10.4876 11.4216 10.8349 11.1291 11.1274C10.8366 11.4199 10.4893 11.6519 10.1072 11.8102C9.72498 11.9685 9.31537 12.05 8.90171 12.05C8.48804 12.05 8.07843 11.9685 7.69626 11.8102C7.31408 11.6519 6.96683 11.4199 6.67432 11.1274C6.38182 10.8349 6.14979 10.4876 5.99149 10.1055C5.83319 9.72329 5.75171 9.31368 5.75171 8.90001C5.75171 8.06458 6.08358 7.26337 6.67432 6.67263C7.26506 6.08189 8.06628 5.75002 8.90171 5.75002C9.73714 5.75002 10.5384 6.08189 11.1291 6.67263C11.7198 7.26337 12.0517 8.06458 12.0517 8.90001Z" stroke="#AAB2BD" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
</svg></div>
                        <div class="selected-address__content">
                            <div class="selected-address__type" id="address-type-badge">
                                <?php echo $default_delivery_type === 'pvz' ? 'Пункт выдачи' : 'Курьером'; ?>
                            </div>
                            <div class="selected-address__text" id="address-text">
                                <?php 
                                if ($default_address) {
                                    echo esc_html(explode(', ', $default_address['pvz_address'])[2] . ', ' . implode(', ', array_slice(explode(', ', $default_address['pvz_address']), 4)));
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Форма выбора адреса -->
                    <div class="address-form <?php echo $default_address ? 'collapsed' : ''; ?>" id="address-form">
                        
                        <!-- Сохранённые адреса -->
                        <div class="saved-addresses-container" id="saved-addresses-container">
                            <?php if (!empty($addresses)) : ?>
                            <div class="saved-addresses__label">Сохранённые адреса</div>
                            <div class="saved-addresses-list" id="saved-addresses-list">
                                <?php foreach ($addresses as $addr) : 
                                    $is_default = !empty($addr['is_default']);
                                    $is_pvz = !empty($addr['pvz_code']);
                                ?>
                                <div class="address-option <?php echo $is_default ? 'active' : ''; ?>"
                                     data-id="<?php echo esc_attr($addr['id']); ?>"
                                     data-type="<?php echo $is_pvz ? 'pvz' : 'door'; ?>"
                                     data-city="<?php echo esc_attr($addr['city'] ?? ''); ?>"
                                     data-address="<?php echo esc_attr($addr['address'] ?? ''); ?>"
                                     data-postcode="<?php echo esc_attr($addr['postcode'] ?? ''); ?>"
                                     data-city-code="<?php echo esc_attr($addr['city_code'] ?? ''); ?>"
                                     data-pvz-code="<?php echo esc_attr($addr['pvz_code'] ?? ''); ?>"
                                     data-pvz-name="<?php echo esc_attr($addr['pvz_name'] ?? ''); ?>"
                                     data-pvz-address="<?php echo esc_attr($addr['pvz_address'] ?? ''); ?>">
                                    <div class="address-option__radio"><div class="radio-inner"></div></div>
                                    <div class="address-option__content">
                                        <span class="address-option__badge"><?php echo $is_pvz ? 'ПВЗ' : 'Адрес'; ?></span>
                                        <div class="address-option__main">
                                            <?php 
                                                $parts = explode(', ', $addr['pvz_address']);
                                                echo esc_html($is_pvz ? ($parts[2] . ', ' . implode(', ', array_slice($parts, 4)) ?: 'ПВЗ') : ($addr['city'] . ', ' . $addr['address'] ?: $addr['city'])); 
                                            ?>
                                        </div>
                                        
                                    </div>
                                    <?php if ($is_default) : ?>
                                    <span class="address-option__default"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M11.001 0C17.0763 0.00020563 22.001 4.92499 22.001 11C22.001 17.075 17.0763 21.9998 11.001 22C4.92552 22 0 17.0751 0 11C0 4.92487 4.92552 0 11.001 0ZM17.209 6.27051C16.8513 5.92546 16.2817 5.93633 15.9365 6.29395L9.1543 13.4648L6.08691 10.2148C5.74172 9.85725 5.17211 9.84723 4.81445 10.1924C4.45681 10.5376 4.44681 11.1072 4.79199 11.4648L8.50684 15.3857C8.67645 15.5615 8.91004 15.6611 9.1543 15.6611C9.39852 15.6611 9.63216 15.5615 9.80176 15.3857L17.2314 7.54395C17.5766 7.18629 17.5667 6.61568 17.209 6.27051Z" fill="#34C759" />
</svg></span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="divider-or"><span>или добавьте новый</span></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Тип доставки -->
                        <div class="delivery-type-selector">
                            <button type="button" class="delivery-tab <?php echo $default_delivery_type === 'pvz' ? 'active' : ''; ?>" data-type="pvz">
                                📦 Пункт выдачи
                            </button>
                            <button type="button" class="delivery-tab <?php echo $default_delivery_type === 'door' ? 'active' : ''; ?>" data-type="door">
                                🚚 Курьером
                            </button>
                        </div>
                        
                        <!-- Секция ПВЗ -->
                        <div class="pvz-section <?php echo $default_delivery_type === 'pvz' ? '' : 'hidden'; ?>" id="pvz-section">
                            <button type="button" class="pvz-picker" id="open-pvz-map">
                                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
<path d="M10.4585 20.918C4.68555 20.918 -0.000488281 16.2319 -0.000488281 10.459C-0.000488281 4.68604 4.68555 0 10.4585 0C16.2314 0 20.9175 4.68604 20.9175 10.459C20.9175 16.2319 16.2314 20.918 10.4585 20.918ZM10.4585 19.1748C15.2778 19.1748 19.1743 15.2783 19.1743 10.459C19.1743 5.63965 15.2778 1.74316 10.4585 1.74316C5.63916 1.74316 1.74268 5.63965 1.74268 10.459C1.74268 15.2783 5.63916 19.1748 10.4585 19.1748ZM10.3662 6.91113C9.62793 6.91113 9.0332 6.31641 9.0332 5.56787C9.0332 4.81934 9.62793 4.22461 10.3662 4.22461C11.1147 4.22461 11.6992 4.81934 11.6992 5.56787C11.6992 6.31641 11.1147 6.91113 10.3662 6.91113ZM8.66406 16.2012C8.24365 16.2012 7.91553 15.8936 7.91553 15.4731C7.91553 15.0732 8.24365 14.7554 8.66406 14.7554H9.93555V10.0591H8.83838C8.41797 10.0591 8.08984 9.75146 8.08984 9.33105C8.08984 8.93115 8.41797 8.61328 8.83838 8.61328H10.7661C11.2891 8.61328 11.5659 8.98242 11.5659 9.53613V14.7554H12.8374C13.2578 14.7554 13.5859 15.0732 13.5859 15.4731C13.5859 15.8936 13.2578 16.2012 12.8374 16.2012H8.66406Z" fill="#AAB2BD"/>
</svg> <span>Найти ближайший офис CDEK</span> <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 19" fill="none">
<path d="M5.57764 18.8979C3.7832 18.8979 2.37842 18.3853 1.44531 17.4521C0.491699 16.5088 -0.000488281 15.104 -0.000488281 13.3096V5.58838C-0.000488281 3.79395 0.501953 2.38916 1.44531 1.4458C2.36816 0.522949 3.7832 0 5.57764 0H13.3193C15.124 0 16.5186 0.512695 17.4517 1.4458C18.4053 2.38916 18.9077 3.79395 18.9077 5.58838V13.3096C18.9077 15.104 18.4053 16.5088 17.4517 17.4521C16.5288 18.375 15.124 18.8979 13.3193 18.8979H5.57764ZM12.7554 12.0688C13.2168 12.0688 13.5244 11.71 13.5244 11.2178V6.23438C13.5244 5.60889 13.186 5.36279 12.6426 5.36279H7.62842C7.12598 5.36279 6.80811 5.67041 6.80811 6.14209C6.80811 6.60352 7.13623 6.91113 7.64893 6.91113H9.55615L11.125 6.74707L9.47412 8.28516L5.59814 12.1611C5.44434 12.3149 5.3418 12.5303 5.3418 12.7354C5.3418 13.2173 5.64941 13.5249 6.11084 13.5249C6.36719 13.5249 6.57227 13.4326 6.72607 13.2788L10.5918 9.40283L12.1196 7.77246L11.9658 9.42334V11.2383C11.9658 11.7407 12.2734 12.0688 12.7554 12.0688Z" fill="#AAB2BD"/>
</svg>
                            </button>
                            <div class="pvz-result hidden" id="pvz-result">
                                <span class="pvz-result__icon">✓</span>
                                <div class="pvz-result__content">
                                    <div class="pvz-result__name" id="pvz-result-name"></div>
                                    <div class="pvz-result__address" id="pvz-result-address"></div>
                                </div>
                                <button type="button" class="pvz-result__change" id="change-pvz">Изменить</button>
                            </div>
                        </div>
                        
                        <!-- Секция адреса -->
                        <div class="door-section <?php echo $default_delivery_type === 'door' ? '' : 'hidden'; ?>" id="door-section">
                            <div class="form-row">
                                <label class="form-label">Город *</label>
                                <input type="text" class="form-input" id="input-city" placeholder="Москва"
                                       value="<?php echo esc_attr($default_address['city'] ?? ''); ?>">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Улица, дом, квартира</label>
                                <input type="text" class="form-input" id="input-address" placeholder="ул. Пушкина, д. 10"
                                       value="<?php echo esc_attr($default_address['address'] ?? ''); ?>">
                            </div>
                            <div class="form-row">
                                <label class="form-label">Индекс</label>
                                <input type="text" class="form-input" id="input-postcode" placeholder="123456"
                                       value="<?php echo esc_attr($default_address['postcode'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="form-actions">
                            <?php if ($default_address) : ?>
                            <button type="button" class="btn-secondary" id="cancel-address">Отмена</button>
                            <?php endif; ?>
                            <button type="button" class="btn-primary" id="save-address">
                                <span class="btn-text">Сохранить адрес</span>
                                <span class="btn-loading hidden">⏳</span>
                            </button>
                        </div>
                    </div>
                </section>
                
                <!-- КОНТАКТЫ -->
                <section class="checkout-card">
                    <h2 class="card-title">Контактные данные</h2>
                    <div class="form-row">
                        <label class="form-label">ФИО *</label>
                        <input type="text" class="form-input" id="input-name" placeholder="Иванов Иван Иванович"
                               value="<?php echo esc_attr(trim($checkout->get_value('billing_first_name') . ' ' . $checkout->get_value('billing_last_name'))); ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label">Телефон *</label>
                        <input type="tel" class="form-input" id="input-phone" placeholder="+7 999 999-99-99"
                               value="<?php echo esc_attr($checkout->get_value('billing_phone')); ?>">
                    </div>
                    <div class="form-row">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-input" id="input-email" placeholder="mail@example.com"
                               value="<?php echo esc_attr($checkout->get_value('billing_email')); ?>">
                    </div>
                </section>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            </div>
            
            <!-- ПРАВАЯ КОЛОНКА -->
            <div class="checkout-sidebar">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>
        </div>

        <?php 
            $address = '';
            $postcode = '';

            if($default_address['address']) {
                $address = esc_attr($default_address['address']);
            } else {
                $address = esc_attr($default_address['pvz_address']);
            }

            if ($default_address['postcode']) {
                $postcode = esc_attr($default_address['postcode']);
            } else {
                $postcode = esc_attr($default_address['pvz_code']);
            }
        ?>
        
        <!-- СКРЫТЫЕ ПОЛЯ WooCommerce -->
        <input type="hidden" name="billing_first_name" id="billing_first_name" value="<?php echo esc_attr($checkout->get_value('billing_first_name')); ?>">
        <input type="hidden" name="billing_last_name" id="billing_last_name" value="<?php echo esc_attr($checkout->get_value('billing_last_name')); ?>">
        <input type="hidden" name="billing_phone" id="billing_phone" value="<?php echo esc_attr($checkout->get_value('billing_phone')); ?>">
        <input type="hidden" name="billing_email" id="billing_email" value="<?php echo esc_attr($checkout->get_value('billing_email')); ?>">
        <input type="hidden" name="billing_city" id="billing_city" value="<?php echo esc_attr($default_address['city'] ?? ''); ?>">
        <input type="hidden" name="billing_address_1" id="billing_address_1" value="<?php echo $address; ?>">
        <input type="hidden" name="billing_postcode" id="billing_postcode" value="<?php echo $postcode; ?>">
        <input type="hidden" name="billing_country" value="RU">
        <input type="hidden" name="billing_state" id="billing_state" value="<?php echo esc_attr($default_address['city'] ?? ''); ?>">
        
        
        
        
        
        <input type="hidden" name="cdek_pvz_code" id="cdek_pvz_code" value="<?php echo esc_attr($default_address['pvz_code'] ?? ''); ?>">
        <input type="hidden" name="cdek_pvz_name" id="cdek_pvz_name" value="<?php echo esc_attr($default_address['pvz_name'] ?? ''); ?>">
        <input type="hidden" name="cdek_pvz_address" id="cdek_pvz_address" value="<?php echo esc_attr($default_address['pvz_address'] ?? ''); ?>">
        <input type="hidden" name="cdek_city_code" id="cdek_city_code" value="<?php echo esc_attr($default_cdek_code); ?>">
        <input type="hidden" name="delivery_type" id="delivery_type" value="<?php echo esc_attr($default_delivery_type); ?>">
        <input type="hidden" name="shipping_method[0]" id="shipping_method" value="">
    </form>
</div>

<?php if (function_exists('cdek_render_modal')) cdek_render_modal(); ?>

<style>
:root {
    --bg: #F5F7FA;
    --white: #FFFFFF;
    --black: #1D1D1F;
    --gray-100: #F5F5F7;
    --gray-200: #E5E5E7;
    --gray-300: #D1D1D6;
    --gray-400: #A1A1A6;
    --gray-500: #86868B;
    --primary: #191919;
    --success: #34C759;
    --danger: #FF3B30;
    --warning: #FF9500;
    --radius-md: 12px;
    --radius-lg: 16px;
    --radius-xl: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --transition: 0.25s ease;
    --font: -apple-system, BlinkMacSystemFont, 'SF Pro Display', sans-serif;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.checkout-page {
    font-family: var(--font);
    background: var(--bg);
    min-height: 100vh;
    color: var(--black);
    font-size: 15px;
    line-height: 1.5;
}

.checkout-grid {
    margin-top: 30px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 24px;
    align-items: start;
}

.checkout-main { display: flex; flex-direction: column; gap: 24px; }
.checkout-sidebar { position: sticky; top: 24px; }

.checkout-card {
}

.card-title {
    font-weight: 700;
    font-size: 18px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: #000;
    margin-bottom: 16px;
}

/* Selected Address */
.selected-address {
    display: flex;
    align-items: center;
    gap: 14px;    
    background: var(--white);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    cursor: pointer;
    transition: all var(--transition);
}

.selected-address.hidden { display: none; }
.selected-address:hover { background: var(--gray-200); }

.selected-address__icon { font-size: 20px; }
.selected-address__content { flex: 1; }

.selected-address__type {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--primary);
    background: rgba(0, 113, 227, 0.1);
    padding: 3px 8px;
    border-radius: 4px;
    margin-bottom: 6px;
}

.selected-address__text { font-size: 15px; font-weight: 500; }
.selected-address__text .address-detail {
    display: block;
    font-size: 13px;
    font-weight: 400;
    color: var(--gray-500);
    margin-top: 2px;
}

.selected-address__edit {
    width: 36px;
    height: 36px;
    background: var(--white);
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
}

/* Address Form */
.address-form {
    overflow: hidden;
    transition: all 0.4s ease;
}

.address-form.collapsed {
    max-height: 0;
    opacity: 0;
    margin-top: 0;
    pointer-events: none;
}

.address-form:not(.collapsed) {
    max-height: 2000px;
    opacity: 1;
    margin-top: 20px;
}

.saved-addresses__label {
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-500);
    margin-bottom: 12px;
}

.address-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--gray-100);
    border: 2px solid transparent;
    border-radius: var(--radius-md);
    cursor: pointer;
    margin-bottom: 8px;
    transition: all var(--transition);
}

.address-option:hover { background: var(--gray-200); }
.address-option.active { background: rgba(0, 113, 227, 0.08); border-color: var(--primary); }

.address-option__radio {
    width: 20px;
    height: 20px;
    border: 2px solid var(--gray-300);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
    flex-shrink: 0;
    margin-top: 2px;
}

.address-option.active .address-option__radio { border-color: var(--primary); }

.radio-inner {
    width: 10px;
    height: 10px;
    background: var(--primary);
    border-radius: 50%;
    transform: scale(0);
    transition: transform var(--transition);
}

.address-option.active .radio-inner { transform: scale(1); }

.address-option__content { flex: 1; }
.address-option__badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--gray-500);
    background: var(--gray-200);
    padding: 2px 6px;
    border-radius: 3px;
    margin-bottom: 4px;
}

.address-option__main { font-size: 14px; font-weight: 500; }
.address-option__sub { font-size: 13px; color: var(--gray-500); margin-top: 2px; }


.divider-or {
    display: flex;
    align-items: center;
    margin: 20px 0;
    color: var(--gray-400);
    font-size: 13px;
}

.divider-or::before, .divider-or::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--gray-200);
}

.divider-or span { padding: 0 16px; }

/* Delivery Tabs */
.delivery-type-selector { display: flex; gap: 10px; margin-bottom: 20px; }

.delivery-tab {
    flex: 1;
    padding: 16px;
    background: var(--gray-100);
    border: 2px solid transparent;
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    transition: all var(--transition);
}

.delivery-tab:hover { background: var(--gray-200); }
.delivery-tab.active { background: var(--white); border-color: var(--primary); }

/* PVZ Section */
.pvz-section.hidden, .door-section.hidden { display: none; }

.pvz-picker {
    width: 100%;
    padding: 18px;
    background: var(--white);
    border-radius: var(--radius-md);
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    transition: all var(--transition);
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.pvz-picker span{
    flex: 1 1 auto;
}


.pvz-picker:hover { background: var(--gray-200); border-color: var(--primary); }

.pvz-result {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: rgba(52, 199, 89, 0.1);
    border: 1px solid var(--success);
    border-radius: var(--radius-md);
    margin-top: 12px;
}

.pvz-result.hidden { display: none; }
.pvz-result__icon { color: var(--success); font-size: 18px; }
.pvz-result__content { flex: 1; }
.pvz-result__name { font-size: 14px; font-weight: 600; }
.pvz-result__address { font-size: 13px; color: var(--gray-500); margin-top: 2px; }
.pvz-result__change { background: none; border: none; color: var(--primary); font-size: 13px; cursor: pointer; padding: 4px 8px; }

/* Form Elements */
.form-row { margin-bottom: 16px; }
.form-row:last-child { margin-bottom: 0; }
.form-label { display: block; font-size: 13px; font-weight: 500; color: var(--gray-500); margin-bottom: 8px; }

.form-input {
    width: 100%;
    height: 52px;
    padding: 0 16px;
    background: var(--gray-100);
    border: 2px solid transparent;
    border-radius: var(--radius-md);
    font-size: 15px;
    font-family: inherit;
    outline: none;
    transition: all var(--transition);
}

/* .form-input:focus { background: var(--white); border-color: var(--primary); } */

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--gray-200);
}

.btn-secondary {
    padding: 12px 24px;
    background: transparent;
    border: none;
    color: var(--gray-500);
    font-size: 14px;
    cursor: pointer;
    border-radius: var(--radius-md);
}

.btn-secondary:hover { background: var(--gray-100); }

.btn-primary {
    padding: 12px 24px;
    background: var(--primary);
    border: none;
    color: var(--white);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover { background: #0062CC; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-loading.hidden { display: none; }

/* Payment */
.payment-methods { display: flex; flex-direction: column; gap: 10px; }

.payment-option {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: var(--gray-100);
    border: 2px solid transparent;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition);
}

.payment-option:hover { background: var(--gray-200); }
.payment-option.active, .payment-option:has(input:checked) { background: rgba(0, 113, 227, 0.08); border-color: var(--primary); }
.payment-option input { display: none; }
.payment-option__radio { width: 20px; height: 20px; border: 2px solid var(--gray-300); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.payment-option:has(input:checked) .payment-option__radio { border-color: var(--primary); }
.payment-option:has(input:checked) .radio-inner { transform: scale(1); }
.payment-option__name { font-size: 15px; font-weight: 500; }

/* Toast Notifications */
.cdek-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    border-radius: var(--radius-md);
    color: var(--white);
    font-size: 14px;
    font-weight: 500;
    z-index: 10000;
    animation: toastIn 0.3s ease;
    max-width: 320px;
}

.cdek-toast--success { background: var(--success); }
.cdek-toast--error { background: var(--danger); }
.cdek-toast--warning { background: var(--warning); }


.checkout-grid .checkout-card .form-row,
#door-section .form-row{
    background: white;
    padding: 24px;
    border-radius: 24px;
    margin: 0 0 24px;
}


.checkout-grid .checkout-card .form-row .form-label,
#door-section .form-row .form-label{
    font-weight: 400;
    font-size: 12px;
    color: #aab2bd;
    line-height: normal;
    margin-bottom: 4px;
}

.checkout-grid .checkout-card .form-row .form-input,
#door-section .form-row .form-input{
    font-family: var(--font-family);
    font-weight: 500;
    font-size: 16px;
    color: #191919;
    height: auto;
    padding: 0;
    background: inherit;
    border-radius: 0;
}

@keyframes toastIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 1024px) {
    .checkout-grid { grid-template-columns: 1fr; }
    .checkout-sidebar { position: static; }
}

@media (max-width: 640px) {
    .delivery-type-selector { flex-direction: column; }
}
</style>

<script>
jQuery(function($) {
    'use strict';
    
    var cdekNonce = '<?php echo wp_create_nonce("cdek_pro_nonce"); ?>';
    var ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
    
    // State
    var state = {
        deliveryType: $('#delivery_type').val() || 'pvz',
        selectedAddressId: null,
        isFormOpen: !$('#address-form').hasClass('collapsed'),
        lastCity: $('#billing_city').val(),
        lastPostcode: $('#billing_postcode').val()
    };
    
    // ========================================
    // Toast Notifications
    // ========================================
    
    function notify(message, type) {
        type = type || 'success';
        
        // Удаляем старые
        $('.cdek-toast').remove();
        
        var $toast = $('<div class="cdek-toast cdek-toast--' + type + '">' + message + '</div>');
        $('body').append($toast);
        
        setTimeout(function() {
            $toast.fadeOut(300, function() { $(this).remove(); });
        }, 3000);
    }
    
    // ========================================
    // Форма адреса - открыть/закрыть
    // ========================================
    
    // Редактировать адрес
    $('#edit-address-btn, #selected-address').on('click', function(e) {
        if ($(e.target).is('#edit-address-btn') || $(e.target).closest('#edit-address-btn').length === 0) {
            openAddressForm();
        }
    });
    
    // Отмена
    $('#cancel-address').on('click', function() {
        closeAddressForm();
    });
    
    function openAddressForm() {
        $('#address-form').removeClass('collapsed');
        state.isFormOpen = true;
    }
    
    function closeAddressForm() {
        $('#address-form').addClass('collapsed');
        state.isFormOpen = false;
    }
    
    // ========================================
    // Сохранённые адреса - клик
    // ========================================
    
    $(document).on('click', '.address-option', function() {
        var $opt = $(this);
        
        $('.address-option').removeClass('active');
        $opt.addClass('active');
        
        state.selectedAddressId = $opt.data('id');
        
        var data = {
            type: $opt.data('type'),
            city: $opt.data('city'),
            address: $opt.data('address'),
            postcode: $opt.data('postcode'),
            cityCode: $opt.data('city-code'),
            pvzCode: $opt.data('pvz-code'),
            pvzName: $opt.data('pvz-name'),
            pvzAddress: $opt.data('pvz-address')
        };
        
        applyAddressData(data);
        updateSelectedAddressDisplay();
        closeAddressForm();
    });
    
    function applyAddressData(data) {
        console.log('Applying address data:', data);
        
        // Для ПВЗ
        if (data.type === 'pvz') {
            let parsedAddress = { 
                city: data.city || '', 
                region: '', 
                index: '', 
                fullAddress: data.pvzAddress || '',
                fullStreet: ''
            };
            
            // Парсим адрес если есть pvzAddress
            if (data.pvzAddress) {
                parsedAddress = parsePvzAddress(data.pvzAddress);
            }
            
            // Заполняем СДЭК поля
            $('#cdek_pvz_code').val(data.pvzCode || '');
            $('#cdek_pvz_name').val(data.pvzName || '');
            $('#cdek_pvz_address').val(data.pvzAddress || '');
            $('#cdek_city_code').val(data.cityCode || '');
            
            // Обязательные поля WooCommerce
            const city = data.city || parsedAddress.city || '';
            const region = parsedAddress.region || city || 'МОСКВА'; // По умолчанию Москва
            
            // Адрес для billing_address_1 - используем улицу с домом или полный адрес
            const billingAddress = parsedAddress.fullStreet || data.pvzName || data.pvzAddress || '';
            
            $('#billing_city, #shipping_city').val(city);
            $('#billing_address_1, #shipping_address_1').val(billingAddress);
            $('#billing_state, #shipping_state').val(region); // Важно: заполняем регион!
            $('#billing_country, #shipping_country').val('RU');
            $('#billing_postcode, #shipping_postcode').val(parsedAddress.index || '');
            
            // Для адреса доставки ПВЗ можно использовать полный адрес в address_2
            $('#billing_address_2, #shipping_address_2').val(data.pvzAddress || '');
            
            state.deliveryType = 'pvz';
            $('#delivery_type').val('pvz');
            
        } else {
            // Адрес курьером
            $('#billing_city, #shipping_city').val(data.city || '');
            $('#billing_address_1, #shipping_address_1').val(data.address || '');
            $('#billing_state, #shipping_state').val(data.city || 'МОСКВА'); // Город как регион
            $('#billing_country, #shipping_country').val('RU');
            $('#billing_postcode, #shipping_postcode').val(data.postcode || '');
            $('#billing_address_2, #shipping_address_2').val('');
            $('#cdek_city_code').val(data.cityCode || '');
            
            // Очищаем ПВЗ поля
            $('#cdek_pvz_code, #cdek_pvz_name, #cdek_pvz_address').val('');
            
            state.deliveryType = 'door';
            $('#delivery_type').val('door');
        }
        
        // Обновляем UI
        updateDeliveryUI(data.type);
        
        // Проверяем изменился ли город
        var cityChanged = (data.city !== state.lastCity);
        state.lastCity = data.city;
        
        // Триггерим обновление checkout
        setTimeout(function() {
            $(document.body).trigger('update_checkout');
        }, 100);
    }
    
    // ========================================
    // Табы типа доставки
    // ========================================
    
    $('.delivery-tab').on('click', function() {
        var type = $(this).data('type');
        
        $('.delivery-tab').removeClass('active');
        $(this).addClass('active');
        
        state.deliveryType = type;
        $('#delivery_type').val(type);
        
        // Сбрасываем выбор сохранённых адресов
        $('.address-option').removeClass('active');
        state.selectedAddressId = null;
        
        if (type === 'pvz') {
            $('#pvz-section').removeClass('hidden');
            $('#door-section').addClass('hidden');
        } else {
            $('#pvz-section').addClass('hidden');
            $('#door-section').removeClass('hidden');
        }
    });
    
    // ========================================
    // ПВЗ
    // ========================================
    
    $('#open-pvz-map, #change-pvz').on('click', function() {
        // Открываем модалку СДЭК
        if ($('#cdek-modal').length) {
            $('#cdek-modal').addClass('cdek-modal--open');
            $('body').addClass('cdek-modal-open');
        } else {
            notify('Карта СДЭК недоступна', 'error');
        }
    });
    
    function parsePvzAddress(pvzAddress) {
        if (!pvzAddress || typeof pvzAddress !== 'string') {
            return { 
                index: '', 
                country: '', 
                region: '', 
                city: '', 
                street: '', 
                house: '', 
                building: '', 
                apartment: '',
                fullAddress: '',
                fullStreet: ''
            };
        }
        
        const parts = pvzAddress.split(',').map(part => part.trim());
        const result = {
            index: parts[0] || '',
            country: parts[1] || '',
            region: parts[2] || '', // Регион (Москва)
            city: parts[3] || '',   // Город (Москва)
            street: parts[4] || '',
            house: parts[5] || '',
            building: parts[6] || '',
            apartment: parts[7] || '',
            fullAddress: pvzAddress
        };
        
        // Собираем улицу с домом
        result.fullStreet = [result.street, result.house, result.building]
            .filter(Boolean)
            .join(', ');
            
        // Если город не указан, берем регион
        if (!result.city && result.region) {
            result.city = result.region;
        }
        
        return result;
    }

    // Вспомогательная функция для обновления UI (вынесена наружу)
    function updateDeliveryUI(deliveryType) {
        $('.delivery-tab').removeClass('active');
        $('.delivery-tab[data-type="' + deliveryType + '"]').addClass('active');
        
        if (deliveryType === 'pvz') {
            $('#pvz-section').removeClass('hidden');
            $('#door-section').addClass('hidden');
        } else {
            $('#pvz-section').addClass('hidden');
            $('#door-section').removeClass('hidden');
        }
    }


    // Обновленный обработчик события выбора ПВЗ
    $(document).on('cdek_pvz_selected', function(e, data) {
        if (data && data.code) {
            // Парсим адрес для извлечения города
            let parsedAddress = { city: data.city || '' };
            
            if (data.address) {
                parsedAddress = parsePvzAddress(data.address);
            }
            
            // Заполняем скрытые поля
            $('#cdek_pvz_code').val(data.code);
            $('#cdek_pvz_name').val(data.name || '');
            $('#cdek_pvz_address').val(data.address || '');
            
            if (data.city_code) {
                $('#cdek_city_code').val(data.city_code);
            }
            
            // Используем город из распарсенного адреса или из данных
            const city = parsedAddress.city || data.city || '';
            $('#billing_city, #shipping_city').val(city);
            state.lastCity = city;
            
            // Для ПВЗ в основное поле адреса ставим полный адрес ПВЗ
            $('#billing_address_1, #shipping_address_1').val(data.address || '');
            
            // Индекс из распарсенного адреса
            if (parsedAddress.index) {
                $('#billing_postcode, #shipping_postcode').val(parsedAddress.index);
            }
            
            // Форматируем адрес как в PHP (Москва, ул. Динамовская, 1А, 110а)
            let formattedAddress = '';
            if (data.address) {
                const addressParts = data.address.split(', ');
                if (addressParts.length >= 5) {
                    const cityPart = addressParts[2];
                    const streetPart = addressParts.slice(4).join(', ');
                    formattedAddress = cityPart + ', ' + streetPart;
                } else {
                    formattedAddress = data.address;
                }
            }
            
            // Обновляем UI выбора ПВЗ в форме
            $('#pvz-result-name').text(data.name || 'ПВЗ');
            $('#pvz-result-address').text(formattedAddress || '');
            $('#pvz-result').removeClass('hidden');
            $('#open-pvz-map').hide();
            
            state.deliveryType = 'pvz';
            $('#delivery_type').val('pvz');
            
            // Обновляем selected-address блок с форматированным адресом
            $('#address-type-badge').text('Пункт выдачи');
            $('#address-text').html(formattedAddress || 'ПВЗ');
            $('#selected-address').removeClass('hidden');
            
            // Закрываем форму адреса
            $('#address-form').addClass('collapsed');
            state.isFormOpen = false;
            
            // Автосохранение адреса
            <?php if ($is_logged_in) : ?>
            savePvzAddress(data);
            <?php endif; ?>
            
            notify('ПВЗ выбран', 'success');
            $(document.body).trigger('update_checkout');
        }
    });
    
    // Функция автосохранения ПВЗ
    function savePvzAddress(data) {
        $.post(ajaxUrl, {
            action: 'cdek_save_address',
            nonce: cdekNonce,
            type: 'pvz',
            city: data.city || $('#billing_city').val(),
            city_code: data.city_code || $('#cdek_city_code').val(),
            address: '',
            postcode: '',
            pvz_code: data.code,
            pvz_name: data.name || '',
            pvz_address: data.address || '',
            is_default: true
        }, function(response) {
            if (response.success && response.data.addresses) {
                console.log(response);
                updateAddressesList(response.data.addresses);
                console.log('PVZ address saved automatically');
            }
        });
    }
    
    // ========================================
    // Ввод адреса курьером
    // ========================================
    
    var addressTimer;
    $('#input-city, #input-postcode').on('input change', function() {
        clearTimeout(addressTimer);
        
        addressTimer = setTimeout(function() {
            var city = $('#input-city').val().trim();
            var address = $('#input-address').val().trim();
            var postcode = $('#input-postcode').val().trim();
            
            if (city.length >= 2) {
                // Обновляем скрытые поля
                $('#billing_city, #shipping_city').val(city);
                $('#billing_address_1, #shipping_address_1').val(address);
                $('#billing_postcode, #shipping_postcode').val(postcode);
                
                // Сбрасываем ПВЗ
                $('#cdek_pvz_code, #cdek_pvz_name, #cdek_pvz_address').val('');
                
                state.deliveryType = 'door';
                $('#delivery_type').val('door');
                
                // Проверяем изменился ли город
                if (city !== state.lastCity || postcode !== state.lastPostcode) {
                    console.log('Door address changed, triggering update_checkout');
                    state.lastCity = city;
                    state.lastPostcode = postcode;
                    
                    // Триггерим обновление
                    $(document.body).trigger('update_checkout');
                }
            }
        }, 600);
    });
    
    // Также обновляем при изменении полного адреса
    $('#input-address').on('change', function() {
        var address = $(this).val().trim();
        $('#billing_address_1, #shipping_address_1').val(address);
    });
    
    // ========================================
    // Сохранение адреса
    // ========================================
    
    $('#save-address').on('click', function() {
        var $btn = $(this);
        var type = state.deliveryType;
        
        // Валидация
        if (type === 'pvz' && !$('#cdek_pvz_code').val()) {
            notify('Выберите пункт выдачи на карте', 'warning');
            return;
        }
        
        if (type === 'door' && !$('#input-city').val().trim()) {
            notify('Укажите город', 'warning');
            return;
        }
        
        // Собираем данные
        var data = {
            action: 'cdek_save_address',
            nonce: cdekNonce,
            type: type,
            city: type === 'door' ? $('#input-city').val() : $('#billing_city').val(),
            city_code: $('#cdek_city_code').val(),
            address: type === 'door' ? $('#input-address').val() : '',
            postcode: type === 'door' ? $('#input-postcode').val() : '',
            pvz_code: type === 'pvz' ? $('#cdek_pvz_code').val() : '',
            pvz_name: type === 'pvz' ? $('#cdek_pvz_name').val() : '',
            pvz_address: type === 'pvz' ? $('#cdek_pvz_address').val() : '',
            is_default: true
        };
        
        // Показываем загрузку
        $btn.prop('disabled', true);
        $btn.find('.btn-text').addClass('hidden');
        $btn.find('.btn-loading').removeClass('hidden');
        
        $.post(ajaxUrl, data, function(response) {
            $btn.prop('disabled', false);
            $btn.find('.btn-text').removeClass('hidden');
            $btn.find('.btn-loading').addClass('hidden');
            
            if (response.success) {
                notify('Адрес сохранён', 'success');

                console.log(response);
                
                // Обновляем список адресов
                if (response.data.addresses) {
                    updateAddressesList(response.data.addresses);
                }
                
                // Обновляем отображение
                updateSelectedAddressDisplay();
                
                // Закрываем форму
                closeAddressForm();
                
                // Показываем выбранный адрес
                $('#selected-address').removeClass('hidden');
                
                // Обновляем checkout
                $(document.body).trigger('update_checkout');
            } else {
                notify(response.data.message || 'Ошибка сохранения', 'error');
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $btn.find('.btn-text').removeClass('hidden');
            $btn.find('.btn-loading').addClass('hidden');
            notify('Ошибка сети', 'error');
        });
    });
    
    function updateAddressesList(addresses) {
        var $container = $('#saved-addresses-container');
        
        if (!addresses || addresses.length === 0) {
            $container.html('');
            return;
        }
        
        var html = '<div class="saved-addresses__label">Сохранённые адреса</div>';
        html += '<div class="saved-addresses-list" id="saved-addresses-list">';
        
        addresses.forEach(function(addr) {
            var isPvz = !!addr.pvz_code;
            var isDefault = !!addr.is_default;
            
            html += '<div class="address-option ' + (isDefault ? 'active' : '') + '"';
            html += ' data-id="' + (addr.id || '') + '"';
            html += ' data-type="' + (isPvz ? 'pvz' : 'door') + '"';
            html += ' data-city="' + (addr.city || '') + '"';
            html += ' data-address="' + (addr.address || '') + '"';
            html += ' data-postcode="' + (addr.postcode || '') + '"';
            html += ' data-city-code="' + (addr.city_code || '') + '"';
            html += ' data-pvz-code="' + (addr.pvz_code || '') + '"';
            html += ' data-pvz-name="' + (addr.pvz_name || '') + '"';
            html += ' data-pvz-address="' + (addr.pvz_address || '') + '">';
            html += '<div class="address-option__radio"><div class="radio-inner"></div></div>';
            html += '<div class="address-option__content">';
            html += '<span class="address-option__badge">' + (isPvz ? 'ПВЗ' : 'Адрес') + '</span>';
            html += '<div class="address-option__main">' + (isPvz ? (addr.pvz_name || 'ПВЗ') : (addr.address || addr.city || '')) + '</div>';
            
            
            html += '</div>';
            
            if (isDefault) {
                html += '<span class="address-option__default"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11.001 0C17.0763 0.00020563 22.001 4.92499 22.001 11C22.001 17.075 17.0763 21.9998 11.001 22C4.92552 22 0 17.0751 0 11C0 4.92487 4.92552 0 11.001 0ZM17.209 6.27051C16.8513 5.92546 16.2817 5.93633 15.9365 6.29395L9.1543 13.4648L6.08691 10.2148C5.74172 9.85725 5.17211 9.84723 4.81445 10.1924C4.45681 10.5376 4.44681 11.1072 4.79199 11.4648L8.50684 15.3857C8.67645 15.5615 8.91004 15.6611 9.1543 15.6611C9.39852 15.6611 9.63216 15.5615 9.80176 15.3857L17.2314 7.54395C17.5766 7.18629 17.5667 6.61568 17.209 6.27051Z" fill="#34C759" /></svg></span>';
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        html += '<div class="divider-or"><span>или добавьте новый</span></div>';
        
        $container.html(html);
    }
    
    function updateSelectedAddressDisplay() {
        var type = state.deliveryType;
        var typeLabel = type === 'pvz' ? 'Пункт выдачи' : 'Курьером';
        var addressHtml = '';
        
        if (type === 'pvz') {
            var name = $('#cdek_pvz_name').val();
            var fullAddress = $('#cdek_pvz_address').val();
            
            // addressHtml = name || 'ПВЗ';
            
            if (fullAddress) {
                // Извлекаем город и адрес по аналогии с PHP
                var addressParts = fullAddress.split(', ');
                if (addressParts.length >= 5) {
                    var city = addressParts[2]; // Москва
                    var streetAddress = city + ', ' + streetAddress; // ул. Динамовская, 1А, 110а
                    addressHtml +=  city + ', ' + streetAddress ;
                } else {
                    // Если формат не соответствует ожидаемому, показываем полный адрес
                    addressHtml += fullAddress;
                }
            }
        } else {
            var city = $('#input-city').val() || $('#billing_city').val();
            var address = $('#input-address').val() || $('#billing_address_1').val();
            
            addressHtml = city || 'Адрес';
            if (address) {
                addressHtml += ', ' + address;
            }
        }
        
        $('#address-type-badge').text(typeLabel);
        $('#address-text').html(addressHtml);
        $('#selected-address').removeClass('hidden');
    }
    
    // ========================================
    // Контакты
    // ========================================
    
    $('#input-name').on('change input', function() {
        var parts = $(this).val().trim().split(' ');
        $('#billing_first_name, #shipping_first_name').val(parts[0] || '');
        $('#billing_last_name, #shipping_last_name').val(parts.slice(1).join(' ') || '');
    });
    
    $('#input-phone').on('change input', function() {
        $('#billing_phone').val($(this).val());
    });
    
    $('#input-email').on('change input', function() {
        $('#billing_email').val($(this).val());
    });
    
    // ========================================
    // Оплата
    // ========================================
    
    $('.payment-option').on('click', function() {
        $('.payment-option').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
    });
    
    // ========================================
    // Обработка выбора тарифа
    // ========================================
    
    $(document).on('change', 'input[name="shipping_method[0]"]', function() {
        var method = $(this).val();
        $('#shipping_method').val(method);
        console.log('Shipping method selected:', method);
    });
    
    // ========================================
    // Init
    // ========================================
    
    // Синхронизация контактов при загрузке
    $('#input-name').trigger('change');
    $('#input-phone').trigger('change');
    $('#input-email').trigger('change');
    
    // Если есть город — триггерим обновление
    if ($('#billing_city').val() || $('#cdek_pvz_code').val()) {
        console.log('Initial checkout update');
        $(document.body).trigger('update_checkout');
    }
    
    // Логирование для отладки
    $(document.body).on('updated_checkout', function() {
        console.log('Checkout updated');
    });
});
</script>