<?php

class LoymaxWebApp_userportal_config {
    private $wpdb;


    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    private function get_component_config($component) {
        $configs_SQL = 'SELECT config_key, config_name, config_value, config_type, is_option, component FROM ' . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME;
        $configs_SQL .= ' WHERE component = "' . $component . '"';
        $configs_SQL .= ' AND config_key NOT IN (';

        require_once( plugin_dir_path( __FILE__ ) . 'loymax-smart-configurations.php');
        $smart_configs_names = "'" . implode("','", LoymaxWebApp_smart_configurations::get_smart_configs_names()) . "'";

        $configs_SQL .= $smart_configs_names . ')';

        $configs = $this->wpdb->get_results( $configs_SQL, OBJECT_K );

        foreach ( $configs as $key => $config ) {
            if ( $config->config_type == 'bool' ) {
                $configs[ $key ]->config_value = filter_var( $config->config_value, FILTER_VALIDATE_BOOLEAN );
            }
        }

        return $configs;
    }

    public static function get_components() {
        global $wpdb;
        $configs_SQL = 'SELECT c_key, name, selected, description, menu_item_id, is_paid FROM ' . LOYMAX_WEB_APP_COMPONENT_TABLE_NAME;

        $configs = $wpdb->get_results( $configs_SQL, OBJECT_K );

        foreach ( $configs as $key => $config ) {
            $configs[ $key ]->selected = filter_var( $config->selected, FILTER_VALIDATE_BOOLEAN );
            $configs[ $key ]->is_paid = filter_var( $config->is_paid, FILTER_VALIDATE_BOOLEAN );
        }

        self::add_order_to_components( $configs );

        return $configs;
    }

    public static function add_order_to_components($components) {
        if ( !get_option( 'loymax-navigation-menu-id' ) ) {
            if ( !get_option( 'loymax-component-order' ) ) {
                require_once( plugin_dir_path( __FILE__ ) . '/../admin/loymax-config.php');
                $configs_default = new LoymaxWebApp_configs();
                foreach ( $configs_default->loymax_components as $component => $config ) {
                    $components[ $component ]->menu_order = $config[ 'menu_order' ];
                }
            } else {
                $component_order = json_decode( get_option( 'loymax-component-order' ), true );
                foreach ( $components as $component => $config ) {
                    $config->menu_order = $component_order[ $component ][ 'menu_order' ];
                }
            }
        } else {
            $menu_id = get_option( 'loymax-navigation-menu-id' );
            $menu_items = wp_get_nav_menu_items( $menu_id );
            $menu_items_dictionary = array();
            foreach ( $menu_items as $item ) {
                $menu_items_dictionary[ $item->db_id ] = $item;
            }
            foreach ( $components as $component => $config ) {
                $config->menu_order = $menu_items_dictionary[ $config->menu_item_id ]->menu_order;
            }
        }
    }

