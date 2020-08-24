<?php

class LoymaxWebApp_settings_page {
    public function __construct() {}

    private function show_header() {
        ?>
        <header class="lmx-settings__header">
            <div class="lmx-settings__logo-container">
                <p class="lmx-logo">
                    <a href="https://loymax.ru/" target="_blank">
                        <img src="<?php echo esc_url( untrailingslashit( plugins_url( '/', LOYMAX_WEB_APP_PLUGIN_FILE ) ) ); ?>/public/images/logo/loymax.svg"
                             alt="Loymax" />
                    </a>
                </p>
            </div>
            <div class="lmx-navigation-bar">
                <div class="lmx-navigation__container">
                    <button class="lmx-mobile-nav-trigger"><span>Modules</span><i class="monstericon-arrow"></i>
                    </button>
                    <nav class="lmx-navigation__items">
                        <a id="lmx-nav-modules" href="#/" class="lmx-navigation-tab-link" onclick="changeTab('')"><?php _e('Modules', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                        <a id="lmx-nav-configs" href="#/configs" class="lmx-navigation-tab-link" onclick="changeTab('configs')"><?php _e('Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                        <a id="lmx-nav-api" href="#/api" class="lmx-navigation-tab-link" onclick="changeTab('api')"><?php _e('API', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                        <a id="lmx-nav-theme" href="#/theme" class="lmx-navigation-tab-link" onclick="changeTab('theme')"><?php _e('Theme', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                        <a id="lmx-nav-user-portal" href="#/user-portal" class="lmx-navigation-tab-link" onclick="changeTab('user-portal')"><?php _e('Personal Account', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                    </nav>
                </div>
            </div>
        </header>
        <?php
    }

    private function show_main_container() {
        ?>
        <main class="lmx-settings__card loymax-upsell" id="lmx-settings-main-container">
            <div id="lmx-userportal-config" class="loymax lmx-config-tab lmx-config">
                <?php
                require_once( plugin_dir_path( __FILE__ ) . 'loymax-userportal-config.php');
                $element = new LoymaxWebApp_userportal_config();
                $element->show();
                ?>
            </div>
            <div id="lmx-common-config" class="loymax lmx-config-tab lmx-config">
                <h1 class="lmx-tab-header"><?php _e('Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1>
                <?php
                require_once( plugin_dir_path( __FILE__ ) . 'loymax-common-config.php');
                $element = new LoymaxWebApp_common_config();
                $element->show();
                ?>
            </div>
            <div id="lmx-set-api" class="loymax lmx-config-tab lmx-config">
                <?php
                require_once( plugin_dir_path( __FILE__ ) . 'loymax-set-api.php');
                $element = new LoymaxWebApp_set_api();
                $element->show();
                ?>
            </div>
            <div id="lmx-customify-theme-installer" class="loymax lmx-config-tab lmx-config">
                <?php
                require_once( plugin_dir_path( __FILE__ ) . 'loymax-customify-theme-installer.php');
                $element = new LoymaxWebApp_customify_theme_installer();
                $element->show();
                ?>
            </div>
            <div id="lmx-user-portal" class="loymax lmx-config-tab lmx-config">
                <?php
                require_once( plugin_dir_path( __FILE__ ) . 'loymax-userportal-page.php');
                $element = new LoymaxWebApp_userportal_page();
                $element->show_user_portal_page_info();
                ?>
            </div>
            <script type="text/javascript">
                changeTab();
            </script>
            <p class="lmx-documentation-link">
                <?php _e('Read more about Personal Account configuration in the documentation.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                <br>
                <a class="lmx-link" href="https://cdn.loymax.tech/js/v2.2/docs/index.html" target="_blank"><?php _e('Click here', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a> <?php _e('to go to the documentation.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
            </p>
        </main>
        <?php
    }

    public function show() {
        ?>
        <div class="lmx-settings">
            <?php
            $this->show_header();
            $this->show_main_container();
            ?>
        </div>
        <?php
    }
}
