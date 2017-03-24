<?php
function remove_head_admin()
{
    global $wpdb;

    $ID = $wpdb->get_results("SELECT * FROM wpcy_1c_stat order by id desc");
    $col_all = $ID[0]->col_all;
    $col_edit = $ID[0]->col_edit;
    $sek = $ID[0]->sek;
    $date = $ID[0]->date;

    $ID = $wpdb->get_results("SELECT * FROM wpcy_1c_stat where `col_edit` not like '0' order by id desc");
    $col_all1 = $ID[0]->col_all;
    $col_edit1 = $ID[0]->col_edit;
    $sek1 = $ID[0]->sek;
    $date1 = $ID[0]->date;

    if (isset($_GET["on"])) {
        $sql = $wpdb->prepare("INSERT INTO `wpcy_1c_stat` (date, sek, col_all, col_edit, data) VALUES (now(), %d, %d, %d, %s)", 0, 0, 0, "Обмен ON");
        $wpdb->query($sql);
    }

    if (isset($_GET["off"])) {
        $sql = $wpdb->prepare("INSERT INTO `wpcy_1c_stat` (date, sek, col_all, col_edit, data) VALUES (now(), %d, %d, %d, %s)", 0, 0, 0, "Обмен OFF");
        $wpdb->query($sql);
    }
    $status = $wpdb->get_results("SELECT data FROM wpcy_1c_stat where `col_edit` like '0' AND `col_all` like '0' AND `sek` like '0'  order by id desc");
    $stat = $status[0]->data;
    $style_off = "";
    $style_on = "";
    if ($stat == 'Обмен OFF') $style_off = 'background-color: #b8cde3;'; else $style_on = 'background-color: #b8cde3;';
    echo '
   <div class="updated vc_license-activation-notice">
   <table style="
    width: 60%;
"> 
   <tr>
   <td>
<p style="
    font-size: 16px;
    ">Последняя <b>синхронизация</b> - <b style="
    color: #46b450;
">' . date("d.m.Y H:i:s", strtotime($date)) . '</b><br> 
Распознано - <b style="color: #00a2e3;">' . $col_all . '</b> шт.<br>
Изменено - <b style="color: #00a2e3;">' . $col_edit . '</b> шт.<br>
Выполнено за - <b style="color: #00a2e3;">' . $sek . '</b> сек.<br>
</p>
</td>
<td>
<p style="
    font-size: 16px;
    ">Последние <b>изменение</b> - <b style="
    color: #46b450;
">' . date("d.m.Y H:i:s", strtotime($date1)) . '</b><br> 
Распознано - <b style="color: #00a2e3;">' . $col_all1 . '</b> шт.<br>
Изменено - <b style="color: #00a2e3;">' . $col_edit1 . '</b> шт.<br>
Выполнено за - <b style="color: #00a2e3;">' . $sek1 . '</b> сек.<br>
</p>
</td>

<td>
<p><a href="/wp-admin/?on=1" class="button" style="
    width: 80px;
    text-align: -webkit-center;' . $style_on . '
">on</a></p> 
<p><a href="/wp-admin/?off=1" class="button" style="
    width: 80px;
    text-align: -webkit-center;' . $style_off . '
">off</a></p>

</td>

<td>
<p><a href="/wp-content/wp-1c/phpinfo.php" target="_blank"><img class="irc_mi" src="https://static.remoteshaman.com:8080/images/logo/log-logo.jpg" alt="лог"  width="70"></a></p>
</td>
</tr>
</table> 
</div>';
}

//панель обмена с 1С//

add_action('admin_head', 'remove_head_admin');
function show_profile_fields($user)
{ ?>
    <h3>Дополнительная информация</h3>
    <!-- добавляется ещё один блок в профиле, в админке -->
    <table class="form-table">
        <!-- добавляю поле телефон -->
        <tr>
            <th><label for="phone">Телефон</label></th>
            <td><input type="text" name="phone" id="phone"
                       value="<?php echo esc_attr(get_the_author_meta('phone', $user->ID)); ?>"
                       class="regular-text"/><br/></td>
        </tr>
        <!-- добавля поле соглашение -->
        <th><label for="soglashenie">Соглашение</label></th>
        <td><?php $soglashenie = get_user_meta($user->ID, 'soglashenie', true); ?>
            <ul>
                <li><input value="Соглашение" name="soglashenie[1]"
                           <?php echo ($soglashenie[1] == 'Соглашение') ? ' checked="checked"' : '' ?>type="checkbox"/>
                    Я согласен на получение акционных предложений и рассылок
                </li>
            </ul>
        </td>
        <!-- добавляем поле соглашение2 -->
        <th><label for="soglashenie2">Обработка персональных данных</label></th>
        <td><?php $soglashenie2 = get_user_meta($user->ID, 'soglashenie2', true); ?>
            <ul>
                <li><input value="Обработки персональных данных" name="soglashenie2[1]"
                           <?php echo ($soglashenie2[1] == 'Обработка персональных данных') ? ' checked="checked"' : '' ?>type="checkbox"/>
                    Я согласен с условиями использования и обработки моих персональных данных
                </li>
            </ul>
        </td>
        </tr>
        <!-- закрываю теги и применяем функцию -->
    </table>
<?php }

add_action('show_user_profile', 'show_profile_fields');
add_action('edit_user_profile', 'show_profile_fields');


// Сохраняю результаты в админке

function save_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id))
        return false;
    update_user_meta($user_id, 'phone', $_POST['phone']);
    update_user_meta($user_id, 'soglashenie', $_POST['soglashenie']);
    update_user_meta($user_id, 'soglashenie2', $_POST['soglashenie2']);
}

add_action('personal_options_update', 'save_profile_fields');
add_action('edit_user_profile_update', 'save_profile_fields');
//////////////////////////////////////////////////////////////////

////////Добавляем поля в форму регистрации
add_action('register_form', 'show_fields');
add_action('register_post', 'check_fields', 10, 3);
add_action('user_register', 'register_fields');

function show_fields()
{
    /* добавляею поля "Телефон" и "Соглашение" в форму регистрации в WordPress */
    ?>
    <p>
    <div class="Tele">
        <label>Телефон<br/>
            <input id="phone" class="input" type="text" value="<?php echo $_POST['phone']; ?>" name="phone"/></label>
    </div>
    </p>
    <p>


    <div class="Soglasie"><input value="Соглашение" name="soglashenie[1]"
                                 <?php echo ($soglashenie[1] == 'Соглашение') ? ' checked="checked"' : '' ?>type="checkbox"
                                 checked/> <label> Я даю согласие на получение акционных предложений и рассылок</label>
    </div>
    </p>
    <div class="Soglasie2"><input value="Обработка персональных данных" name="soglashenie2[1]"
                                  <?php echo ($soglashenie2[1] == 'Обработка персональных данных') ? ' checked="checked"' : '' ?>type="checkbox"
                                  checked/> <label> Я согласен с <a href="https://refinish.ua/usloviya-i-soglashenie/"
                                                                    target="_blank">условиями использования</a> и
            обработки моих персональных данных</label></div>
    </p>
<?php }

