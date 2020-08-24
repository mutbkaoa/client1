<?php
class LoymaxWebApp_install_wizard {
    static $steps = array(
            'start' => array(
                'title' => "Let's start?",
            ),
            'theme' => array(
                'title' => 'Theme Setting',
            ),
            'components' => array(
                'title' => 'Section Settings',
            ),
            'common' => array(
                'title' => 'Settings',
            ),
            'api' => array(
                'title' => 'API Settings',
            ),
            'page' => array(
                'title' => 'Page Placement',
            ),
            'finished' => array(
                'title' => 'Installation Complete',
            ),
        );
    private $current_step;

    public function __construct( $current_step ) {
        $this->current_step = $current_step;

        add_action( 'admin_init', array( $this, 'show' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    public function enqueue_scripts() {
        wp_enqueue_style( 'loymax-style', plugins_url( 'admin/loymax-plugin-styles.css', LOYMAX_WEB_APP_PLUGIN_FILE ) );
        wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.min.css' );
        wp_enqueue_style( 'buttons', false );
        wp_enqueue_style( 'list-tables', false );
        wp_enqueue_style( 'wp-admin', false );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js' );
        wp_enqueue_script( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js' );
        wp_enqueue_script( 'loymax-admin-js', plugins_url( 'admin/admin.js', LOYMAX_WEB_APP_PLUGIN_FILE ), array(), md5( microtime() ), false );
        wp_print_scripts();
    }

    private function show_start_page() {
        ?>
        <h1><?php _e('Welcome to the Personal Account Installation Wizard!', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1>
        <div>
            <p><?php _e('Thanks for choosing the LoymaxWebApp plugin.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            <p><?php _e('The Installation Wizard will help you to create a Personal Account page for your site, as well as to quickly configure its design and the necessary modules.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            <p><?php _e('If you do not want to configure Personal Account, you can close the Installation Wizard and return to the WordPress toolbar. The Installation Wizard can be started later.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            <p><?php _e('After installing the Personal Account, its settings can be changed at any time in the <b>Loymax&nbsp;→&nbsp;Settings</b> section.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
        </div>
        <div class="lmx__button-container">
            <button class="lmx-button lmx-button--white" type="button" onclick="window.location = '<?= add_query_arg( 'step', 'exit' ) ?>'"><?php _e('Skip This Step', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
            <button class="lmx-button lmx-button--blue" type="button" onclick="window.location = '<?= add_query_arg( 'step', 'theme' ) ?>'"><?php _e('Install', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
        </div>
        <?php
    }

    private function show_finished_page() {
        ?>
        <h1><?php _e('Installation and Configuration of the Personal&nbsp;Account Are Complete', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1>
        <div>
            <p><?php _e('Thanks for choosing the LoymaxWebApp plugin!', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            <p><?php _e('At any time, you can change your account settings in the <b>Loymax&nbsp;→&nbsp;Settings</b> section', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
        </div>
        <div class="lmx__button-container">
            <button class="lmx-button lmx-button--blue" type="button" onclick="window.location = '<?= add_query_arg( 'step', 'exit' ) ?>'"><?php _e('Finish', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
        </div>
        <?php
    }

    private function show_next_step() {
        switch ($this->current_step) {
            case 'start':
                $this->show_start_page();
                break;
            case 'theme':
                ?> <h1><?php _e('Theme Setting', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
                require_once(plugin_dir_path(__FILE__) . 'loymax-customify-theme-installer.php');
                LoymaxWebApp_customify_theme_installer::show(true);
                break;
            case 'components':
                ?> <h1><?php _e('Section Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
                require_once(plugin_dir_path(__FILE__) . 'loymax-userportal-config.php');
                $new_config = new LoymaxWebApp_userportal_config();
                $new_config->show(true);
                break;
            case 'common':
                ?> <h1><?php _e('Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
                require_once(plugin_dir_path(__FILE__) . 'loymax-common-config.php');
                $new_common_config = new LoymaxWebApp_common_config();
                $new_common_config->show(true);
                break;
            case 'api':
                ?> <h1><?php _e('API Settings', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
                require_once(plugin_dir_path(__FILE__) . 'loymax-set-api.php');
                LoymaxWebApp_set_api::show(true);
                break;
            case 'page':
                ?> <h1><?php _e('Page Placement', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1> <?php
                require_once(plugin_dir_path(__FILE__) . 'loymax-userportal-page.php');
                LoymaxWebApp_userportal_page::show();
                break;
            case 'finished':
                delete_option('loymax_install_wizard_in_progress');
                $this->show_finished_page();
                break;
        }
    }

    static function get_next_step_link( $step = '' ) {
        if ( ! $step ) {
            $step = 'start';
        }

        $keys = array_keys( LoymaxWebApp_install_wizard::$steps );
        if ( end( $keys ) === $step ) {
            return admin_url();
        }

        $step_index = array_search( $step, $keys, true );
        if ( false === $step_index ) {
            return '';
        }

        return add_query_arg( 'step', $keys[ $step_index + 1 ] );
    }

    public function show() {
        if ( $this->current_step === 'exit' ) {
            if ( get_option( 'loymax_install_wizard_in_progress' ) ) {
                wp_redirect( admin_url( 'plugins.php' ) );
            } else {
                wp_redirect( admin_url( 'admin.php?page=loymax-plugin' ) );
            }
            exit();
        }
        $this->setup_wizard_header();
        ?><div class="lmx-install-wizard__card-content"><?php $this->show_next_step();?></div><?php
        $this->setup_wizard_footer();
        exit();
    }

    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <meta name="viewport" content="width=device-width" />
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title><?php _e('LoymaxWebApp Setup Wizard', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></title>
                <?php
                do_action( 'admin_enqueue_scripts' );
                do_action( 'admin_print_styles' );
                do_action( 'admin_head' );
                ?>
            </head>
            <body class="wp-core-ui lmx lmx-install-wizard">
                <div class="lmx-install-wizard__card">
                    <div class="lmx-install-wizard__header">
                        <p class="lmx-logo">
                            <a href="https://loymax.ru/" target="_blank">
                                <img src="<?php echo esc_url( untrailingslashit( plugins_url( '/', LOYMAX_WEB_APP_PLUGIN_FILE ) ) ); ?>/public/images/logo/loymax.svg"
                                     alt="Loymax" />
                            </a>
                        </p>
                    </div>
                    <div class="lmx-install-wizard__body">
        <?php
    }

    public function setup_wizard_footer() {
        ?>
                    </div>
                </div>
        <?php $this->setup_wizard_steps(); ?>
            </body>
        </html>
        <?php
    }

    public function setup_wizard_steps() {
        $output_steps = LoymaxWebApp_install_wizard::$steps;
        ?>
        <ul class="lmx-steps-dots">
            <?php
            foreach ( $output_steps as $step_key => $step ) {
                $is_completed = array_search( $this->current_step, array_keys( LoymaxWebApp_install_wizard::$steps ), true ) > array_search( $step_key, array_keys( LoymaxWebApp_install_wizard::$steps ), true );

                if ( $step_key === $this->current_step ) {
                    ?>
                    <li class="lmx-step-dot-active" title="<?php echo esc_html__( $step[ 'title' ], LOYMAX_WEB_APP_DOMAIN_NAME  ); ?>">
                        <span><?= $step_key ?></span>
                    </li>
                    <?php
                } elseif ($is_completed) {
                    ?>
                    <li class="lmx-step-dot-active" title="<?php echo esc_html__( $step[ 'title' ], LOYMAX_WEB_APP_DOMAIN_NAME ); ?>">
                        <a href="<?php echo esc_url( add_query_arg( 'step', $step_key ) ); ?>"><?php echo esc_html__( $step[ 'title' ], LOYMAX_WEB_APP_DOMAIN_NAME ); ?></a>
                    </li>
                    <?php
                } else {
                    ?>
                    <li title="<?php echo esc_html__( $step[ 'title' ], LOYMAX_WEB_APP_DOMAIN_NAME ); ?>">
                        <span><?= $step_key ?></span>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
        <?php
    }
}
