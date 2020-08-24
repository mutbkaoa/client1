<?php
class LoymaxWebApp_smart_configurations {
    private static $smart_configurations = array(
        'requestUnreadMessage' => array(
            'get_value' => 'get_smart_config_value',
        )
    );

    private static function get_smart_config_value ($component_name) {
        global $wpdb;
        $configs_SQL = 'SELECT selected FROM ' . LOYMAX_WEB_APP_COMPONENT_TABLE_NAME;
        $configs_SQL .= ' WHERE c_key = "' . $component_name . '"';
        $configs = $wpdb->get_results( $configs_SQL, OBJECT_K );
        $selected = filter_var( array_shift($configs)->selected, FILTER_VALIDATE_BOOLEAN );

        return $selected;
    }

    private static function is_smart_config($name) {
        return !( is_null( self::$smart_configurations[ $name ] ) );
    }

    private static function get_smart_config_by_name($name) {
        return self::$smart_configurations[ $name ];
    }

    public static function process_smart_configurations($configs) {
        foreach ( $configs as $key => $config ) {
            if ( $config->component !== null && self::is_smart_config( $config->config_key ) ) {
                $function = self::get_smart_config_by_name( $config->config_key )[ 'get_value' ];
                $configs[ $key ]->config_value = self::$function( $config->component );
            }
        }
    }

    public static function get_smart_configs_names() {
        return array_keys( self::$smart_configurations );
    }
}