    public function show($is_installation = false) {
        if ( !$is_installation ) {
            ?>
            <script type="text/javascript">
                jQuery(function($){
                    $('input.lmx-input-checkbox-component').change(function(event){
                        var data = {
                            'lmx-action': 'checked-element',
                            name: event.target.id,
                            value: event.target.checked
                        };
                        $.ajax({
                            type: 'POST',
                            data: $.param(data),
                        });
                    });
                });
            </script>
            <?php
        }
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('.lmx-btn-tooltip-right').tooltip({
                    placement: 'right',
                    template: '<div class="tooltip lmx-tooltip lmx-tooltip-right"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                });
                $('.lmx-btn-tooltip-left').tooltip({
                    placement: 'left',
                    template: '<div class="tooltip lmx-tooltip lmx-tooltip-left"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                });
            });

            function changeVisibleForm(element) {
                element.classList.toggle('lmx-hidden-form');
            }

            function updateComponentSelection(element) {
                var componentName = element.id;
                var hiddenInput = document.getElementById('hidden-input-' + componentName);
                if (hiddenInput) {
                    hiddenInput.value = element.checked;
                }
            }
        </script>
        <div class="lmx-settings-component">
            <script type="text/javascript">
                initSortable(<?= $is_installation ?>);
            </script>
            <?php
            if ($is_installation) {
            ?>
            <h3><?php _e('Mark the sections of the Personal Account you want to add and configure', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h3>
            <form id="loymax-all-options" name="loymax-all-options" method="post" enctype="multipart/form-data">
                <input type="hidden" id="hidden-input-orders" name="sortable">
            <?php
            } else {
                ?> <h1 class="lmx-tab-header"><?php _e('Modules', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
            }
            ?> <div id="loymax-sortable-container" class="lmx-sortable-container"> <?php

            $components = self::get_components();

            usort( $components, function ( $a, $b ) {
                if ( $a->menu_order == $b->menu_order ) {
                    return 0;
                }
                return ( $a->menu_order < $b->menu_order ) ? -1 : 1;
            } );

            foreach ($components as $config) {
                $component = $config->c_key;
                $configs = $this->get_component_config($component);
                ?>
                <div id="sortable-<?= $component ?>" class="lmx-sortable-component">
                    <div class="lmx-sortable-component-header lmx-hidden-form">
                    <?php
                    if ( $is_installation ) {
                        ?>
                            <input type="hidden" id="hidden-input-<?= $component ?>" name="component-<?= $component ?>" value="<?= $config->selected ? 'true' : 'false' ?>">
                        <?php
                    }
                    ?>
                        <span class="lmx-draggable-element wp-menu-image dashicons-before dashicons-menu-alt lmx-button--move" onmousedown="hideForm(this.parentElement)"></span>
                        <input class="lmx-input-checkbox-component"
                           id="<?= $component ?>"
                           type="checkbox"
                        <?= $config->selected ? 'checked' : '' ?>
                        <?php
                        if ( $is_installation ) {
                            ?> onchange="updateComponentSelection(this)" <?php
                        }
                        ?>
                        />
                        <h3 class="lmx-sortable-component-name"><?php _e( $config->name, LOYMAX_WEB_APP_DOMAIN_NAME ); ?></h3>
                    <?php
                    if ( $config->is_paid ) {
                        ?>
                        <span class="wp-menu-image dashicons-before lmx-btn-tooltip-right lmx-button--paid"
                              style='background-image: url("<?php echo esc_url( untrailingslashit( plugins_url( '/', LOYMAX_WEB_APP_PLUGIN_FILE ) ) ); ?>/public/images/paid.png")'
                              data-toggle="tooltip"
                              title="<?php _e( 'Use of paid modules must be agreed with the API provider', LOYMAX_WEB_APP_DOMAIN_NAME ); ?>"
                        ></span>
                        <?php
                    }
                    if (!empty($configs)) {
                        ?>
                        <span class="wp-menu-image dashicons-before dashicons-admin-generic lmx-button--settings"
                              onclick="changeVisibleForm(this.parentElement)"></span>
                        <?php
                    }
                    ?>
                        <span class="wp-menu-image dashicons-before dashicons-editor-help lmx-btn-tooltip-left lmx-button--help<?= empty($configs) ? ' lmx-margin-left--auto' : ''?>"
                              data-toggle="tooltip"
                              title="<?php _e( $config->description, LOYMAX_WEB_APP_DOMAIN_NAME ); ?>"
                        ></span>
                    </div>
                <?php
                if ( !empty($configs) ) {
                    if ( !$is_installation ) {
                        ?> <form class="lmx-options-table" name="loymax-options" method="post" enctype="multipart/form-data"> <?php
                    }
                    ?> <div class="lmx-settings-table-container"><?php
                    require_once( plugin_dir_path( __FILE__ ) . 'loymax-common-config.php' );
                    LoymaxWebApp_common_config::generate_options_html_table( $configs );
                    ?></div> <?php
                    if ( !$is_installation ) {
                        ?>
                        <div class="lmx__button-container">
                            <input type="hidden" name="lmx-action" value="update-config">
                            <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Save', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                        </div>
                        </form>
                        <?php
                    }
                }
                ?>
                </div>
                <?php
            }
            ?> </div>
            <?php

            if ($is_installation) {
            ?>
                <div class="lmx__button-container">
                    <button class="lmx-button lmx-button--white" type="button" onclick="window.location = '<?= LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) ?>'">
                        <?php _e('Skip This Step', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                    </button>
                    <input type="hidden" name="lmx-action" value="update-all-config">
                    <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                </div>
            </form>
            <?php
            } else {
                ?>
            </form>
                <?php
            }
            ?>
        </div>
        <?php
    }

    public function update_component_config() {
        $is_selected = $_POST['value'] === 'true' ? 1 : 0;
        $this->wpdb->update(
            LOYMAX_WEB_APP_COMPONENT_TABLE_NAME,
            array('selected' => $is_selected),
            array('c_key' => $_POST['name'])
        );
    }

    public function update_all_components_config() {
        foreach ( $_POST as $key => $config_value ) {
            if ( $key !== 'lmx-action') {
                // Если $key содержит "component-" - значит это поле в БД из таблицы компонентов
                if ( substr_count( $key, 'component-' ) !== 0) {
                    $splitName = explode("-", $key);
                    $is_selected = ($config_value === 'true');
                    $this->wpdb->update(
                        LOYMAX_WEB_APP_COMPONENT_TABLE_NAME,
                        array('selected' => $is_selected),
                        array('c_key' => $splitName[1])
                    );
                } elseif ($key === 'sortable' && $config_value !== null ) {
                    $this->update_menu_order(explode(",", $config_value));
                } else {
                    $this->wpdb->update(
                        LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
                        array( 'config_value' => wp_unslash( esc_html( $config_value ) ) ),
                        array( 'config_key' => $key )
                    );
                }
            }
        }
        add_action('admin_notices', function () {
            $content = '<p><b>';
            $content .= __('Settings updated!', LOYMAX_WEB_APP_DOMAIN_NAME );
            $content .= '</b></p>';
            LoymaxWebApp_Plugin::show_notice(false, $content);
        });
    }

    public function update_menu_order($orders) {
        $component_order = array();
        foreach ($orders as $index => $component) {
            $key = explode("-", $component)[1];
            $component_order[ $key ] = array( 'menu_order' => $index + 1 );
        }
        if ( !get_option( 'loymax-navigation-menu-id' ) ) {
            update_option( 'loymax-component-order', json_encode( $component_order ) );
        } else {
            require_once( plugin_dir_path( __FILE__ ) . 'loymax-menu-for-userportal.php');
            $menu = new LoymaxWebApp_menu_for_userportal();
            $menu->update_menu_order( $component_order );
        }
    }
}
