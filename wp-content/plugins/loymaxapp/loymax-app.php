<?php

/**
 * Plugin Name: LoymaxWebApp
 * Description: Loymax loyalty program User Portal configuration.
 * Version: 3
 * Author URI:  https://loymax.ru/
 * Author: Loymax solutions
 * Text Domain: loymax-app
 * Domain Path: /languages/
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * License: GPL2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
global $wpdb;

if ( ! defined( 'LOYMAX_WEB_APP_PLUGIN_FILE' ) ) {
    define( 'LOYMAX_WEB_APP_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LOYMAX_WEB_APP_CONFIGS_TABLE_NAME' ) ) {
    define( 'LOYMAX_WEB_APP_CONFIGS_TABLE_NAME', strval( $wpdb->prefix ) . 'loymax' );
}

if ( ! defined( 'LOYMAX_WEB_APP_COMPONENT_TABLE_NAME' ) ) {
    define( 'LOYMAX_WEB_APP_COMPONENT_TABLE_NAME', strval( $wpdb->prefix ) . 'loymax_components' );
}

if ( ! defined( 'LOYMAX_WEB_APP_DOMAIN_NAME' ) ) {
    define( 'LOYMAX_WEB_APP_DOMAIN_NAME', 'loymax-app' );
}

if ( ! class_exists( 'LoymaxWebApp_Plugin' ) ) {
    class LoymaxWebApp_Plugin {
        private $wpdb;
        private $plugin_title;
        private $config_page_slug;
        public $loymax_page_ID;
        private $updater;

        public function __construct() {
            global $wpdb;

            $this->wpdb = $wpdb;
            $this->plugin_title = __( 'User Portal settings', LOYMAX_WEB_APP_DOMAIN_NAME ) . ' Loymax';
            $this->config_page_slug = 'loymax-plugin';
            $this->loymax_page_ID = get_option( 'loymax_page_ID' );

            $wpdb->show_errors();

            $this->load_plugin_textdomain();

            $this->add_actions();
            require( dirname( __FILE__ ) . '/widget/loymax-app-widget.php' );
            $this->add_filters();
            $this->create_updater();
        }

        private function go_to_next_step() {
            if ( $_GET[ 'page' ] === 'loymax-setup' ) {
                wp_redirect( LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) );
            }
        }

        private function add_actions() {
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_handler' ) );

	        add_action( 'wp_head',  array( $this, 'disallow_robots_on_staging' ) );
            add_action( 'shutdown', array( $this, 'check_bootstrap_presence' ) );

            add_action( 'admin_menu', array( $this, 'setup_menu' ) );

            add_action( 'admin_enqueue_scripts', function ( $hook ) {
                if ( strpos( $hook, 'page_loymax-plugin' ) !== false ) {
                    wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.min.css' );
                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'jquery-ui-dialog' );
                    wp_enqueue_script( 'jquery-ui-sortable' );
                    wp_enqueue_script( 'popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js' );
                    wp_enqueue_script( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js' );

                    wp_enqueue_style( 'loymax-style', plugins_url( 'admin/loymax-plugin-styles.css', __FILE__ ) );
                }
                wp_enqueue_script( 'loymax-admin-js', plugins_url( 'admin/admin.js', __FILE__ ), array(), md5( microtime() ), false );
            } );

            add_action( 'init', function () {
                if ( !empty( $_REQUEST['action'] ) ) {
                    $action = $_REQUEST['action'];
                    $slug = $_REQUEST['slug'];

                    if ( $action === 'update-plugin' && $slug === 'loymaxapp' ) {
                        $this->backup_custom_templates();
                    } elseif ( $action === 'do-plugin-upgrade' && !empty( $_REQUEST['checked'] ) ) {
                        foreach ($_REQUEST['checked'] as $item) {
                            if (stripos( $item, 'loymax-app') !== false) {
                                $this->backup_custom_templates();
                            }
                        }
                    }
                }
                if ( $_POST && ! empty( $_POST ) ) {
                    if ( isset( $_POST[ 'lmx-action' ] ) ) {
                        require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-install-wizard.php' );
                        switch ( $_POST[ 'lmx-action' ] ) {
                            case 'generate-page':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-userportal-page.php' );
                                $generate_page_settings = new LoymaxWebApp_userportal_page();
                                $generate_page_settings->generate_default_page( $this );
                                break;
                            case 'published-page':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-userportal-page.php' );
                                $generate_page_settings = new LoymaxWebApp_userportal_page();
                                $generate_page_settings->return_to_publish( $this->loymax_page_ID );
                                $generate_page_settings->generate_default_page( $this );
                                break;
                            case 'update-config':
                                $this->update_config();
                                break;
                            case 'set-api':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-set-api.php' );
                                LoymaxWebApp_set_api::save_api();
                                break;
                            case 'install-customify':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-customify-helper.php' );
                                $customify_helper = new LoymaxWebApp_customify_helper();
                                $customify_helper->install_customify();
                                break;
                            case 'checked-element':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-userportal-config.php' );
                                $userportal_config = new LoymaxWebApp_userportal_config();
                                $userportal_config->update_component_config();

                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-menu-for-userportal.php');
                                $menu = new LoymaxWebApp_menu_for_userportal();
                                $menu->update_visible_menu_items();
                                break;
                            case 'update-all-config':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-userportal-config.php' );
                                $userportal_config = new LoymaxWebApp_userportal_config();
                                $userportal_config->update_all_components_config();
                                break;
                            case 'update-menu-orders':
                                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-userportal-config.php' );
                                $userportal_config = new LoymaxWebApp_userportal_config();
                                $userportal_config->update_menu_order( $_POST[ 'orders' ] );
                                break;
                            case 'skip-install':
                                delete_option( 'loymax_install_wizard_in_progress' );
                                break;
                        }
                        // Переход на следующий шаг для установщика
                        $this->go_to_next_step();
                    }
                }
                if ( !empty( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'loymax-setup' ) {
                    if ( !current_user_can( 'manage_options' )) return;
                    $current_step = $_GET[ 'step' ] ? $_GET[ 'step' ] : 'start';
                    require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-install-wizard.php');
                    new LoymaxWebApp_install_wizard( $current_step );
                }
            } );

            add_action( 'widgets_init', function(){
                register_widget( 'LoymaxWebApp_Widget' );
            });
            add_action( 'upgrader_process_complete', function() {
                if ( get_option('loymax_plugin_updated') ) {
                    $this->backup_custom_templates(true);
                }
            } );

            add_action( 'plugins_loaded', array( $this, 'update_tables' ) );

            add_action( 'before_delete_post', function ( $post_id ) {
                if ( $this->loymax_page_ID && $post_id == strval ( $this->loymax_page_ID ) ) {
                    add_option( 'loymax_page_delete_prevented', true );
                    wp_redirect( admin_url( 'edit.php?post_status=trash&post_type=page' ) );
                    exit();
                } elseif ( get_option( 'loymax-navigation-menu-id' ) ) {
                    $component = $this->get_component_by_menu_item_id( $post_id );
                    if ( count($component) > 0 ) {
                        add_option( 'loymax_menu_delete_prevented', true );
                        wp_redirect( admin_url( 'nav-menus.php' ) );
                        exit();
                    }
                }
            } );

            add_action( 'pre_delete_term', function ( $term_id ) {
                if ( get_option( 'loymax-navigation-menu-id' ) && $term_id == get_option( 'loymax-navigation-menu-id' ) ) {
                    add_option( 'loymax_menu_delete_prevented', true );
                    wp_redirect( admin_url( 'nav-menus.php' ) );
                    exit();
                }
            } );

            add_action( 'admin_notices', array( $this, 'error_delete_page' ) );
            add_action( 'admin_notices', array( $this, 'error_delete_menu' ) );

            add_action( 'edit_post', function ( $post_id ) {
                if ( $this->loymax_page_ID && $post_id == strval ( $this->loymax_page_ID ) ) {
                    $this->update_links($post_id);
                }
            });

            add_action( 'update_option_page_on_front', function ( $old_value, $value, $options ) {
                if ( !$this->loymax_page_ID ) {
                    return;
                }
                if ( $value == strval ( $this->loymax_page_ID ) ) {
                    $this->update_links($value);
                } elseif ($old_value == strval ( $this->loymax_page_ID ) ) {
                    $this->update_links($old_value);
                }
            }, 10, 3 );

            if ( get_option( 'loymax_install_wizard_in_progress' ) ) {
                add_action( 'admin_notices', function() {
                    if ( get_option( 'loymax_install_wizard_in_progress' ) ) {
                        $screen = get_current_screen();
                        if ( $screen->id == 'toplevel_page_loymax-plugin' || $screen->id == 'plugins' ) {
                            wp_enqueue_style( 'loymax-style', plugins_url( 'admin/loymax-plugin-styles.css', __FILE__ ) );
                            ?>
                            <div id="notice-loymax-setup" class="notice notice-loymax-setup">
                                <p><b><?= __( 'Welcome to', LOYMAX_WEB_APP_DOMAIN_NAME ) ?> LoymaxWebApp!</b></p>
                                <p><?= __( 'You need to configure your Personal Account', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
                                <button class="button button-primary" type="button">
                                    <a class="not-href" href="<?= admin_url( 'admin.php?page=loymax-setup' ) ?>"><?= __( 'Run Installation Wizard', LOYMAX_WEB_APP_DOMAIN_NAME ); ?></a>
                                </button>
                                <button id="skip-install" class="button button-default" type="button">
                                    <?= __( 'Skip Installation', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                                </button>
                            </div>
                            <?php
                        }
                    }
                });
            }
        }

        private function get_component_by_menu_item_id($menu_item_id) {
            $component_SQL = 'SELECT c_key, name, selected, description, menu_item_id, is_paid FROM ' . LOYMAX_WEB_APP_COMPONENT_TABLE_NAME;
            $component_SQL .= ' WHERE menu_item_id = "' . $menu_item_id . '"';
            return $this->wpdb->get_results( $component_SQL, OBJECT_K );
        }

        private function update_links($post_id) {
            $old_link = get_option( 'loymax-page-link' );
            $new_link = wp_make_link_relative( get_permalink( $post_id ) );
            if ( $old_link === $new_link ) {
                return;
            }
            update_option( 'loymax-page-link', $new_link );
            require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-menu-for-userportal.php');
            $menu_for_userportal = new LoymaxWebApp_menu_for_userportal();
            $menu_for_userportal->update_menu_item_links();
        }

        public function error_delete_page() {
            if ( $_GET[ 'post_status' ] == 'trash' && $_GET[ 'post_type' ] == 'page' && get_option( 'loymax_page_delete_prevented' ) ) {
                delete_option ( 'loymax_page_delete_prevented' );
                $page_name = get_option( 'loymax-page-title' );
                $content = "<p>";
                $content .= __('Attention! The', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= ' "' . $page_name . '" ';
                $content .= __('page is used and protected from deletion by the LoymaxWebApp plugin', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= '</p>';
                LoymaxWebApp_Plugin::show_notice(true, $content);
            }
        }

        public function error_delete_menu() {
            if ( get_option( 'loymax_menu_delete_prevented' ) ) {
                delete_option ( 'loymax_menu_delete_prevented' );
                $menu_name = wp_get_nav_menu_object( get_option( 'loymax-navigation-menu-id' ) )->name;
                $content = "<p>";
                $content .= __('Attention! The', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= ' "' . $menu_name . '" ';
                $content .= __('menu is used and protected from deletion by the LoymaxWebApp plugin', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= '</p>';
                LoymaxWebApp_Plugin::show_notice(true, $content);
            }
        }

        public function load_plugin_textdomain() {
            if ( function_exists( 'determine_locale' ) ) {
                $locale = determine_locale();
            } else {
                $locale = is_admin() ? get_user_locale() : get_locale();
            }

            $locale = apply_filters( 'plugin_locale', $locale, LOYMAX_WEB_APP_DOMAIN_NAME );

            unload_textdomain( LOYMAX_WEB_APP_DOMAIN_NAME );
            load_textdomain( LOYMAX_WEB_APP_DOMAIN_NAME, WP_LANG_DIR . '/plugins/loymax-app-' . $locale . '.mo' );
            load_plugin_textdomain( LOYMAX_WEB_APP_DOMAIN_NAME, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
        }

        private function backup_custom_templates($restore = false) {
            $plugin_path = wp_normalize_path( plugin_dir_path( __FILE__ ) . 'custom-templates' );
            $wp_uploads = wp_upload_dir( null, false );
            $wp_uploads_dir = $wp_uploads['basedir'];
            $uploads_path = wp_normalize_path( $wp_uploads_dir . '/lmx-custom-templates' );
            if ( $restore ) {
                delete_option('loymax_plugin_updated');
                rename($uploads_path, $plugin_path);
            } else {
                add_option('loymax_plugin_updated', 1);
                rename($plugin_path, $uploads_path);
            }
        }

        public function disallow_robots_on_staging() {
            $is_staging = preg_match( '/web-.+-stg\.loymax\.tech/', $_SERVER['HTTP_HOST'] );
            if ( $is_staging ) {
                ?>
                <meta name="robots" content="noindex, follow">
                <?php
            }
        }

        private function create_updater() {
            require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-updater.php');
            $this->updater = new LoymaxWebApp_updater( __FILE__ );
        }

        private function add_filters() {
            add_filter( 'auto_update_plugin', array( $this, 'disable_auto_update' ), 999 );
            add_filter( 'body_class', array( $this, 'add_custom_body_attributes' ), 999 );
        }

        public function update_tables() {
            $this->updater->update_tables();
        }

        public function disable_auto_update( $update = null, $item = null ){
            if ( $item->slug === plugin_basename( __FILE__ ) ) {
                return false;
            } else {
                return $update;
            }
        }

        public function add_custom_body_attributes( $classes ) {
            $classes[] = '" ng-class="{
                    \'lmx-authorised\': isAuth() && !authInProcess,
                    \'lmx-unauthorised\': !isAuth() || authInProcess,
                    \'lmx-hasMessages\': isAuth() && hasMessages,
                    \'lmx-noPersonalOffers\': isAuth() && noPersonalOffers,
                    \'lmx-noPersonalGoods\': isAuth() && noPersonalGoods,
                    \'lmx-userStatus\': isAuth() && userStatus,
                    \'lmx-no-balance\': isAuth() && userInfo.baseBalanceAccount === undefined,
                }';

            return $classes;
        }

        private function show_updating_error() {
            if ( get_option( 'loymax_update_error' ) != null ) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?= __( 'An error occurred while updating', LOYMAX_WEB_APP_DOMAIN_NAME ) ?> LoymaxWebApp: <?= get_option( 'loymax_update_error' ) ?>.</p>
                </div>
                <?php
            }
        }

        public function setup_menu() {
            add_menu_page( $this->plugin_title, 'Loymax', 'manage_options', 'loymax-plugin', function () {
                require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-configs-page.php');
                $settings = new LoymaxWebApp_settings_page();
                $this->show_updating_error();
                $settings->show();
            } );

            add_dashboard_page( '', '', 'manage_options', 'loymax-setup' );
        }

        public function check_bootstrap_presence() {
            global $wp_styles;

            if ( $wp_styles->registered ) {
                foreach ( $wp_styles->registered as $registered_stylesheet ) {
                    if ( preg_match( '/bootstrap(?:\.min)?\.css/', $registered_stylesheet->src )) {
                        ?>
                        <script type="text/javascript">
                            jQuery(function () {
                                jQuery('body').addClass('lmx-bootstrapped');
                            });
                        </script>
                        <?php
                        break;
                    }
                }
            }
        }

        private function generate_app_config( $is_wpglobus_active ) {
            $configs = $this->get_configs();

            require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-smart-configurations.php');
            LoymaxWebApp_smart_configurations::process_smart_configurations($configs);

            $current_locale = 'ru';
            if ($is_wpglobus_active) {
                $current_locale = WPGlobus::Config()->language;
            }

            $custom_templates_url = wp_make_link_relative( plugin_dir_url( __FILE__ ) ) . 'custom-templates';
            ?>

            <script type="text/javascript">
                (function ( e ) {
                    'use strict';

                    e.lmxConfig = {};
                    e.lmxConfig.components = {};
                    e.lmxConfig.common = {};
                    <?php
                    foreach ( $configs as $config ) {
                        if ( $config->is_option ) {
                        if ( $config->component !== null ) {
                    ?>if (!e.lmxConfig.components['<?php echo $config->component; ?>']) {
                        e.lmxConfig.components['<?php echo $config->component; ?>'] = {};
                    }
                    e.lmxConfig.components['<?php echo $config->component; ?>']['<?php echo $config->config_key; ?>'] = <?php
                        } else {
                    ?>e.lmxConfig.common['<?php echo $config->config_key; ?>'] = <?php
                        }
                        switch ( $config->config_type ) {
                            case 'bool':
                                echo( $config->config_value ? 'true' : 'false' );
                                break;
                            case 'object':
                                echo wp_kses_stripslashes( $config->config_value );
                                break;
                            default:
                                if (strpos( $config->config_key, 'redirect' ) !== false) {
                                    echo('"' . home_url() . substr(get_option( 'loymax-page-link' ), 0, -1) . $config->config_value . '"');
                                } else {
                                    echo('"' . $config->config_value . '"');
                                }
                                break;
                        }
                        ?>;
                        <?php
                        } else {
                        // TODO: #37528
                        if ($config->config_key === 'apiHost') {
                            $config->config_key = 'host';
                        }
                        if ($config->config_key === 'showcaseApiHost') {
                            $config->config_key = 'showcase';
                        }
                        ?>e.lmxConfig['<?php echo $config->config_key; ?>'] = <?php
                        switch ( $config->config_type ) {
                            case 'bool':
                                echo( $config->config_value ? 'true' : 'false' );
                                break;
                            case 'object':
                                echo wp_kses_stripslashes( $config->config_value );
                                break;
                            default:
                                if (strpos( $config->config_key, 'redirect' ) !== false) {
                                    echo('"' . home_url() . substr(get_option( 'loymax-page-link' ), 0, -1) . $config->config_value . '"');
                                } else {
                                    echo('"' . $config->config_value . '"');
                                }
                                break;
                        }
                        ?>;
                        <?php
                        }
                    }
                        ?>e.lmxConfig.locales = "<?php echo( $current_locale ); ?>";
                    <?php if ( ! empty( $custom_templates ) ): ?>
                    e.lmxConfig.templatesPath = "<?php echo( $custom_templates_url ); ?>";
                    e.lmxConfig.customTemplates = <?php echo( wp_unslash( json_encode( $custom_templates ) ) ); ?>;
                    <?php endif;
                        ?>e.lmxConfig['localesPath'] = "<?php echo( $custom_templates_url ); ?>";
                } )( window );
            </script>
            <?php
        }

        private function include_scripts( $wpglobus_helper ) {
            wp_deregister_script( 'jquery-core' );
            wp_register_script( 'jquery-core', '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js' );
            wp_enqueue_script( 'jquery' );

            wp_enqueue_script( 'loymax-update-menu', plugins_url( 'includes/js/updateCurrentMenuItem.js', __FILE__ ), array( 'jquery' ), md5( microtime() ) );

            wp_enqueue_script( 'loymax-dependencies', plugins_url( 'public/dependencies.js', __FILE__ ), array(), '2.0.0' );
            $deps = array( 'jquery', 'loymax-dependencies' );

            if ( $wpglobus_helper->get_is_wpglobus_active() ) {
                $deps[] = 'wpglobus';
                $wpglobus_helper->add_inline_script();
            }
            wp_enqueue_script( 'loymax-app', plugins_url( 'public/app.min.js', __FILE__ ), $deps, md5( microtime() ) );

            wp_enqueue_style( 'loymax-styles', plugins_url( 'public/css/style.min.css', __FILE__ ), array(), md5( microtime() ) );
            wp_enqueue_style( 'loymax-glyphicons', plugins_url( 'public/lib/glyphicons/css/glyphicons.min.css', __FILE__ ), array(), null );
        }

        private function init_custom_templates() {
            $custom_templates_path = wp_normalize_path( plugin_dir_path( __FILE__ ) . 'custom-templates' );

            if ( is_dir( $custom_templates_path ) ) {
                $custom_templates = array();

                $plugin_directory = new RecursiveDirectoryIterator( $custom_templates_path, RecursiveDirectoryIterator::SKIP_DOTS );
                $iterator = new RecursiveIteratorIterator( $plugin_directory, RecursiveIteratorIterator::LEAVES_ONLY );

                foreach ( $iterator as $file_info ) {
                    if ( $file_info->getExtension() === 'html' ) {
                        array_push( $custom_templates, str_replace( $custom_templates_path . '/', '', wp_normalize_path( $file_info->getPathname() ) ) );
                    }
                }
            }
        }

        public function wp_enqueue_scripts_handler() {
            require_once( plugin_dir_path( __FILE__ ) . 'includes/loymax-wp-globus-helper.php');
            $wpglobus_helper = new LoymaxWebApp_Wp_Globus_helper();

            $this->init_custom_templates();
            $this->generate_app_config( $wpglobus_helper->get_is_wpglobus_active() );
            $this->include_scripts( $wpglobus_helper );
        }

        public static function get_configs( $redirect_options_only = null ) {
            global $wpdb;
            $configs_SQL = 'SELECT config_key, config_name, config_value, config_type, is_option, component FROM ' . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME;
            if ( ! is_null( $redirect_options_only ) ) {
                $configs_SQL .= ' WHERE config_key LIKE \'redirect%\'';
            }
            $configs_SQL .= ' ORDER BY is_option';
            $configs = $wpdb->get_results( $configs_SQL, OBJECT_K );

            foreach ( $configs as $key => $config ) {
                if ( $config->config_type == 'bool' ) {
                    $configs[ $key ]->config_value = filter_var( $config->config_value, FILTER_VALIDATE_BOOLEAN );
                }
            }

            return $configs;
        }

        private function update_config() {
            foreach ( $_POST as $key => $config_value ) {
                $this->wpdb->update(
                    LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
                    array( 'config_value' => wp_unslash( esc_html( $config_value ) ) ),
                    array( 'config_key' => $key )
                );
            }
        }

        public static function show_notice( $is_error, $content ) {
            ?>
            <div class="notice is-dismissible
                <?= ( 'notice-' . ( $is_error ? 'error' : 'success' ) ) ?>
             ">
                <?= $content ?>
            </div>
            <?php
        }
    }
    new LoymaxWebApp_Plugin();
}
?>
