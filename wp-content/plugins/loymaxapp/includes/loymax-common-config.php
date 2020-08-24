<?php

class LoymaxWebApp_common_config {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    private function get_configs() {
        $configs_SQL = 'SELECT config_key, config_name, config_value, config_type, is_option, component FROM ' . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME;
        $configs_SQL .= ' WHERE component IS NULL AND is_option = TRUE';
        $configs = $this->wpdb->get_results( $configs_SQL, OBJECT_K );

        foreach ( $configs as $key => $config ) {
            if ( $config->config_type == 'bool' ) {
                $configs[ $key ]->config_value = filter_var( $config->config_value, FILTER_VALIDATE_BOOLEAN );
            }
        }

        return $configs;
    }

    public static function generate_options_html_table($configs) {
        $hidden_configs = array( 'requestUserAttributes' );
        ?>
        <table class="widefat striped lmx-settings-table">
            <?php
            foreach ( $configs as $config ):
                $type = ( $config->config_type == 'bool' ) ? 'checkbox' : $config->config_type;
                $config_key = esc_html( $config->config_key );
                $config_name = esc_html__( $config->config_name, LOYMAX_WEB_APP_DOMAIN_NAME );
                $config_value = esc_html( $config->config_value );
                if ( in_array( $config->config_key, $hidden_configs ) ) {
                    continue;
                }
                ?>
                <tr id="<?= $config_key ?>_row">
                    <td colspan="<?= ( $type == 'checkbox' ) ? 2 : 1 ?>" class="lmx-setting-td-label"><label for="<?= $config_key ?>"><?= $config_name ?></label></td>
                    <td
                    <?php
                        if ( $type == 'checkbox' ) {
                            echo 'class="lmx-td-checkbox"';
                        } else {
                            echo 'colspan="2"';
                        }
                    ?>
                    >
                        <?php if ( $type == 'checkbox' ): ?>
                            <input type="hidden"
                                   id="<?= $config_key ?>_hidden"
                                   name="<?= $config_key ?>"
                                   value="false"
                            />
                        <?php endif; ?>

                        <?php if ( $type == 'object' ): ?>
                            <textarea id="<?= $config_key ?>"
                                      name="<?= $config_key ?>"
                            >
                            <?= $config_value ?>
                        </textarea>
                        <?php else: ?>
                            <input type="<?= esc_html( $type ) ?>"
                                   id="<?= $config_key ?>"
                                   name="<?= $config_key ?>"
                                   value="<?= ( ( $type == 'checkbox' ) ? 'true' : $config_value ) ?>"
                                <?php
                                if ( $type == 'checkbox' && $config_value == true ) {
                                    echo 'checked';
                                }
                                ?>
                            />
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach;?>
        </table>
        <?php
    }

    public function show($is_installation = false) {
        ?>
        <form id="loymax-configs" name="loymax-configs" method="post" enctype="multipart/form-data" class="lmx-common-config">
            <input type="hidden" name="lmx-action" value="update-config">
            <?php
            $configs = $this->get_configs();
            $this->generate_options_html_table($configs); ?>
            <div class="lmx__button-container">
                <?php
                if ( $is_installation ) {
                    ?>
                    <button class="lmx-button lmx-button--white" type="button" onclick="window.location = '<?= LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) ?>'">
                        <?php _e('Skip This Step', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                    </button>
                    <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                    <?php
                } else {
                    ?>
                    <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Save', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                    <?php
                }
                ?>
            </div>
        </form>
        <?php
    }
}
