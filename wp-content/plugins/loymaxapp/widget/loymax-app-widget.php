<?php
if ( ! class_exists( 'LoymaxWebApp_Widget' ) ) {
    class LoymaxWebApp_Widget extends WP_Widget {
        public function __construct() {
            parent::__construct( 'loymax-widget', 'Loymax', array() );
        }

        public function widget( $args, $instance ) {
            echo ( $args['before_widget'] );
            echo ( '<div class="loymax-container">
                        <user-info
                            ng-if="isAuth() && !authInProcess && currentLocation != \'registration\'"
                            base-url="' . get_option( 'loymax-page-link' ) . '"
                            passive-logout></user-info>
                    </div>'
            );
            echo ( $args['after_widget'] );
        }
    }
}
?>