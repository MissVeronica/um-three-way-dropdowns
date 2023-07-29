# Ultimate Member - Three Way Dropdown options
Extension to Ultimate Member for defining three way dropdown options in a spreadsheet saved as a CSV file.
Create the CSV file with an app which can export a file with CSV format like Excel or LibreOffice etc.
Only single select dropdowns are supported.

## CSV file format
1. Three columns one column for each of the three levels https://imgur.com/a/gqXH9Fo
2. CSV file field separator ;
3. Upload the CSV file with the name "dropdowns.csv"
4. Create and upload the CSV file to this folder:  .../wp-content/uploads/ultimatemember/threewaydropdowns/

## UM Form Settings
1. Callback dropdown top level: get_custom_top_list_dropdown
2. Callback dropdown mid level: get_custom_mid_list_dropdown
3. Callback dropdown bottom level: get_custom_btm_list_dropdown
4. Examples with: State, Section, Group https://imgur.com/a/maYaqwd

## Test file
1. See the file "dropdowns.csv" in the plugin directory: um-three-way-dropdowns-main

## Updates
None

## Installation
1. Install by downloading the plugin ZIP file and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Three Way Dropdown options