/* Валидация соглашения */
function so_33122634_validation_registration($errors, $username, $password, $email)
{
    if (empty($_POST['soglashenie2'])) {
        throw new Exception(__('Вам необходимо принять условия использования нашего магазина.', 'text-domain'));
    }
    return $errors;
}

add_action('woocommerce_process_registration_errors', 'so_33122634_validation_registration', 10, 4);

function check_fields($login, $email, $errors)
{
    /*
     * Функция проверки полей смотрит, чтобы они не оставались пустыми,
     */
    global $phone, $soglashenie;
    if ($_POST['phone'] == '') {
        $errors->add('empty_realname', "ОШИБКА: Не указан город!");
    } else {
        $phone = $_POST['phone'];
    }
    if ($_POST['soglashenie'] == '') {
        $errors->add('empty_realname', "ОШИБКА: Не указана страна!");
    } else {
        $soglashenie = $_POST['soglashenie'];
    }
}

function register_fields($user_id, $password = "", $meta = array())
{
    update_user_meta($user_id, 'soglashenie', $_POST['soglashenie']);
    update_user_meta($user_id, 'phone', $_POST['phone']);
}

// Ссылка на сайт в логин входа в админку
/*add_filter( 'login_headerurl', 'custom_login_header_url' );
function custom_login_header_url($url) {

return 'https://refinish.ua/';
}*/

// Стиль входа в админку
/*function custom_login_css() {
echo '<link rel="stylesheet" type="text/css" href="'.get_stylesheet_directory_uri().'/login/login-styles.css" />';
}

add_action('login_head', 'custom_login_css');*/


define('SNSSARA_THEME_DIR', get_template_directory());
define('SNSSARA_THEME_URI', get_template_directory_uri());

// Require framework
require_once(SNSSARA_THEME_DIR . '/framework/init.php');
/**
 *  Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
 **/
add_action('vc_before_init', 'snssara_vcSetAsTheme');
function snssara_vcSetAsTheme()
{
    vc_set_as_theme(true);
}

// Initialising Visual shortcode editor
if (class_exists('WPBakeryVisualComposerAbstract')) {
    function requireVcExtend()
    {
        include_once(get_template_directory() . '/vc_extend/extend-vc.php');
    }

    add_action('init', 'requireVcExtend', 2);
}

/**
 *  Width of content, it's max width of content without sidebar.
 **/
if (!isset($content_width)) {
    $content_width = 660;
}

/**
 *  Set base function for theme.
 **/
if (!function_exists('snssara_setup')) {
    function snssara_setup()
    {
        global $snssara_opt;
        // Load default theme textdomain.
        load_theme_textdomain('snssara', SNSSARA_THEME_DIR . '/languages');
        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');
        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support('post-thumbnails');
        // Add title-tag, it auto title of head
        add_theme_support('title-tag');
        // Enable support for Post Formats.
        add_theme_support('post-formats',
            array(
                'video', 'audio', 'quote', 'link', 'gallery'
            )
        );

        // Register images size
        add_image_size('snssara_megamenu_thumb', 250, 150, true);
        add_image_size('snssara_post_thumbnail', 830, 460, true); // post thumbnail default
        add_image_size('snssara_blog_layout2_thumbnail_size', 790, 486, true); // blog layout 2 345x243
        add_image_size('snssara_blog_masonry_thumbnail_size', 270, 192, true);
        add_image_size('snssara_related_posts', 250, 177, true);
        add_image_size('snssara_latest_posts_layout1', 520, 360, true);
        // add_image_size('snssara_latest_posts_layout2', 174, 120, true);
        add_image_size('snssara_latest_posts_layout2', 348, 240, true);
        add_image_size('snssara_latest_posts_layout3', 329, 190, true);
        add_image_size('snssara_latest_posts_layout4', 270, 180, true);
        add_image_size('snssara_latest_posts_widget_list', 85, 60, true);
        add_image_size('snssara_content_search_thumbnail_size', 500, 243, false);
        add_image_size('snssara_latest_posts_widget_accordion', 228, 134, true);
        add_image_size('snssara_testimonial_avatar', 102, 102, true);
        add_image_size('snssara_products_agency_thumb', 259, 180, true);

        add_image_size('snssara_product_tabs_featured', 520, 580, true);
        add_image_size('snssara_product_list_thumb', 200, 200, true);

        //Setup the WordPress core custom background & custom header feature.
        $default_background = array(
            'default-color' => '#FFF',
        );
        add_theme_support('custom-background', $default_background);
        $default_header = array();
        add_theme_support('custom-header', $default_header);
        // Register navigations
        register_nav_menus(array(
            'top_navigation' => esc_html__('Top navigation', 'snssara'),
            'main_navigation' => esc_html__('Main navigation', 'snssara'),
            'vertical_navigation' => esc_html__('Vertical navigation', 'snssara'),
            'footer_navigation' => esc_html__('Footer navigation', 'snssara'),
        ));
        // Editor style
        add_editor_style('assets/css/editor-style.css');
    }

    add_action('init', 'snssara_setup'); // or add_action( 'after_setup_theme', 'snssara_setup' );
}
add_action('after_setup_theme', 'snssara_woocommerce_support');
function snssara_woocommerce_support()
{
    add_theme_support('woocommerce');
}

add_filter('body_class', 'snssara_bodyclass');
function snssara_bodyclass($classes)
{
    // Check header layout
    $snssara_headerLayout = snssara_get_option('header_layout', 'layout_1');
    // Get page config
    $page_config = false;
    if (snssara_metabox('snssara_header_layout') != '') {
        $snssara_headerLayout = snssara_metabox('snssara_header_layout');
        $page_config = true;
    }
    $classes[] = 'sns_header_' . $snssara_headerLayout;
    $classes[] = 'is_sns_header_' . $snssara_headerLayout;


    if (snssara_get_option('use_boxedlayout', 0) == 1) {
        $classes[] = 'boxed-layout';
    }
    if (snssara_get_option('advance_tooltip', 1) == 1) {
        $classes[] = 'use-tooltip';
    }
    if (snssara_get_option('use_stickmenu') == 1) {
        $classes[] = 'use_stickmenu';
    }
    if (snssara_get_option('woo_uselazyload') == 1) {
        $classes[] = 'use_lazyload';
    }

    $woo_grid_spacing = snssara_get_option('woo_grid_spacing', '');
    if (function_exists('is_product_category') && is_product_category()) {
        $cate = get_queried_object();
        $woo_grid_spacing = get_woocommerce_term_meta($cate->term_id, 'snssara_product_cat_grid_spacing');
    }
    if ($woo_grid_spacing != '') {
        $classes[] = 'woo_grid_spacing';
    }

    return $classes;
}

