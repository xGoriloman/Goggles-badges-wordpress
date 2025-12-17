<?php
/**
 * Template Name: Избранные товары
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main class="page">
    <section class="section__title">
        <h1 class="title">Избранные товары</h1>
    </section>
    <?php 
        if (is_user_logged_in()) {
            ?>
                <?php 
                $user_id = get_current_user_id();
                $favorites = get_user_meta($user_id, 'user_favorites', true);
                
                if (!empty($favorites) && is_array($favorites)) {
                    $args = array(
                        'post_type' => 'product',
                        'post__in' => $favorites,
                        'posts_per_page' => -1,
                        'post_status' => 'publish'
                    );
                    
                    $favorite_products = new WP_Query($args);
                    
                    if ($favorite_products->have_posts()) {
                        ?>

                        <section class="section__form-block form-block">
                            <div class="catalog__container">
                                <?php echo get_sidebar();?>
                                <div class="catalog__products products" id="products-container">
                                    <?php 
                                        
                                        while ($favorite_products->have_posts()) {
                                            $favorite_products->the_post();
                                            wc_get_template_part('content', 'product');
                                        }
                                        
                                        wp_reset_postdata();
                                        ?>

                                </div>
                            </div>
                        </section>
                        <?php 
                    } else {
                    ?>
                    <section style="font-weight: 700;font-size: 1.25rem;letter-spacing: 0.02em;text-transform: uppercase;text-align: center;color: #000;display: flex;flex-direction: column;height: calc(100vw - 52px);align-items: center;justify-content: center;">
                            <h3>В избранном нет товаров</h3>
                    </section>
                    <?php 
                    }
                } else {
                    ?>
                    <section style="font-weight: 700;font-size: 1.25rem;letter-spacing: 0.02em;text-transform: uppercase;text-align: center;color: #000;display: flex;flex-direction: column;height: calc(100vw - 52px);align-items: center;justify-content: center;">
                            <h3>В избранном нет товаров</h3>
                    </section>
                    <?php 
                }


                ?>
            
            <?php 
            } else {
            ?>
                <section class="login-required">
                    <div class="login-required__container">
                        <h3>Требуется авторизация</h3>
                        <p>Для просмотра избранных товаров необходимо войти в систему</p>
                        <div class="auth-buttons">
                            <a href="<?php echo home_url('/login/'); ?>" class="button button-black">Войти</a>
                            <a href="<?php echo home_url('/register/'); ?>" class="button button-transparent">Зарегистрироваться</a>
                    </div>
                </section>
            <?php 
            }
        ?>
</main>
    
<?php get_footer(); ?>