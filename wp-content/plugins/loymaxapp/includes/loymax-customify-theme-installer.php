<?php

class LoymaxWebApp_customify_theme_installer {
    public static function show($is_installation = false) {
        $theme_slug = 'customify';

        $current_theme = wp_get_theme();
        $is_current_theme_customify = $current_theme->get( 'Name' ) === 'Customify';

        $theme = wp_get_theme( $theme_slug );
        $is_theme_install = $theme->exists();

        $text_primary_btn = '';
        $text_theme = $is_current_theme_customify ? __('You have activated the <strong>Customify</strong> theme.', LOYMAX_WEB_APP_DOMAIN_NAME ) : __('We recommend using the <strong>Customify</strong> theme.', LOYMAX_WEB_APP_DOMAIN_NAME );
        if ( !$is_theme_install ) {
            $text_primary_btn = ( $is_installation ? __('Install and Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) : __('Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) );
        } elseif ( !$is_current_theme_customify ) {
            $text_primary_btn = ( $is_installation ? __('Activate and Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) : __('Activate', LOYMAX_WEB_APP_DOMAIN_NAME ) );
        }

        if ($is_installation) {
            require_once( plugin_dir_path( __FILE__ ) . 'loymax-install-wizard.php');
        } else {
            ?> <h1 class="lmx-tab-header"><?php _e('Theme', LOYMAX_WEB_APP_DOMAIN_NAME) ?></h1> <?php
        }
        ?>
        <form id="lmx-customify" name="lmx-customify" method="post" enctype="multipart/form-data">
            <input id="install-customify" name="lmx-action" type="hidden" value="install-customify">
            <div class="lmx-customify-text">
                <p><?= $text_theme ?></p>
                <p><?php _e('This theme enables you to configure flexibly the appearance of various elements of the site interface according to your requirements.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            </div>
            <?php
            if ($is_installation) {
            ?>
            <div class="lmx__button-container">
                <?php
                if ($is_theme_install && $is_current_theme_customify) {
                    ?>
                    <button class="lmx-button lmx-button--blue" type="button" onclick="window.location = '<?= LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) ?>'">
                        <?php _e('Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                    </button>
                    <?php
                } else {
                    ?>
                    <button class="lmx-button lmx-button--white" type="button" onclick="window.location = '<?= LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) ?>'">
                        <?php _e('Skip This Step', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                    </button>
                    <button class="lmx-button lmx-button--blue" type="submit"><?= $text_primary_btn ?></button>
                    <?php
                }
                ?>
            </div>
            <?php
            } else {
                ?>
                <div class="lmx__button-container">
                    <?php
                    if ( !$is_theme_install ) {
                        ?>
                        <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Install', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                        <?php
                    } elseif ( !$is_current_theme_customify ) {
                        ?>
                        <button class="lmx-button lmx-button--blue" type="submit"><?php _e('Activate', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </form>
        <?php
    }
}
