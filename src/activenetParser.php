<?php

final class ActivenetParser {
    private $document;
    private $documentType;
    private $processedDocument;
    private $brochureSections;
    private $activityTypes;
    private $sites;
    private $output;

    public function __construct($quiet = false) {
        //Process any Files that were sent to the page via POST
        if (isset($_FILES['documents'])){
            if (is_array(($this->checkFilesUpload()))) {
                foreach($this->checkFilesUpload() as $file) {
                    //Process each file through importData
                    if (!empty($file) && file_exists($file)) $this->importData($file);
                }
                //Once all files are processed, render the output 
                if (!$quiet) print $this->renderedOutput();
            }
        } else {
            //If no files were sent, render the upload form
            if (!$quiet) print $this->displayUploadForm();
        }
    }

    public function displayUploadForm() {
        return trim(preg_replace("/[ ]{12}/","",'
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <meta name="description" content="">
                <meta name="author" content="Hal Wong">
                <meta name="generator" content="">
                <title>Process ActiveNet Files</title>
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
                <style>
                html,
                body {
                    height: 100%;
                }
                body {
                    display: -ms-flexbox;
                    display: flex;
                    -ms-flex-align: center;
                    align-items: center;
                    padding-top: 40px;
                    padding-bottom: 40px;
                    background-color: #f5f5f5;
                }
                .form-signin {
                    width: 100%;
                    max-width: 330px;
                    padding: 15px;
                    margin: auto;
                }
                .form-signin .checkbox {
                    font-weight: 400;
                }
                .form-signin .form-control {
                    position: relative;
                    box-sizing: border-box;
                    height: auto;
                    padding: 10px;
                    font-size: 16px;
                }
                .form-signin .form-control:focus {
                    z-index: 2;
                }
                </style>
                </head>
                <body class="text-center">
                    <form class="form-signin" enctype="multipart/form-data" action="" method="POST">
                    <h1 class="h3 mb-3 font-weight-normal">Upload your files for processing</h1>
                    <h6 class="h6 mb-3">(While choosing your files, press CTRL+Click to select multiple files)</h6>
                    <div class="checkbox mb-3">
                    <label>
                    <input type="checkbox" value="download" name="download" checked> Automatically Download Results
                    </label>
                    </div>
                    <label for="files" class="sr-only">Files</label>
                    <input type="file" name="documents[]"  multiple="multiple" class="form-control" required autofocus />
                    <button value="Upload" class="btn btn-lg btn-primary btn-block" type="submit">Upload</button>
                    <p class="mt-5 mb-3 text-muted">&copy; 2019</p>
                    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
                    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
                    </form>
                </body>
            </html>
        '));
    }

    public function checkFilesUpload() {
        $files = false;
        if (isset($_FILES['documents'])) {
            $files = array();
            foreach($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                if (file_exists($_FILES['documents']['tmp_name'][$key])) array_push($files, $_FILES['documents']['tmp_name'][$key]);
            }
        }
        return $files;
    }

    private function importData($file) {
        //read file as string
        $this->document = file_get_contents($file);
     
        //parse the full document into an array of sections
        $this->brochureSections = explode("<ParaStyle:BrochureSection>", $this->document);

        //Initialize Master Document with MetaTag Heading. We will parse the data and perform all the text transformations using this document
        if(!isset($this->processedDocument)) {
            $this->processedDocument = array('_Heading' => $this->brochureSections[0]);
        } 
        
        //parse each section, parsing the section Title and the sectionContent
        foreach($this->brochureSections as $index => $section) {         
            list($sectionTitle, $sectionContent) = $this->extractTitleContent($section, '<ParaStyle:>');
            if (!empty($sectionTitle)) $this->processActivityTypes($sectionTitle, $sectionContent);
        }

        //textProcessing loops through the structure of the document parsing all components to the Master Document
        return $this->textProcessing();
    }

    //Text Processing
    public function textProcessing() {
        //Processing for each main Section
        if (is_array($this->processedDocument)) foreach($this->processedDocument as $sectionTitle => $section) {
            
            //Processing for each activityType
            if (is_array($section)) foreach($section as $activityTypeTitle => $activityType) {
                
                //Processing for each Site                
                foreach($activityType as $siteTitle => $site) {
                    $this->processedDocument[$sectionTitle][$activityTypeTitle][$siteTitle] = $this->ageProcessing($site);

                    //Processing for each Course
                    if (is_array($site)) foreach($site as $siteName => $course) {
                        $this->processedDocument[$sectionTitle][$activityTypeTitle][$siteTitle][$siteName] = $this->courseProcessing($course);
                    }   
                }
            }
        }
        
        return $this->processedDocument;
    }

    //Function to Extract the Title based on a delimiter and to output the found Title with the remaining Content
    public function extractTitleContent($source, $delimiter) {
        $extractedTitle = trim(strstr($source, $delimiter, true));
        $extractedContent = strstr($source, $delimiter);        
        $extractedAge = null; $extractedPrice = null;
        preg_match('~[<]ParaStyle[:]BrochureSubSection[>](.*?)[<]0x2003[>]~', $extractedContent, $ageOutput);
        if (isset($ageOutput[1])) $extractedAge = $ageOutput[1];
        preg_match('~[<]ParaStyle[:]keyfeestotal[>][$](.*?)\/~', $extractedContent, $priceOutput);
        if (isset($priceOutput[1])) $extractedPrice = $priceOutput[1];
        return array($extractedTitle, $extractedContent, $extractedAge, $extractedPrice);
    }

    //function to Extract the Title of the ActivityType and to process all children Sites
    private function processActivityTypes($title, $content) {
        $ageGroup = null;
        //Parse each activityType per Section
        $this->activityTypes = explode("<ParaStyle:ActivityTitle>", $content);
        foreach($this->activityTypes as $activityType) {
            list($activityTypeTitle, $activityTypeContent, $activityAge, $extractedPrice) = $this->extractTitleContent($activityType, '<0x000D>');

            if (preg_match("/Preschool|Parent(.*?)Tot|Baby/i",$activityTypeTitle)) $ageGroup = "Preschool";
            if (preg_match("/Children/i",$activityTypeTitle)) $ageGroup = "Children";
            if (preg_match("/Teen/i",$activityTypeTitle)) $ageGroup = "Teen";
            if (preg_match("/55[+]/",$activityTypeTitle)) $ageGroup = "55";
            if (preg_match("/Adult(?![ ]55)/i",$activityTypeTitle)) $ageGroup = "Adult";

            //Eliminates non-standard empty lines without a Title (Random linefeeds, etc)
            if (!$activityTypeTitle) {
                continue;
            }

            $this->processSites($ageGroup, $title, $activityTypeTitle."_".$activityAge."_".$extractedPrice, $activityTypeContent);
        }
        return $this->processedDocument;
    }

    private function processSites($ageGroup = null, $section, $activityType, $content) {        
        $this->sites = explode("<ParaStyle:site>", $content);
        
        //parse each activityType into Sites
        foreach($this->sites as $index => $site) {
            $courses = explode("\n", $site);        
            list($siteName, $siteContent) = $this->extractTitleContent($courses[0], '<0x000D>');

            //Merge all of the found content into master document
            if (!empty($section) && !empty($activityType)) {
                if (empty($siteName)) {
                    //Store the Metadata Heading of each ActivityType
                    $this->processedDocument[$section][$activityType]['_Heading'] = $courses[0];
                    continue;
                }
                $this->processCourses($section, $activityType, $siteName, $courses);
            }
        }
        return $this->processedDocument;
    }

    private function processCourses($section, $activityType, $siteName, $courses) {
        foreach($courses as $course) {

            if (strlen($course) > 1) {
                //Only process Courses if they have a Date structure (eliminates linefeeds, and malformed rows)
                $foundDate = preg_match("/\t(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[ ]\d?\d\t\d?\d[:]\d\d(a|p)m/", $course, $courseDate);
                if ($foundDate) {                    
                    $winterMonths = preg_match("/\t(Jan|Feb|Mar)[ ]\d?\d\t\d?\d[:]\d\d(a|p)m/", $course, $winterMonths);
                    $courseNumber = explode("<ParaStyle:Course>", $course);

                    if ($winterMonths) {
                        //If a winter month is found, add a year to the winter course to make it precede the fall courses
                        $this->processedDocument[$section][$activityType][$siteName][strtotime($winterMonths[0].'+1 year')."_".$courseNumber[1]] = $course;
                    } else {
                        if (is_array($courseDate) && isset($courseDate[0])) $this->processedDocument[$section][$activityType][$siteName][strtotime($courseDate[0])."_".$courseNumber[1]] = $course;
                    }
                }
            }
        }
        //k(ey)Sort all the Courses by the unixtimestamp of the date
        if (isset($this->processedDocument[$section][$activityType][$siteName]) && is_array($this->processedDocument[$section][$activityType][$siteName])) ksort($this->processedDocument[$section][$activityType][$siteName]);
        return $this->processedDocument;
    }

    public function ageProcessing($content) {
        if (!is_string($content)) {
            return false;
        }
        $extractedAge = "";

        preg_match('~[<]ParaStyle[:]BrochureSubSection[>](.*?)[<]0x2003[>]~', $content, $ageOutput);
        if (!empty($ageOutput[1])) $extractedAge = $ageOutput[1];
    
        //Rule when it sees only Year and Weeks without Months
        $extractedAge =  preg_replace_callback(
            "/(\d+)y (\d+)w/",
            function ($matches) {
                //Convert to months if less than 2 years
                if ($matches[1] < 3) {
                    return intval($matches[1] * 12 + $matches[2])."m";
                } else {
                    //Otherwise strip weeks as it is erroneous 
                    return intval($matches[1]);
                }                
            },
            $extractedAge
        );
        
        //Rule when it sees only Year and Months without Weeks
        $extractedAge =  preg_replace_callback(
            "/(\d+)y (\d+)m(?! \dw)/",
            function ($matches) {
                //When less than 2 years, use months to round up
                if ((intval($matches[1] * 12 + $matches[2])) > 24) {
                    return intval($matches[1] + 1);    
                } else {
                    return intval($matches[1] * 12 + $matches[2])."m";
                }           
            },
            $extractedAge
        );
        
        //Rule when it sees only Xy 11m 4w which means to add a year to X
        $extractedAge =  preg_replace_callback(
            "/(\d+)y 11m \d{1,2}w/",
            function ($matches) {
                return intval($matches[1]+1);
            },
            $extractedAge
        );


        //Rule when it sees only Xy 11w, strip the weeks
        $extractedAge =  preg_replace_callback(
            "/(\d\d?+) 11w/",
            function ($matches) {
                return intval($matches[1]);
            },
            $extractedAge
        );

        //Rule to round up to half year
        $extractedAge =  preg_replace_callback(
            "/(\d+)y (5|6)m 4w/",
            function ($matches) {
                return intval($matches[1]+1).".5";
            },
            $extractedAge
        );
                
        $ageFind = array(
            "/^At least /",
            "/( and up| but less than (99|100))$/",
            "/(?<=\d) 1\/2/",
            "/^Less than /"
        );

        $ageReplace = array(
            "",
            "+",
            ".5",
            "Under "
        );

        $extractedAge = preg_replace( $ageFind, $ageReplace, $extractedAge );

        //Year Comparsion Rule
        $extractedAge =  preg_replace_callback(
            "/(\d?+) but less than (\d{1,2}+)(?!m)/",
            function ($matches) {
                return trim($matches[1])."-".($matches[2]==1?"12m":$matches[2]-1);
            },
            $extractedAge
        );

        //Year Comparsion Rule
        $extractedAge =  preg_replace_callback(
            "/(\dm +) but less than (\d{1,2}+)(?!m)/",
            function ($matches) {
                return trim($matches[1])."-".($matches[2]==1?"12m":$matches[2]-1);
            },
            $extractedAge
        );

        //Month Comparsion Rule
        $extractedAge =  preg_replace_callback(
            "/(\d?+) but less than (\d{1,2}+)m/",
            function ($matches) {
                return trim($matches[1])."-".intval($matches[2])."m";
            },
            $extractedAge
        );

        //Exception Tests
        $errorCondition = ""; //No error condition by default

        //If any entries failed the conversion, flag them
        preg_match("/At least|[lL]ess than|and up/", $extractedAge, $compare);
        if (!empty($compare)) {
            $errorCondition = "ERROR";
            $extractedAge = $ageOutput[1];
        };

        //Convert months to be able to properly compare against years
        $compare = explode("-", $extractedAge);
        foreach($compare as $key => &$value) {

            //Catch Edge Case where months are only specified on the left
            if (preg_match("/(\d{1,2})m/", $compare[0]) && !preg_match("/(\d{1,2})m/", $compare[1])) $compare[1] = intval($compare[1]) * 12;

            $value =  preg_replace_callback(
                "/(\d?+)m/",
                function ($matches) {
                    return intval($matches[0])/12;
                },
                $value
            );
        }

        //If the age range is out of bound, flag it
        if (count($compare) == 2 && $compare[0] >= $compare[1]) {
            $errorCondition = "ERROR";
            $extractedAge = $ageOutput[1];    
        }

        //If the age is empty, flag it
        if (empty($extractedAge)) {
            $errorCondition = "ERROR";
            $extractedAge = "No Age Provided";    
        }

        //Add Years Label
        $yearRange = preg_match( "/(-\d{1,2}½$)|(-\d{1,2}$)|(^\d{1,2}[+]$)|(\d{1,2}[.]5$)|(^Under \d{1,2})/", $extractedAge, $yearRange);
        if (!empty($yearRange)) $extractedAge .= " yrs";

        //Add spaced dash
        $extractedAge = preg_replace("/-/", " - ", $extractedAge);

        return preg_replace("/[<]ParaStyle[:]BrochureSubSection[>](.*?)[<]0x2003[>]/", "<ParaStyle:BrochureSubSection".$errorCondition.">".$extractedAge."<0x2003>", $content);
    }

    public function courseProcessing($content) {
        $processedCourse = $content;

        $findDateTabs = array(
            "/(?<=(Mon|Tue|Wed|Thu|Fri|Sat|Sun))\t/" //Tab after Day of Week
        );
        
        $processedCourse = preg_replace( $findDateTabs, " ", $processedCourse );
        
        //Abbreviate Mon-Fri
        $processedCourse = preg_replace( "/^Mon,Tue,Wed,Thu,Fri /", "Mon-Fri ", $processedCourse );

        $removalRules = array(
            "/\r|\n/", //linefeeds
            "/((?<=\d:\d\d)am(?=-\d?\d:\d\dam))|(?<=\d:\d\d)pm(?=-\d?\d:\d\dpm)/", //duplicate AM/PM field
            "/(?<=\d):00(?=((a|p)m)?|-)/", //top of the hour minutes
        );

        //Process Text Rules
        return preg_replace( $removalRules, "", $processedCourse  );
    }

    public function outputObject() {
        return $this->processedDocument;
    }

    public function renderedOutput() {
        if (is_array($this->processedDocument)) {
            //Processing for each main Section
            foreach($this->processedDocument as $sectionTitle => $section) {

                //Skip Sections Drop-In Programs and TRH Employees Only 
                preg_match("/(Drop[-]In Programs|TRH Employees Only)/", $sectionTitle, $matches);
                if (!empty($matches[0])) {
                    continue;
                }

                if ($sectionTitle == "_Heading") {
                    $this->output = $section;
                    continue;
                }   

                $this->output .= "<ParaStyle:BrochureSection>".$sectionTitle."\r\n<ParaStyle:>\r\n";

                //Processing for each activityType
                foreach($section as $activityTypeTitle => $activityType) {

                    $this->output .= "<ParaStyle:ActivityTitle>".explode("_", $activityTypeTitle)[0];
                    
                    //Processing for each Site
                    foreach($activityType as $siteTitle => $site) {
                        if ($siteTitle == "_Heading") {
                            $this->output .= $site;
                            continue;
                        }

                        $this->output .= "<ParaStyle:site>".$siteTitle;

                        //Processing for each Course
                        foreach($site as $key => $course) {
                            $this->output .= "<ParaStyle:Course>\r\n".$course;
                        }
                        $this->output .= "\r\n<ParaStyle:>\r\n";    
                    }   
                }
            }
        }

        if (!headers_sent()) {
            if (isset($_POST['download'])) {
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Cache-Control: private', false);
                header('Content-Type: application/octet-stream; charset=Windows-1252);');
                header('Content-Disposition: attachment; filename="'.time()."_activenet_fixed" . '.txt";');
                header('Content-Transfer-Encoding: binary');    
            } else {
                header('Content-Type: text/plain; charset=Windows-1252);');
            }    
        }
        
        return iconv( "Windows-1252", 'Windows-1252//TRANSLIT', $this->output );
    }
}
?>