function snssara_widgetlocations()
{
    // Register widgetized locations
    if (function_exists('register_sidebar')) {
        register_sidebar(array(
            'name' => esc_html__('Main Area', 'snssara'),
            'id' => 'widget-area',
            'description' => esc_html__('These are widgets for the Widget Area.', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget %2$s">',
            'after_widget' => '</aside>',
            'before_title' => '<h3 class="widget-title"><span>',
            'after_title' => '</span></h3>',
        ));

        register_sidebar(array(
            'name' => esc_html__('Menu Sidebar #1', 'snssara'),
            'id' => 'menu_sidebar_1',
            'description' => esc_html__('These are widgets for Mega Menu Columns style. This sidebar displayed in the top of column.', 'snssara'),
            'before_widget' => '<div class="sidebar-menu-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 style="display:none;">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Menu Sidebar #2', 'snssara'),
            'id' => 'menu_sidebar_2',
            'description' => esc_html__('These are widgets for Mega Menu Columns style. This sidebar displayed in the right of column.', 'snssara'),
            'before_widget' => '<div class="sidebar-menu-widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 style="display:none;">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #1 Top', 'snssara'),
            'id' => 'footer-1-top',
            'description' => esc_html__('These are widgets for the top of Footer (apply for Footer Layout 1).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-md-4 col-sm-6">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #1 Middle', 'snssara'),
            'id' => 'footer-1-middle',
            'description' => esc_html__('These are widgets for the Footer Middle (apply for Footer Layout 1).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-md-3 col-sm-6">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #2 Top', 'snssara'),
            'id' => 'footer-2-top',
            'description' => esc_html__('These are widgets for the top of Footer (apply for Footer Layout 2).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-sm-6">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #2 Middle', 'snssara'),
            'id' => 'footer-2-middle',
            'description' => esc_html__('These are widgets for the Footer Middle (apply for Footer Layout 2).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-md-12 col-sm-12">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #3 Top', 'snssara'),
            'id' => 'footer-3-top',
            'description' => esc_html__('These are widgets for the top of Footer (apply for Footer Layout 2).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-sm-6">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(array(
            'name' => esc_html__('Footer #3 Middle', 'snssara'),
            'id' => 'footer-3-middle',
            'description' => esc_html__('These are widgets for the Footer Middle (apply for Footer Layout 2).', 'snssara'),
            'before_widget' => '<aside id="%1$s" class="widget widget-footer %2$s col-md-12 col-sm-12">',
            'after_widget' => '</aside>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ));

        register_sidebar(
            array(
                'name' => esc_html__('Right Sidebar', 'snssara'),
                'id' => 'right-sidebar',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            ));

        register_sidebar(
            array(
                'name' => esc_html__('Left Sidebar', 'snssara'),
                'id' => 'left-sidebar',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            ));

        register_sidebar(
            array(
                'name' => esc_html__('Woo Sidebar', 'snssara'),
                'id' => 'woo-sidebar',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            ));
        register_sidebar(
            array(
                'name' => esc_html__('Woo2 Sidebar', 'snssara'),
                'id' => 'woo2-sidebar',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            ));

        register_sidebar(
            array(
                'name' => esc_html__('Product Sidebar', 'snssara'),
                'id' => 'product-sidebar',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h3 class="widget-title"><span>',
                'after_title' => '</span></h3>',
            ));
    }
}

add_action('widgets_init', 'snssara_widgetlocations');

/*
 *  Add class css custom widget
 */
function snssara_class_sidebar_params($params)
{
    $sidebar_id = $params[0]['id'];
    // Custom to add class for each widget
    if ($sidebar_id == 'footer-2-top' || $sidebar_id == 'footer-3-top') {

        global $sns_widget_num; // Global a counter array
        $this_id = $params[0]['id']; // Get the id for the current sidebar we're processing
        $arr_registered_widgets = wp_get_sidebars_widgets(); // Get an array of ALL registered widgets

        if (!$sns_widget_num) {// If the counter array doesn't exist, create it
            $sns_widget_num = array();
        }

        if (!isset($arr_registered_widgets[$this_id]) || !is_array($arr_registered_widgets[$this_id])) { // Check if the current sidebar has no widgets
            return $params; // No widgets in this sidebar... bail early.
        }

        if (isset($sns_widget_num[$this_id])) { // See if the counter array has an entry for this sidebar
            $sns_widget_num[$this_id]++;
        } else {
            $sns_widget_num[$this_id] = 1;
        }

        $class = 'class="widget-' . $sns_widget_num[$this_id] . ' ';


        if ($sns_widget_num[$this_id] == 1) { // If this is the first widget
            $class .= 'col-md-3 ';
        } else {
            $class .= 'sns-col-md-94 ';
        }


        $params[0]['before_widget'] = str_replace('class="', $class, $params[0]['before_widget']);

        return $params;
    }

    return $params;
}

add_filter('dynamic_sidebar_params', 'snssara_class_sidebar_params');

/**
 *  Add styles & scripts
 **/
function snssara_scripts()
{
    global $snssara_opt, $snssara_obj;
    $optimize = '.min';
    //$optimize = '';
    // Enqueue style
    $css_file = $snssara_obj->theme_css_file();
    wp_enqueue_style('bootstrap', SNSSARA_THEME_URI . '/assets/css/bootstrap.min.css');
    wp_enqueue_style('animate', SNSSARA_THEME_URI . '/assets/css/animate.css');
    wp_enqueue_style('owlcarousel', SNSSARA_THEME_URI . '/assets/css/owl.carousel.min.css');
    wp_enqueue_style('fonts-awesome', SNSSARA_THEME_URI . '/assets/fonts/awesome/css/font-awesome.min.css');
    wp_enqueue_style('fonts-awesome-animation', SNSSARA_THEME_URI . '/assets/fonts/awesome/css/font-awesome-animation.min.css');
    wp_enqueue_style('select2', SNSSARA_THEME_URI . '/assets/css/select2.css');
    wp_enqueue_style('path-loader', SNSSARA_THEME_URI . '/assets/css/loader-effect.css');
    wp_enqueue_style('snssara-ie9', SNSSARA_THEME_URI . '/assets/css/ie9.css');
    wp_enqueue_style('elegant-font', SNSSARA_THEME_URI . '/assets/fonts/elegant_font/css/elegant-font.min.css');
    wp_enqueue_style('woocommerce', SNSSARA_THEME_URI . '/assets/css/woocommerce.min.css');
    wp_enqueue_style('snssara-theme-style', SNSSARA_THEME_URI . '/assets/css/' . $css_file);

    wp_register_script('owlcarousel', SNSSARA_THEME_URI . '/assets/js/owl.carousel.min.js', array('jquery'), '', true);
    wp_register_script('masonry', SNSSARA_THEME_URI . '/assets/js/masonry.pkgd.min.js', array('jquery'), '', true);
    wp_register_script('imagesloaded', SNSSARA_THEME_URI . '/assets/js/imagesloaded.pkgd.min.js', array('jquery'), '', true);
    wp_register_script('countdown', SNSSARA_THEME_URI . '/assets/countdown/jquery.countdown.min.js', array('jquery'), '2.1.0', true);
    // Enqueue script
    wp_enqueue_script('bootstrap', SNSSARA_THEME_URI . '/assets/js/bootstrap.min.js', array('jquery'), '', true);
    wp_enqueue_script('jqtransform', SNSSARA_THEME_URI . '/assets/js/bootstrap-tabdrop.min.js', array('jquery'), '', true);
    wp_enqueue_script('select2', SNSSARA_THEME_URI . '/assets/js/select2.min.js', array(), '', true);
    wp_register_script('modernizr', SNSSARA_THEME_URI . '/assets/js/modernizr.custom.js', array(), '', true);
    wp_register_script('path-loader', SNSSARA_THEME_URI . '/assets/js/path-loader.js', array(), '', true);
    if (snssara_get_option('woo_uselazyload') == 1) wp_enqueue_script('lazyload', SNSSARA_THEME_URI . '/assets/js/jquery.lazyload.min.js', array(), '', true);
    wp_enqueue_script('snssara-script', SNSSARA_THEME_URI . '/assets/js/sns-script' . $optimize . '.js', array('jquery'), '', true);
    // IE
    wp_enqueue_script('html5shiv', SNSSARA_THEME_URI . '/assets/js/html5shiv.min.js', array('jquery'), '');
    wp_script_add_data('html5shiv', 'conditional', 'lt IE 9');
    wp_enqueue_script('respond', SNSSARA_THEME_URI . '/assets/js/respond.min.js', array('jquery'), '');
    wp_script_add_data('respond', 'conditional', 'lt IE 9');
    // Add style inline with option in admin theme option
    wp_add_inline_style('snssara-theme-style', snssara_cssinline());

    // Code to embed the javascript file that makes the Ajax request
    wp_enqueue_script('ajax-request', SNSSARA_THEME_URI . '/assets/js/ajax.js', array('jquery'));
    // Code to declare the URL to the file handing the AJAX request
    $js_params = array(
        'ajaxurl' => admin_url('admin-ajax.php')
    );
    global $wp_query, $wp;
    $js_params['query_vars'] = $wp_query->query_vars;
    $js_params['current_url'] = esc_url(home_url($wp->request));

    wp_localize_script('ajax-request', 'sns', $js_params);

}

add_action('wp_enqueue_scripts', 'snssara_scripts');

/*
 * Enqueue admin styles and scripts
 */
function snssara_admin_styles_scripts()
{
    wp_enqueue_style('snssara_admin_style', SNSSARA_THEME_URI . '/admin/assets/css/admin-style.css');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('sns-woo-chosen', SNSSARA_THEME_URI . '/admin/assets/css/chosen.min.css');

    wp_enqueue_media();
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('snssara_admin_template_js', SNSSARA_THEME_URI . '/admin/assets/js/admin_template.js', array('jquery', 'wp-color-picker'), false, true);
    //wp_enqueue_script('snssara_admin_woo', SNSSARA_THEME_URI.'/admin/assets/js/admin.js', array( 'jquery' ), false, false);
}

add_action('admin_enqueue_scripts', 'snssara_admin_styles_scripts');

/**
 * CSS inline
 **/
function snssara_cssinline()
{
    global $snssara_opt, $snssara_obj;
    $inline_css = '';
    // Body style
    $bodycss = '';
    $body_font = '';
    if ($snssara_obj->getOption('use_boxedlayout') == 1) {
        if ($snssara_opt['body_bg_type'] == 'pantern') {
            $body_bg_type_pantern = snssara_get_option('body_bg_type_pantern', '');
            $bodycss .= 'background-image: url(' . SNSSARA_THEME_URI . '/assets/img/patterns/' . $body_bg_type_pantern . ');';
        } elseif ($snssara_opt['body_bg_type'] == 'img') {
            $bodycss .= 'background-image: url(' . $snssara_opt['body_bg_type_img']['url'] . ');';
        }
    }
    if (isset($snssara_opt['body_font']) && is_array($snssara_opt['body_font'])) {
        foreach ($snssara_opt['body_font'] as $propety => $value)
            if ($value != 'true' && $value != 'false' && $value != '' && $propety != 'subsets')
                $body_font .= $propety . ':' . $value . ';';

        if ($body_font != '') $bodycss .= $body_font;
    }
    $inline_css .= 'body {' . $bodycss . '}';
    $inline_css .= '.woocommerce ul.products li.product h3 {' . $body_font . '}';
    // Selectors use google font
    if (isset($snssara_opt['secondary_font_target']) && $snssara_opt['secondary_font_target']) {
        if (isset($snssara_opt['secondary_font']) && is_array($snssara_opt['secondary_font'])) {
            $secondary_font = '';
            foreach ($snssara_opt['secondary_font'] as $propety => $value)
                if ($value != 'true' && $value != 'false' && $value != '' && $propety != 'subsets')
                    $secondary_font .= $propety . ':' . $value . ';';

            if ($secondary_font != '') $inline_css .= $snssara_opt['secondary_font_target'] . ' {' . $secondary_font . '}';
        }
    }

    return $inline_css;
}

/*
 * Custom CSS theme
 */
if (!function_exists('snssara_wp_head')) {
    function snssara_wp_head()
    {
        echo '<!-- Custom CSS -->
                <style type="text/css">';
        require get_template_directory() . '/assets/css/custom.css.php';

        echo '</style>
            <!-- end custom css -->';
    }

    add_action('wp_head', 'snssara_wp_head', 1000);
}

/* 
 * Add tpl footer
 */
function snssara_tplfooter()
{
    $output = '';
    ob_start();
    require SNSSARA_THEME_DIR . '/tpl-footer.php';
    $output = ob_get_clean();
    echo $output;
}

add_action('wp_footer', 'snssara_tplfooter');

/* 
 * Custom code
 */
if (!function_exists('snssara_wp_foot')) {
    function snssara_wp_foot()
    {
        // write out custom code
        $output = '';
        ob_start();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                <?php if( snssara_get_option('tag_showmore', '1') == '1' ): ?>
                if (jQuery('.widget_tag_cloud').length > 0) {
                    var $tag_display_first = <?php echo absint(snssara_get_option('tag_display_first', 7)) - 1?>;
                    var $number_tags = jQuery('.widget_tag_cloud .tagcloud a').length;
                    var $_this = jQuery('.widget_tag_cloud .tagcloud');
                    var $view_all_tags = "<?php echo esc_html__('View all tags', 'snssara');?>";
                    var $hide_all_tags = "<?php echo esc_html__('Hide all tags', 'snssara');?>";

                    if ($number_tags > $tag_display_first) {
                        jQuery('.widget_tag_cloud .tagcloud a:gt(' + $tag_display_first + ')').addClass('is_visible').hide();
                        jQuery($_this).append('<div class="view-more-tag"><a href="#" title="">' + $view_all_tags + '</a></div>');

                        jQuery('.widget_tag_cloud .tagcloud .view-more-tag a').click(function () {
                            if (jQuery(this).hasClass('active')) {
                                if (jQuery($_this).find('a').hasClass('is_hidden')) {
                                    $_this.find('.is_hidden').removeClass('is_hidden').addClass('is_visible').stop().slideUp(300);
                                }
                                jQuery(this).removeClass('active');
                                jQuery(this).html($view_all_tags);

                            } else {
                                if (jQuery($_this).find('a').hasClass('is_visible')) {
                                    $_this.find('.is_visible').removeClass('is_visible').addClass('is_hidden').stop().slideDown(400);
                                }
                                jQuery(this).addClass('active');
                                jQuery(this).html($hide_all_tags);
                            }

                            return false;
                        });
                    }
                }
                <?php endif; ?>
                <?php echo snssara_get_option('advance_customjs', '');?>
            });
        </script>
        <?php
        $output = ob_get_clean();
        echo $output;
    }

    add_action('wp_footer', 'snssara_wp_foot', 100);
}
//
if (!function_exists('snssara_preloadingeffect')) {
    function snssara_preloadingeffect()
    {
        if (snssara_get_option('use_pathloader') == 1) {
            wp_enqueue_script('modernizr');
            wp_enqueue_script('path-loader');
            ?>
            <div id="ip-container" class="ip-container">
                <header class="ip-header">
                    <div class="ip-loader">
                        <svg class="ip-inner" width="60px" height="60px" viewBox="0 0 80 80">
                            <path class="ip-loader-circlebg"
                                  d="M40,10C57.351,10,71,23.649,71,40.5S57.351,71,40.5,71 S10,57.351,10,40.5S23.649,10,40.5,10z"/>
                            <path id="ip-loader-circle" class="ip-loader-circle"
                                  d="M40,10C57.351,10,71,23.649,71,40.5S57.351,71,40.5,71 S10,57.351,10,40.5S23.649,10,40.5,10z"/>
                        </svg>
                    </div>
                </header>
            </div>
            <?php
        }
    }
}
/**
 *  Tile for page, post
 **/
