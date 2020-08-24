<?php

class LoymaxWebApp_customify_helper {
    private $stable_version;
    private $theme_slug;

    public function __construct() {
        $this->stable_version = 0.3;
        $this->theme_slug = 'customify';
    }

    public function install_customify() {


        try {
            $theme = wp_get_theme( $this->theme_slug );

            if ( !$theme->exists() ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                include_once ABSPATH . 'wp-admin/includes/theme.php';

                $skin = new Automatic_Upgrader_Skin();
                $upgrader = new Theme_Upgrader( $skin );
                $api = themes_api(
                    'theme_information',
                    array(
                        'slug' => $this->theme_slug,
                        'fields' => array( 'sections' => false ),
                    )
                );
                $result = $upgrader->install( $api->download_link );

                if (is_wp_error( $result )) {
                    throw new Exception( $result->get_error_message() );
                } elseif ( is_wp_error( $skin->result ) ) {
                    throw new Exception( $skin->result->get_error_message() );
                } elseif ( is_null( $result ) ) {
                    throw new Exception(__('Unable to connect to the filesystem. Please confirm your credentials.', LOYMAX_WEB_APP_DOMAIN_NAME ));
                } else {
                    add_action( 'admin_notices', function () {
                        $content = '<p><b>';
                        $content .= __('The Customify theme installed successfully.', LOYMAX_WEB_APP_DOMAIN_NAME );
                        $content .= '</b></p>';
                        LoymaxWebApp_Plugin::show_notice(false, $content);
                    } );
                }
            }

            $this->activate_theme();
        } catch ( Exception $e ) {
            add_action( 'admin_notices', function() {
                $href = esc_url( admin_url( 'update.php?action=install-theme&theme=customify&_wpnonce=' . wp_create_nonce( 'install-theme_customify' ) ) );
                $content = '<p><b>';
                $content .= __('An error occurred while installing the Customify theme.', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= '</b></p><p>';
                $content .= __('To install it manually', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= ' <a href="' . $href . '">';
                $content .= __('click here', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= '</a></p>';
                LoymaxWebApp_Plugin::show_notice(true, $content);
            });
        }
    }

    private function move_sidebar_to_left() {
        $theme_mods_customify = get_option('theme_mods_customify');
        $theme_mods_customify['page_sidebar_layout'] = 'sidebar-content';
        $theme_mods_customify['sidebar_layout'] = 'sidebar-content';
        $theme_mods_customify['posts_sidebar_layout'] = 'sidebar-content';
        $theme_mods_customify['posts_archives_sidebar_layout'] = 'sidebar-content';
        $theme_mods_customify['search_sidebar_layout'] = 'sidebar-content';
        $theme_mods_customify['404_sidebar_layout'] = 'sidebar-content';
        update_option('theme_mods_customify', $theme_mods_customify);
    }

    private function activate_theme() {
        switch_theme( $this->theme_slug );

        $theme = wp_get_theme( $this->theme_slug );
        $version = $theme['Version'];
        preg_match( '/^(\d+\.)?(\d+\.)/', $version, $found );
        $version = floatval( substr( $found[0], 0, -1 ) );
        if ( $version <= $this->stable_version ) {
            $this->move_sidebar_to_left();
        }
        add_action( 'admin_notices', function () {
            $content = '<p><b>';
            $content .= __('The Customify theme activated successfully.', LOYMAX_WEB_APP_DOMAIN_NAME );
            $content .= '</b></p>';
            LoymaxWebApp_Plugin::show_notice( false, $content );
        } );
    }
}
