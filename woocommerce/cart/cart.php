<?php
/**
 * Template Name: Карзина
 * Template Post Type: cart
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php get_header(); ?>

<main class="page">
    <section class="section__title">
        <h1 class="title"><?php the_title(); ?></h1>
    </section>
            
    <?php if(WC()->cart->is_empty()){
        ?>
        <section style="font-weight: 700;font-size: 1.25rem;letter-spacing: 0.02em;text-transform: uppercase;text-align: center;color: #000;display: flex;flex-direction: column;height: calc(100vw - 52px);align-items: center;justify-content: center;">
		<h3>В корзине пока пусто</h3>
	</section>
        <?php 
        } else {
            ?>
            <section class="section-cart cart">
                <div class="cart__container ">
                    <!-- Основная таблица товаров -->
                    <div class="cart__main">
                        <?php do_action('woocommerce_before_cart'); ?>

                        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                            <?php wp_nonce_field( 'woocommerce-cart' ); ?> 
                            <?php do_action('woocommerce_before_cart_table'); ?>

                            <table class="cart__table woocommerce-cart-form__contents">
                                <tbody>
                                    <?php do_action('woocommerce_before_cart_contents'); ?>

                                    <?php
                                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                            
                                            // Получаем бренд
                                            $brand = '';
                                            $brands = wp_get_post_terms($product_id, 'product_brand');
                                            if (!empty($brands)) {
                                                $brand = $brands[0]->name;
                                            }
                                            
                                            // Получаем выбранный размер
                                            $selected_size = '';
                                            if (isset($cart_item['variation']['attribute_pa_size'])) {
                                                $selected_size = $cart_item['variation']['attribute_pa_size'];
                                            }
                                            
                                            // Получаем доступные размеры
                                            $available_sizes = array();
                                            if ($_product->is_type('variable')) {
                                                $attributes = $_product->get_variation_attributes();
                                                if (isset($attributes['pa_size'])) {
                                                    $available_sizes = $attributes['pa_size'];
                                                }
                                            }
                                            ?>
									
									<?php 
											// Получаем название товара без размера
											$product_name = $_product->get_name();

											// Убираем размер из названия если он есть в конце через дефис
											if ($selected_size) {
												// Паттерн: пробел, дефис, пробел и размер в конце
												$pattern = '/\s*-\s*' . preg_quote($selected_size, '/') . '$/i';
												$product_name = preg_replace($pattern, '', $product_name);

												// Альтернативный паттерн: размер в скобках
												$pattern2 = '/\s*\(' . preg_quote($selected_size, '/') . '\)$/i';
												$product_name = preg_replace($pattern2, '', $product_name);
											}

											// Также можно получить название родительского товара для вариативных
											if ($_product->is_type('variation')) {
												$parent_product = wc_get_product($_product->get_parent_id());
												if ($parent_product) {
													$product_name = $parent_product->get_name();
												}
											}
									?>
                                            <tr class="cart__row">  
                                                <td class="cart__cell cart__cell--image">
                                                    <?php if ($product_permalink) : ?>
                                                        <a href="<?php echo esc_url($product_permalink); ?>" class="cart__image-link">
                                                    <?php endif; ?>
                                                    
                                                    <div class="cart__image-ibg">
                                                        <?php
                                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                                        echo $thumbnail;
                                                        ?>
                                                    </div>
                                                    
                                                    <?php if ($product_permalink) : ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="cart__cell cart__cell--info">
                                                    <div class="cart__product-info">
                                                        <?php if ($brand) : ?>
                                                            <a class="cart__brand" href="<?php echo get_term_link($brands[0]); ?>"><?php echo esc_html($brand); ?></a>
                                                        <?php endif; ?>

                                                        <?php if ($product_permalink) : ?>
                                                            <a class="cart__name" href="<?php echo esc_url($product_permalink); ?>">
            													<?php echo wp_kses_post($product_name); ?>
                                                            </a>
                                                        <?php else : ?>
                                                            <a class="cart__name">
                                                                <?php echo wp_kses_post($_product->get_name()); ?>
                                                            </a>
                                                        <?php endif; ?>

                                                        <div class="cart__bottom">
                                                            <div class="cart__quantity quantity">
                                                                <button type="button" 
                                                                    class="quantity__button quantity__button_minus" 
                                                                    data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                                                    <svg width="12" height="2" viewBox="0 0 12 2" fill="none">
                                                                        <path d="M1 1H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                                                    </svg>
                                                                </button>
                                                                
                                                                <div class="quantity__input">
                                                                    <input value="<?php echo esc_attr($cart_item['quantity']); ?>" 
                                                                        type="number" 
                                                                        class="quantity__field" 
                                                                        data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                                                        data-min="1"
                                                                        data-max="<?php echo $_product->get_max_purchase_quantity() ? $_product->get_max_purchase_quantity() : '-1'; ?>"
                                                                        name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]">
                                                                </div>
                                                                
                                                                <button type="button" class="quantity__button quantity__button_plus" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Увеличить количество">
                                                                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                                        <path d="M1 6H11M6 1V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                                                    </svg>
                                                                </button>																
                                                            </div>
															
															<?php if ($selected_size) : ?>
															<div class="cart__size-display">
																<span class="cart__size-value"><?php echo esc_html($selected_size); ?></span>
															</div>
															<?php endif; ?>

                                                            <?php if (!empty($available_sizes)) : ?>
																<div class="cart__attributes">
																	<?php foreach ($available_sizes as $size) : 
																		$active_class = ($size === $selected_size) ? 'cart__attribute--active' : '';
																	?>
																		<button type="button" class="cart__attribute <?php echo $active_class; ?>" 
																				data-size="<?php echo esc_attr($size); ?>"
																				data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
																			<?php echo esc_html($size); ?>
																		</button>
																	<?php endforeach; ?>
																</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="cart__cell cart__cell--price">
                                                    <div class="cart__prices">
                                                        <?php
                                                        $regular_price = $_product->get_regular_price();
                                                        $sale_price = $_product->get_sale_price();
                                                        $price = $_product->get_price();
                                                        $total_price = $price * $cart_item['quantity'];

                                                        if ($sale_price && $regular_price > $sale_price) {
                                                            $total_regular_price = $regular_price * $cart_item['quantity'];
                                                            echo '<div class="cart__price cart__price--new">' . wc_price($total_price) . '</div>';
                                                            echo '<div class="cart__price cart__price--old">' . wc_price($total_regular_price) . '</div>';
                                                        } else {
                                                            echo '<div class="cart__price cart__price--new">' . wc_price($total_price) . '</div>';
                                                        }
                                                        ?>
                                                    </div>

                                                    <button class="cart__remove"  data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                                            <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <?php do_action('woocommerce_cart_contents'); ?>

                                    <?php do_action('woocommerce_after_cart_contents'); ?>
                                </tbody>
                            </table>
                            <input type="submit" class="button hidden-update-button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" style="display: none;" />

                            <?php do_action('woocommerce_after_cart_table'); ?>
                        </form>

                        <?php do_action('woocommerce_before_cart_collaterals'); ?>
                    </div>

                    <!-- Боковая панель корзины -->
                    <div class="cart__sidebar sidebar-cart">
						<div class="cart-totals-fragment">
							<div class="sidebar-cart-premium">
								<h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

								<table class="sidebar-cart-premium__table">
									<tbody>
										<!-- Сумма товаров -->
										<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--subtotal">
											<th>Сумма товаров</th>
											<td><?php wc_cart_totals_subtotal_html(); ?></td>
										</tr>

										<?php
										// Рассчитываем общую скидку на все товары
										$total_sale_discount = 0;

										foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
											$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

											if ( $_product && $_product->exists() ) {
												$regular_price = $_product->get_regular_price();
												$sale_price = $_product->get_sale_price();

												// Если есть скидка на товар
												if ( $sale_price && $regular_price > $sale_price ) {
													$item_discount = ( $regular_price - $sale_price ) * $cart_item['quantity'];
													$total_sale_discount += $item_discount;
												}
											}
										}

										// Если общая скидка больше 0, показываем её
										if ( $total_sale_discount > 0 ) : ?>
											<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--sale-discount">
												<th>Скидка на товары</th>
												<td style="color: #f80f4e; font-weight: 600;">
													-<?php echo wc_price( $total_sale_discount ); ?>
												</td>
											</tr>
										<?php endif; ?>

										<?php
										// Отдельно показываем купоны если они есть
										foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
											<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--coupon-discount">
												<th>Скидка по купону "<?php echo esc_html( $coupon->get_code() ); ?>"</th>
												<td style="color: #f80f4e;">
													-<?php wc_cart_totals_coupon_html( $coupon ); ?>
												</td>
											</tr>
										<?php endforeach; ?>

										<!-- ИТОГО -->
										<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--total">
											<th>Итого к оплате:</th>
											<td><?php wc_cart_totals_order_total_html(); ?></td>
										</tr>

										<?php
										// Показываем сколько покупатель сэкономил
										$total_regular_price = 0;
										$total_sale_price = 0;

										foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
											$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

											if ( $_product && $_product->exists() ) {
												$regular_price = $_product->get_regular_price();
												$sale_price = $_product->get_sale_price();
												$price = $_product->get_price();

												if ( $sale_price && $regular_price > $sale_price ) {
													$total_regular_price += $regular_price * $cart_item['quantity'];
													$total_sale_price += $sale_price * $cart_item['quantity'];
												} else {
													$total_regular_price += $price * $cart_item['quantity'];
													$total_sale_price += $price * $cart_item['quantity'];
												}
											}
										}

										$total_savings = $total_regular_price - $total_sale_price;

										if ( $total_savings > 0 ) : ?>
											<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--savings">
												<td colspan="2" style="text-align: center; padding-top: 20px; color: #4CAF50; font-weight: 700; font-size: 1.1em;">
													🎉 Вы экономите: <?php echo wc_price( $total_savings ); ?>
												</td>
											</tr>
										<?php endif; ?>

									</tbody>
								</table>

								<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="sidebar-cart-premium__button">
									Оформить заказ
								</a>
							</div>
						</div>
					</div>
                </div>
            </section>
            <script>
                function smartGoBack() {
                    // Проверяем, есть ли страница в истории
                    if (document.referrer && document.referrer.indexOf(window.location.hostname) !== -1) {
                        history.back();
                    } else {
                        // Если нет истории или пришел с другого сайта - на главную
                        window.location.href = '<?php echo home_url(); ?>';
                    }
                    return false;
                }

                // JavaScript для корзины
                jQuery(document).ready(function($) {
    
                    // Функция для обновления количества и отправки формы
                    function updateCartAndSubmit(cartKey, newQuantity) {
                        // 1. Находим поле количества
                        var $qtyField = $('input[name="cart[' + cartKey + '][qty]"]');
                        
                        // 2. Устанавливаем новое значение
                        $qtyField.val(newQuantity);
                        
                        // 3. Находим форму
                        var $form = $qtyField.closest('form.woocommerce-cart-form');
                        
                        // 4. Явно вызываем клик по кнопке обновления и отправляем форму
                    $form.find('input[name="update_cart"]').prop('disabled', false).trigger('click'); 
                        
                        // ИЛИ (если trigger('click') не сработает из-за кастомных обработчиков):
                        // $form.submit();
                    }
                    
                    // --- 1. Изменение количества (+/-) ---
                    $(document).on('click', '.quantity__button_minus, .quantity__button_plus', function(e) {
                        e.preventDefault(); 
                        
                        var $button = $(this);
                        var $input = $button.closest('.quantity').find('.quantity__field');
                        var currentValue = parseInt($input.val()) || 1;
                        
                        // Извлекаем cartKey из атрибута name поля input
                        // Важно: $input должен быть найден до того, как его значение изменится
                        var cartKey = $input.attr('name').match(/cart\[(.*?)\]/)[1]; 
                        
                        var newQuantity;
                        
                        if ($button.hasClass('quantity__button_plus')) {
                            newQuantity = currentValue + 1;
                        } else {
                            newQuantity = currentValue > 1 ? currentValue - 1 : 1;
                        }
                        
                        // Вызываем функцию обновления
                        updateCartAndSubmit(cartKey, newQuantity);
                    });

                    // Также исправьте удаление товара и изменение размера:
                    $(document).on('click', '.cart__remove', function(e) {
                        e.preventDefault();
                        var cartKey = $(this).data('cart-key');
                        removeFromCart(cartKey);
                    });
                    
                    // Изменение размера
                    $('.cart__attribute').on('click', function() {
                        var $button = $(this);
                        var $attributes = $button.closest('.cart__attributes');
                        var cartKey = $button.data('cart-key');
                        var newSize = $button.data('size');
                        
                        // Обновляем активный класс
                        $attributes.find('.cart__attribute').removeClass('cart__attribute--active');
                        $button.addClass('cart__attribute--active');
                        
                        // Обновляем вариацию в корзине
                        updateCartVariation(cartKey, newSize);
                    });
                    
                    function updateCartQuantity(cartKey, quantity) {
                        $.ajax({
                            url: wc_cart_fragments_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'woocommerce_update_cart_quantity',
                                cart_key: cartKey,
                                quantity: quantity
                            },
                            success: function(response) {
                                $(document.body).trigger('wc_fragment_refresh');
                                location.reload(); // Перезагружаем страницу для обновления цен
                            }
                        });
                    }
                    
                    function removeFromCart(cartKey) {
                        $.ajax({
                            url: wc_cart_fragments_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'woocommerce_remove_from_cart',
                                cart_key: cartKey
                            },
                            success: function(response) {
                                $(document.body).trigger('wc_fragment_refresh');
                                location.reload(); // Перезагружаем страницу для обновления
                            }
                        });
                    }
                    
                    
                    function updateCartVariation(cartKey, newSize) {
                        // !!! ВАМ НУЖЕН PHP-ОБРАБОТЧИК ДЛЯ ЭТОГО AJAX-ХУКА !!!
                        // Если обработчик есть, то код может выглядеть так:
                        
                        $.ajax({
                            url: wc_cart_fragments_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'woocommerce_update_cart_variation', // <-- ЭТОТ ХУК ДОЛЖЕН БЫТЬ ОПРЕДЕЛЕН В PHP
                                cart_key: cartKey,
                                new_size: newSize
                            },
                            success: function(response) {
                                // Если успешно, перезагружаем страницу или обновляем фрагменты
                                window.location.reload(); 
                            }
                        });
                    }
                });
            </script>
            <?php 
        
        }?>
</main>



<?php get_footer(); ?>