function snssara_pagetitle()
{
    // Disable title in page
    if (is_page() && function_exists('rwmb_meta') && rwmb_meta('snssara_showtitle') == '2') return;
    // Show title in page, single post
    if (is_single() || is_page() || (is_home() && get_option('show_on_front') == 'page')) : ?>
        <h1 class="page-header">
            <?php the_title(); ?>
        </h1>
        <?php
    // Show title for category page
    elseif (is_category()) : ?>
        <h1 class="page-header">
            <?php single_cat_title(); ?>
        </h1>
        <?php
    // Author
    elseif (is_author()) : ?>
        <h1 class="page-header">
            <?php
            printf(esc_html__('All posts by: %s', 'snssara'), get_the_author());
            ?>
        </h1>
        <?php if (get_the_author_meta('description')) : ?>
            <header class="archive-header">
                <div class="author-description"><p><?php the_author_meta('description'); ?></p></div>
            </header>
        <?php endif; ?>
        <?php
    // Tag
    elseif (is_tag()) : ?>
        <h1 class="page-header">
            <?php printf(esc_html__('Tag Archives: %s', 'snssara'), single_tag_title('', false)); ?>
        </h1>
        <?php
        $term_description = term_description();
        if (!empty($term_description)) : ?>
            <header class="archive-header">
                <?php printf('<div class="taxonomy-description">%s</div>', $term_description); ?>
            </header>
        <?php endif; ?>
        <?php
    // Search
    elseif (is_search()) : ?>
        <h1 class="page-header"><?php printf(esc_html__('Search Results for: %s', 'snssara'), get_search_query()); ?></h1>
        <?php
    // Archive
    elseif (is_archive()) : ?>
        <?php the_archive_title('<h1 class="page-header">', '</h1>'); ?>
        <?php
        if (get_the_archive_description()): ?>
            <header class="archive-header">
                <?php the_archive_description('<div class="taxonomy-description">', '</div>'); ?>
            </header>
            <?php
        endif;
        ?>
        <?php
    // Default
    else : ?>
        <h1 class="page-header">
            <?php the_title(); ?>
        </h1>
        <?php
    endif;
}


