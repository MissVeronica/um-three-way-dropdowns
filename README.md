# Ultimate Member - Two and Three Way Dropdown options
Extension to Ultimate Member for defining two or three way dropdown options in spreadsheets saved as CSV files.
Create the CSV files with an app which can export a file with CSV format like Excel or LibreOffice etc.
Only UM Form's single select dropdowns are supported.

## CSV files and format
1. Two or Three columns one column for each of the two or three levels https://imgur.com/a/gqXH9Fo
2. Column sort order Top, Middle, Bottom
3. CSV file field separators selectable.
4. Create and upload the CSV files to this folder:  <code>.../wp-content/uploads/ultimatemember/threewaydropdowns/</code>
5. All Dropdown selections use the same uploads folder.
6. Single or double quotes around text fields are removed by the plugin.

## UM Settings in main tab Dropdowns
1. Select the Top dropdown's "Label - meta_key" - The Middle dropdown will rely on the Top parent meta_key for Options selection
2. Select the Middle dropdown's "Label - meta_key" - The Bottom dropdown will rely on the Middle parent meta_key for Options selection
3. CSV File Names (one name per line) - Enter the CSV files names one per line.
4. CSV File spreadsheet select two or three columns - Select the two or three columns in the spreadsheet where you have Top, Middle and Bottom options.
5. CSV File header line remove - Click if you have a header line in the first line of the CSV files.
6. CSV File field separator - Select the separator character.
7. https://imgur.com/a/2PmljWx

## Migration from Version 2 to 3
1. The first Dropdown section is reusing settings and callbacks from version 2 when being enabled.
2. New settings for top and middle level meta-keys must be selected.

## UM Form Settings
1. Callbacks are displayed at each dropdown's enable checkbox
2. Examples with: State, Section, Group https://imgur.com/a/maYaqwd

## Test file
1. Use the file "dropdowns.csv" or "dropdowns-a.csv" in the plugin directory: um-three-way-dropdowns-main 

## Demo
1. https://imgur.com/a/IyiEr86
2. Test with World Countries, States and Cities from https://github.com/dr5hn/countries-states-cities-database/blob/master/csv/cities.csv
3. Extracted the three fields and moved them in order Countries, States, Cities and CSV file size almost 6 MByte.
4. Test CSV file with 196 countries, 3497 States and 150362 Cities parsed and cached in 1.7 seconds by the web hosting server.

## Updates
1. Version 2.0.0 Multiple CSV files being cached and CSV spreadsheet column selections.
2. Version 2.1.0 Bug fixing when plugin first run.
3. Version 2.2.0 Header line removal, line terminator update
4. Version 2.3.0 CSV Field Separator selection.
5. Version 3.1.0 Support for 5 CSV dropdowns with either 2 or 3 levels. Updated the documentation in the plugin.

## Installation
1. Install by downloading the plugin ZIP file and install as a new Plugin, which you upload in WordPress -> Plugins -> Add New -> Upload Plugin.
2. Activate the Plugin: Ultimate Member - Three Way Dropdown options
