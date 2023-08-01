<?php
/**
 * Plugin Name:     Ultimate Member - Three Way Dropdown options
 * Description:     Extension to Ultimate Member for defining two or three way dropdown options in a spreadsheet saved as a CSV file.
 * Version:         3.0.0
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

    public $selects   = array( '' );

    public $cache_top = array();
    public $cache_mid = array();
    public $cache_btm = array();

    public $number_files = array();
    public $rows_files   = array();

    public $notice  = array();
    public $warning = array();

    public $levels = 0;

    public $separators = array( 
                                'colon'     => ':',
                                'semicolon' => ';',
                                'comma'     => ',',
                                'tab'       => "\t",
                                'space'     => ' ',
                            );

    function __construct() {

        if ( is_admin() && ! defined( 'DOING_AJAX' )) {

            add_filter( 'um_settings_custom_subtabs',                       array( $this, 'um_settings_custom_tabs_three_way_dropdowns' ), 10, 1 );
            add_filter( 'um_settings_structure',                            array( $this, 'um_settings_structure_three_way_dropdowns_3' ), 10, 1 );
            add_filter( 'um_settings_section_three_way_dropdowns__content', array( $this, 'contents_three_way_dropdowns_tab' ), 10, 2 );

        }
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

    public function cache_update_current_csv_files( $section ) {

        $top_meta = UM()->options()->get( 'um_three_way_dropdowns_top_meta' . $section );
        $mid_meta = UM()->options()->get( 'um_three_way_dropdowns_mid_meta' . $section );

        $this->cache_top[$section] = $this->update_cache_option( 'three_way_dropdowns_top' .  $section, $this->top_level );
        $this->cache_mid[$section] = $this->update_cache_option( 'three_way_dropdowns_mid_' . $top_meta, $this->mid_level );
        if ( $this->levels == 3 ) {
            $this->cache_btm[$section] = $this->update_cache_option( 'three_way_dropdowns_btm_' . $mid_meta, $this->btm_level );
        }
    }

    public function um_settings_custom_tabs_three_way_dropdowns( $array ) {

        $array[] = 'three_way_dropdowns';
        return $array;
    }

    public function contents_three_way_dropdowns_tab( $html, $section_fields ) {

        echo '<div class="clear"></div><h4>Migration from Version 2 to 3</h4>';
        echo '<div>The first Dropdown section is reusing settings and callbacks from version 2 when being enabled.
                   <br>New settings for top and middle level meta-keys must be selected.
                   <br>Both two way and three way dropdowns are now supported.';
    }

    public function um_settings_structure_three_way_dropdowns_3( $settings_structure ) {

        foreach( UM()->builtin()->all_user_fields as $meta_key => $value ) {

            if ( $value['type'] == 'select' ) {
                $label = isset( $value['label'] ) ? $value['label'] : $value['title'];
                $this->selects[$meta_key] = $label . ' - ' . $meta_key;
            }
        }

        asort( $this->selects );

        $settings_structure['three_way_dropdowns'] = array( 
                        'title'  => __( 'Dropdowns', 'ultimate-member' ),
                        'sections' => array(
                            '' => array(
                                    'title'  => __( 'Intro', 'ultimate-member' ),                                                
                                    ),
                            'dropdown' => array(
                                    'title'  => __( 'Dropdown', 'ultimate-member' ),
                                    'fields' => $this->create_setting_structures( '' ),                                                
                                    ),
                            'dropdown_a' => array(
                                    'title'  => __( 'Dropdown A', 'ultimate-member' ),
                                    'fields' => $this->create_setting_structures( '_dropdown_a' ),                                                
                                    ),                                                       
                            'dropdown_b' => array(
                                    'title'  => __( 'Dropdown B', 'ultimate-member' ),
                                    'fields' => $this->create_setting_structures( '_dropdown_b' ),                                                
                                    ),
                            'dropdown_c' => array(
                                    'title'  => __( 'Dropdown C', 'ultimate-member' ),
                                    'fields' => $this->create_setting_structures( '_dropdown_c' ),                                                
                                    ),
                            'dropdown_d' => array(
                                    'title'  => __( 'Dropdown D', 'ultimate-member' ),
                                    'fields' => $this->create_setting_structures( '_dropdown_d' ),                                                
                                    ),
                        ),
                    );

        return $settings_structure;
    }


    public function read_current_csv_files( $section ) {

        $this->top_level = array();
        $this->mid_level = array();
        $this->btm_level = array();

        $csv_files = array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", UM()->options()->get( 'um_three_way_dropdowns_files' . $section ))));

        $this->rows_files[$section] = 6;
        if ( is_array( $csv_files ) && isset( $csv_files[0] ) && ! empty( $csv_files[0] )) {
            $this->rows_files[$section] = count( $csv_files );
            $this->number_files[$section] = 0;

            $csv_columns = array_map( 'sanitize_text_field', array_map( 'trim', UM()->options()->get( 'um_three_way_dropdowns_columns' . $section ) ));
            if ( is_array( $csv_columns ) && isset( $csv_columns[0] )) {
                
                $this->levels = count( $csv_columns );
                if ( in_array( $this->levels, array( 2, 3 ))) {

                    $separator = sanitize_text_field( trim( UM()->options()->get( 'um_three_way_dropdowns_separator' . $section )));

                    if ( array_key_exists( $separator, $this->separators )) {
                        $separator = $this->separators[$separator];
                    } else {
                        $separator = false;
                    } 

                    if ( ! empty( $separator )) {

                        foreach( $csv_files as $csv_file_name ) {

                            if ( empty( $csv_file_name )) {
                                continue;
                            }

                            $csv_file = WP_CONTENT_DIR . '/uploads/ultimatemember/threewaydropdowns/' . $csv_file_name;
                            if ( file_exists( $csv_file ) && is_file( $csv_file )) {

                                $this->number_files[$section]++;
                                $csv_content = file_get_contents( $csv_file );

                                if ( ! empty( $csv_content )) {

                                    if ( strpos( $csv_content, $separator ) !== false ) {

                                        if ( strpos( $csv_content, "\n" ) !== false ) {
                                            $terminator = "\n";
                                        } else {
                                            $terminator = "\r";
                                        }

                                        $csv_contents = array_map( 'sanitize_text_field', array_map( 'trim', explode( $terminator, $csv_content )));

                                        if ( UM()->options()->get( 'um_three_way_dropdowns_header' . $section ) == 1 ) {
                                            unset( $csv_contents[0] );
                                        }

                                        $top = '';
                                        $mid = '';

                                        foreach( $csv_contents as $key => $csv_content ) {

                                            if ( empty( trim( $csv_content ))) {
                                                continue;
                                            }

                                            $csv_row_item = array_map( 'sanitize_text_field', array_map( 'trim', explode( $separator, str_replace( array( '"', "'" ), '', $csv_content ))));

                                            if ( empty( $csv_row_item[$csv_columns[0]] )) {
                                                $csv_row_item[$csv_columns[0]] = $top;
                                            }

                                            if ( $top != $csv_row_item[$csv_columns[0]] ) {

                                                $top = $csv_row_item[$csv_columns[0]];
                                                $mid = $csv_row_item[$csv_columns[1]];

                                                if ( $this->levels == 3 ) {
                                                    $btm = $csv_row_item[$csv_columns[2]];
                                                    $this->btm_level[$mid][$btm] = $btm;
                                                }

                                                $this->top_level[$top] = $top;
                                                $this->mid_level[$top][$mid] = $mid;                                                

                                            } else {

                                                if ( empty( $csv_row_item[$csv_columns[1]] )) {
                                                    $csv_row_item[$csv_columns[1]] = $mid;
                                                }

                                                if ( $mid != $csv_row_item[$csv_columns[1]] ) {

                                                    $mid = $csv_row_item[$csv_columns[1]];

                                                    if ( $this->levels == 3 ) {
                                                        $btm = $csv_row_item[$csv_columns[2]];
                                                        $this->btm_level[$mid][$btm] = $btm;
                                                    }

                                                    $this->mid_level[$top][$mid] = $mid;
                                                    

                                                } else {

                                                    if ( $this->levels == 3 && ! empty( $csv_row_item[$csv_columns[2]] )) {

                                                        $btm = $csv_row_item[$csv_columns[2]];
                                                        $this->btm_level[$mid][$btm] = $btm;
                                                    }
                                                }
                                            }
                                        }

                                    } else {

                                        $this->notice[$section][] = sprintf( __( 'Wrong CSV file field separator in file "%s"', 'ultimate-member' ), $csv_file_name );
                                    }

                                } else {

                                    $this->warning[$section][] = sprintf( __( 'CSV file "%s" is empty', 'ultimate-member' ), $csv_file_name );
                                }

                            } else {

                                $this->warning[$section][] = sprintf( __( 'CSV file "%s" not found', 'ultimate-member' ), $csv_file_name );
                            }
                        }

                    } else {

                        $this->notice[$section][] = __( 'CSV file field separator missing', 'ultimate-member' );
                    }

                } else {

                    $this->notice[$section][] = __( 'Wrong number of CSV file columns selected', 'ultimate-member' );
                }

            } else {

                $this->notice[$section][] = __( 'No CSV file columns selected', 'ultimate-member' );
            }

        } else {

            $this->notice[$section][] = __( 'No CSV files', 'ultimate-member' );
        }
    }

    public function process_current_csv_files( $section ) {

        $description = '';

        if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) == 1 ) {

            $start = microtime(true); 
            $this->read_current_csv_files( $section );

            if ( ! isset( $this->notice[$section][0] )) {
                $this->cache_update_current_csv_files( $section );
            }

            $response = microtime(true) - $start;

            if ( isset( $this->notice[$section][0] )) {
                $description .= __( 'Errors:', 'ultimate-member' );
                $description .= '<br>' . implode( '<br>', $this->notice[$section] );

            } else {

                if ( isset( $this->warning[$section][0] )) {    
                    $description .= __( 'Warnings:', 'ultimate-member' );
                    $description .= '<br>' . implode( '<br>', $this->warning[$section] ) . '<br>';    
                }

                $description .= __( 'Cache status:', 'ultimate-member' );
                $description .= '<br>' . sprintf( __( '%d CSV files were parsed and cached in %f seconds', 'ultimate-member' ), $this->number_files[$section], $response );

                $description .= '<br>' . __( 'Top:', 'ultimate-member' );
                if ( $this->cache_top[$section] ) {
                    $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
                } 
                $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), count( $this->top_level ));
                $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), strlen( serialize( $this->top_level )) );

                $description .= '<br>' . __( 'Middle:', 'ultimate-member' );
                if ( $this->cache_mid[$section] ) {
                    $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
                } 
                $count = 0;
                foreach( $this->mid_level as $option ) {
                    foreach( $option as $item ) {
                        if ( ! empty( $item )) $count++;
                    }
                }
                $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), $count );
                $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), strlen( serialize( $this->mid_level )) );

                if ( $this->levels == 3 ) {
                    $description .= '<br>' . __( 'Bottom:', 'ultimate-member' );
                    if ( $this->cache_btm[$section] ) {
                        $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
                    }
                    $count = 0;
                    foreach( $this->btm_level as $option ) {
                        foreach( $option as $item ) {
                            if ( ! empty( $item )) $count++;
                        }
                    } 
                    $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), $count );
                    $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), strlen( serialize( $this->btm_level )) );
                }
            }

        } else {

            $this->rows_files[$section] = 6;
        }

        return $description;
    }

    public function create_setting_structures( $section ) {

        $settings_structure[] = array(
            'id'            => 'um_three_way_dropdowns_active' . $section,
            'type'          => 'checkbox',
            'label'         => __( 'Enable this Three Way Dropdowns section', 'ultimate-member' ) . 
                                    '<br>' . __( 'UM Forms Dropdown Callbacks:', 'ultimate-member' ) .
                                    '<br>' . sprintf( __( 'Top level: "%s"', 'ultimate-member' ), 'get_custom_top_list' . $section ) .
                                    '<br>' . sprintf( __( 'Middle level: "%s"', 'ultimate-member' ), 'get_custom_mid_list' . $section ) .
                                    '<br>' . sprintf( __( 'Bottom level: "%s"', 'ultimate-member' ), 'get_custom_btm_list' . $section ),
            'tooltip'       => __( 'Click to activate this dropdown.', 'ultimate-member' ),
        );

        $settings_structure[] = array(    
            'id'            => 'um_three_way_dropdowns_top_meta' . $section,
            'type'          => 'select',
            'label'         => __( 'Select the Top dropdown\'s "Label - meta_key"', 'ultimate-member' ),
            'tooltip'       => __( 'The Middle dropdown will rely on the Top parent meta_key for Options selection', 'ultimate-member' ),
            'options'       => $this->selects,
            'size'          => 'medium',
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(    
            'id'            => 'um_three_way_dropdowns_mid_meta' . $section,
            'type'          => 'select',
            'label'         => __( 'Select the Middle dropdown\'s "Label - meta_key"', 'ultimate-member' ),
            'tooltip'       => __( 'The Bottom dropdown will rely on the Middle parent meta_key for Options selection', 'ultimate-member' ),
            'options'       => $this->selects,
            'size'          => 'medium',
            'description'   => __( 'This setting is not required for two levels dropdowns', 'ultimate-member' ),
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'            => 'um_three_way_dropdowns_files' . $section,
            'type'          => 'textarea',
            'label'         => __( 'CSV File Names (one name per line)', 'ultimate-member' ),
            'tooltip'       => __( 'Enter one CSV file name per line.', 'ultimate-member' ),
            'description'   => $this->process_current_csv_files( $section ),
            'args'          => array(
                        'textarea_rows' => $this->rows_files[$section] ),
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'            => 'um_three_way_dropdowns_columns' . $section,
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
            'label'         => __( 'CSV File three spreadsheet columns two or three', 'ultimate-member' ),
            'tooltip'       => __( 'Select the two or three columns in the spreadsheet where you have Top, Middle if two and Bottom if three options.', 'ultimate-member' ),
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'            => 'um_three_way_dropdowns_header' . $section,
            'type'          => 'checkbox',
            'label'         => __( 'CSV File header line remove', 'ultimate-member' ),
            'tooltip'       => __( 'Click if you have a header line in the first line of the CSV files.', 'ultimate-member' ),
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'            => 'um_three_way_dropdowns_separator' . $section,
            'type'          => 'select',
            'size'          => 'small',
            'options'       => array(   'no'         => '',
                                        'comma'      => 'Comma',
                                        'colon'      => 'Colon',
                                        'semicolon'  => 'Semicolon',
                                        'tab'        => 'Tabulator',
                                        'space'      => 'Blank Space',                                       
                                    ),
            'label'         => __( 'CSV File field separator', 'ultimate-member' ),
            'tooltip'       => __( 'Select the CSV File field separator character.', 'ultimate-member' ),
            'conditional'   => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        return $settings_structure;
    }
}

UM()->classes['um_three_way_dropdowns'] = new UM_Three_Way_Dropdowns();


    function setup_custom_top_list_dropdown( $section ) {

        if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) != 1 ) {
            return array( __( 'Not active', 'ultimate-member' ));
        }

        $dropdown_options = get_option( 'three_way_dropdowns_top' . $section );

        if ( empty( $dropdown_options )) {
            return array( __( 'No options', 'ultimate-member' ));
        }

        return $dropdown_options;
    }

    function setup_custom_mid_btm_list_dropdown( $parent, $section, $level_1, $level_2 ) {

        if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) != 1 ) {
            return array( __( 'Not active', 'ultimate-member' ));
        }

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;

        if ( empty( $parent )) {
            $parent = sanitize_text_field( trim( UM()->options()->get( "um_three_way_dropdowns_{$level_1}_meta{$section}" )));
        }

        $dropdown_options = get_option( "three_way_dropdowns_{$level_2}_{$parent}"  );        

        if ( empty( $parent_option )) {

            $all_options = array();
            if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) ==  'um_populate_dropdown_options' ) {
                foreach ( $dropdown_options as $options ) {
                    $all_options = array_merge( $options, $all_options );
                }
            }

            return $all_options;
        }

        $dropdown_option = isset( $dropdown_options[$parent_option] ) ? $dropdown_options[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Option error', 'ultimate-member' ));
        }

        return $dropdown_option;
    }

    function get_custom_top_list_dropdown() {

        return setup_custom_top_list_dropdown( '' );
    }

    function get_custom_top_list_dropdown_a() {

        return setup_custom_top_list_dropdown( '_dropdown_a' );
    }

    function get_custom_top_list_dropdown_b() {

        return setup_custom_top_list_dropdown( '_dropdown_b' );
    }

    function get_custom_top_list_dropdown_c() {

        return setup_custom_top_list_dropdown( '_dropdown_c' );
    }

    function get_custom_top_list_dropdown_d() {

        return setup_custom_top_list_dropdown( '_dropdown_d' );
    }

    function get_custom_mid_list_dropdown( $parent = false ) {

        return setup_custom_mid_btm_list_dropdown( $parent, '', 'top', 'mid' );
    }

    function get_custom_mid_list_dropdown_a( $parent = false ) {

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_a', 'top', 'mid' );
    }

    function get_custom_mid_list_dropdown_b( $parent = false ) {

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_b', 'top', 'mid' );
    }

    function get_custom_mid_list_dropdown_c( $parent = false ) {

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_c', 'top', 'mid' );
    }

    function get_custom_mid_list_dropdown_d( $parent = false ) {

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_d', 'top', 'mid' );
    }

    function get_custom_btm_list_dropdown( $parent = false ) {  

        return setup_custom_mid_btm_list_dropdown( $parent, '', 'mid', 'btm' );
    }

    function get_custom_btm_list_dropdown_a( $parent = false ) {  

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_a', 'mid', 'btm' );
    }

    function get_custom_btm_list_dropdown_b( $parent = false ) {  

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_b', 'mid', 'btm' );
    }

    function get_custom_btm_list_dropdown_c( $parent = false ) {  

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_c', 'mid', 'btm' );
    }

    function get_custom_btm_list_dropdown_d( $parent = false ) {  

        return setup_custom_mid_btm_list_dropdown( $parent, '_dropdown_d', 'mid', 'btm' );
    }