// Excerpt Function
if (!function_exists('snssara_excerpt')) {
    function snssara_excerpt($limit, $afterlimit = '...')
    {
        $limit = ($limit) ? $limit : 55;
        $excerpt = get_the_excerpt();
        if ($excerpt != '') {
            $excerpt = explode(' ', strip_tags($excerpt), intval($limit));
        } else {
            $excerpt = explode(' ', strip_tags(get_the_content()), intval($limit));
        }
        if (count($excerpt) >= $limit) {
            array_pop($excerpt);
            $excerpt = implode(" ", $excerpt) . ' ' . $afterlimit;
        } else {
            $excerpt = implode(" ", $excerpt);
        }
        $excerpt = preg_replace('`[[^]]*]`', '', $excerpt);
        return strip_shortcodes($excerpt);
    }
}

/*
 * Ajax page navigation
 */
function snssara_ajax_load_next_page()
{
    // Get current layout
    global $snssara_blog_layout, $snssara_obj;
    $snssara_blog_layout = isset($_POST['snssara_blog_layout']) ? esc_html($_POST['snssara_blog_layout']) : '';
    if ($snssara_blog_layout == '') $snssara_blog_layout = $snssara_obj->getOption('blog_type');

    // Get current page
    $page = $_POST['page'];

    // Current query vars
    $vars = $_POST['vars'];

    // Convert string value into corresponding data types
    foreach ($vars as $key => $value) {
        if (is_numeric($value)) $vars[$key] = intval($value);
        if ($value == 'false') $vars[$key] = false;
        if ($value == 'true') $vars[$key] = true;
    }

    // Item template file 
    $template = $_POST['template'];

    // Return next page
    $page = intval($page) + 1;

    $posts_per_page = get_option('posts_per_page');

    if ($page == 0) $page = 1;
    $offset = ($page - 1) * $posts_per_page;
    /*
     * This is confusing. Just leave it here to later reference
     *
    
     if(!$vars['ignore_sticky_posts']){
     $offset += $sticky_posts;
     }
     *
     */

    // Get more posts per page than necessary to detect if there are more posts
    $args = array('post_status' => 'publish', 'posts_per_page' => $posts_per_page + 1, 'offset' => $offset);
    $args = array_merge($vars, $args);

    // Remove unnecessary variables
    unset($args['paged']);
    unset($args['p']);
    unset($args['page']);
    unset($args['pagename']); // This is necessary in case Posts Page is set to static page

    $query = new WP_Query($args);

    $idx = 0;
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $idx = $idx + 1;
            if ($idx < $posts_per_page + 1)
                get_template_part($template, get_post_format());
        }

        if ($query->post_count <= $posts_per_page) {
            // There are no more posts
            // Print a flag to detect
            echo '<div id="sns-load-more-no-posts" class="no-posts"><!-- --></div>';
        }
    } else {
        // No posts found
    }

    /* Restore original Post Data*/
    wp_reset_postdata();

    die('');
}

// When the request action is "load_more", the snssara_ajax_load_next_page() will be called
add_action('wp_ajax_load_more', 'snssara_ajax_load_next_page');
add_action('wp_ajax_nopriv_load_more', 'snssara_ajax_load_next_page');

// Word Limiter
function snssara_limitwords($string, $word_limit)
{
    $words = explode(' ', $string);
    return implode(' ', array_slice($words, 0, $word_limit));
}

