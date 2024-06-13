<?php
/**
 * Plugin Name:     Ultimate Member - Three Way Dropdown options
 * Description:     Extension to Ultimate Member for defining two or three way dropdown options in a spreadsheet saved as a CSV file.
 * Version:         3.2.0
 * Requires PHP:    7.4
 * Author:          Miss Veronica
 * License:         GPL v2 or later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:      https://github.com/MissVeronica
 * Text Domain:     ultimate-member
 * Domain Path:     /languages
 * UM version:      2.8.6
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
if ( ! class_exists( 'UM' ) ) return;


Class UM_Three_Way_Dropdowns {

    public $top_level = array();
    public $mid_level = array();
    public $btm_level = array();

    public $selects   = array( '' );

    public $cache     = array();
    public $cache_top = array();
    public $cache_mid = array();
    public $cache_btm = array();

    public $number_files    = array();
    public $rows_files      = array();
    public $autoload_status = array();

    public $notice   = array();
    public $warning  = array();
    public $response = array();

    public $levels = 0;
    public $tab_name = 'three_way_dropdowns';
    public $upload_csv_dir = WP_CONTENT_DIR . '/uploads/ultimatemember/threewaydropdowns/';

    public $separators = array( 
                                'colon'     => ':',
                                'semicolon' => ';',
                                'comma'     => ',',
                                'tab'       => "\t",
                                'space'     => ' ',
                            );

    public $select_separator = array(
                                'no'         => '',
                                'comma'      => 'Comma',
                                'colon'      => 'Colon',
                                'semicolon'  => 'Semicolon',
                                'tab'        => 'Tabulator',
                                'space'      => 'Blank Space',
                                );

    public $spreadsheet_columns = array(
                                        0  => 'A',
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
                                    );

    function __construct() {

        define( 'Three_Way_Plugin_Path', plugin_dir_path( __FILE__ ) );

        if ( is_admin() && ! defined( 'DOING_AJAX' )) {

            add_filter( 'um_settings_custom_subtabs',                       array( $this, 'um_settings_custom_tabs_three_way_dropdowns' ), 10, 1 );
            add_filter( 'um_settings_structure',                            array( $this, 'um_settings_structure_main_three_way_dropdowns' ), 10, 1 );
            add_filter( 'um_settings_section_three_way_dropdowns__content', array( $this, 'contents_three_way_dropdowns_tab' ), 10, 2 );
            add_filter( 'um_settings_custom_subtabs',                       array( $this, 'um_settings_custom_subtabs_three_way_dropdowns' ), 10, 2 );

        }
    }

    public function update_cache_option( $level, $name, $option_value, $autoload, $section ) {

        $option_name = 'three_way_dropdowns_' . $level . $name;
        $this->cache[$section] = false;

        $load = 'yes';
        if ( $autoload != 1 ) {
            $load = 'no';
        }

        $all_options = wp_load_alloptions();

        if ( array_key_exists( $option_name, $all_options )) {

            $this->autoload_status[$section] = true;
            if ( $load == 'no' ) {
                delete_option( $option_name );
                $this->autoload_status[$section] = false;
            }

        } else {

            $this->autoload_status[$section] = false;
            if ( $load == 'yes' ) {
                delete_option( $option_name );
                $this->autoload_status[$section] = true;
            }
        }

        $start = microtime(true);
        $current_value = get_option( $option_name );
        $response = microtime(true) - $start;

        if ( $current_value !== false ) {

            $this->response[$section][$level] = $response;

            if ( $current_value === $option_value ) {
                return false;

            } else {

                update_option( $option_name, $option_value, $load );
                $this->cache[$section] = true;
                return true;
            }

        } else {

            add_option( $option_name, $option_value, null, $load );
            $this->cache[$section] = true;
            return true;
        }
    }

    public function cache_update_current_csv_files( $section ) {

        $top_meta = '_' . sanitize_text_field( UM()->options()->get( 'um_three_way_dropdowns_top_meta' . $section ));
        $mid_meta = '_' . sanitize_text_field( UM()->options()->get( 'um_three_way_dropdowns_mid_meta' . $section ));
        $autoload = UM()->options()->get( 'um_three_way_dropdowns_wp_autoload' . $section );

        $this->cache_top[$section] = $this->update_cache_option( 'top', $section, $this->top_level, $autoload, $section );
        $this->cache_mid[$section] = $this->update_cache_option( 'mid', $top_meta, $this->mid_level, $autoload, $section );
        if ( $this->levels == 3 ) {
            $this->cache_btm[$section] = $this->update_cache_option( 'btm', $mid_meta, $this->btm_level, $autoload, $section );
        }
    }

    public function um_settings_custom_tabs_three_way_dropdowns( $array ) {

        $array[] = $this->tab_name;
        return $array;
    }

    public function um_settings_custom_subtabs_three_way_dropdowns( $array, $current_tab ) {

        if ( $current_tab == $this->tab_name ) {
            $array[] = '';
        }
        return $array;
    }

    public function contents_three_way_dropdowns_tab( $html, $section_fields ) {

        echo '<div class="clear"></div><h4>Two and Three Way Dropdown options version 3.2.2</h4>
              <div><p>New in this version</p>
              <p>1. A second multi-select setting for defining the texts to display for the User in the Forms dropdown.<br/>
              If no selections is made the same columns as options is being used for the Form display text.</p>
              2. WordPress autoload of options. Whether to load the options when WordPress starts up. Default is "No"</p>
              <p>3. Database read times for each option</p>
              <p>4. Backend code improvements</p></div>';
    }

    public function um_settings_structure_main_three_way_dropdowns( $settings_structure ) {

        foreach( UM()->builtin()->all_user_fields as $meta_key => $value ) {

            $this->selects[$meta_key] = $meta_key;
            if ( isset( $value['type'] ) && $value['type'] == 'select' ) {
                $label = isset( $value['label'] ) ? $value['label'] : $value['title'];
                $this->selects[$meta_key] = $label . ' - ' . $meta_key;
            }
        }

        asort( $this->selects );

        $settings_structure[$this->tab_name] = array(
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

    public function read_current_txt_files( $section ) {

        $files = glob( $this->upload_csv_dir . '*' );

        if ( is_array( $files ) && ! empty( $files )) {

            foreach( $files as $txt_file ) {

                if ( substr( $txt_file, -4 ) == '.txt' ) {

                    if ( file_exists( $txt_file ) && is_file( $txt_file )) {

                        $txt_content = file_get_contents( $txt_file );

                        if ( ! empty( $txt_content )) {

                            if ( strpos( $txt_content, "\n" ) !== false ) {
                                $terminator = "\n";
                            } else {
                                $terminator = "\r";
                            }

                            $txt_contents = array_map( 'sanitize_text_field', array_map( 'trim', explode( $terminator, $txt_content )));
                            $csv_content = array();

                            foreach( $txt_contents as $txt_content ) {

                                if ( strpos( $txt_content, '{' ) !== false || strpos( $txt_content, '}' ) !== false ) {
                                    continue;
                                }

                                if ( strpos( $txt_content, '[' ) !== false ) {
                                    $csv_line = array();
                                    $column_A = explode( '"', $txt_content );
                                    continue;
                                }

                                if ( strpos( $txt_content, ']' ) !== false ) {
                                    continue;
                                }

                                $sub_column = explode( '"', $txt_content );

                                if ( isset( $sub_column[1] )) {
                                    $csv_line[] = $column_A[1];
                                    $csv_line[] = $sub_column[1];
                                    $csv_content[] = implode( ';', $csv_line );
                                    $csv_line = array();
                                }
                            }

                            $csv_content = array_map( 'sanitize_text_field', $csv_content );
                            $csv_content = implode( "\r", $csv_content );

                            file_put_contents( $txt_file . '.csv', $csv_content );
                        }
                    }
                }
            }
        }
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

            $csv_columns = UM()->options()->get( 'um_three_way_dropdowns_columns' . $section );
            if ( is_array( $csv_columns )) {

                $csv_columns = array_map( 'sanitize_text_field', array_map( 'trim', $csv_columns ));

                if ( isset( $csv_columns[0] )) {

                    $csv_text_columns = UM()->options()->get( 'um_three_way_dropdowns_text_columns' . $section );
                    if ( ! is_array( $csv_text_columns )) {
                        $csv_text_columns = $csv_columns;
                    } else {
                        $csv_text_columns = array_map( 'sanitize_text_field', array_map( 'trim', $csv_text_columns ));
                    }

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

                                $csv_file = $this->upload_csv_dir . $csv_file_name;
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
                                                        $this->btm_level[$mid][$btm] = $csv_row_item[$csv_text_columns[2]];
                                                    }

                                                    $this->top_level[$top] = $csv_row_item[$csv_text_columns[0]];
                                                    $this->mid_level[$top][$mid] = $csv_row_item[$csv_text_columns[1]];

                                                } else {

                                                    if ( empty( $csv_row_item[$csv_columns[1]] )) {
                                                        $csv_row_item[$csv_columns[1]] = $mid;
                                                    }

                                                    if ( $mid != $csv_row_item[$csv_columns[1]] ) {

                                                        $mid = $csv_row_item[$csv_columns[1]];

                                                        if ( $this->levels == 3 ) {
                                                            $btm = $csv_row_item[$csv_columns[2]];
                                                            $this->btm_level[$mid][$btm] = $csv_row_item[$csv_text_columns[2]];
                                                        }

                                                        $this->mid_level[$top][$mid] = $csv_row_item[$csv_text_columns[1]];

                                                    } else {

                                                        if ( $this->levels == 3 && ! empty( $csv_row_item[$csv_columns[2]] )) {

                                                            $btm = $csv_row_item[$csv_columns[2]];
                                                            $this->btm_level[$mid][$btm] = $csv_row_item[$csv_text_columns[2]];
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

                    $this->notice[$section][] = __( 'No CSV file option columns selected', 'ultimate-member' );
                }

            } else {

                $this->notice[$section][] = __( 'No CSV file option columns selected', 'ultimate-member' );
            }

        } else {

            $this->notice[$section][] = __( 'No CSV files', 'ultimate-member' );
        }
    }

    public function process_current_csv_files( $section ) {

        $description = '';

        if ( isset( $_GET['tab']) && $_GET['tab'] == $this->tab_name ) {
            if ( isset( $_GET['section']) && ( $_GET['section'] == substr( $section, 1 ) || $_GET['section'] == 'dropdown' )) {

                if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) == 1 ) {

                    $start = microtime(true);

                    $this->read_current_txt_files( $section );
                    $this->read_current_csv_files( $section );

                    if ( ! isset( $this->notice[$section] )) {
                        $this->cache_update_current_csv_files( $section );
                        $cache = '';
                        if ( (bool)$this->cache[$section] ) {
                            $cache = __( 'and cached', 'ultimate-member' );
                        }
                    }

                    $response = microtime(true) - $start;

                    if ( isset( $this->notice[$section] ) && count( $this->notice[$section] ) > 0 ) {
                        $description .= __( 'Errors:', 'ultimate-member' );
                        $description .= '<br>' . implode( '<br>', $this->notice[$section] );

                    } else {

                        if ( isset( $this->warning[$section][0] ) && ! empty( $this->warning[$section][0] )) {
                            $description .= __( 'Warnings:', 'ultimate-member' );
                            $description .= '<br>' . implode( '<br>', $this->warning[$section] ) . '<br>';
                        }

                        $description .= __( 'Cache status:', 'ultimate-member' );
                        if ( $this->number_files[$section] == 1 ) {
                            $description .= '<br>' . sprintf( __( '%d CSV file was parsed %s in %f seconds', 'ultimate-member' ), $this->number_files[$section], $cache, $response );
                        } else {
                            $description .= '<br>' . sprintf( __( '%d CSV files were parsed %s in %f seconds', 'ultimate-member' ), $this->number_files[$section], $cache, $response );
                        }

                        if ( isset( $this->autoload_status[$section] ) && $this->autoload_status[$section] ) {
                            $description .= '<br>' . __( 'WP Autoload for all options', 'ultimate-member' );
                        }

                        $description .= '<br>' . __( 'Top:', 'ultimate-member' );
                        if ( isset( $this->cache_top[$section] ) && (bool)$this->cache_top[$section] ) {
                            $description .= ' ' . __( 'File cache updated', 'ultimate-member' );
                        }
                        $description .= ' ' . sprintf( __( 'options %d', 'ultimate-member' ), count( $this->top_level ));
                        $description .= ' ' . sprintf( __( 'length %d characters', 'ultimate-member' ), strlen( serialize( $this->top_level )) );
                        if ( isset( $this->response[$section]['top'] )) {
                            $description .= ' ' . sprintf( __( 'Time to read: %f seconds', 'ultimate-member' ), $this->response[$section]['top'] );
                        }

                        $description .= '<br>' . __( 'Middle:', 'ultimate-member' );
                        if ( isset( $this->cache_mid[$section] ) && (bool)$this->cache_mid[$section] ) {
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
                        if ( isset( $this->response[$section]['mid'] )) {
                            $description .= ' ' . sprintf( __( 'Time to read: %f seconds', 'ultimate-member' ), $this->response[$section]['mid'] );
                        }

                        if ( $this->levels == 3 ) {
                            $description .= '<br>' . __( 'Bottom:', 'ultimate-member' );
                            if ( isset( $this->cache_btm[$section] ) && (bool)$this->cache_btm[$section] ) {
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
                            if ( isset( $this->response[$section]['btm'] )) {
                                $description .= ' ' . sprintf( __( 'Time to read: %f seconds', 'ultimate-member' ), $this->response[$section]['btm'] );
                            }
                        }
                    }

                } else {

                    $this->rows_files[$section] = 6;
                }
            }
        }

        return $description;
    }

    public function create_setting_structures( $section ) {

        if ( ! isset( $this->rows_files[$section] )) {
            $this->rows_files[$section] = 6;
        }

        $settings_structure = array();

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_active' . $section,
            'type'           => 'checkbox',
            'label'          => __( 'Enable this Three Way Dropdowns section', 'ultimate-member' ) .
                                    '<br>' . __( 'UM Forms Dropdown Callbacks:', 'ultimate-member' ) .
                                    '<br>' . sprintf( __( 'Top level: "%s"', 'ultimate-member' ), 'get_custom_top_list_dropdown' . substr( $section, -2 )) .
                                    '<br>' . sprintf( __( 'Middle level: "%s"', 'ultimate-member' ), 'get_custom_mid_list_dropdown' . substr( $section, -2 )) .
                                    '<br>' . sprintf( __( 'Bottom level: "%s"', 'ultimate-member' ), 'get_custom_btm_list_dropdown' . substr( $section, -2 )),
            'checkbox_label' => __( 'Click to activate this dropdown.', 'ultimate-member' ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_top_meta' . $section,
            'type'           => 'select',
            'label'          => __( 'Select the Top dropdown\'s "Label - meta_key"', 'ultimate-member' ),
            'description'    => __( 'The Middle dropdown will rely on the Top parent meta_key for Options selection', 'ultimate-member' ),
            'options'        => $this->selects,
            'size'           => 'medium',
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_mid_meta' . $section,
            'type'           => 'select',
            'label'          => __( 'Select the Middle dropdown\'s "Label - meta_key"', 'ultimate-member' ),
            'tooltip'        => __( 'The Bottom dropdown will rely on the Middle parent meta_key for Options selection', 'ultimate-member' ),
            'options'        => $this->selects,
            'size'           => 'medium',
            'description'    => __( 'This setting is not required for two level dropdowns', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_files' . $section,
            'type'           => 'textarea',
            'label'          => __( 'CSV File Names (one name per line)', 'ultimate-member' ),
            'tooltip'        => __( 'Enter the CSV files names one per line.', 'ultimate-member' ),
            'description'    => $this->process_current_csv_files( $section ),
            'args'           => array( 'textarea_rows' => $this->rows_files[$section] ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_columns' . $section,
            'type'           => 'select',
            'multi'          => true,
            'size'           => 'small',
            'options'        => $this->spreadsheet_columns,
            'label'          => __( 'CSV File spreadsheet select two or three columns for option values', 'ultimate-member' ),
            'description'    => __( 'Select the two or three columns in the spreadsheet where you have Top, Middle if two and include also Bottom if three options values.', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_text_columns' . $section,
            'type'           => 'select',
            'multi'          => true,
            'size'           => 'small',
            'options'        => $this->spreadsheet_columns,
            'label'          => __( 'CSV File spreadsheet select two or three columns for option texts', 'ultimate-member' ),
            'description'    => __( 'Select the two or three columns in the spreadsheet where you have Top, Middle if two and include also Bottom if three options text message.', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_header' . $section,
            'type'           => 'checkbox',
            'label'          => __( 'CSV File header line remove', 'ultimate-member' ),
            'checkbox_label' => __( 'Click if you have a header line in the first line of the CSV files.', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_separator' . $section,
            'type'           => 'select',
            'size'           => 'small',
            'options'        => $this->select_separator,
            'label'          => __( 'CSV File column separator', 'ultimate-member' ),
            'description'    => __( 'Select the CSV File column separator character.', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        $settings_structure[] = array(
            'id'             => 'um_three_way_dropdowns_wp_autoload' . $section,
            'type'           => 'checkbox',
            'label'          => __( 'WordPress autoload of options', 'ultimate-member' ),
            'checkbox_label' => __( 'Click to improve response times of options load.', 'ultimate-member' ),
            'conditional'    => array( 'um_three_way_dropdowns_active' . $section, '=', 1 ),
        );

        return $settings_structure;
    }
}

UM()->classes['um_three_way_dropdowns'] = new UM_Three_Way_Dropdowns();




    function setup_custom_top_list_dropdown( $section ) {

        //re_initialize_dropdown_functionality();

        if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) != 1 ) {
            return array( __( 'Plugin not active', 'ultimate-member' ));
        }

        $dropdown_options = get_option( 'three_way_dropdowns_top' . $section );

        if ( empty( $dropdown_options )) {
            return array( __( 'Options empty', 'ultimate-member' ));
        }

        return $dropdown_options;
    }

    function setup_custom_mid_btm_list_dropdown( $parent, $section, $level_1, $level_2 ) {

        if ( UM()->options()->get( 'um_three_way_dropdowns_active' . $section ) != 1 ) {
            return array( __( 'Plugin not active', 'ultimate-member' ));
        }

        $parent_option = isset( $_POST['parent_option'] ) ? sanitize_text_field( $_POST['parent_option'] ) : false;
        if ( empty( $parent )) {
            $parent = sanitize_text_field( trim( UM()->options()->get( "um_three_way_dropdowns_{$level_1}_meta{$section}" )));
        }

        $dropdown_options = get_option( "three_way_dropdowns_{$level_2}_{$parent}" );

        if ( empty( $parent_option )) {

            $all_options = array();
            if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'um_populate_dropdown_options' ) {

                foreach ( $dropdown_options as $options ) {
                    $all_options = array_merge( $options, $all_options );
                }
            }

            return $all_options;
        }

        $dropdown_option = isset( $dropdown_options[$parent_option] ) ? $dropdown_options[$parent_option] : false;

        if ( empty( $dropdown_option )) {
            return array( __( 'Options empty for mid or bottom level', 'ultimate-member' ));
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

