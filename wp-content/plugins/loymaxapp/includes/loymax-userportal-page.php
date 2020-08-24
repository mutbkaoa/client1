<?php

class LoymaxWebApp_userportal_page {
    private $user_ID;

    public function __construct() {
        global $user_ID;
        $this->user_ID = $user_ID;
    }

    public static function restore_trashed_page_modal() {
        ?>
        <script type="text/javascript">
            var publishedPageModal;

            jQuery(function () {
                publishedPageModal = jQuery('#loymax-publishedPage-form').dialog({
                    autoOpen: false,
                    width: 500,
                    height: 250,
                    modal: true,
                    draggable: false,
                    dialogClass: "lmx-dialog",
                });
            });

            function loymaxShowDeleteModal(pageName) {
                publishedPageModal.find('input#page-title-modal').val(pageName);
                publishedPageModal.find('#itemNameField').html('<strong>' + pageName + '</strong>');
                publishedPageModal.dialog('open');
            }
        </script>

        <div style="display: none;">
            <form class="lmx-dialog__card" id="loymax-publishedPage-form" name="loymax-publishedPage-form" method="post" enctype="multipart/form-data">
                <h1 class="lmx-dialog__header"><?php _e('Do you want to continue?', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1>
                <input id="loymax-published-page" type="hidden" name="lmx-action" value="published-page">
                <input id="page-title-modal" name="lmx-page-title" type="hidden" value="" />
                <div class="lmx-dialog__text">
                    <?php _e('The page', LOYMAX_WEB_APP_DOMAIN_NAME ) ?> <span id="itemNameField"></span> <?php _e('is in the Trash. If you continue, the page will be re-published.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                </div>
                <div class="lmx__button-container">
                    <button class="lmx-button lmx-button--white" type="button" onclick="publishedPageModal.dialog('close')"><?php _e('Cancel', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                    <button class="lmx-button lmx-button--blue" type="submit" autofocus><?php _e('Continue', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></button>
                </div>
            </form>
        </div>
        <?php
    }

    public static function show() {
        $is_post_trash = false;
        $page_name = '';
        $loymax_page_ID = get_option( 'loymax_page_ID' );
        if ($loymax_page_ID) {
            $page = get_post( $loymax_page_ID );
            $page_name = $page->post_title;
            if ($page->post_status === 'trash') {
                $is_post_trash = true;
                LoymaxWebApp_userportal_page::restore_trashed_page_modal();
            }
        }
        ?>
        <form id="loymax-generate-page" name="loymax-generate-page" method="post" enctype="multipart/form-data">
            <input id="generate-page" name="lmx-action" type="hidden" value="generate-page">
            <p><?php _e('Enter the name of the page on which the Personal Account will be placed.', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></p>
            <input id="page-title"
                   name="lmx-page-title"
                   type="text"
                   value="<?= $page_name ?>"
                   class="lmx__input--text"
            />
            <div class="lmx__button-container">
                <button class="lmx-button lmx-button--white" type="button" onclick="window.location = '<?= LoymaxWebApp_install_wizard::get_next_step_link( $_GET[ 'step' ] ) ?>'">
                    <?php _e('Skip This Step', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                </button>
                <button class="lmx-button lmx-button--blue"
                    <?php
                    if ($is_post_trash) {
                        ?> type="button" onclick="event.preventDefault(); loymaxShowDeleteModal(jQuery('#page-title').val());" <?php
                    } else {
                        ?> type="submit" <?php
                    }
                    ?>>
                    <?php _e('Create', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                </button>
            </div>
        </form>
        <?php
    }

    private function get_minified_user_page_html( $content ) {
        return preg_replace( '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s', '', $content );
    }

    public function generate_default_page($plugin) {
        if ($plugin->loymax_page_ID) {
            $this->regenerate_default_page($plugin);
            return;
        }

        $page_title = sanitize_text_field( $_POST['lmx-page-title'] );
        update_option( 'loymax-page-title', $page_title );
        $page = get_page_by_title( $page_title );

        if ( ! ( isset( $page->ID ) && $page->post_status === 'publish' ) ) {
            $default_page = array(
                'post_type' => 'page',
                'post_title' => $page_title,
                'post_content' => $this->get_minified_user_page_html( file_get_contents( __DIR__ . '/../admin/page.html' ) ),
                'post_status' => 'publish',
                'post_author' => $this->user_ID,
            );
            $created_page_ID = wp_insert_post( $default_page );

            if ( is_int( $created_page_ID ) && $created_page_ID > 0 ) {

                $created_page_link = wp_make_link_relative( get_page_link( $created_page_ID ) );
                update_option( 'loymax-page-link', $created_page_link );

                require_once( plugin_dir_path( __FILE__ ) . 'loymax-menu-for-userportal.php');
                $menu = new LoymaxWebApp_menu_for_userportal();
                $menu->create_menu_for_page();

                update_option( 'loymax_page_ID', $created_page_ID );
                $plugin->loymax_page_ID = $created_page_ID;

                add_action( 'admin_notices', function () {
                    $content = '<p><b>';
                    $content .= __('Page generated successfully.', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $content .= '</b><br>';
                    $content .= __('To navigate through the user\'s portal, a <b>menu</b> with the same name has been created <i>(<b>&laquo;Appearance&raquo; &rarr; &laquo;Menus&raquo;</b>)</i>.', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $content .= '<br>';
                    $content .= __('To display this menu, you must place it in a separate menu area <i>(<b>&laquo;Appearance&raquo; &rarr; &laquo;Menus&raquo; &rarr; &laquo;Manage Locations&raquo;</b>)</i> or in the&nbsp;<b>&laquo;Custom menu&raquo;</b> Widget<i>(<b>&laquo;Appearance&raquo; &rarr; &laquo;Widgets&raquo;</b>)</i>.', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $content .= '<br>';
                    $content .= __('Also, a <b><i>Loymax widget</i></b> has been added to display a panel of basic information about the portal user.', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $content .= '<br>';
                    $content .= __('To customize the display of this widget, it is required to place it in the desired widget area <i>(<b>&laquo;Appearance&raquo; &rarr; &laquo;Widgets&raquo;</b>)</i>.', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $content .= '</p>';
                    LoymaxWebApp_Plugin::show_notice(false, $content);
                } );
            }
        } else {
            add_action( 'admin_notices', function () {
                $content = '<p>';
                $content .= __('The title of the page has conflict with the existing page. Please select another one.', LOYMAX_WEB_APP_DOMAIN_NAME );
                $content .= '</p>';
                LoymaxWebApp_Plugin::show_notice(true, $content );
            } );
        }
    }

    private function regenerate_default_page($plugin) {
        $page_title = sanitize_text_field( $_POST['lmx-page-title'] );
        update_option( 'loymax-page-title', $page_title );
        $default_page = array(
            'ID' => $plugin->loymax_page_ID,
            'post_title' => $page_title,
            'post_content' => $this->get_minified_user_page_html( file_get_contents( __DIR__ . '/../admin/page.html' ) ),
        );

        wp_update_post( $default_page );

        require_once( plugin_dir_path( __FILE__ ) . 'loymax-menu-for-userportal.php');
        $menu = new LoymaxWebApp_menu_for_userportal();
        $menu->update_menu_name( $page_title );

        add_action( 'admin_notices', function () {
            $content = '<p><b>';
            $content .= __('Page successfully re-generated', LOYMAX_WEB_APP_DOMAIN_NAME );;
            $content .= '</b></p>';
            LoymaxWebApp_Plugin::show_notice(false, $content );
        } );
    }

    public function return_to_publish($loymax_page_ID) {
        $default_page = array(
            'ID' => $loymax_page_ID,
            'post_status' => 'publish'
        );

        wp_update_post( $default_page );
    }

    public function show_user_portal_page_info() {
        $is_post_trash = false;
        $page_name = '';
        $post_status = __('Not created', LOYMAX_WEB_APP_DOMAIN_NAME );
        $page_url = '';
        $loymax_page_ID = get_option( 'loymax_page_ID' );
        if ($loymax_page_ID) {
            $page = get_post( $loymax_page_ID );
            $page_name = $page->post_title;
            $page_url = get_permalink($loymax_page_ID);
            switch ( $page->post_status ) {
                case 'trash':
                    $post_status = __('In trash', LOYMAX_WEB_APP_DOMAIN_NAME );
                    $is_post_trash = true;
                    require_once( plugin_dir_path( __FILE__ ) . 'loymax-userportal-page.php');
                    LoymaxWebApp_userportal_page::restore_trashed_page_modal();
                    break;
                case 'publish':
                    $post_status = __('Published', LOYMAX_WEB_APP_DOMAIN_NAME );
                    break;
                default:
                    $post_status = __('Not published', LOYMAX_WEB_APP_DOMAIN_NAME );
                    break;
            }
        }
        ?>
        <div class="loymax-user-portal-page">
            <h1 class="lmx-tab-header"><?php _e('Status of the Personal Account', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></h1>
            <form id="loymax-generate-page" name="loymax-generate-page" method="post" enctype="multipart/form-data">
                <input id="generate-page" name="lmx-action" type="hidden" value="generate-page">
                <table class="widefat striped lmx-user-page-table">
                    <tr>
                        <td class="lmx-user-portal-page-td-label"><?php _e('Page with Personal Account', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></td>
                        <td>
                            <input id="page-title"
                                   name="lmx-page-title"
                                   type="text"
                                   value="<?= $page_name ?>"
                                   class="lmx__input--text"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td class="lmx-user-portal-page-td-label"><?php _e('Status', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></td>
                        <td><?= $post_status ?></td>
                    </tr>
                </table>
                <?php
                if ( $page_url !== '' ) {
                    ?>
                    <div  class="lmx-page-link">
                        <a class="lmx-link" href="<?= $page_url ?>"><?php _e('Go to the Personal Account page', LOYMAX_WEB_APP_DOMAIN_NAME ) ?></a>
                    </div>
                    <?php
                }
                ?>
                <div class="lmx__button-container">
                    <button class="lmx-button lmx-button--blue"
                        <?php
                        if ($is_post_trash) {
                            ?> type="button" onclick="event.preventDefault(); loymaxShowDeleteModal(jQuery('#page-title').val());" <?php
                        } else {
                            ?> type="submit" <?php
                        }
                        ?>>
                        <?php _e('Save', LOYMAX_WEB_APP_DOMAIN_NAME ) ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
}
