<?php
/**
 * The template for displaying all pages
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<main class="page section">
    <div class="page__container section__container">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>

                <div class="page__content">
                    <?php if (has_post_thumbnail() && !is_front_page()) : ?>
                        <div class="page__thumbnail">
                            <?php the_post_thumbnail('full', array('class' => 'page__image')); ?>
                        </div>
                    <?php endif; ?>

                    <div class="page__text">
                        <?php the_content(); ?>
                        
                        <?php
                        // Пагинация для многостраничных постов
                        wp_link_pages(array(
                            'before' => '<div class="page-links">' . esc_html__('Страницы:', 'your-theme'),
                            'after'  => '</div>',
                        ));
                        ?>
                    </div>
                </div>

                <?php
                // Комментарии
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>

            <?php endwhile; ?>
        <?php else : ?>
            <div class="page__not-found">
                <h2><?php esc_html_e('Страница не найдена', 'your-theme'); ?></h2>
                <p><?php esc_html_e('Извините, но страница которую вы ищете не существует.', 'your-theme'); ?></p>
                <a href="<?php echo home_url(); ?>" class="button button-black">
                    <?php esc_html_e('Вернуться на главную', 'your-theme'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php 
	if(is_privacy_policy() ){
		?>
			<style>
				.page__container{					
    				margin-top: 2.5em;
				}
				.page__text {
				  max-width: 800px;
				  margin: 0 auto;
				  font-size: 1.1rem;
				  line-height: 1.7;
				  color: #333;
				}
				.page__text > * + * {
				  margin-top: 1.5em;
				}
				.page__text h4.wp-block-heading {
				  font-size: 1.6rem;
				  font-weight: 700;
				  color: var(--black-color);
				  margin-top: 2.5em;
				  margin-bottom: 0.5em;
				  padding-bottom: 0.3em;
				  border-bottom: 1px solid #e6e9ed;
				}
				.page__text ul.wp-block-list {
				  list-style: disc;
				  padding-left: 25px;
				}
				.page__text li {
				  margin-bottom: 0.5em;
				}
				.page__text a {
				  color: #007bff;
				  text-decoration: none;
				  transition: text-decoration 0.3s ease;
				}

				.page__text a:hover {
				  text-decoration: underline;
				}
				.page__text strong {
				  color: var(--black-color);
				  font-weight: 600;
				}
				.page__text hr.wp-block-separator {
				  margin-top: 2.5em;
				  border: none;
				  border-top: 1px solid #e6e9ed;
				}
			
			</style>
		<?php
		
	}
?>
<?php get_footer(); ?>