<?php
/**
 * Edit address form (Переопределено для выбора ПВЗ СДЭК)
 */

defined( 'ABSPATH' ) || exit;

$load_address = empty( $load_address ) ? 'shipping' : $load_address; 

if ( $load_address !== 'shipping' ) {
    wc_get_template( 'myaccount/form-edit-address.php', array( 'load_address' => $load_address ) );
    return;
}

// --- ИСПОЛЬЗУЕМ КОНСТАНТЫ ИЗ functions.php ---
$customer_id = get_current_user_id();
$saved_pvz = get_user_meta($customer_id, CDEK_SAVED_POINTS_META, true); 
$current_pvz_code = get_user_meta($customer_id, CDEK_PVZ_CODE_FIELD, true); 

if (!is_array($saved_pvz) || empty($saved_pvz)) {
    // ЗАГЛУШКА, если плагин не хранит список избранных ПВЗ
    $saved_pvz = [
        ['address' => 'Большая Никитская, д. 24/1', 'code' => 'MSK001'],
        ['address' => 'проспект Мира, д. 150, корп. 2', 'code' => 'MSK002'],
        ['address' => 'Покровка, д. 27, стр. 1', 'code' => 'MSK003'],
    ];
}
?>

<div class="address-page-content">

    <!-- Форма для отправки данных адреса -->
    <form method="post" id="wc-pvz-form" action="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping' ) ); ?>">
    
        <!-- Поле поиска / Выбора пункта (Заглушка для внешнего вида макета) -->
        <div class="pvz-search-block">
            <input type="text" id="pvz-selected-address" placeholder="Выберите пункт получения" readonly> 
            <span class="search-icon">􀊫</span> 
        </div>

        <!-- Блок Карты (Контейнер для виджета СДЭК) -->
        <div class="pvz-map-container">
            <div id="cdek-map" style="width: 100%; height: 320px;">
                <!-- Виджет СДЭК будет инициализирован здесь через JS -->
            </div>
            
            <?php 
            // ИМИТАЦИЯ БЛОКА ДЕТАЛЕЙ ПВЗ (Если он отображается по умолчанию, а не через JS)
            // В большинстве случаев это контролирует сам виджет CDEK.
            ?>
        </div>
        
        <!-- Блок "Мои пункты" -->
        <h2 class="pf-dindisplay-pro-bold my-pvz-header">Мои пункты</h2>
        
        <div class="address-card" style="margin-top: 0; margin-bottom: 120px;">
            <?php 
            if ( !empty($saved_pvz) ):
                foreach ( $saved_pvz as $point ) : 
                    
                    $pvz_code = $point['code'] ?? ''; 
                    $address_text = $point['address'] ?? ''; 

                    if (empty($pvz_code)) continue;
                    
                    $is_selected = ($pvz_code == $current_pvz_code); 
                    ?>

                    <div class="address-item pvz-item <?php echo $is_selected ? 'selected' : ''; ?>" 
                        data-pvz-code="<?php echo esc_attr( $pvz_code ); ?>"
                        data-address-text="<?php echo esc_attr( $address_text ); ?>">
                        
                        <div class="address-icon"></div>
                        
                        <div class="address-text">
                            <?php echo esc_html( $address_text ); ?>
                        </div>
                        
                    </div>
                <?php endforeach; 
            else:
                echo '<p style="padding: 16px; font-size: 14px; color: #AAB2BD;">У вас пока нет избранных пунктов выдачи.</p>';
            endif;
            ?>
        </div>
        
        <!-- СКРЫТОЕ ПОЛЕ, КОТОРОЕ СОХРАНЯЕТСЯ В МЕТАДАННЫЕ ПОЛЬЗОВАТЕЛЯ -->
        <input type="hidden" class="cdek-pvz-code" 
               id="<?php echo esc_attr( CDEK_PVZ_CODE_FIELD ); ?>" 
               name="<?php echo esc_attr( CDEK_PVZ_CODE_FIELD ); ?>" 
               value="<?php echo esc_attr( $current_pvz_code ); ?>">
        
        <!-- Обязательные поля WC для отправки формы адреса -->
        <input type="hidden" name="action" value="edit_address" />
        <input type="hidden" name="address_name" value="shipping" />
        <?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
        
    </form>

</div>

<!-- Кнопка "Сохранить" (Фиксированная внизу) -->
<button type="button" class="btn-save-fixed woocommerce-Button" id="btn-save-pvz">
    Сохранить
</button>