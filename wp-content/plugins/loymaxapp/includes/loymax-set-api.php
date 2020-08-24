<?php

class LoymaxWebApp_set_api {
    public static function show($is_installation = false) {
        global $wpdb;
        $configs_SQL = 'SELECT config_key, config_name, config_value, config_type, is_option FROM ' . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME;
        $configs_SQL .= ' WHERE config_key = "apiHost"';
        $old_api_url = $wpdb->get_results( $configs_SQL, OBJECT_K )['apiHost']->config_value;
        if ( !$is_installation ) {
            ?> <h1 class="lmx-tab-header"><?php _e('Loymax API Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
        } else {
            ?> <p><?php _e('Loymax API address', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p> <?php
        }
        ?>
        <form id="loymax-set-api" name="loymax-set-api" method="post" enctype="multipart/form-data">
            <input id="set-api" name="lmx-action" type="hidden" value="set-api">
            <input id="use-demo" name="use-demo" type="hidden" value="0">
            <input type="text"
                   id="api-host-input"
                   name="api-host-input"
                   value="<?= $old_api_url ?>"
                   class="lmx__input--text"
            />
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

    public static function save_api() {
        global $wpdb;
        $url = sanitize_text_field($_POST['api-host-input']);
        $wpdb->update(
            LOYMAX_WEB_APP_CONFIGS_TABLE_NAME,
            array( 'config_value' => wp_unslash( esc_html( $url ) ) ),
            array( 'config_key' => 'apiHost' )
        );
    }
}
