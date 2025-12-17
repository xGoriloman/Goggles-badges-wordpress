<?php
/**
 * Template Name: Корзина
 * Template Post Type: page
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

                                            // Получаем min/max значения для количества
                                            $min_quantity = 1;
                                            $max_quantity = -1; // -1 означает без ограничений
                                            
                                            // Для простых товаров
                                            if ($_product->is_type('simple')) {
                                                $min_quantity = $_product->get_min_purchase_quantity() ?: 1;
                                                $max_quantity = $_product->get_max_purchase_quantity() ?: -1;
                                                
                                                // Проверяем количество на складе
                                                if ($_product->managing_stock()) {
                                                    $stock_quantity = $_product->get_stock_quantity();
                                                    if ($stock_quantity && ($max_quantity === -1 || $stock_quantity < $max_quantity)) {
                                                        $max_quantity = $stock_quantity;
                                                    }
                                                }
                                            }
                                            
                                            // Для вариативных товаров
                                            if ($_product->is_type('variation')) {
                                                $min_quantity = $_product->get_min_purchase_quantity() ?: 1;
                                                $max_quantity = $_product->get_max_purchase_quantity() ?: -1;
                                                
                                                // Проверяем количество на складе
                                                if ($_product->managing_stock()) {
                                                    $stock_quantity = $_product->get_stock_quantity();
                                                    if ($stock_quantity && ($max_quantity === -1 || $stock_quantity < $max_quantity)) {
                                                        $max_quantity = $stock_quantity;
                                                    }
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
                                                                <button type="button" class="quantity__button quantity__button_minus" 
                                                                        data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                                                        aria-label="Уменьшить количество">
                                                                    <svg width="12" height="2" viewBox="0 0 12 2" fill="none">
                                                                        <path d="M1 1H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                                                                    </svg>
                                                                </button>
                                                                
                                                                <div class="quantity__input">
                                                                    <input value="<?php echo esc_attr($cart_item['quantity']); ?>" 
                                                                        autocomplete="off" 
                                                                        type="text" 
                                                                        name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" 
                                                                        class="quantity__field" 
                                                                        data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                                                        data-min="<?php echo esc_attr($min_quantity); ?>"
                                                                        data-max="<?php echo esc_attr($max_quantity); ?>"
                                                                        aria-label="Количество товара">
                                                                </div>
                                                                
                                                                <button type="button" class="quantity__button quantity__button_plus"
                                                                        data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                                                        aria-label="Увеличить количество">
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

                                                    <button class="cart__remove" 
                                                            aria-label="Удалить товар"
                                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>"
                                                            data-product-id="<?php echo esc_attr($product_id); ?>">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
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
						<div class="cart-totals-fragment" data-fragment="custom-cart-totals">
							<div class="sidebar-cart-premium">
								<h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

								<table class="sidebar-cart-premium__table">
									<tbody>
										<!-- Сумма товаров -->
										<div class="cart__sidebar sidebar-cart">
											<div class="cart-totals-fragment" data-fragment="custom-cart-totals">
												<div class="sidebar-cart-premium">
													<h3 class="sidebar-cart-premium__title">Ваш заказ</h3>

													<table class="sidebar-cart-premium__table">
														<tbody>
															<?php
															// Рассчитываем общую сумму по обычным ценам и общую скидку
															$total_regular_price = 0;
															$total_sale_discount = 0;

															foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
																$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

																if ( $_product && $_product->exists() ) {
																	$regular_price = $_product->get_regular_price();
																	$sale_price = $_product->get_sale_price();
																	$quantity = $cart_item['quantity'];

																	// Сумма по обычной цене
																	$total_regular_price += $regular_price * $quantity;

																	// Если есть скидка на товар
																	if ( $sale_price && $regular_price > $sale_price ) {
																		$item_discount = ( $regular_price - $sale_price ) * $quantity;
																		$total_sale_discount += $item_discount;
																	}
																}
															}
															?>

															<!-- Цена без скидок -->
															<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--regular-price">
																<th>Сумма</th>
																<td><?php echo wc_price( $total_regular_price ); ?></td>
															</tr>

															<?php
															// Если общая скидка больше 0, показываем её
															if ( $total_sale_discount > 0 ) : ?>
																<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--sale-discount">
																	<th>Скидка</th>
																	<td>
																		-<?php echo wc_price( $total_sale_discount ); ?>
																	</td>
																</tr>
															<?php endif; ?>

															<!-- ИТОГО -->
															<tr class="sidebar-cart-premium__row sidebar-cart-premium__row--total">
																<th>Итого к оплате:</th>
																<td><?php wc_cart_totals_order_total_html(); ?></td>
															</tr>                                    

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
            <?php 
        
        }?>
</main>

<?php get_footer(); ?>