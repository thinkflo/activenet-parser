# activenet-parser

 Processes one or many plain-text Brochure Export files from ActiveNet's Front Desk Web Application and provides a single, clean output in the original export format

 Activenet File Parser v1.0.2 
 ----------------------------
 Build: April 10, 2019

 Requirements: 
 PHP7.1+ (configured with file_uploads=On, magic_quotes_gpc=On), PHPUnit7.3+
 (Backwards compatible with PHP5.4+)

 Author: 
 Hal Wong (hal@thinkflo.com)

 Description:
 Processes one or many plain-text Brochure Export files from ActiveNet's Front Desk Web Application 
 and provides a single, clean output in the original export format.

 Purpose: 
 The Activenet export files are formatted for InDesign but contain many system-generated 
 fields that requires manual parsing, manipulation and reformatting in order to be fit 
 for publication.  This script automates this work; reformatting Date and Age fields using 
 strict pattern matching, while combining and re-sorting from multiple source files to 
 generated a unified output based in the original InDesign export format.

 Workflow Sequence:
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

 PHP Command Manifest: 
 explode, list, array, intval, strstr, trim, empty, count, print, is_string, is_array, isset, strlen, utf8_encode
 preg_match, preg_replace_callback, preg_replace, file_exists, file_get_contents, strtotime, header, headers_sent

 Execute Tests:
 phpunit --bootstrap vendor/autoload.php tests/activenetParserTest.php