//
if (!function_exists('snssara_sharebox')) {
    function snssara_sharebox($layout = '', $args = array())
    {
        $default = array(
            'position' => 'top',
            'animation' => 'true'
        );
        $args = wp_parse_args((array)$args, $default);

        $path = SNSSARA_THEME_DIR . '/tpl-sharebox';
        if ($layout != '') {
            $path = $path . '-' . $layout;
        }
        $path .= '.php';

        if (is_file($path)) {
            require($path);
        }

    }
}
//
if (!function_exists('snssara_relatedpost')) {
    function snssara_relatedpost()
    {
        global $post;
        if ($post) {
            $post_id = $post->ID;
        } else {
            // Return if cannot find any post
        }

        $relate_count = snssara_get_option('related_num');
        $get_related_post_by = snssara_get_option('related_posts_by');

        $args = array(
            'post_status' => 'publish',
            'posts_per_page' => $relate_count,
            'orderby' => 'date',
            'ignore_sticky_posts' => 1,
            'post__not_in' => array($post_id)
        );

        if ($get_related_post_by == 'cat') {
            $categories = wp_get_post_categories($post_id);
            $args['category__in'] = $categories;
        } else {
            $posttags = wp_get_post_tags($post_id);

            $array_tags = array();
            if ($posttags) {
                foreach ($posttags as $tag) {
                    $tags = $tag->term_id;
                    array_push($array_tags, $tags);
                }
            }
            $args['tag__in'] = $array_tags;
        }

        $relates = new WP_Query($args);

        $template_name = '/framework/tpl/posts/related_post.php';
        if (is_file(SNSSARA_THEME_DIR . $template_name)) {
            include(SNSSARA_THEME_DIR . $template_name);
        }

        wp_reset_postdata();
    }
}

/*
 * Function to display number of posts.
 */
function snssara_get_post_views($post_id)
{
    $count_key = 'post_views_count';
    $count = get_post_meta($post_id, $count_key, true);
    if ($count == '') {
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
        return esc_html__('0 view', 'snssara');
    }
    return $count . esc_html__(' View', 'snssara');
}

/*
 * Function to count views.
 */
function snssara_set_post_views($post_id)
{
    $count_key = 'post_views_count';
    $count = get_post_meta($post_id, $count_key, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
    } else {
        $count++;
        update_post_meta($post_id, $count_key, $count);
    }
}


