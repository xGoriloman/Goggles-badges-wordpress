<?php get_header(); ?>

<main class="page">
    <section class="section__error-block error-block">
        <div class="error-block__container">
            <div class="error-block__content">
                <div class="error-block__text">
                    <p>Такой страницы <br>не существует</p>
                    <!-- Ваш SVG код для 404 страницы -->
                    <svg class="error-block__bg" width="657" height="270" viewBox="0 0 657 270" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Вставьте ваш SVG код из верстки -->
                    </svg>
                </div>
                <a href="<?php echo home_url(); ?>" class="error-block__button button">
                    Вернуться на главную
                </a>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>