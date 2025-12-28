<?php
/**
 * Edit addresses - управление адресами СДЭК (ПВЗ + Курьерская доставка)
 * Скопировать в: theme/woocommerce/myaccount/edit-address.php
 */
defined('ABSPATH') || exit;

$cdek_addresses = function_exists('cdek_get_addresses') ? cdek_get_addresses() : array();
$pvz_addresses = array_filter($cdek_addresses, function($a) { return !empty($a['pvz_code']); });
$door_addresses = array_filter($cdek_addresses, function($a) { return empty($a['pvz_code']) && !empty($a['address']); });

$yandex_api_key = '';
if (class_exists('CDEK_Pro')) $yandex_api_key = CDEK_Pro::get_option('yandex_api_key', '');
if (empty($yandex_api_key)) $yandex_api_key = get_option('cdek_yandex_api_key', '');

$default_city = 'Москва';
$default_city_code = 44;
if (class_exists('CDEK_Pro')) {
    $default_city = CDEK_Pro::get_option('sender_city', 'Москва');
    $default_city_code = CDEK_Pro::get_option('sender_city_code', 44);
}
?>
<div class="addresses-page">
    <div class="page-header">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>" class="back-btn"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <h1 class="page-title">Мои адреса</h1>
    </div>
    
    <!-- Табы -->
    <div class="tabs">
        <button type="button" class="tab active" data-tab="pvz">Пункты выдачи</button>
        <button type="button" class="tab" data-tab="door">Курьером</button>
    </div>
    
    <!-- Таб ПВЗ -->
    <div class="tab-content active" id="tab-pvz">
        <div class="search-section">
            <div class="search-input-wrapper">
                <input type="text" id="city-search" class="search-input" placeholder="Введите город..." value="<?php echo esc_attr($default_city); ?>" autocomplete="off">
                <div class="search-icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="9" cy="9" r="7" stroke="#AAB2BD" stroke-width="2"/><path d="M15 15L19 19" stroke="#AAB2BD" stroke-width="2" stroke-linecap="round"/></svg></div>
                <div id="city-suggestions" class="city-suggestions"></div>
            </div>
        </div>
        
        <div class="map-section">
            <div class="map-container">
                <div id="pvz-map" class="pvz-map"></div>
                <div id="map-loading" class="map-loading"><div class="spinner"></div><span>Загрузка...</span></div>
            </div>
            <div class="selected-pvz-panel hidden" id="selected-panel">
                <div class="selected-pvz-info">
                    <div class="selected-pvz-name" id="sel-name"></div>
                    <div class="selected-pvz-address" ><b>Адрес:</b> <span id="sel-addr"></span></div>
                    <button type="button" class="btn-add-pvz" id="btn-add"><span class="btn-text">Добавить</span><span class="btn-load hidden">...</span></button>
                    <div class="selected-pvz-time" ><b>Время работы:</b> <span id="sel-time"></span></div>
                </div>
            </div>
        </div>
        
        <div class="addresses-section">
            <h2 class="section-title">Сохранённые ПВЗ</h2>
            <div class="addresses-list" id="pvz-list">
                <?php if (!empty($pvz_addresses)) : foreach ($pvz_addresses as $a) : 
                    $def = !empty($a['is_default']);
                    $parts = explode(', ', $a['pvz_address'] ?? '');
                    $disp = count($parts) >= 5 ? implode(', ', array_slice($parts, 4)) : ($a['pvz_address'] ?? '');
                ?>
                <div class="address-card <?php echo $def ? 'is-default' : ''; ?>" data-id="<?php echo esc_attr($a['id']); ?>" data-pvz-code="<?php echo esc_attr($a['pvz_code'] ?? ''); ?>" data-type="pvz">
                    <div class="address-radio"><input type="radio" name="default_address" value="<?php echo esc_attr($a['id']); ?>" <?php checked($def); ?> class="address-radio-input"><span class="address-radio-mark"></span></div>
                    <div class="address-content">
                        <div class="address-name"><?php echo esc_html($a['pvz_name'] ?? 'ПВЗ'); ?></div>
                        <div class="address-text"><?php echo esc_html($disp); ?></div>
                    </div>
                    <button type="button" class="address-delete" data-id="<?php echo esc_attr($a['id']); ?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
                </div>
                <?php endforeach; else : ?>
                <div class="empty-addresses"><div class="empty-icon">📍</div><p class="empty-text">Нет сохранённых ПВЗ</p><p class="empty-hint">Выберите пункт на карте</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Таб Курьер -->
    <div class="tab-content" id="tab-door">
        <div class="map-section">
            <div class="map-container">
                <div id="door-map" class="pvz-map"></div>
                <div id="door-map-loading" class="map-loading"><div class="spinner"></div><span>Загрузка...</span></div>
            </div>
            <div class="door-hint">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 9 7 9s7-3.75 7-9c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#AAB2BD"/></svg>
                <span>Кликните на карту или введите адрес вручную</span>
            </div>
        </div>
        
        <div class="address-form" id="door-form">
            <div class="form-group">
                <label class="form-label">Город</label>
                <input type="text" id="door-city" class="form-input" placeholder="Москва" value="<?php echo esc_attr($default_city); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Улица, дом</label>
                <input type="text" id="door-street" class="form-input" placeholder="ул. Пушкина, д. 10">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Квартира</label>
                    <input type="text" id="door-apt" class="form-input" placeholder="25">
                </div>
                <div class="form-group">
                    <label class="form-label">Подъезд</label>
                    <input type="text" id="door-entrance" class="form-input" placeholder="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Этаж</label>
                    <input type="text" id="door-floor" class="form-input" placeholder="5">
                </div>
            </div>
            <button type="button" class="btn-add-door" id="btn-add-door">
                <span class="btn-text">Добавить адрес</span>
                <span class="btn-load hidden">...</span>
            </button>
        </div>
        
        <div class="addresses-section">
            <h2 class="section-title">Сохранённые адреса</h2>
            <div class="addresses-list" id="door-list">
                <?php if (!empty($door_addresses)) : foreach ($door_addresses as $a) : 
                    $def = !empty($a['is_default']);
                    $full = trim(($a['city'] ?? '') . ', ' . ($a['address'] ?? ''));
                    if (!empty($a['apartment'])) $full .= ', кв. ' . $a['apartment'];
                ?>
                <div class="address-card <?php echo $def ? 'is-default' : ''; ?>" data-id="<?php echo esc_attr($a['id']); ?>" data-type="door">
                    <div class="address-radio"><input type="radio" name="default_door" value="<?php echo esc_attr($a['id']); ?>" <?php checked($def); ?> class="address-radio-input"><span class="address-radio-mark"></span></div>
                    <div class="address-content">
                        <div class="address-name">Курьерская доставка</div>
                        <div class="address-text"><?php echo esc_html($full); ?></div>
                    </div>
                    <button type="button" class="address-delete" data-id="<?php echo esc_attr($a['id']); ?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
                </div>
                <?php endforeach; else : ?>
                <div class="empty-addresses"><div class="empty-icon">🏠</div><p class="empty-text">Нет сохранённых адресов</p><p class="empty-hint">Добавьте адрес для курьера</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($yandex_api_key)) : ?><script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo esc_attr($yandex_api_key); ?>&lang=ru_RU"></script><?php endif; ?>

