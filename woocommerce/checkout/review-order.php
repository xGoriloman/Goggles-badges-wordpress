<?php
/**
 * Review order table - с фильтрацией тарифов по типу доставки
 * Скопировать в: theme/woocommerce/checkout/review-order.php
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

// Получаем текущий тип доставки из сессии
$delivery_type = WC()->session ? WC()->session->get('cdek_delivery_type', '') : '';
$has_pvz_selected = WC()->session ? (bool) WC()->session->get('cdek_pvz_code', '') : false;
?>

<!-- ПРАВАЯ КОЛОНКА -->
<div class="checkout-sidebar-inner woocommerce-checkout-review-order-table">
	
	<!-- КОРЗИНА -->
	<section class="checkout-card">
		<h2 class="card-title">Ваш заказ</h2>
		<div class="cart-items">
			<?php 
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}
				$quantity = $cart_item['quantity'];
				$line_total = $cart_item['line_total'];
			?>
			<div class="cart-item">
				<div class="cart-item__image">
					<?php echo $_product->get_image(); ?>
				</div>
				<div class="cart-item__info">
					<div class="cart-item__name"><?php echo wp_kses_post( $_product->get_name() ); ?></div>
					<div class="cart-item__qty">
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
						× <?php echo $quantity; ?>
					</div>
				</div>
				<div class="cart-item__price"><?php echo wc_price($line_total); ?></div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	
	<!-- ТАРИФЫ ДОСТАВКИ -->
	<section class="checkout-card" id="tariffs-section">
		<h2 class="card-title">Способ доставки</h2>
		
		<div class="tariffs-container" id="shipping-methods-container">
			<?php 
			do_action( 'woocommerce_review_order_before_shipping' );
			
			$packages = WC()->shipping()->get_packages();
			
			foreach ( $packages as $i => $package ) {
				$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
				$available_methods = $package['rates'];
				$index = $i;
				
				if ( ! empty( $available_methods ) ) : 
					// Фильтруем тарифы в зависимости от выбранного типа доставки
					$filtered_methods = array();
					
					foreach ( $available_methods as $method_id => $method ) {
						$is_local_pickup = (strpos($method_id, 'local_pickup') !== false);
						$meta = $method->get_meta_data();
						$method_delivery_type = $meta['delivery_type'] ?? '';
						$requires_pvz = $meta['requires_pvz'] ?? false;

						if ($is_local_pickup) {
							$filtered_methods[$method_id] = $method;
						}
						// Если выбран ПВЗ — показываем только тарифы до склада (ПВЗ)
						elseif ($has_pvz_selected && $delivery_type === 'pvz') {
							// Показываем тарифы: склад-склад, дверь-склад (type = pvz)
							if ($method_delivery_type === 'pvz' || $requires_pvz) {
								$filtered_methods[$method_id] = $method;
							}
						}
						// Если выбрана курьерская доставка — показываем тарифы до двери
						elseif ($delivery_type === 'door') {
							// Показываем тарифы: склад-дверь, дверь-дверь (type = door)
							if ($method_delivery_type === 'door' || !$requires_pvz) {
								$filtered_methods[$method_id] = $method;
							}
						}
						// Если тип не выбран — показываем все
						else {
							$filtered_methods[$method_id] = $method;
						}
					}
					
					// Если после фильтрации ничего не осталось — показываем все
					if (empty($filtered_methods)) {
						$filtered_methods = $available_methods;
					}
				?>
					<ul class="shipping-methods-list" id="shipping_method_<?php echo $index; ?>">
						<?php foreach ( $filtered_methods as $method ) : 
							$method_id = $method->get_id();
							$is_checked = $method_id === $chosen_method;
							
							// Если ничего не выбрано, выбираем первый
							if (empty($chosen_method) && $method === reset($filtered_methods)) {
								$is_checked = true;
							}
							
							$meta = $method->get_meta_data();
							$requires_pvz = $meta['requires_pvz'] ?? false;
							$method_delivery_type = $meta['delivery_type'] ?? '';
						?>
						<li class="shipping-method-item <?php echo $is_checked ? 'selected' : ''; ?>">
							<label class="shipping-method-label" 
								   data-method="<?php echo esc_attr($method_id); ?>" 
								   data-requires-pvz="<?php echo $requires_pvz ? '1' : '0'; ?>"
								   data-delivery-type="<?php echo esc_attr($method_delivery_type); ?>">
								<input type="radio" 
									   name="shipping_method[<?php echo $index; ?>]" 
									   data-index="<?php echo $index; ?>"
									   id="shipping_method_<?php echo $index; ?>_<?php echo esc_attr( sanitize_title( $method_id ) ); ?>"
									   value="<?php echo esc_attr( $method_id ); ?>" 
									   class="shipping_method"
									   <?php checked( $is_checked ); ?>>
								<span class="method-radio"></span>
								<span class="method-info">
									<span class="method-name"><?php echo wp_kses_post( $method->get_label() ); ?></span>
									<span class="method-type">
										<?php 
										if ($requires_pvz || $method_delivery_type === 'pvz') {
											echo 'В пункт выдачи';
										} elseif($method_delivery_type === 'door') {
											echo 'Курьером до двери';
										} else {
											echo "Приехать к нам";
										}
										?>
									</span>
								</span>
								<span class="method-cost"><?php echo wc_price( $method->get_cost() ); ?></span>
							</label>
						</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<div class="no-shipping-available">
						<p>Нет доступных способов доставки</p>
						<p class="no-shipping-hint">Укажите адрес для расчёта стоимости</p>
					</div>
				<?php endif;
			}
			
			do_action( 'woocommerce_review_order_after_shipping' );
			?>
		</div>
	</section>

	
                
	<!-- ОПЛАТА -->
	<section class="checkout-card">
		<h2 class="card-title">Способ оплаты</h2>
		<div class="payment-methods">
			<?php 
			$gateways = WC()->payment_gateways->get_available_payment_gateways();
			$first = true;
			foreach ($gateways as $gateway) : 
			?>
			<label class="payment-option <?php echo $first ? 'active' : ''; ?>">
				<input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($first); ?>>
				<div class="payment-option__radio"><div class="radio-inner"></div></div>
				<span class="payment-option__name"><?php echo esc_html($gateway->get_title()); ?></span>
			</label>
			<?php $first = false; endforeach; ?>
		</div>
	</section>
	
	<!-- ИТОГО -->
	<section class="checkout-card summary-card">
		<div class="summary-row">
			<span>Сумма</span>
			<span class="summary-value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>
		
		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<div class="summary-row discount">
			<span>Скидка</span>
			<span class="summary-value">−<?php wc_cart_totals_coupon_html( $coupon ); ?></span>
		</div>
		<?php endforeach; ?>
		
		<div class="summary-row">
			<span>Доставка</span>
			<span class="summary-value" id="summary-shipping-cost">
				<?php 
				$shipping_total = WC()->cart->get_shipping_total();
				echo $shipping_total > 0 ? wc_price( $shipping_total ) : '—';
				?>
			</span>
		</div>
		
		<div class="summary-divider"></div>
		
		<div class="summary-total order-total">
			<span>Итого</span>
			<span id="summary-total"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>
		
		<button type="submit" class="submit-btn" name="woocommerce_checkout_place_order" id="place_order">
			Оформить заказ
		</button>
		
		<p class="policy-text">
			Нажимая кнопку, вы соглашаетесь с <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>">политикой конфиденциальности</a>
		</p>
	</section>
</div>

<style>
.checkout-sidebar-inner {
	display: flex;
	flex-direction: column;
	gap: 20px;
}

.cart-items {
	display: flex;
	flex-direction: column;
	gap: 16px;
}

.cart-item {
	display: flex;
	align-items: center;
	gap: 12px;
	padding-bottom: 16px;
	border-bottom: 1px solid #E5E5E7;
}

.cart-item:last-child {
	padding-bottom: 0;
	border-bottom: none;
}

.cart-item__image {
	width: 60px;
	height: 60px;
	background: #F5F5F7;
	border-radius: 12px;
	overflow: hidden;
	flex-shrink: 0;
}

.cart-item__image img {
	width: 100%;
	height: 100%;
	object-fit: contain;
}

.cart-item__info {
	flex: 1;
	min-width: 0;
}

.cart-item__name {
	font-size: 14px;
	font-weight: 500;
	line-height: 1.3;
	margin-bottom: 4px;
}

.cart-item__qty {
	font-size: 13px;
	color: #86868B;
}

.cart-item__price {
	font-size: 15px;
	font-weight: 600;
	white-space: nowrap;
}

/* Shipping Methods */
.tariffs-container {
	margin-top: -8px;
}

