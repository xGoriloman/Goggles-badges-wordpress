<?php
/**
 * The template for displaying single product pages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header(); ?>

    <main class="page">
        <div class="back__container section__title">
            <a href="javascript:history.back()" class="back__button">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icon/arrow-black.svg" alt="Назад" />
                Назад
            </a>
        </div>

        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            
            <?php wc_get_template_part('content', 'single-product'); ?>
            
        <?php endwhile; ?>
    </main>

<?php get_footer(); ?>