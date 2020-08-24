<?php

class LoymaxWebApp_updater {
    private $wpdb;
    private $jal_db_version;

    private $installed_version;
    private $configs_default;
    private $is_transaction_started;
    private $wpdb_error;

    public function __construct( $file ) {
        global $wpdb;
        $wpdb->hide_errors();
        $this->wpdb = $wpdb;
        $this->jal_db_version = '3';
        require_once( plugin_dir_path( __FILE__ ) . '../admin/loymax-config.php');
        $this->configs_default = new LoymaxWebApp_configs();
        $this->installed_version = floatval( get_option( 'jal_db_version' ) );

        $this->is_transaction_started = false;
        $this->wpdb_error = '';

        $this->register_activation_hooks($file);
    }

    private function register_activation_hooks($file) {
        register_activation_hook( $file, array( $this, 'create_options_table' ) );
        register_activation_hook( $file, array( $this, 'create_components_table' ) );
        register_activation_hook( $file, array( $this, 'update_tables' ) );
        register_activation_hook( $file, array( $this, 'start_install' ) );
    }

    private function start_transaction() {
        if ( !$this->is_transaction_started ) {
            $this->is_transaction_started = true;
            $this->wpdb->query('START TRANSACTION');
        }
    }

    private function end_transaction($is_success) {
        if ( $this->is_transaction_started ) {
            if ( $is_success ) {
                $this->wpdb->query('COMMIT');
            } else {
                $this->wpdb_error = $this->wpdb->last_error;
                $this->wpdb->query('ROLLBACK');
            }
        }
        $this->is_transaction_started = false;
    }

