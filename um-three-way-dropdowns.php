<?php
/**
 * Plugin Name:     Ultimate Member - Three Way Dropdown options
 * Description:     Extension to Ultimate Member for defining three way dropdown options in a spreadsheet saved as a CSV file.
 * Version:         2.1.0
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

    public $cache_top;
    public $cache_mid;
    public $cache_btm;

    public $number_files = 0;
    public $rows_files   = 6;

    function __construct() {

        add_filter( 'um_settings_structure', array( $this, 'um_settings_structure_three_way_dropdowns' ), 10, 1 );
    }

    public function update_cache_option( $option_name, $option_value ) {

        $current_value = get_option( $option_name );

        if ( $current_value !== false ) {

            if ( $current_value === $option_value ) {
                return false;

            } else {
            
                update_option( $option_name, $option_value );
                return true;
            }

        } else {

            add_option( $option_name, $option_value, null, 'no' );
            return true;
        }
    }

    public function cache_update_current_csv_files() {

        $this->cache_top = $this->update_cache_option( 'three_way_dropdowns_top', $this->top_level );
        $this->cache_mid = $this->update_cache_option( 'three_way_dropdowns_mid', $this->mid_level );
        $this->cache_btm = $this->update_cache_option( 'three_way_dropdowns_btm', $this->btm_level );
    }

    public function read_current_csv_files() {

        $csv_files = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", UM()->options()->get( 'um_three_way_dropdowns_files' ))));

        if ( ! empty( $csv_files )) {
            $this->rows_files = count( $csv_files );

            $csv_columns = UM()->options()->get( 'um_three_way_dropdowns_columns' );
            if ( ! empty( $csv_columns )) {

                $csv_columns = array_map( 'sanitize_text_field', array_map( 'trim', $csv_columns ));
                if (  in_array( count( $csv_columns ), array( 2, 3 ))) {

                    foreach( $csv_files as $csv_file ) {

                        $csv_file = WP_CONTENT_DIR . '/uploads/ultimatemember/threewaydropdowns/' . $csv_file;
                        if ( file_exists( $csv_file ) && is_file( $csv_file )) {

                            $this->number_files++;
                            $csv_content = file_get_contents( $csv_file );

                            if ( ! empty( $csv_content )) {

                                $csv_contents = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", $csv_content )));

                                $top = '';
                                $mid = '';

                                foreach( $csv_contents as $csv_content ) {

                                    $csv_row_item = array_map( 'sanitize_text_field', array_map( 'trim', explode( ';', $csv_content )));

                                    if ( ! empty( $csv_row_item[$csv_columns[0]]) || $top != $csv_row_item[$csv_columns[0]] ) {

                                        $top = $csv_row_item[$csv_columns[0]];
                                        $mid = $csv_row_item[$csv_columns[1]];
                                        $btm = $csv_row_item[$csv_columns[2]];

                                        $this->top_level[$top] = $top;
                                        $this->mid_level[$top][$mid] = $mid;
                                        $this->btm_level[$mid][$btm] = $btm;

                                    } else {

                                        if ( ! empty( $csv_row_item[$csv_columns[1]]) || $mid != $csv_row_item[$csv_columns[1]] ) {

                                            $mid = $csv_row_item[$csv_columns[1]];
                                            $btm = $csv_row_item[$csv_columns[2]];

                                            $this->mid_level[$top][$mid] = $mid;
                                            $this->btm_level[$mid][$btm] = $btm;

                                        } else {

                                            if ( ! empty( $csv_row_item[$csv_columns[2]] )) {

                                                $btm = $csv_row_item[$csv_columns[2]];
                                                $this->btm_level[$mid][$btm] = $btm;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function um_settings_structure_three_way_dropdowns( $settings_structure ) {

        $start = microtime(true); 

        $this->read_current_csv_files();
        $this->cache_update_current_csv_files();

        $response = microtime(true) - $start;

        $description = __( 'Cache status:', 'ultimate-member' );
        $description .= '<br>' . sprintf( __( '%d CSV files were parsed and cached in %f seconds', 'ultimate-member' ), $this->number_files, $response );

        $description .= '<br>' . __( 'Top:', 'ultimate-member' );
        if ( $this->cache_top ) {
            $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
        } 
        $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), count( $this->top_level ));
        $length = strlen( serialize( $this->top_level ));
        $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), $length );

        $description .= '<br>' . __( 'Middle:', 'ultimate-member' );
        if ( $this->cache_mid ) {
            $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
        } 
        $count = 0;
        foreach( $this->mid_level as $option ) {
            foreach( $option as $item ) {
                if ( ! empty( $item )) $count++;
            }
        }
        $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), $count );
        $length = strlen( serialize( $this->mid_level ));
        $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), $length );

        $description .= '<br>' . __( 'Bottom:', 'ultimate-member' );
        if ( $this->cache_btm ) {
            $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
        }
        $count = 0;
        foreach( $this->btm_level as $option ) {
            foreach( $option as $item ) {
                if ( ! empty( $item )) $count++;
            }
        } 
        $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), $count );
        $length = strlen( serialize( $this->btm_level ));
        $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), $length );

        if ( $this->rows_files < 2 ) {
            $this->rows_files = 6;
        }

        $settings_structure['misc']['fields'][] = array(
            'id'            => 'um_three_way_dropdowns_files',
            'type'          => 'textarea',
            'label'         => __( 'Three Way Dropdowns - CSV File Names (one name per line)', 'ultimate-member' ),
            'tooltip'       => __( 'Enter one CSV file name per line.', 'ultimate-member' ),
            'args'          => array(
                                'textarea_rows' => $this->rows_files ),
            'description'   => $description,
            );

        $settings_structure['misc']['fields'][] = array(
            'id'            => 'um_three_way_dropdowns_columns',
            'type'          => 'select',
            'multi'         => true,
            'size'          => 'small',
            'options'       => array(   0  => 'A',
                                        1  => 'B',
                                        2  => 'C',
                                        3  => 'D',
                                        4  => 'E',
                                        5  => 'F',
                                        6  => 'G',
                                        7  => 'H',
                                        8  => 'I',
                                        9  => 'J',
                                        11 => 'K',
                                        12 => 'L',
                                        13 => 'M',
                                        14 => 'N',
                                        15 => 'O',
                                        16 => 'P',
                                        17 => 'Q',
                                        18 => 'R',
                                        19 => 'S',
                                        20 => 'T',
                                    ),
            'label'         => __( 'Three Way Dropdowns - CSV File three spreadsheet columns', 'ultimate-member' ),
            'tooltip'       => __( 'Select the three columns in the spreadsheet where you have Top, Middle and Bottom options.', 'ultimate-member' ),
            );

        return $settings_structure;
    }
}

UM()->classes['um_three_way_dropdowns'] = new UM_Three_Way_Dropdowns();


    function get_custom_top_list_dropdown() {

        $dropdown_options = get_option( 'three_way_dropdowns_top' );

        if ( empty( $dropdown_options )) {
            return array( __( 'No options', 'ultimate-member' ));
        }

        return $dropdown_options;
    }

    function get_custom_mid_list_dropdown( $has_parent = false ) {  

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;
        $dropdown_options = get_option( 'three_way_dropdowns_mid' );        

        if ( empty( $parent_option )) {

            $all_options = array();
            foreach ( $dropdown_options as $options ) {
                $all_options = array_merge( $options, $all_options );
            }

            return $all_options;
        }

        $dropdown_option = isset( $dropdown_options[$parent_option] ) ? $dropdown_options[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        return $dropdown_option;
    }

    function get_custom_btm_list_dropdown( $has_parent = false ) {  

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;
        $dropdown_options = get_option( 'three_way_dropdowns_btm' );

        if ( empty( $parent_option )) {

            $all_options = array();
            foreach ( $dropdown_options as $options ) {
                $all_options = array_merge( $options, $all_options );
            }

            return $all_options;
        }
        
        $dropdown_option = isset( $dropdown_options[$parent_option] ) ? $dropdown_options[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        return $dropdown_option;
    }