<style>
:root{--bg:#F5F7FA;--white:#FFF;--black:#1D1D1F;--gray-100:#F5F5F7;--gray-200:#E5E5E7;--gray-400:#AAB2BD;--gray-500:#86868B;--primary:#191919;--green:#00B33C;--success:#34C759;--danger:#FF3B30;--blue:#191919;--radius-md:12px;--radius-lg:16px;--radius-xl:24px;}
*{box-sizing:border-box}.addresses-page{font-family:var(--font-family);max-width:420px;margin:0 auto;padding:0 16px 40px;background:var(--bg);min-height:100vh}
.page-header{
    display: grid;
    grid-template-columns: 0.2fr 1fr .2fr;align-items:center;gap:12px;padding:16px 0;position:sticky;top:0;z-index:100;background:var(--bg)}
.back-btn{width:40px;height:40px;;border-radius:100px;display:flex;align-items:center;justify-content:center;color:var(--black);text-decoration:none}
.page-title{font-size:18px;font-weight:700;text-transform:uppercase;letter-spacing:.02em;margin:0}

/* Табы */
.tabs{display:flex;gap:8px;margin-bottom:20px;background:var(--gray-100);padding:4px;border-radius:var(--radius-lg)}
.tab{flex:1;padding:12px;background:transparent;border:none;border-radius:var(--radius-md);font-size:14px;font-weight:600;color:var(--gray-500);cursor:pointer;transition:all .2s}
.tab.active{background:var(--white);color:var(--black);box-shadow:0 2px 8px rgba(0,0,0,.08)}
.tab-content{display:none}.tab-content.active{display:block}

#tab-door .map-section{display:none}

/* Поиск */
.search-section{margin-bottom:16px}.search-input-wrapper{position:relative}
.search-input{width:100%;padding:16px 50px 16px 20px;background:var(--white);border:none;border-radius:var(--radius-lg);font-size:16px;outline:none}
.search-input:focus{box-shadow:0 0 0 2px var(--primary)}.search-icon{position:absolute;right:18px;top:50%;transform:translateY(-50%)}
.city-suggestions{position:absolute;top:100%;left:0;right:0;background:var(--white);border-radius:var(--radius-md);box-shadow:0 8px 24px rgba(0,0,0,.12);margin-top:8px;max-height:250px;overflow-y:auto;z-index:200;display:none}
.city-suggestions.active{display:block}.city-suggestion{padding:14px 20px;cursor:pointer;border-bottom:1px solid var(--gray-100)}
.city-suggestion:hover{background:var(--gray-100)}.city-suggestion-name{font-size:15px;font-weight:500}
.city-suggestion-region{font-size:13px;color:var(--gray-500);margin-top:2px}

/* Карта */
.map-section{position:relative; margin-bottom:20px}.map-container{position:relative;background:var(--white);border-radius:var(--radius-xl);overflow:hidden;height:280px}
.pvz-map{width:100%;height:100%}.map-loading{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--white);gap:12px;color:var(--gray-500)}
.map-loading.hidden{display:none}.spinner{width:32px;height:32px;border:3px solid var(--gray-200);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.door-hint{display:flex;align-items:center;gap:8px;padding:12px 16px;background:var(--white);border-radius:var(--radius-md);margin-top:12px;font-size:13px;color:var(--gray-500)}

/* Панель выбранного ПВЗ */
.selected-pvz-panel{position: absolute;
    bottom: 5px;
    left: 5px;
    right: 5px;
display:flex;align-items:center;gap:12px;padding:16px;background:var(--white);border-radius:var(--radius-lg);margin-top:12px;box-shadow:0 2px 8px rgba(0,0,0,.08);animation:slideUp .3s ease}
.selected-pvz-panel.hidden{display:none}.selected-pvz-info{flex:1;min-width:0}
.selected-pvz-name{font-size:15px;font-weight:600;margin-bottom:7px}.selected-pvz-address{font-size:13px;color:var(--gray-500);margin-bottom:14px}
.selected-pvz-time{font-size:12px;color:var(--gray-400)}
.btn-add-pvz{padding:12px 20px;background:var(--primary);color:#fff;border:none;border-radius:100px;font-size:14px;font-weight:600;cursor:pointer;white-space:nowrap;transition:all .2s; width: 100%;margin-bottom: 7px;}
.btn-add-pvz:hover{background:#009930}.btn-add-pvz:disabled{opacity:.6;cursor:not-allowed}.btn-add-pvz .btn-load.hidden,.btn-add-door .btn-load.hidden{display:none}
@keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* Форма адреса */
.address-form{background:var(--white);border-radius:var(--radius-xl);padding:20px;margin-bottom:24px}
.form-group{margin-bottom:16px}.form-group:last-child{margin-bottom:0}
.form-label{display:block;font-size:12px;font-weight:600;color:var(--gray-500);margin-bottom:6px;text-transform:uppercase}
.form-input,.form-textarea{width:100%;padding:14px 16px;background:var(--gray-100);border:none;border-radius:var(--radius-md);font-size:15px;font-family:inherit;outline:none;transition:all .2s}
.form-input:focus,.form-textarea:focus{background:var(--white);box-shadow:0 0 0 2px var(--primary)}
.form-input.filled{background:rgba(52,199,89,.1);transition:background .3s}
.form-textarea{min-height:80px;resize:vertical}
.form-row{display:flex;gap:12px}.form-row .form-group{flex:1}
.btn-add-door{width:100%;padding:16px;background:var(--blue);color:#fff;border:none;border-radius:100px;font-size:15px;font-weight:600;cursor:pointer;margin-top:8px;transition:all .2s}
.btn-add-door:hover{background:#313131}.btn-add-door:disabled{opacity:.6;cursor:not-allowed}

/* Секция адресов */
.addresses-section{margin-bottom:24px}.section-title{font-size:16px;font-weight:700;text-transform:uppercase;margin:0 0 12px 4px;letter-spacing:.02em}
.addresses-list{background:var(--white);border-radius:var(--radius-xl);overflow:hidden}
.address-card{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--gray-100);transition:background .15s;animation:fadeIn .3s ease}
.address-card:last-child{border-bottom:none}.address-card:hover{background:var(--gray-100)}.address-card.is-default{background:rgba(52,199,89,.06)}
.address-card.new-added{animation:highlightNew .6s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
@keyframes highlightNew{0%,100%{background:rgba(52,199,89,.06)}50%{background:rgba(52,199,89,.2)}}
.address-radio{position:relative;flex-shrink:0}.address-radio-input{position:absolute;opacity:0;cursor:pointer;width:24px;height:24px}
.address-radio-mark{display:block;width:22px;height:22px;border:2px solid var(--gray-400);border-radius:50%;position:relative;cursor:pointer}
.address-radio-mark::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(0);width:12px;height:12px;background:var(--success);border-radius:50%;transition:transform .2s}
.address-radio-input:checked+.address-radio-mark{border-color:var(--success)}.address-radio-input:checked+.address-radio-mark::after{transform:translate(-50%,-50%) scale(1)}
.address-content{flex:1;min-width:0;cursor:pointer}.address-name{font-size:12px;color:var(--gray-400);margin-bottom:4px;text-transform:uppercase}
.address-text{font-size:15px;font-weight:500;color:var(--black)}
.address-delete{width:40px;height:40px;background:0;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--gray-400);flex-shrink:0}
.address-delete:hover{background:rgba(255,59,48,.1);color:var(--danger)}
.empty-addresses{text-align:center;padding:40px 24px}.empty-icon{font-size:48px;margin-bottom:12px}
.empty-text{font-size:16px;font-weight:500;margin:0 0 4px}.empty-hint{font-size:14px;color:var(--gray-400);margin:0}
.cdek-toast{position:fixed;bottom:30px;left:50%;transform:translateX(-50%);padding:14px 24px;border-radius:var(--radius-md);color:#fff;font-size:14px;font-weight:500;z-index:10000;animation:toastIn .3s}
.cdek-toast--success{background:var(--success)}.cdek-toast--error{background:var(--danger)}.cdek-toast--info{background:var(--primary)}
@keyframes toastIn{from{opacity:0;transform:translateX(-50%) translateY(20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
.confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px}
.confirm-dialog{background:var(--white);border-radius:var(--radius-xl);padding:28px 24px 24px;max-width:320px;width:100%;text-align:center}
.confirm-title{font-size:20px;font-weight:700;margin:0 0 8px}.confirm-text{font-size:15px;color:var(--gray-500);margin:0 0 24px}
.confirm-actions{display:flex;gap:12px}.confirm-btn{flex:1;padding:14px;border:none;border-radius:100px;font-size:16px;font-weight:600;cursor:pointer}
.confirm-btn--cancel{background:var(--gray-100);color:var(--black)}.confirm-btn--delete{background:var(--danger);color:#fff}
</style>

<script>
jQuery(function($){
    var cfg={ajaxUrl:'<?php echo admin_url("admin-ajax.php"); ?>',nonce:'<?php echo wp_create_nonce("cdek_pro_nonce"); ?>',defCity:'<?php echo esc_js($default_city); ?>',defCode:<?php echo intval($default_city_code); ?>,hasMap:typeof ymaps!=='undefined'};
    var st={pvzMap:null,doorMap:null,cityCode:cfg.defCode,cityName:cfg.defCity,offices:[],markers:[],selOffice:null,savedPvz:[],doorMarker:null,selectedCoords:null};
    
    // Собираем сохранённые ПВЗ
    $('#pvz-list .address-card').each(function(){var c=$(this).data('pvz-code');if(c)st.savedPvz.push(String(c));});
    
    // === ТАБЫ ===
    $('.tab').on('click',function(){
        var tab=$(this).data('tab');
        $('.tab').removeClass('active');$(this).addClass('active');
        $('.tab-content').removeClass('active');$('#tab-'+tab).addClass('active');
        if(tab==='door'&&!st.doorMap&&cfg.hasMap)initDoorMap();
    });
    
    // === ИНИЦИАЛИЗАЦИЯ ===
    function init(){initPvzMap();bindEvents();if(cfg.defCode)loadOffices(cfg.defCode);}
    
    // === КАРТА ПВЗ ===
    function initPvzMap(){
        if(!cfg.hasMap){$('#map-loading').html('<div style="font-size:40px">🗺️</div><p>Карта недоступна</p>');return;}
        ymaps.ready(function(){
            st.pvzMap=new ymaps.Map('pvz-map',{center:[55.76,37.64],zoom:10,controls:['zoomControl','geolocationControl']});
            $('#map-loading').addClass('hidden');
        });
    }
    
    // === КАРТА КУРЬЕРА ===
    function initDoorMap(){
        if(!cfg.hasMap){$('#door-map-loading').html('<div style="font-size:40px">🗺️</div><p>Карта недоступна</p>');return;}
        ymaps.ready(function(){
            st.doorMap=new ymaps.Map('door-map',{center:[55.76,37.64],zoom:12,controls:['zoomControl','geolocationControl']});
            $('#door-map-loading').addClass('hidden');
            
            // Клик по карте - установка адреса
            st.doorMap.events.add('click',function(e){
                var coords=e.get('coords');
                placeMarker(coords);
                geocodeCoords(coords);
            });
        });
    }
    
    // === РАЗМЕЩЕНИЕ МАРКЕРА ===
    function placeMarker(coords){
        st.selectedCoords=coords;
        if(st.doorMarker){st.doorMap.geoObjects.remove(st.doorMarker);}
        st.doorMarker=new ymaps.Placemark(coords,{},{preset:'islands#blueCircleDotIcon',draggable:true});
        st.doorMarker.events.add('dragend',function(){
            var newCoords=st.doorMarker.geometry.getCoordinates();
            st.selectedCoords=newCoords;
            geocodeCoords(newCoords);
        });
        st.doorMap.geoObjects.add(st.doorMarker);
        st.doorMap.setCenter(coords,16,{duration:300});
    }
    
    // === ГЕОКОДИРОВАНИЕ КООРДИНАТ ===
    function geocodeCoords(coords){
        ymaps.geocode(coords,{results:1}).then(function(res){
            var obj=res.geoObjects.get(0);
            if(obj){
                var addr=obj.getAddressLine()||'';
                var props=obj.properties.getAll();
                var meta=props.metaDataProperty?.GeocoderMetaData?.Address?.Components||[];
                
                var city='',street='',house='';
                meta.forEach(function(c){
                    if(c.kind==='locality'||c.kind==='city')city=c.name;
                    if(c.kind==='street')street=c.name;
                    if(c.kind==='house')house=c.name;
                });
                
                // Если город не найден, берём из полного адреса
                if(!city){
                    var parts=addr.split(', ');
                    if(parts.length>=2)city=parts[1]||parts[0];
                }
                
                $('#door-city').val(city||cfg.defCity);
                $('#door-street').val(street+(house?', д. '+house:''));
                
                // Подсвечиваем заполненные поля
                $('#door-city, #door-street').addClass('filled');
                setTimeout(function(){$('#door-city, #door-street').removeClass('filled');},1000);
            }
        }).catch(function(err){
            console.error('Geocode error:',err);
            notify('Не удалось определить адрес','error');
        });
    }
    
    // === СОБЫТИЯ ===
    function bindEvents(){
        // Поиск города
        var timer;
        $('#city-search').on('input',function(){var q=$(this).val().trim();clearTimeout(timer);if(q.length>=2)timer=setTimeout(function(){searchCities(q);},300);else $('#city-suggestions').removeClass('active').empty();});
        $(document).on('click',function(e){if(!$(e.target).closest('.search-input-wrapper').length)$('#city-suggestions').removeClass('active');});
        $(document).on('click','.city-suggestion',function(){var c=$(this).data('code'),n=$(this).data('name');$('#city-search').val(n);$('#city-suggestions').removeClass('active');st.cityCode=c;st.cityName=n;hidePanel();loadOffices(c);});
        
        // Добавление ПВЗ
        $('#btn-add').on('click',function(){if(st.selOffice)addPvz(st.selOffice);});
        
        // Добавление адреса курьера
        $('#btn-add-door').on('click',addDoorAddress);
        
        // Выбор по умолчанию - ПВЗ
        $(document).on('change','#pvz-list .address-radio-input',function(){
            var $card=$(this).closest('.address-card');
            $('#pvz-list .address-card').removeClass('is-default');
            $card.addClass('is-default');
            setDefault($(this).val());
        });
        
        // Выбор по умолчанию - Курьер
        $(document).on('change','#door-list .address-radio-input',function(){
            var $card=$(this).closest('.address-card');
            $('#door-list .address-card').removeClass('is-default');
            $card.addClass('is-default');
            setDefault($(this).val());
        });
        
        $(document).on('click','.address-content',function(){$(this).siblings('.address-radio').find('input').prop('checked',true).trigger('change');});
        
        // Удаление
        $(document).on('click','.address-delete',function(e){
            e.stopPropagation();
            var id=$(this).data('id'),$c=$(this).closest('.address-card'),type=$c.data('type');
            showConfirm('Удалить?','Адрес будет удалён',function(){deleteAddr(id,$c,type);});
        });
    }
    
    // === ПОИСК ГОРОДОВ ===
    function searchCities(q){
        $.post(cfg.ajaxUrl,{action:'cdek_get_cities',nonce:cfg.nonce,query:q},function(r){
            if(r.success&&r.data&&r.data.length){
                var h='';r.data.slice(0,8).forEach(function(c){h+='<div class="city-suggestion" data-code="'+c.code+'" data-name="'+esc(c.city)+'"><div class="city-suggestion-name">'+esc(c.city)+'</div>'+(c.region?'<div class="city-suggestion-region">'+esc(c.region)+'</div>':'')+'</div>';});
                $('#city-suggestions').html(h).addClass('active');
            }
        });
    }
    
    // === ЗАГРУЗКА ПВЗ ===
    function loadOffices(code){
        $('#map-loading').removeClass('hidden').html('<div class="spinner"></div><span>Загрузка ПВЗ...</span>');
        $.post(cfg.ajaxUrl,{action:'cdek_get_offices',nonce:cfg.nonce,city_code:code},function(r){
            $('#map-loading').addClass('hidden');
            if(r.success&&r.data){st.offices=r.data;showOnMap(r.data);}else notify('Ошибка загрузки','error');
        }).fail(function(){$('#map-loading').addClass('hidden');notify('Ошибка сети','error');});
    }
    
    // === МАРКЕРЫ НА КАРТЕ ===
    function showOnMap(offices){
        if(!st.pvzMap)return;
        st.markers.forEach(function(m){st.pvzMap.geoObjects.remove(m);});st.markers=[];
        if(!offices||!offices.length){notify('Нет ПВЗ в городе','info');return;}
        var bounds=[];
        offices.forEach(function(o){
            if(o.location?.latitude&&o.location?.longitude){
                var coords=[parseFloat(o.location.latitude),parseFloat(o.location.longitude)];bounds.push(coords);
                var added=st.savedPvz.indexOf(String(o.code))!==-1;
                var m=new ymaps.Placemark(coords,{hintContent:o.name||'ПВЗ'},{preset:added?'islands#greenCircleDotIcon':'islands#darkGreenDotIcon'});
                m.events.add('click',function(){selectOffice(o);});
                st.pvzMap.geoObjects.add(m);st.markers.push(m);
            }
        });
        if(bounds.length)st.pvzMap.setBounds(ymaps.util.bounds.fromPoints(bounds),{checkZoomRange:true,zoomMargin:50});
    }
    
    // === ВЫБОР ПВЗ ===
    function selectOffice(o){
        st.selOffice=o;
        var added=st.savedPvz.indexOf(String(o.code))!==-1;
        $('#sel-name').text(o.name||'ПВЗ СДЭК');
        $('#sel-addr').text(o.location?.address_full||o.location?.address||'');
        $('#sel-time').text(o.work_time?'🕐 '+o.work_time:'');
        var $b=$('#btn-add');
        if(added){$b.prop('disabled',true).find('.btn-text').text('Добавлен');}
        else{$b.prop('disabled',false).find('.btn-text').text('Добавить');}
        $('#selected-panel').removeClass('hidden');
        if(o.location?.latitude)st.pvzMap.setCenter([parseFloat(o.location.latitude),parseFloat(o.location.longitude)],15,{duration:300});
    }
    
    function hidePanel(){$('#selected-panel').addClass('hidden');st.selOffice=null;}
    
    // === ДОБАВЛЕНИЕ ПВЗ ===
    function addPvz(o){
        if(st.savedPvz.indexOf(String(o.code))!==-1){notify('Уже добавлен','info');return;}
        var $b=$('#btn-add').prop('disabled',true);$b.find('.btn-text').addClass('hidden');$b.find('.btn-load').removeClass('hidden');
        $.post(cfg.ajaxUrl,{action:'cdek_save_address',nonce:cfg.nonce,type:'pvz',city:o.location?.city||st.cityName,city_code:st.cityCode,pvz_code:o.code,pvz_name:o.name||'ПВЗ',pvz_address:o.location?.address_full||'',is_default:true},function(r){
            $b.find('.btn-text').removeClass('hidden');$b.find('.btn-load').addClass('hidden');
            if(r.success){
                notify('ПВЗ добавлен','success');st.savedPvz.push(String(o.code));$b.find('.btn-text').text('Добавлен');
                if(r.data.addresses)updatePvzList(r.data.addresses,o.code);
                showOnMap(st.offices);
            }else{$b.prop('disabled',false);notify(r.data?.message||'Ошибка','error');}
        }).fail(function(){$b.prop('disabled',false);$b.find('.btn-text').removeClass('hidden');$b.find('.btn-load').addClass('hidden');notify('Ошибка сети','error');});
    }
    
    // === ДОБАВЛЕНИЕ АДРЕСА КУРЬЕРА ===
    function addDoorAddress(){
        var city=$('#door-city').val().trim();
        var street=$('#door-street').val().trim();
        var apt=$('#door-apt').val().trim();
        var entrance=$('#door-entrance').val().trim();
        var floor=$('#door-floor').val().trim();
        
        if(!city||!street){notify('Заполните город и улицу','error');return;}
        
        var $b=$('#btn-add-door').prop('disabled',true);$b.find('.btn-text').addClass('hidden');$b.find('.btn-load').removeClass('hidden');
        
        $.post(cfg.ajaxUrl,{
            action:'cdek_save_address',nonce:cfg.nonce,type:'door',
            city:city,city_code:st.cityCode,address:street,
            apartment:apt,entrance:entrance,floor:floor,
            is_default:true
        },function(r){
            $b.find('.btn-text').removeClass('hidden');$b.find('.btn-load').addClass('hidden');$b.prop('disabled',false);
            if(r.success){
                notify('Адрес добавлен','success');
                // Очищаем форму
                $('#door-street,#door-apt,#door-entrance,#door-floor').val('');
                if(st.doorMarker){st.doorMap.geoObjects.remove(st.doorMarker);st.doorMarker=null;}
                if(r.data.addresses)updateDoorList(r.data.addresses);
            }else{notify(r.data?.message||'Ошибка','error');}
        }).fail(function(){$b.prop('disabled',false);$b.find('.btn-text').removeClass('hidden');$b.find('.btn-load').addClass('hidden');notify('Ошибка сети','error');});
    }
    
    // === ОБНОВЛЕНИЕ СПИСКА ПВЗ ===
    function updatePvzList(addrs,newCode){
        var pvz=addrs.filter(function(a){return a.pvz_code;});
        st.savedPvz=pvz.map(function(a){return String(a.pvz_code);});
        if(!pvz.length){$('#pvz-list').html('<div class="empty-addresses"><div class="empty-icon">📍</div><p class="empty-text">Нет сохранённых ПВЗ</p><p class="empty-hint">Выберите пункт на карте</p></div>');return;}
        var h='';
        pvz.forEach(function(a){
            var def=newCode?String(a.pvz_code)===String(newCode):!!a.is_default;
            var isNew=newCode&&String(a.pvz_code)===String(newCode);
            var parts=(a.pvz_address||'').split(', '),disp=parts.length>=5?parts.slice(4).join(', '):(a.pvz_address||'');
            h+='<div class="address-card '+(def?'is-default':'')+(isNew?' new-added':'')+'" data-id="'+a.id+'" data-pvz-code="'+(a.pvz_code||'')+'" data-type="pvz">';
            h+='<div class="address-radio"><input type="radio" name="default_address" value="'+a.id+'" '+(def?'checked':'')+' class="address-radio-input"><span class="address-radio-mark"></span></div>';
            h+='<div class="address-content"><div class="address-name">'+esc(a.pvz_name||'ПВЗ')+'</div><div class="address-text">'+esc(disp)+'</div></div>';
            h+='<button class="address-delete" data-id="'+a.id+'"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>';
        });
        $('#pvz-list').html(h);
        if(newCode){var $n=$('#pvz-list .address-card[data-pvz-code="'+newCode+'"]');if($n.length)$('html,body').animate({scrollTop:$n.offset().top-100},300);}
    }
    
    // === ОБНОВЛЕНИЕ СПИСКА КУРЬЕРСКИХ АДРЕСОВ ===
    function updateDoorList(addrs){
        var door=addrs.filter(function(a){return !a.pvz_code&&a.address;});
        if(!door.length){$('#door-list').html('<div class="empty-addresses"><div class="empty-icon">🏠</div><p class="empty-text">Нет сохранённых адресов</p><p class="empty-hint">Добавьте адрес для курьера</p></div>');return;}
        var h='';
        door.forEach(function(a,i){
            var def=i===0; // Новый всегда первый и по умолчанию
            var full=((a.city||'')+', '+(a.address||'')).replace(/^, /,'');
            if(a.apartment)full+=', кв. '+a.apartment;
            h+='<div class="address-card '+(def?'is-default new-added':'')+'" data-id="'+a.id+'" data-type="door">';
            h+='<div class="address-radio"><input type="radio" name="default_door" value="'+a.id+'" '+(def?'checked':'')+' class="address-radio-input"><span class="address-radio-mark"></span></div>';
            h+='<div class="address-content"><div class="address-name">Курьерская доставка</div><div class="address-text">'+esc(full)+'</div></div>';
            h+='<button class="address-delete" data-id="'+a.id+'"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6 6L14 14M14 6L6 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div>';
        });
        $('#door-list').html(h);
        $('html,body').animate({scrollTop:$('#door-list').offset().top-100},300);
    }
    
    // === УСТАНОВИТЬ ПО УМОЛЧАНИЮ ===
    function setDefault(id){
        $.post(cfg.ajaxUrl,{action:'cdek_set_default_address',nonce:cfg.nonce,address_id:id},function(r){
            if(!r.success)notify(r.data?.message||'Ошибка','error');
        });
    }
    
    // === УДАЛЕНИЕ ===
    function deleteAddr(id,$c,type){
        var code=$c.data('pvz-code');
        $.post(cfg.ajaxUrl,{action:'cdek_delete_address',nonce:cfg.nonce,address_id:id},function(r){
            if(r.success){
                notify('Удалён','success');
                if(type==='pvz'&&code){var i=st.savedPvz.indexOf(String(code));if(i!==-1)st.savedPvz.splice(i,1);}
                var $list=type==='pvz'?$('#pvz-list'):$('#door-list');
                var radioName=type==='pvz'?'default_address':'default_door';
                $c.slideUp(300,function(){
                    $(this).remove();
                    if(!$list.find('.address-card').length){
                        var empty=type==='pvz'?'<div class="empty-addresses"><div class="empty-icon">📍</div><p class="empty-text">Нет сохранённых ПВЗ</p><p class="empty-hint">Выберите пункт на карте</p></div>':'<div class="empty-addresses"><div class="empty-icon">🏠</div><p class="empty-text">Нет сохранённых адресов</p><p class="empty-hint">Добавьте адрес для курьера</p></div>';
                        $list.html(empty);
                    }else if(!$list.find('input[name="'+radioName+'"]:checked').length){
                        var $first=$list.find('.address-card').first();
                        $first.find('input').prop('checked',true);$first.addClass('is-default');
                        setDefault($first.find('input').val());
                    }
                    if(type==='pvz')showOnMap(st.offices);
                    if(st.selOffice&&String(st.selOffice.code)===String(code))$('#btn-add').prop('disabled',false).find('.btn-text').text('Добавить');
                });
            }else notify(r.data?.message||'Ошибка','error');
        }).fail(function(){notify('Ошибка сети','error');});
    }
    
    // === УТИЛИТЫ ===
    function notify(m,t){$('.cdek-toast').remove();$('<div class="cdek-toast cdek-toast--'+(t||'success')+'">'+esc(m)+'</div>').appendTo('body');setTimeout(function(){$('.cdek-toast').fadeOut(300,function(){$(this).remove();});},3000);}
    function showConfirm(t,x,cb){var $o=$('<div class="confirm-overlay"><div class="confirm-dialog"><div class="confirm-title">'+esc(t)+'</div><div class="confirm-text">'+esc(x)+'</div><div class="confirm-actions"><button class="confirm-btn confirm-btn--cancel">Отмена</button><button class="confirm-btn confirm-btn--delete">Удалить</button></div></div></div>');$('body').append($o);$o.find('.confirm-btn--cancel').on('click',function(){$o.remove();});$o.find('.confirm-btn--delete').on('click',function(){$o.remove();cb();});$o.on('click',function(e){if($(e.target).is('.confirm-overlay'))$o.remove();});}
    function esc(t){if(!t)return '';var d=document.createElement('div');d.textContent=t;return d.innerHTML;}
    
    init();
});
</script>