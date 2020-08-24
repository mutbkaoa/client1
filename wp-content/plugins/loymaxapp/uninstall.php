<?php
/**
 * LoymaxWebApp - uninstall script
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
//Ensure the uninstall.php file was only called by WordPress and not directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die();
}

global $wpdb;

if ( ! defined( 'LOYMAX_WEB_APP_CONFIGS_TABLE_NAME' ) ) {
    define( 'LOYMAX_WEB_APP_CONFIGS_TABLE_NAME', strval( $wpdb->prefix ) . 'loymax' );
}

if ( ! defined( 'LOYMAX_WEB_APP_COMPONENT_TABLE_NAME' ) ) {
    define( 'LOYMAX_WEB_APP_COMPONENT_TABLE_NAME', strval( $wpdb->prefix ) . 'loymax_components' );
}

$wpdb->query( "DROP TABLE IF EXISTS " . LOYMAX_WEB_APP_CONFIGS_TABLE_NAME );
$wpdb->query( "DROP TABLE IF EXISTS " . LOYMAX_WEB_APP_COMPONENT_TABLE_NAME );

delete_option( 'jal_db_version' );
delete_option( 'loymax_install_wizard_in_progress' );
delete_option( 'loymax_page_ID' );
delete_option( 'loymax-page-link' );
delete_option( 'loymax-navigation-menu-id' );
delete_option( 'loymax_page_delete_prevented' );
delete_option( 'loymax_menu_delete_prevented' );
delete_option( 'loymax-page-title' );
delete_option( 'loymax-component-order' );
delete_option( 'loymax-personal-menu-item-id' );
