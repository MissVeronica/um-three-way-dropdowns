<?php
/**
 * Plugin Name:     Ultimate Member - Three Way Dropdown options
 * Description:     Extension to Ultimate Member for defining three way dropdown options in a spreadsheet saved as a CSV file.
 * Version:         1.0.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.6.9
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;


Class UM_Three_Way_Dropdowns {

    public $top_level = array();
    public $mid_level = array();
    public $btm_level = array();

    function __construct() {

        $csv_content = file_get_contents( WP_CONTENT_DIR . '/uploads/ultimatemember/threewaydropdowns/dropdowns.csv' );

        if ( ! empty( $csv_content )) {

            $csv_contents = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", $csv_content )));

            foreach( $csv_contents as $csv_content ) {

                $csv_row_item = array_map( 'sanitize_text_field', array_map( 'trim', explode( ';', $csv_content )));

                if ( ! empty( $csv_row_item[0])) {

                    $top = $csv_row_item[0];
                    $mid = $csv_row_item[1];
                    $btm = $csv_row_item[2];

                    $this->top_level[$top] = $top;

                    $this->mid_level[$top][''] = '';
                    $this->mid_level[$top][$mid] = $mid;

                    $this->btm_level[$mid][''] = '';
                    $this->btm_level[$mid][$btm] = $btm;

                } else {

                    if ( ! empty( $csv_row_item[1])) {

                        $mid = $csv_row_item[1];
                        $btm = $csv_row_item[2];

                        $this->mid_level[$top][''] = '';
                        $this->mid_level[$top][$mid] = $mid;
        
                        $this->btm_level[$mid][''] = '';
                        $this->btm_level[$mid][$btm] = $btm;

                    } else {

                        if ( empty( $csv_row_item[2] )) break;
                        $btm = $csv_row_item[2];

                        $this->btm_level[$mid][$btm] = $btm;
                    }
                }
            }
        }        
    }
}

UM()->classes['um_three_way_dropdowns'] = new UM_Three_Way_Dropdowns();


    function get_custom_top_list_dropdown() {
    
        $dropdown_options = UM()->classes['um_three_way_dropdowns']->top_level;
        
        if ( empty( $dropdown_options )) {
            return array( __( 'No options', 'ultimate-member' ));
        }

        return $dropdown_options;
    }

    function get_custom_mid_list_dropdown( $has_parent = false ) {  

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;

        if ( empty( $parent_option ) || empty( $has_parent )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        $dropdown_option = isset( UM()->classes['um_three_way_dropdowns']->mid_level[$parent_option] ) ? UM()->classes['um_three_way_dropdowns']->mid_level[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        return $dropdown_option;
    }

    function get_custom_btm_list_dropdown( $has_parent = false ) {  

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;

        if ( empty( $parent_option ) || empty( $has_parent )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        $dropdown_option = isset( UM()->classes['um_three_way_dropdowns']->btm_level[$parent_option] ) ? UM()->classes['um_three_way_dropdowns']->btm_level[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        return $dropdown_option;
    }