.shipping-methods-list {
	list-style: none;
	padding: 0;
	margin: 0;
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.shipping-method-item {
	margin: 0;
}

.shipping-method-label {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 14px 16px;
	background: #F5F5F7;
	border: 2px solid transparent;
	border-radius: 12px;
	cursor: pointer;
	transition: all 0.2s ease;
}

.shipping-method-label:hover {
	background: #E5E5E7;
}

.shipping-method-item.selected .shipping-method-label,
.shipping-method-label:has(input:checked) {
	background: rgba(0, 113, 227, 0.08);
	border-color: #191919;
}

.shipping-method-label input {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}

.method-radio {
	width: 20px;
	height: 20px;
	border: 2px solid #D1D1D6;
	border-radius: 50%;
	flex-shrink: 0;
	position: relative;
	transition: all 0.2s ease;
}

.shipping-method-label:has(input:checked) .method-radio {
	border-color: #191919;
}

.method-radio::after {
	content: '';
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%) scale(0);
	width: 10px;
	height: 10px;
	background: #191919;
	border-radius: 50%;
	transition: transform 0.2s ease;
}

.shipping-method-label:has(input:checked) .method-radio::after {
	transform: translate(-50%, -50%) scale(1);
}

.method-info {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.method-name {
	font-size: 14px;
	font-weight: 500;
}

.method-type {
	font-size: 12px;
	color: #86868B;
}

.method-cost {
	font-size: 15px;
	font-weight: 600;
	white-space: nowrap;
}

.no-shipping-available {
	padding: 24px;
	text-align: center;
	background: #F5F5F7;
	border-radius: 12px;
}

.no-shipping-available p {
	margin: 0;
	font-size: 14px;
	color: #86868B;
}

.no-shipping-hint {
	margin-top: 8px !important;
	font-size: 13px !important;
}

/* Summary */
.summary-card {
	padding: 24px 0;
}

.summary-row {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 12px;
	font-size: 15px;
}

.summary-row span:first-child {
	color: #86868B;
}

.summary-value {
	font-weight: 600;
	color: #1D1D1F;
}

.summary-row.discount .summary-value {
	color: #FF3B30;
}

.summary-divider {
	height: 1px;
	background: #E5E5E7;
	margin: 16px 0;
}

.summary-total {
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 18px;
	font-weight: 700;
	margin-bottom: 24px;
}

.summary-total span:first-child {
	text-transform: uppercase;
}

.submit-btn {
	width: 100%;
	height: 56px;
	background: #191919;
	color: #FFFFFF;
	border: none;
	border-radius: 100px;
	font-size: 16px;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
	background: #333;
}

.submit-btn:disabled {
	opacity: 0.5;
	cursor: not-allowed;
}

.policy-text {
	font-size: 12px;
	color: #86868B;
	text-align: center;
	margin-top: 16px;
	margin-bottom: 0;
}

.policy-text a {
	color: #191919;
	text-decoration: none;
}

/* Hide WooCommerce defaults */
.woocommerce-shipping-totals,
.woocommerce-shipping-destination {
	display: none !important;
}
</style>

<script>
jQuery(function($) {
	'use strict';
	
	// Обработка выбора метода доставки
	$(document).on('change', '.shipping_method', function() {
		var $input = $(this);
		var methodId = $input.val();
		
		$('.shipping-method-item').removeClass('selected');
		$input.closest('.shipping-method-item').addClass('selected');
		
		$('#shipping_method').val(methodId);
		
		// Проверяем тип тарифа
		var $label = $input.closest('.shipping-method-label');
		var requiresPvz = $label.data('requires-pvz') == 1;
		var deliveryType = $label.data('delivery-type');
		
		// Если выбран тариф до ПВЗ, но ПВЗ не выбран — показываем предупреждение
		if (requiresPvz || deliveryType === 'pvz') {
			var pvzCode = $('#cdek_pvz_code').val();
			if (!pvzCode) {
				// Можно автоматически открыть карту
				console.log('PVZ required but not selected');
			}
		}
		
		console.log('Shipping method selected:', methodId, 'requiresPvz:', requiresPvz);
	});
	
	// При обновлении checkout
	$(document.body).on('updated_checkout', function() {
		var $checked = $('.shipping_method:checked');
		if ($checked.length) {
			$('.shipping-method-item').removeClass('selected');
			$checked.closest('.shipping-method-item').addClass('selected');
			$('#shipping_method').val($checked.val());
		}
		console.log('Checkout updated');
	});
	
	// Клик по label
	$(document).on('click', '.shipping-method-label', function(e) {
		if (!$(e.target).is('input')) {
			$(this).find('input').prop('checked', true).trigger('change');
		}
	});
});
</script>