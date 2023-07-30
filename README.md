# Ultimate Member - Three Way Dropdown options
Extension to Ultimate Member for defining three way dropdown options in spreadsheets saved as CSV files.
Create the CSV files with an app which can export a file with CSV format like Excel or LibreOffice etc.
Only UM Form's single select dropdowns are supported.

## CSV file format
1. Three columns one column for each of the three levels https://imgur.com/a/gqXH9Fo
2. CSV file field separator ;
3. Create and upload the CSV files to this folder:  .../wp-content/uploads/ultimatemember/threewaydropdowns/

## UM Settings in tab Misc
1. Three Way Dropdowns - CSV File Names (one name per line) - Enter one CSV file name per line.
2. Three Way Dropdowns - CSV File three spreadsheet columns - Select the three columns in the spreadsheet where you have Top, Middle and Bottom options.
3. https://imgur.com/a/MI4rUVw

## UM Form Settings
1. Callback dropdown top level: get_custom_top_list_dropdown
2. Callback dropdown mid level: get_custom_mid_list_dropdown
3. Callback dropdown bottom level: get_custom_btm_list_dropdown
4. Examples with: State, Section, Group https://imgur.com/a/maYaqwd

## Test file
1. Use the file "dropdowns.csv" in the plugin directory: um-three-way-dropdowns-main
2. Columns A, B, C are used in the spreadsheet  

## Updates
Version 2.0.0 Multiple CSV files and CSV spreadsheet column selections.

## Installation
1. Install by downloading the plugin ZIP file and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Three Way Dropdown options
