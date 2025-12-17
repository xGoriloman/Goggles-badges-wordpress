<?php
/**
 * My Account
 * Шаблон страницы профиля пользователя
 * 
 * @package WooCommerce\Templates
 */

// Если открыт специфичный endpoint — используем стандартный шаблон

if (!is_user_logged_in()) {
    wp_safe_remote_get(wc_get_page_permalink('myaccount'));
}

$current_user = wp_get_current_user();
?>

<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>



<?php get_footer();