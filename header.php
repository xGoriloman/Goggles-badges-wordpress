<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,100;1,200;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
</head>
<body <?php body_class(); ?>>
    <div class="wrapper">
        <header class="header">
            <div class="header__container">
                <a href="<?php echo home_url(); ?>" class="header__logo logo">
                    <?php bloginfo('name'); ?>
                </a>
                
                <div class="header__menu menu">
                    <button type="button" class="menu__icon icon-menu">
                        <span></span>
                    </button>
                    
                    <nav class="menu__body">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'container' => false,
                            'menu_class' => 'menu__list',
                            'walker' => new Custom_Walker_Nav_Menu()
                        ));
                        ?>
                    </nav>
                </div>
            </div>
        </header>