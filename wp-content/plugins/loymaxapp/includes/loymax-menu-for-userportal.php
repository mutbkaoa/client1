<?php
class LoymaxWebApp_menu_for_userportal {
    private $configs_default;

    public function __construct() {
        require_once( plugin_dir_path( __FILE__ ) . '/../admin/loymax-config.php');
        $this->configs_default = new LoymaxWebApp_configs();
    }

    public function create_menu_for_page( ) {
        global $wpdb;
        $page_link = get_option( 'loymax-page-link' );
        $page_title = get_option( 'loymax-page-title' );
        if ( !is_null( get_option( 'loymax-navigation-menu-id' ) ) ) {
            wp_delete_nav_menu( get_option( 'loymax-navigation-menu-id' ) );
        } elseif ( wp_get_nav_menu_object( $page_title ) ) {
            wp_delete_nav_menu( $page_title );
        }

        require_once( plugin_dir_path( __FILE__ ) . 'loymax-userportal-config.php' );
        $components = LoymaxWebApp_userportal_config::get_components();

        $navigation_menu_ID = wp_create_nav_menu( $page_title );
        update_option( 'loymax-navigation-menu-id', $navigation_menu_ID );

        $menu_items = $this->get_full_items( $components );

        usort( $menu_items, function ( $a, $b ) {
            if ( is_null( $a['order'] ) && !is_null( $b['order'] ) ) {
                return 1;
            }
            if ( !is_null( $a['order'] ) && is_null( $b['order'] ) ) {
                return -1;
            }
            if ( is_null( $a['order'] ) && is_null( $b['order'] ) ) {
                return 0;
            }
            if ( $a['order'] == $b['order'] ) {
                return 0;
            }
            return ( $a['order'] < $b['order'] ) ? -1 : 1;
        } );

        for ( $i = 0; $i < count( $menu_items ); ++$i ) {
            $item = $menu_items[ $i ];
            $classes = $this->get_menu_item_classes( $item );

            $menu_item_id = wp_update_nav_menu_item( $navigation_menu_ID, 0, array(
                'menu-item-title' => $item['name'],
                'menu-item-classes' => 'loymax-menu-item ' . $classes,
                'menu-item-url' => $page_link . '#/' . $item['href'],
                'menu-item-status' => 'publish',
                'menu-item-position' => $i + 1,
            ) );
            if ( $item['href'] == 'personal') {
                update_option( 'loymax-personal-menu-item-id', $menu_item_id );
            }
            $wpdb->update(
                LOYMAX_WEB_APP_COMPONENT_TABLE_NAME,
                array( 'menu_item_id' => $menu_item_id ),
                array( 'c_key' => $item['component'] )
            );
        }
        delete_option( 'loymax-component-order' );
    }

    public function update_menu_name($name) {
        $menu_id = get_option( 'loymax-navigation-menu-id' );
        if ( is_null( $menu_id  ) ) {
            $menu_id = wp_create_nav_menu( get_option( 'loymax-page-title' ) );
            update_option( 'loymax-navigation-menu-id', $menu_id );
        }
        wp_update_nav_menu_object($menu_id, array('menu-name' => $name));
    }

    public function update_menu_item_links() {
        $this->update_menu();
    }

    public function update_visible_menu_items() {
        $this->update_menu();
    }

    public function update_menu_order( $updated_order ) {
        $this->update_menu( $updated_order );
    }

    private function get_menu_item_classes( $item ) {
        $classes = '';
        if ( $item['authed_only'] ) {
            $classes = 'lmx-show-when-authed';
        } elseif ( $item['not_authed_only'] ) {
            $classes = 'lmx-hide-when-authed';
        }

        if ( !is_null( $item['selected'] ) && !$item['selected'] ) {
            $classes .= ' lmx-hidden';
        }
        $classes .= " " . $item['href'];
        return $classes;
    }

    private function update_menu( $updated_order = null ) {
        $page_link = get_option( 'loymax-page-link' );
        $menu_id = get_option( 'loymax-navigation-menu-id' );
        $menu_items = wp_get_nav_menu_items( $menu_id );

        $menu_items_dictionary = array();
        foreach ( $menu_items as $item ) {
            $menu_items_dictionary[ $item->db_id ] = $item;
        }

        require_once( plugin_dir_path( __FILE__ ) . 'loymax-userportal-config.php' );
        $components = LoymaxWebApp_userportal_config::get_components();

        if ( !is_null( $updated_order ) ) {
            foreach ( $components as $component => $config ) {
                $config->menu_order = $updated_order[ $component ]['menu_order'];
            }
        }

        $full_items = $this->get_full_items( $components );

        foreach ( $full_items as $item ) {
            $wp_menu_item = $menu_items_dictionary[ $item['menu_item_id'] ];
            if ( is_null( $item[ 'component' ] ) ) {
                if ( $item['href'] === 'personal' ) {
                    $page_link = get_option( 'loymax-page-link' );
                    $order = $wp_menu_item->menu_order;
                    $url = $page_link . '#/' . $item['href'];
                } else {
                    continue;
                }
            } else {
                $url = $page_link . substr( $wp_menu_item->url, strpos( $wp_menu_item->url, "#" ) );
                $order = $item[ 'order' ];
            }

            $classes = $this->get_menu_item_classes( $item );
            wp_update_nav_menu_item( $menu_id, $item['menu_item_id'], array(
                'menu-item-title' => $wp_menu_item->post_title,
                'menu-item-classes' => 'loymax-menu-item ' . $classes,
                'menu-item-url' => $url,
                'menu-item-status' => 'publish',
                'menu-item-position' => $order,
            ) );
        }
    }

    private function get_full_items( $components ) {
        $menu_items = array();
        foreach ( $this->configs_default->loymax_navigation_items_defaults as $item ) {
            if ( !is_null( $item['component'] ) ) {
                $item['order'] = $components[ $item['component'] ]->menu_order;
                $item['selected'] = $components[ $item['component'] ]->selected;
                if ( !is_null( $components[ $item['component'] ]->menu_item_id ) ) {
                    $item['menu_item_id'] = $components[ $item['component'] ]->menu_item_id;
                }
            } elseif ( $item['href'] === 'personal' ) {
                $item['menu_item_id'] = get_option( 'loymax-personal-menu-item-id');
            }
            $menu_items[] = $item;
        }

        return $menu_items;
    }
}
