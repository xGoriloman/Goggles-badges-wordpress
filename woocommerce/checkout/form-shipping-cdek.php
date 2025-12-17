<?php
/**
 * Shipping methods for CDEK in checkout
 */

if (!defined('ABSPATH')) {
    exit;
}

// Получаем доступные методы доставки
$available_methods = WC()->shipping->get_packages();
$chosen_method = isset(WC()->session->chosen_shipping_methods[0]) ? WC()->session->chosen_shipping_methods[0] : '';

// Фильтруем только методы CDEK
$cdek_methods = [];
foreach ($available_methods as $package) {
    foreach ($package['rates'] as $rate) {
        if (strpos($rate->get_id(), 'cdek_ai') !== false) {
            $cdek_methods[] = $rate;
        }
    }
}

if (empty($cdek_methods)) {
    return;
}
?>

<div class="sidebar-cart__delivery">
    <h3 class="sidebar-cart__delivery-title">Способ доставки</h3>
    <div class="sidebar-cart__shipping-methods">
        <?php foreach ($cdek_methods as $method) : 
            $cost = $method->get_cost() + $method->get_shipping_tax();
            $label = $method->get_label();
            $method_id = $method->get_id();
            $checked = $chosen_method === $method_id ? 'checked' : '';
        ?>
            <label class="sidebar-cart__shipping-method <?php echo $checked ? 'sidebar-cart__shipping-method--active' : ''; ?>">
                <input type="radio" name="shipping_method[0]" 
                       value="<?php echo esc_attr($method_id); ?>" 
                       class="sidebar-cart__shipping-radio" 
                       <?php echo $checked; ?> 
                       data-index="0">
                <span class="sidebar-cart__shipping-label">
                    <span class="sidebar-cart__shipping-name"><?php echo esc_html($label); ?></span>
                    <span class="sidebar-cart__shipping-price">
                        <?php echo $cost > 0 ? wc_price($cost) : 'Бесплатно'; ?>
                    </span>
                </span>
            </label>
        <?php endforeach; ?>
    </div>

    <div class="sidebar-cart__destination">
        <?php
        $customer = WC()->customer;
        $city = $customer->get_shipping_city();
        $postcode = $customer->get_shipping_postcode();
        
        if ($city || $postcode) : ?>
            <p class="sidebar-cart__destination-text">
                Доставка до пункта выдачи: 
                <?php echo $city ? esc_html($city) : ''; ?>
                <?php echo $postcode ? "($postcode)" : ''; ?>
            </p>
        <?php else : ?>
            <p class="sidebar-cart__destination-text">Укажите адрес доставки</p>
        <?php endif; ?>
        
        <button type="button" class="sidebar-cart__change-address" 
                onclick="jQuery('a[href=\'#customer_details\']').trigger('click');">
            Изменить адрес
        </button>
    </div>
</div>