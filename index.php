<?php get_header(); ?>

<main class="page">
    <section class="section__content">
        <div class="content__container">
            <?php if (have_posts()) : ?>
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="post-card">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="pagination">
                    <?php the_posts_pagination(); ?>
                </div>
            <?php else : ?>
                <p>Записи не найдены.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>