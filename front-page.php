<?php
/**
 * Template Name: Главная
 * Front page template
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="page">
    <!-- Промо-секция -->
    <section class="section__promo promo scroll-animate">
        <div class="promo__container">
            <div class="promo__body">
                <div class="promo__content">
                    <p class="promo__text scroll-animate">
                        <?php echo get_theme_mod('promo_text', 'Оригинальные вещи Stone Island, CP Company, Premiata'); ?>
                    </p>
                    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="promo__button button-transparent scroll-animate">
                        <span>Смотреть коллекцию</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="15" viewBox="0 0 13 15" fill="none">
                            <path d="M6.14062 0C6.71875 0 7.11719 0.398438 7.11719 0.992188V10.25L7.04688 11.9453L8.89844 9.85156L10.6641 8.10156C10.8438 7.92969 11.0781 7.8125 11.3594 7.8125C11.8906 7.8125 12.2891 8.20312 12.2891 8.75C12.2891 9.00781 12.1875 9.25 11.9844 9.45312L6.86719 14.5781C6.67969 14.7734 6.40625 14.8828 6.14062 14.8828C5.875 14.8828 5.60938 14.7734 5.42188 14.5781L0.304688 9.45312C0.101562 9.25 0 9.00781 0 8.75C0 8.20312 0.398438 7.8125 0.929688 7.8125C1.21094 7.8125 1.44531 7.92969 1.625 8.10156L3.38281 9.85156L5.23438 11.9453L5.17188 10.25V0.992188C5.17188 0.398438 5.5625 0 6.14062 0Z" fill="#F5F7FA" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Популярные товары -->
    <section class="section__popular-products popular-products">
        <div class="popular-products__container">
            <h2 class="popular-products__title title scroll-animate">Популярные товары</h2>
            
            <!-- Категории -->
            <div class="popular-products__category scroll-animate">
                <ul>
                    <li class='active'><a class="active scroll-animate" href="#" data-category="new" data-type="new">Новинки</a></li>
                    <li><a class="scroll-animate" href="#" data-category="product_brand" data-type="brands">Бренды</a></li>
                    <li><a class="scroll-animate" href="#" data-category="man" data-type="category">Мужское</a></li>
                    <li><a class="scroll-animate" href="#" data-category="woman" data-type="category">Женское</a></li>
                    <li><a class="scroll-animate" href="#" data-category="aksessuary" data-type="category">Аксессуары</a></li>
                </ul>
            </div>
            
            <!-- Сетка товаров -->
            <div class="popular-products products" id="products-grid">
                <?php
                    $new_products = new WP_Query(array(
                        'post_type' => 'product',
                        'posts_per_page' => 8,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'post_status' => 'publish',
                    ));

                    if ($new_products->have_posts()) {
                        while ($new_products->have_posts()) {
                            $new_products->the_post();
                            wc_get_template_part('content', 'product');
                        }
                        wp_reset_postdata();
                    } else {
                        echo '<p>Товары не найдены</p>';
                    }
                ?>
            </div>
            
            <!-- Лоадер -->
            <div class="products-loader" style="display: none;">
                <!--<div class="loader">Загрузка...</div>-->
            </div>
        </div>
    </section>

    <!-- О нас -->
    <section class="section__about about">
        <div class="about__container">
            <div class="about__body">
                <div class="about__img">
                    <?php
                    $about_image = get_theme_mod('about_image');
                    if ($about_image) {
                        echo '<img src="' . esc_url($about_image) . '" alt="О нас">';
                    } else {
                        echo '<div class="about__placeholder"></div>';
                    }
                    ?>
                </div>
                <div class="about__content">
                    <h3 class="about__title title scroll-animate">О нас</h3>
                    <p class="about__text scroll-animate">
                        <?php echo get_theme_mod('about_text', 'Мы собираем лучшие вещи от проверенных брендов, чтобы вы могли обновлять гардероб легко и стильно. Простая навигация, актуальные коллекции и удобная покупка прямо в Telegram — всё, чтобы шопинг стал быстрым и приятным.'); ?>
                    </p>
                    
                    <ul class="about__advantages advantages">
                        <li class="advantages__item scroll-animate">
                            <span class="advantages__icon">
								<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
		  <circle cx="24" cy="24" r="24" fill="#F5F7FA" />
		  <path d="M35 25V27C34.9987 27.3643 34.8975 27.7213 34.7076 28.0322C34.5177 28.3431 34.2463 28.5961 33.9228 28.7636C33.7421 29.9407 33.1467 31.0144 32.244 31.7912C31.3414 32.568 30.1909 32.9967 29 33C28.7348 33 28.4804 32.8946 28.2929 32.7071C28.1054 32.5196 28 32.2652 28 32C28 31.7348 28.1054 31.4804 28.2929 31.2929C28.4804 31.1053 28.7348 31 29 31C29.677 30.9985 30.3336 30.7681 30.863 30.3462C31.3925 29.9243 31.7637 29.3358 31.9164 28.6763C31.6358 28.4957 31.4049 28.2476 31.2448 27.9548C31.0847 27.662 31.0005 27.3337 31 27V25C31.0003 24.731 31.0551 24.4648 31.1612 24.2176C31.2672 23.9704 31.4222 23.7472 31.6169 23.5615C31.1007 21.9468 30.0847 20.538 28.7156 19.5383C27.3465 18.5387 25.6952 18 24 18C22.3048 18 20.6535 18.5387 19.2844 19.5383C17.9153 20.538 16.8993 21.9468 16.3831 23.5615C16.5778 23.7472 16.7328 23.9704 16.8388 24.2176C16.9449 24.4648 16.9997 24.731 17 25V27C17 27.5304 16.7893 28.0391 16.4142 28.4142C16.0391 28.7893 15.5304 29 15 29C14.4696 29 13.9609 28.7893 13.5858 28.4142C13.2107 28.0391 13 27.5304 13 27V25C13.0016 24.5698 13.1421 24.1516 13.4005 23.8077C13.659 23.4638 14.0216 23.2125 14.4345 23.0914C15.0576 21.0399 16.3239 19.2431 18.0463 17.9662C19.7686 16.6893 21.8559 16 24 16C26.1441 16 28.2314 16.6893 29.9537 17.9662C31.6761 19.2431 32.9424 21.0399 33.5655 23.0914C33.9784 23.2125 34.341 23.4638 34.5995 23.8077C34.8579 24.1516 34.9984 24.5698 35 25Z" fill="#191919" />
		</svg>
                            </span>
                            <p class="advantages__text">Круглосуточная поддержка</p>
                        </li>
                        <li class="advantages__item scroll-animate">
                            <span class="advantages__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none">
									<circle cx="24" cy="24" r="24" fill="#F5F7FA"/>
									<path d="M18.4766 29.3516C18.0664 29.3516 17.7969 29.0469 17.7969 28.6484C17.7969 28.25 18.0664 27.9453 18.4766 27.9453H20.3164V25.6953H18.4766C18.0664 25.6953 17.7969 25.3906 17.7969 24.9922C17.7969 24.5938 18.0664 24.2891 18.4766 24.2891H20.3164V16.4258C20.3164 15.7578 20.7266 15.3359 21.3711 15.3359H25.6719C28.8477 15.3359 30.8867 17.5625 30.8867 20.5273C30.8867 23.5039 28.8242 25.6953 25.6484 25.6953H22.4258V27.9453H26.7852C27.1953 27.9453 27.4648 28.25 27.4648 28.6484C27.4648 29.0469 27.1953 29.3516 26.7852 29.3516H22.4258V31.0859C22.4258 31.707 21.9922 32.1641 21.3711 32.1641C20.75 32.1641 20.3164 31.707 20.3164 31.0859V29.3516H18.4766ZM22.4258 23.8672H25.1211C27.5352 23.8672 28.7188 22.5664 28.7188 20.5273C28.7188 18.5 27.5469 17.2109 25.1211 17.2109H22.4258V23.8672Z" fill="black"/>
									</svg>
                            </span>
                            <p class="advantages__text scroll-animate">Быстрая доставка</p>
                        </li>
                    </ul>

                    <button class="about__button button-transparent scroll-animate">
                        Скачать реквизиты
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Отзывы -->
    <section class="section__comments comments">
        <div class="comments__wrapper">
            <div class="comments__slider swiper">
                <div class="comments__wrapper swiper-wrapper">
                    <?php
                    // Получаем отзывы из произвольного типа записи или комментарии
                    $testimonials = get_posts(array(
                        'post_type' => 'testimonial',
                        'posts_per_page' => 10,
                        'post_status' => 'publish'
                    ));
                    
                    if ($testimonials) {
                        foreach ($testimonials as $testimonial) {
                            $author = get_the_author_meta('display_name', $testimonial->post_author);
                            $avatar = get_avatar_url($testimonial->post_author, array('size' => 60));
                            $link = get_post_meta($testimonial->ID, 'testimonial_link', true);
                            ?>
                            <div class="comments__slide slide-comments swiper-slide scroll-animate">
                                <div class="slide-comments__top">
                                    <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($author); ?>" class="slide-comments__avatar" />
                                    <div class="slide-comments__name"><?php echo esc_html($author); ?></div>
                                    <?php if ($link): ?>
                                        <a href="<?php echo esc_url($link); ?>" class="slide-comments__link" target="_blank">
                                            <span><?php echo esc_html($link); ?></span>
                                            <svg width="11" height="8" viewBox="0 0 11 8" fill="none">
                                                <path d="M0.145142 7.0166C-0.0448462 6.88477 -0.0518828 6.69434 0.145142 6.55273L7.6954 1.31348L8.78607 0.600586L5.59146 0.65918H2.53758C2.28426 0.65918 2.05909 0.512695 2.05909 0.332031C2.05909 0.15625 2.25612 0 2.55869 0H9.76415C10.0738 0 10.2778 0.146484 10.2778 0.356445L10.2849 5.35156C10.2849 5.55176 10.0597 5.69824 9.79934 5.69824C9.53898 5.69824 9.32085 5.54199 9.32085 5.36133V3.0127L9.41232 1.01562L8.36387 1.78223L0.820654 7.0166C0.609556 7.16309 0.342166 7.14844 0.145142 7.0166Z" fill="#32A8DC" />
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <p class="slide-comments__text">
                                    <?php echo wp_kses_post($testimonial->post_content); ?>
                                </p>
                            </div>
                            <?php
                        }
                    } else {
                        // Запасные отзывы
                        for ($i = 0; $i < 5; $i++) {
                            ?>
                            <div class="comments__slide slide-comments swiper-slide scroll-animate">
                                <p class="slide-comments__text">
                                    Не первый и не последний раз заказываю. Все отлично. Парни
                                    большие молодцы и максимально заряжены помочь, подобрать 💪
                                </p>
                                <div class="slide-comments__top">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/comments/avatar.jpg" alt="Аватар" class="slide-comments__avatar" />
                                    <div class="slide-comments__name">Екатерина Козлова</div>
                                    <a href="https://t.me/gnb_feedback/503" class="slide-comments__link" target="_blank">
                                        <span>https://t.me/gnb_feedback/503</span>
                                        <svg width="11" height="8" viewBox="0 0 11 8" fill="none">
                                            <path d="M0.145142 7.0166C-0.0448462 6.88477 -0.0518828 6.69434 0.145142 6.55273L7.6954 1.31348L8.78607 0.600586L5.59146 0.65918H2.53758C2.28426 0.65918 2.05909 0.512695 2.05909 0.332031C2.05909 0.15625 2.25612 0 2.55869 0H9.76415C10.0738 0 10.2778 0.146484 10.2778 0.356445L10.2849 5.35156C10.2849 5.55176 10.0597 5.69824 9.79934 5.69824C9.53898 5.69824 9.32085 5.54199 9.32085 5.36133V3.0127L9.41232 1.01562L8.36387 1.78223L0.820654 7.0166C0.609556 7.16309 0.342166 7.14844 0.145142 7.0166Z" fill="#32A8DC" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <div class="swiper-pagination comments__pagination"></div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>