        <footer class="footer" style="opacity: 0;">
            <div class="footer__container" style="display: none;">
                <div class="footer__top">
                    <div class="footer__column footer__column--about">
                        <a href="<?php echo home_url('/'); ?>" class="footer__logo logo">
                           Goggles & Badges
                        </a>
                        <p class="footer__text">
                            Интернет-магазин оригинальной одежды от ведущих брендов. Доставка по всей России.
                        </p>
                    </div>

                    <div class="footer__column footer__column--menu">
                        <h3 class="footer__title">Меню</h3>
                        <?php
                        if (has_nav_menu('footer_menu')) {
                            wp_nav_menu([
                                'theme_location' => 'footer_menu',
                                'container' => false,
                                'menu_class' => 'footer__list',
                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            ]);
                        }
                        ?>
                    </div>

                    <div class="footer__column footer__column--customer">
                        <h3 class="footer__title">Покупателям</h3>
                        <?php
                        if (has_nav_menu('customer_menu')) {
                            wp_nav_menu([
                                'theme_location' => 'customer_menu',
                                'container' => false,
                                'menu_class' => 'footer__list',
                                'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            ]);
                        }
                        ?>
                    </div>

                    <div class="footer__column footer__column--contacts">
                        <h3 class="footer__title">Контакты</h3>
                        <ul class="footer__list footer__list--contacts">
                            <li><a href="mailto:example@gogglesnbadges.ru">example@gogglesnbadges.ru</a></li>
                            <li><a href="tel:+79991234567">+7 (999) 123-45-67</a></li>
                        </ul>
                        <div class="footer__socials">
                            <a href="#" target="_blank" aria-label="Telegram">
                                <!-- Иконка Telegram -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.88l-1.42 6.69c-.14.65-.54.81-1.08.5l-2.18-1.61l-1.05 1.01c-.12.12-.22.22-.44.22l.16-2.24l4.1-3.72c.18-.16-.05-.25-.3-.1l-5.07 3.19l-2.14-.67c-.66-.21-.67-.67.14-1l8.26-3.23c.53-.19 1 .13.84.97z"/></svg>
                            </a>
                            <a href="#" target="_blank" aria-label="VK">
                                <!-- Иконка VK -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M13.15 17.5s.24-.09.34-.23c.1-.14.12-.34.12-.34s0-1.82 1.3-2.06c1.19-.22 1.9.72 2.95 1.61.85.74 1.48 1.13 2.06.94.58-.2 1-1 1-1s-.01-1.85-2.22-3.78c-2.3-2.02-1.9-1.63.7-4.52C21.41 7.2 21.84 6.2 21.5 5.5c-.34-.7-.96-1-1.47-1.03-.51-.03-1.1.06-1.1.06s-.62.03-1.09.43c-.47.4-.77.92-.77.92s-.6 1.48-1.3 2.44c-1.38 1.9-1.92 2.08-2.13 1.95-.58-.33-.48-1.42-.48-2.2s.22-2.12-.3-2.39c-.3-.15-.7-.22-1.22-.24-1.2-.04-2.1.24-2.58.55-.48.3-.78.82-.67 1.06.1.24.47.38.7.42.33.06.8.18.96.58.15.4.08 1.1-.2 1.38s-.6.3-1.6.04c-1-.26-1.8-.83-2.5-1.55-.54-.56-1-1.18-1.03-1.21s.2-.18.7-.58c.48-.4.7-.68.6-.9-.08-.22-.3-.4-.7-.4s-1 .04-1.9.83c-.9.78-1.3 1.64-1.1 2.3.18.66.8 1.1 1.4 1.25.6.14 1.08.03 1.08.03s.5-.1.7.04c.2.14.22.4.22.4s-.06 1.1.3 1.34c.36.24.9.12 2.2-1.1.27-.26.5-.55.7-.83.2-.28.4-.58.58-.85l.02.02c.6 1.1 1.2 1.7 1.9 2.1 1.5.8 1.3 1.8.3 2.1-.5.14-1.2 0-2.4-.73-1.2-.73-2.1-1.7-2.3-1.9-.2-.2-.5-.3-.7-.14-.2.14-.3.4-.1.6.2.2.6.5 1.1 1.1.5.6 1.3 1.3 2.5 1.8 1.2.5 2.6.2 3.1-.7z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="footer__bottom">
                    <p class="footer__copy">&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Все права защищены.</p>
                    <a href="<?php echo get_privacy_policy_url()?>" class="footer__policy">Политика конфиденциальности</a>
                </div>
            </div>
        </footer>

    </div>

    <?php wp_footer(); ?>

    </body>
</html>