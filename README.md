# activenet-parser

 Processes one or many plain-text Brochure Export files from ActiveNet's Front Desk Web Application and provides a single, clean output in the original export format

## Activenet File Parser v1.1.1 
 ----------------------------
 Build: April 12, 2019
 Updated: September 12, 2023

 ### Requirements: 
 PHP7.3+ (configured with file_uploads=On, magic_quotes_gpc=On), PHPUnit8.5+

 ### Author: 
 Hal Wong

 ### Description:
 Processes one or many plain-text Brochure Export files from ActiveNet's Front Desk Web Application 
 and provides a single, clean output in the original export format.

 ### Purpose: 
 The Activenet export files are formatted for InDesign but contain many system-generated 
 fields that requires manual parsing, manipulation and reformatting in order to be fit 
 for publication.  This script automates this work; reformatting Date and Age fields using 
 strict pattern matching, while combining and re-sorting from multiple source files to 
 generated a unified output based in the original InDesign export format.

 ### Workflow Sequence:
 - A File Upload Form is displayed when no File Uploads are detected via POST in displayUploadForm
 - When a File Upload is sent via POST to this script, it processes it:
     1) It loops through the $_FILES['documents'] array to handle a single or many attachment(s)
     2) It parses each upload file directly from it's temp location without moving or touching them  
        thus ensuring security and that the files are automatically destroyed once the script finishes
     3) Each file is loaded as a string in ImportData
         a) This parses the Section Title using extractTitleContent
         b) and loops each Section through processActivityTypes
     4) processActivityTypes loops through each section's activityTypes
         a) building a Site listing for each activityType and sorted in processSites
         b) and building a course listing for each activityType's Sites and sorted in processCourses
     5) After importing, the schema of the document is mapped and each Section is run through TextProcessing
         a) looping through each section's activityTypes to reformat the Age field in ageProcessing
         b) then looping through each sites' courses and reformats the Date field in courseProcessing
     6) At this stage, all documents have imported and the report output is generated with renderedOutput
     7) You can also inspect the object of processed output with a method called outputObject

## Installation & Steps to set up the local environment:

The package contains the following file structure:

- activenet/index.php
- activenet/composer.json
- activenet/src/activenetParser.php
- activenet/test/activenetParserTest.php

### 1. Clone this repo:
	```
	git clone git@github.com:thinkflo/activenet-parser.git
	```

You can also [download](https://github.com/thinkflo/activenet-parser/archive/master.zip) the master, unzip, and move to your webroot.

```
cd /path/to/webroot;
wget https://github.com/thinkflo/activenet-parser/archive/master.zip;
unzip activenet-parser-master.zip;
mv ./activenet-parser-master/ ./activenet;
```

### 2. Switch to the activenet directory:
	```
	cd activenet
	```

### 3. Run the Docker command: 
	``` 
	docker compose up 
	``` 

After running this command, the terminal will display a series of log outputs indicating the initialization of the Docker containers. This will look something like this: 
``` 
[+] Running 2/0 ... (Output shortened for clarity) activenet-parser-web-1 | 2023/06/14 11:09:28 [notice] 1#1: start worker process 27 
```

### 4. Open a new tab and navigate to http://0.0.0.0/ or http://localhost/

---------------
Run the Unit and Integration tests to make sure everything passes and that the page is rendering successfully and is operational
```
vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php tests/activenetParserTest.php
```

If you see a screen similar to below then you know that the page is operating properly.
```
PHPUnit 8.5.33 by Sebastian Bergmann and contributors.

.................................                                 33 / 33 (100%)

Time: 211 ms, Memory: 4.00 MB

OK (33 tests, 41 assertions)
```

Now simply visit the url in a web browser: http://localhost


 PHP Command Manifest: 
 explode, list, array, intval, strstr, trim, empty, count, print, is_string, is_array, isset, strlen, utf8_encode
 preg_match, preg_replace_callback, preg_replace, file_exists, file_get_contents, strtotime, header, headers_sent