function snssara_comment($comment, $args, $depth) {
$GLOBALS['comment'] = $comment; ?>
<?php $add_below = ''; ?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
    <div class="comment-body">
        <div class="comment-user-meta">
            <?php echo get_avatar($comment, 70); ?>
        </div>
        <div class="comment-content">
            <h4 class="comment-user"><?php echo get_comment_author_link(); ?></h4>
            <div class="comment-meta">
                <?php edit_comment_link(esc_html__('Edit', 'snssara'), '  ', '') ?>
                <span class="date"><?php printf(esc_html__('%1$s at %2$s', 'snssara'), get_comment_date(), get_comment_time()) ?></span>
                <span class="reply">
                        <?php comment_reply_link(array_merge($args, array('reply_text' => '<i class="fa fa-reply"></i>' . esc_html__('Reply', 'snssara'), 'add_below' => 'comment', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
                    </span>
            </div>
            <?php if ($comment->comment_approved == '0') : ?>
                <p>
                    <em><?php echo esc_html__('Your comment is awaiting moderation.', 'snssara') ?></em><br/>
                </p>
            <?php endif; ?>
            <?php comment_text() ?>
        </div>
    </div>
    <?php
    }
    /**
     *  Breadcrumbs
     **/
    function snssara_breadcrumbs()
    {
        $template_name = '/tpl-breadcrumb.php';
        if (is_file(SNSSARA_THEME_DIR . $template_name)) {
            include(SNSSARA_THEME_DIR . $template_name);
        }
    }

    /*
     * Woocommerce advanced search functionlity
     */
    add_action('pre_get_posts', 'snssara_advanced_search_query', 1000);
    function snssara_advanced_search_query($query)
    {
        if ($query->is_search()) {
            // Category terms search
            if (isset($_GET['snssara_woo_category']) && !empty($_GET['snssara_woo_category'])) {
                $query->set('tax_query', array(array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'term' => array($_GET['snssara_woo_category']))
                ));
            }
        }
        return $query;
    }

    function snssara_advanced_search_form()
    {
        $header_search_box_type = snssara_get_option('header_search_box', 'def');
        $action = esc_url(home_url('/'));
        if (class_exists('WooCommerce')) $action = esc_url(get_permalink(woocommerce_get_page_id('shop')));
        ob_start();
        ?>
        <div class="header-search-form-content">
            <div id="headerSearchForm">
                <form method="get" action="<?php echo esc_url($action); ?>" class="header-search-form">
                    <?php // Get Woocommerce categoies
                    if (class_exists('WooCommerce')):
                        if ($header_search_box_type != 'hide_cat'):
                            $args = array(
                                'taxonomy' => 'product_cat',
                                'orderby' => 'name',
                                'show_count' => 0,
                                'pad_counts' => 0,
                                'hierarchical' => 0,
                                'title_li' => '',
                                'hide_empty' => 0
                            );
                            $all_categories = get_categories($args);
                            ?>
                            <select name="snssara_woo_category">
                                <option value=""><?php echo esc_html__('All Categories', 'snssara'); ?></option>
                                <?php
                                foreach ($all_categories as $cat):?>
                                    <option value="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                            <i class="arrow_triangle-down"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div>
                        <input type="text" name="s" id="s" class="input-search"
                               placeholder="<?php echo esc_attr__('Search entire store here', 'snssara'); ?>"/>
                        <input type="hidden" name="post_type" value="product"/>
                        <button type="submit"><i class="icon_search"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Search Ajax From
     **/
    if (!function_exists('snssara_get_searchform')) {
        function snssara_get_searchform($search_box_type = 'def')
        {
            $exists_woo = (class_exists('WooCommerce')) ? true : false;
            if ($exists_woo) {
                $taxonomy = 'product_cat';
                $post_type = 'product';
                $placeholder_text = esc_html__('Search entire store here', 'snssara');
            } else {
                $taxonomy = 'category';
                $post_type = 'post';
                $placeholder_text = esc_html__('Search', 'snssara');
            }
            $options = '<option value="">' . esc_html__('All categories', 'snssara') . '</option>';
            $options .= snssara_get_searchform_option($taxonomy, 0, 0);
            $uq = rand() . time();
            $form = '<div class="sns-searchwrap" data-useajaxsearch="true" data-usecat-ajaxsearch="true">';
            $form .= '<div class="sns-ajaxsearchbox">
        <form method="get" id="search_form_' . $uq . '" action="' . esc_url(home_url('/')) . '">';
            if ($search_box_type != 'hide_cat') {
                $form .= '<select class="select-cat" name="cat">' . $options . '</select>';
            }
            $form .= '
        <div class="search-input">
            <input type="text" value="' . get_search_query() . '" name="s" id="s_' . $uq . '" placeholder="' . $placeholder_text . '" autocomplete="off" />
            <button type="submit">
                <i class="icon_search"></i>
            </button>
            <input type="hidden" name="post_type" value="' . $post_type . '" />
            <input type="hidden" name="taxonomy" value="' . $taxonomy . '" />
         </div>
        </form></div></div>';
            echo $form;
        }
    }

    if (!function_exists('snssara_get_searchform_option')) {
        function snssara_get_searchform_option($taxonomy = 'product_cat', $parent = 0, $level = 0)
        {
            $options = '';
            $spacing = '';
            for ($i = 0; $i < $level * 3; $i++) {
                $spacing .= '&nbsp;';
            }
            $args = array(
                'number' => '',
                'hide_empty' => 1,
                'orderby' => 'name',
                'order' => 'asc',
                'parent' => $parent
            );
            $select = '';
            $categories = get_terms($taxonomy, $args);
            if (is_search() && isset($_GET['cat']) && $_GET['cat'] != '') {
                $select = $_GET['cat'];
            }
            $level++;
            $selected = '';
            if (is_array($categories)) {
                foreach ($categories as $cat) {
                    if ($select == $cat->slug) $selected = ' selected';
                    else  $selected = '';
                    $options .= '<option value="' . $cat->slug . '"' . $selected . '>' . $spacing . $cat->name . '</option>';
                    $options .= snssara_get_searchform_option($taxonomy, $cat->term_id, $level);
                }
            }
            return $options;
        }
    }

    // Ajax search action
    add_action('wp_ajax_snssara_ajax_search', 'snssara_ajax_search');
    add_action('wp_ajax_nopriv_snssara_ajax_search', 'snssara_ajax_search');
    if (!function_exists('snssara_ajax_search')) {
        function snssara_ajax_search()
        {
            global $wpdb, $post;
            $exists_woo = (class_exists('WooCommerce')) ? true : false;
            if ($exists_woo) {
                $taxonomy = 'product_cat';
                $post_type = 'product';
            } else {
                $taxonomy = 'category';
                $post_type = 'post';
            }
            $num_result = 3;
            $keywords = $_POST['keywords'];
            $category = isset($_POST['category']) ? $_POST['category'] : '';
            $args = array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                's' => $keywords,
                'posts_per_page' => $num_result
            );
            if ($exists_woo) {
                $args['meta_query'] = array(
                    array(
                        'key' => '_visibility',
                        'value' => array('catalog', 'visible'),
                        'compare' => 'IN'
                    )
                );
            }
            if ($category != '') {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => $taxonomy,
                        'terms' => $category,
                        'field' => 'slug'
                    )
                );
            }
            $results = new WP_Query($args);
            if ($results->have_posts()) {
                $extra_class = '';
                if (isset($results->post_count, $results->found_posts) && $results->found_posts > $results->post_count) {
                    $extra_class = 'allcat-result';
                }
                $html = '<ul class="' . $extra_class . '">';
                while ($results->have_posts()) {
                    $results->the_post();
                    $link = get_permalink($post->ID);
                    $image = '';
                    if ($post_type == 'product') {
                        $product = wc_get_product($post->ID);
                        $image = $product->get_image();
                    } else if (has_post_thumbnail($post->ID)) {
                        $image = get_the_post_thumbnail($post->ID, 'thumbnail');
                    }
                    $html .= '<li>';
                    if ($image) {
                        $html .= '<div class="thumbnail">';
                        $html .= '<a href="' . esc_url($link) . '">' . $image . '</a>';
                        $html .= '</div>';
                    }
                    $html .= '<div class="meta">';
                    $html .= '<a href="' . esc_url($link) . '" class="title">' . snssara_ajaxsearch_highlight_key($post->post_title, $keywords) . '</a>';
                    if ($post_type == 'product') {
                        if ($price_html = $product->get_price_html()) {
                            $html .= '<span class="price">' . $price_html . '</span>';
                        }
                    }
                    $html .= '</div>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
                if (isset($results->post_count, $results->found_posts) && $results->found_posts > $results->post_count) {
                    $viewall_text = sprintf(esc_html__('View all %d results', 'snssara'), $results->found_posts);
                    $html .= '<div class="viewall-result">';
                    $html .= '<a href="#">' . $viewall_text . '</a>';
                    $html .= '</div>';
                }
                wp_reset_postdata();

                $return = array();
                $return['html'] = $html;
                $return['keywords'] = $keywords;
                die(json_encode($return));
            } else {
                $return = array();
                if ($exists_woo) {
                    $return['html'] = esc_html__('No products were found matching your selection', 'snssara');
                } else {
                    $return['html'] = esc_html__('No post were found matching your selection', 'snssara');
                }
                $return['keywords'] = $keywords;
                die(json_encode($return));
            }
        }
    }
    // Highlight search key
    if (!function_exists('snssara_ajaxsearch_highlight_key')) {
        function snssara_ajaxsearch_highlight_key($string, $keywords)
        {
            $hl_string = '';
            $position_left = stripos($string, $keywords);
            if ($position_left !== false) {
                $position_right = $position_left + strlen($keywords);
                $hl_string_rightsection = substr($string, $position_right);
                $highlight = substr($string, $position_left, strlen($keywords));
                $hl_string_leftsection = stristr($string, $keywords, true);
                $hl_string = $hl_string_leftsection . '<span class="hightlight">' . $highlight . '</span>' . $hl_string_rightsection;
            } else {
                $hl_string = $string;
            }
            return $hl_string;
        }
    }

    // Match with default search
    add_filter('woocommerce_get_catalog_ordering_args', 'snssara_woo_get_catalog_ordering_args');
    if (!function_exists('snssara_woo_get_catalog_ordering_args')) {
        function snssara_woo_get_catalog_ordering_args($args)
        {
            if (class_exists('WooCommerce') && is_search() && !isset($_GET['orderby']) && get_option('woocommerce_default_catalog_orderby') == 'menu_order'
                && 1 == 1
            ) {
                $args['orderby'] = '';
                $args['order'] = '';
            }
            return $args;
        }
    }

    // WP Like post plugin
    if (function_exists('gs_lp_activate')) {
        // WP Like post Post integration
        remove_filter('the_content', 'gs_lp_add_like');
        add_filter('gs_lp_like_icon', 'snssara_icon_like_heart', 10);
        function snssara_icon_like_heart()
        {
            return 'icon_heart_alt';
        }
    }


    /* Sample data */
    add_action('admin_enqueue_scripts', 'snssara_importlib');
    function snssara_importlib()
    {
        wp_enqueue_script('sampledata', get_template_directory_uri() . '/framework/sample-data/assets/script.js', array('jquery'), '', true);
        wp_enqueue_style('sampledata-css', get_template_directory_uri() . '/framework/sample-data/assets/style.css');
    }

    add_action('wp_ajax_sampledata', 'snssara_importsampledata');
    function snssara_importsampledata()
    {
        locate_template(array('/framework/sample-data/sns-importdata.php'), true, true);
        snssara_importdata();
    }

    /*
    * Enter your function here

    // allow SVG uploads
    add_filter('upload_mimes', 'custom_upload_mimes');
    function custom_upload_mimes ( $existing_mimes=array() ) {
      $existing_mimes['svg'] = 'image/svg+xml';
      return $existing_mimes;
    }
    function fix_svg() {
        echo '<style type="text/css">
              .attachment-266x266, .thumbnail img {
                   width: 100% !important;
                   height: auto !important;
              }
              </style>';
     }
     add_action('admin_head', 'fix_svg');*/

    // Remove product tab description heading
    add_filter('woocommerce_product_description_heading', 'snssara_woocommerce_product_description_heading', 10);
    function snssara_woocommerce_product_description_heading()
    {
        return '';
    }

    function wc_ninja_remove_password_strength()
    {
        if (wp_script_is('wc-password-strength-meter', 'enqueued')) {
            wp_dequeue_script('wc-password-strength-meter');
        }
    }

    add_action('wp_print_scripts', 'wc_ninja_remove_password_strength', 100);


    // Меню админки для менеджера
    add_action('admin_init', 'my_remove_menu_pages', 9999);
    function my_remove_menu_pages()
    {

        global $user_ID;

        if (current_user_can('shop_manager')) {
            remove_menu_page('edit.php?post_type=agency');
            remove_menu_page('edit.php?post_type=infobox');
            remove_menu_page('options-general.php');
            remove_menu_page('edit.php?post_type=page');
            remove_menu_page('tools.php');
            remove_menu_page('edit.php?post_type=brand');
            remove_menu_page('edit.php?post_type=testimonial');
            remove_menu_page('revslider');
            remove_menu_page('vc-general');
            remove_menu_page('snssara');
            remove_menu_page('wpcf7');
            remove_menu_page('WP-Optimize');
            remove_menu_page('panel');
            remove_menu_page('heateor-ss-general-options');
            remove_menu_page('loco');
            remove_menu_page('yith_woocompare_panel');
        }
    }

    // Вывод произвольного атрибута в товаре
    function isa_woocommerce_all_pa()
    {

        global $product;
        $attributes = $product->get_attributes();

        if (!$attributes) {
            return;
        }

        $out = '<ul class="custom-attributes">';

        foreach ($attributes as $attribute) {


            // skip variations
            if ($attribute['is_variation']) {
                continue;
            }


            if ($attribute['is_taxonomy']) {

                $terms = wp_get_post_terms($product->id, $attribute['name'], 'all');

                // get the taxonomy
                $tax = $terms[0]->taxonomy;

                // get the tax object
                $tax_object = get_taxonomy($tax);

                // get tax label
                if (isset ($tax_object->labels->name)) {
                    $tax_label = $tax_object->labels->name;
                } elseif (isset($tax_object->label)) {
                    $tax_label = $tax_object->label;
                }

                foreach ($terms as $term) {

                    $out .= '<li class="' . esc_attr($attribute['name']) . ' ' . esc_attr($term->slug) . '">';
                    $out .= '<span class="attribute-label">' . $tax_label . ': </span> ';
                    $out .= '<span class="attribute-value">' . $term->name . '</span></li>';

                }

            } else {

                $out .= '<li class="' . sanitize_title($attribute['name']) . ' ' . sanitize_title($attribute['value']) . '">';
                $out .= '<span class="attribute-label">' . $attribute['name'] . ': </span> ';
                $out .= '<span class="attribute-value">' . $attribute['value'] . '</span></li>';
            }
        }

        $out .= '</ul>';

        echo $out;
    }

    add_action('woocommerce_single_product_summary', 'isa_woocommerce_all_pa', 25);


    add_filter('woocommerce_email_headers', 'add_bcc_to_wc_mail', 10, 3);
    function add_bcc_to_wc_mail($headers = '', $id = '', $wc_email = array())
    {
        if ($id == 'yith_waitlist_mail_subscribe' || $id == 'yith_waitlist_mail_instock') {
            $headers .= "Bcc: shishkindv@gmail.com";
        }
        return $headers;
    }

    remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);

    add_action('woocommerce_after_shop_loop', 'woocommerce_taxonomy_archive_description', 100);


    add_filter('woocommerce_default_address_fields', 'custom_override_default_address_fields');

    // Our hooked in function - $address_fields is passed via the filter!
    function custom_override_default_address_fields($address_fields)
    {
        $address_fields['address_1']['required'] = false;
        $address_fields['postcode']['required'] = false;
        $address_fields['state']['required'] = false;
        $address_fields['country']['required'] = false;

        return $address_fields;
    }


    add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');

    function custom_override_checkout_fields($fields)
    {
        unset($fields['billing']['billing_postcode']);

        return $fields;
    }

    add_filter('minit-exclude-js', function ($handles) {
        $exclude = array(
            'jquery-blockui',
        );

        return array_merge($handles, $exclude);
    });

    add_action('wp_print_styles', 'remove_styles', 100);
    function remove_styles()
    {
        // add handles for styles you wish to remove
        wp_deregister_style('yith-quick-view');
        wp_deregister_style('contact-form-7');
        wp_deregister_style('yith-wishlist');
        wp_deregister_style('rs-plugin-settings');
        wp_deregister_style('the_champ_frontend_css');
        wp_deregister_style('the_champ_sharing_default_svg');
        wp_deregister_style('yith-wcwl-main');
        wp_deregister_style('jquery-selectBox');
        wp_deregister_style('yith-wcwl-font-awesome');
        wp_deregister_style('animate');
        //wp_deregister_style ('bootstrap');

    }

    add_action('wp_print_scripts', 'my_deregister_javascript', 100);
    function my_deregister_javascript()
    {
        if (!is_page('my-account')) {
            wp_deregister_script('the_champ_ss_general_scripts');
            wp_deregister_script('the_champ_sl_common');
            wp_deregister_script('the_champ_fb_sdk');
            wp_deregister_script('the_champ_sl_facebook');
        }
    }

    function remove_css_class($classes)
    {
        foreach ($classes as $key => $class) {
            if (strstr($class, "comment-author")) {
                unset($classes[$key]);
            }
        }
        return $classes;
    }

    add_filter('comment_class', 'remove_css_class');
    //удалил логин юзера из комментария//

    // New order status AFTER woo 2.2
    add_action('init', 'register_my_new_order_statuses');

    function register_my_new_order_statuses()
    {
        register_post_status('wc-invoiced', array(
            'label' => _x('Загружено в 1С', 'Order status', 'woocommerce'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Загружено в 1С <span class="count">(%s)</span>', 'Загружено в 1С<span class="count">(%s)</span>', 'woocommerce')
        ));
    }

    add_filter('wc_order_statuses', 'my_new_wc_order_statuses');

    // Register in wc_order_statuses.
    function my_new_wc_order_statuses($order_statuses)
    {
        $order_statuses['wc-invoiced'] = _x('Загружено в 1С', 'Order status', 'woocommerce');

        return $order_statuses;
    }//добавил кастомный статус для синхронизации с 1С//

    ?>