    public function create_options_table() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $this->wpdb->get_charset_collate();
        $configs_SQL = "CREATE TABLE " . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME . " (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                config_key varchar(50) NOT NULL,
                config_name varchar(150) NOT NULL,
                config_value text DEFAULT '' NOT NULL,
                config_type varchar(20) DEFAULT 'text' NOT NULL,
                is_option tinyint(1) NOT NULL,
                component varchar(50) DEFAULT NULL NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY config_key (config_key)
            ) $charset_collate;";

        $create_configs_result = dbDelta( $configs_SQL );
        if ( count( $create_configs_result ) > 0 ) {
            $this->init_configs_table();
            add_option( 'jal_db_version', $this->jal_db_version );
        }
    }

    public function create_components_table() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $result = true;

        $charset_collate = $this->wpdb->get_charset_collate();
        $configs_SQL = "CREATE TABLE " . LOYMAX_WEB_APP_COMPONENT_TABLE_NAME . " (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                c_key varchar(50) NOT NULL,
                name varchar(50) NOT NULL,
                selected tinyint(1) NOT NULL,
                description varchar(250) NOT NULL,
                menu_item_id mediumint(9),
                is_paid tinyint(1),
                PRIMARY KEY  (id),
                UNIQUE KEY c_key (c_key)
            ) $charset_collate;";

        $create_configs_result = dbDelta( $configs_SQL );
        $result = $create_configs_result !== false;
        if ( count( $create_configs_result ) > 0 ) {
            return $this->init_components_table();
        }
        return $result;
    }

    private function init_configs_table() {
        foreach ( $this->configs_default->loymax_config_data_defaults as $key => $config ) {
            $this->wpdb->insert(
                LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
                array(
                    'config_key' => $key,
                    'config_name' => $config['name'],
                    'config_value' => $config['value'],
                    'config_type' => $config['type'],
                    'is_option' => $config['isOption'],
                    'component' => $config['component'],
                )
            );
        }
    }

    private function init_components_table() {
        $result = true;
        foreach ( $this->configs_default->loymax_components as $key => $config ) {
            if ( $result === false ) {
                break;
            }
            $result = $this->wpdb->insert(
                LOYMAX_WEB_APP_COMPONENT_TABLE_NAME,
                array(
                    'c_key' => $key,
                    'name' => $config['name'],
                    'selected' => $config['selected'],
                    'description' => $config['description'],
                    'is_paid' => $config['isPaid'],
                )
            );
        }
        $result = $result !== false;
        return $result;
    }

    public function update_tables() {
        if ( $this->installed_version === floatval( $this->jal_db_version ) ) {
            return;
        }
        $this->update();
    }

    public function start_install() {
        add_option( 'loymax_install_wizard_in_progress', true );
    }

    public function update() {
        if ( get_option( 'loymax_update_error' ) !== null ) {
            delete_option( 'loymax_update_error' );
        }
        $result = true;
        if ( $this->installed_version < 2.2 ) {
            $result = $this->update_to_version( '2.2' );
            if ( $result !== false ) {
                $this->installed_version = 2.2;
            }
        }

        if ( $result !== false && $this->installed_version === 2.2 ) {
            $result = $this->update_to_version( '2.3' );
            if ( $result !== false ) {
                $this->installed_version = 2.3;
            }
        }

        if ( $result !== false && $this->installed_version === 2.3 ) {
            $result = $this->update_to_version( '2.4' );
            if ( $result !== false ) {
                $this->installed_version = 2.4;
            }
        }

        if ( $result !== false && $this->installed_version === 2.4 ) {
            $result = $this->update_to_version( '2.5' );
            if ( $result !== false ) {
                $this->installed_version = 2.5;
            }
        }

        if ( $result !== false && $this->installed_version === 2.5 ) {
            $result = $this->update_to_version( '2.6' );
            if ( $result !== false ) {
                $this->installed_version = 2.6;
            }
        }

        if ( $result !== false && $this->installed_version === 2.6 ) {
            $result = $this->update_to_version( '2.7' );
            if ( $result !== false ) {
                $this->installed_version = 2.7;
            }
        }

        if ( $result !== false && $this->installed_version === 2.7 ) {
            $result = $this->update_to_version( '2.8' );
            if ( $result !== false ) {
                $this->installed_version = 2.8;
            }
        }
        if ( $result !== false && $this->installed_version === 2.8 ) {
            $result = $this->update_to_version( '2.9' );
            if ( $result !== false ) {
                $this->installed_version = 2.9;
            }
        }

        if ( $result !== false && $this->installed_version === 2.9 ) {
            $this->wpdb->query("ALTER TABLE " .  LOYMAX_WEB_APP_CONFIGS_TABLE_NAME . " ADD COLUMN component varchar(50) DEFAULT NULL NULL AFTER `is_option`");
            $this->start_transaction();
            $result = $this->create_components_table();
            if ($result !== false) {
                $result = $this->update_configs_table();
            }
            if ($result !== false) {
                $result = $this->update_to_version('3');
                if ( $result !== false ) {
                    $this->update_redirect_options();
                }
             }
            if ( $result !== false ) {
                $this->installed_version = 3;
            }
            $this->end_transaction($result);
        }

        if ( $result === false ) {
            add_option('loymax_update_error', $this->wpdb_error);
            add_action('admin_notices', function () {
                $screen = get_current_screen();
                if ($screen->id != 'toplevel_page_loymax-plugin') {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e('An error occurred while updating LoymaxWebApp. Go to', LOYMAX_WEB_APP_DOMAIN_NAME) ?> <a
                                href="<?= admin_url('admin.php?page=loymax-plugin') ?>"><?php _e('settings', LOYMAX_WEB_APP_DOMAIN_NAME) ?></a> <?php _e('for details.', LOYMAX_WEB_APP_DOMAIN_NAME) ?></p>
                    </div>
                    <?php
                }
            });
        }

        $this->jal_db_version = $this->installed_version;
        update_option( 'jal_db_version', $this->installed_version );
    }

    private function update_to_version( $new_version ) {
        $result = true;

	    /**
	     * Delete deprecated fields
	     */
	    $deletedFields = $this->configs_default->loymax_deleted_in_version[ (string) $new_version ];
	    if ( is_array($deletedFields) && count($deletedFields) > 0 ) {
		    foreach ( $deletedFields as $deprecated_field ) {
			    if ( $result === false ) {
				    break;
			    }
			    $this->start_transaction();
			    $result = $this->wpdb->delete(
                    LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
				    array( 'config_key' => $deprecated_field )
			    );
		    }
	    }

	    /**
	     * Add new fields
	     */
	    $addedFields = $this->configs_default->loymax_new_in_version[ (string) $new_version ];
	    if ( is_array($addedFields) && count($addedFields) > 0 ) {
		    foreach ( $addedFields as $new_field ) {
			    if ( $result === false ) {
				    break;
			    }
			    $this->start_transaction();
			    $config = $this->configs_default->loymax_config_data_defaults[$new_field];
                $component = $config['component'] ? $config['component'] : NULL;

                $params = array(
                    'config_key' => $new_field,
                    'config_name' => $config['name'],
                    'config_value' => $config['value'],
                    'config_type' => $config['type'],
                    'is_option' => $config['isOption'],
                );
                if ( $component !== NULL ) {
                    $params[ 'component' ] = $component;
                }
			    $result = $this->wpdb->replace( LOYMAX_WEB_APP_CONFIGS_TABLE_NAME, $params );
		    }
	    }

        $result = $result !== false;

        $this->end_transaction($result);
        return $result;
    }

    private function update_configs_table() {
        $result = true;
        foreach ( $this->configs_default->loymax_config_data_defaults as $key => $config_value ) {
            $component = ( !$config_value['component'] ) ? ' SET component=NULL,' : ' SET component="' . $config_value['component'] . '",';
            $result = $this->wpdb->query(
                    'UPDATE ' . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME
                    . $component
                    . ' config_name="' . $config_value['name']
                    . '" WHERE config_key="' . $key . '"'
            );
        }
        $result = $result !== false;
        return $result;
    }

    private function update_redirect_options() {
        $redirect_URLs_options = LoymaxWebApp_Plugin::get_configs( true );
        foreach ( $redirect_URLs_options as $redirect_URL ) {
            $this->wpdb->update(
                LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
                array( 'config_value' => substr( $redirect_URL->config_value, strpos( $redirect_URL->config_value, "#" ) ) ),
                array( 'config_key' => $redirect_URL->config_key )
            );
        }
    }
}
