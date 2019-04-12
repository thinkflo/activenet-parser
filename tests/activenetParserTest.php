<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require __DIR__ . "/../src/activenetParser.php";

final class ActivenetParserTest extends TestCase {

    protected $method;

    protected function setUp()
    {

        $_FILES = array(
            'documents' => array(
                'name' => array('test1.txt', 'test2.txt'),
                'type' => array('text/plain', 'text/plain'),
                'size' => array( 542, 542),
                'tmp_name' => array(
                    __DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-1.txt',
                    __DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-2.txt'
                ),
                'error' => array(0, 0)
            )
        );
        
        file_put_contents(__DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-1.txt','
<ASCII-WIN>
<Version:8><FeatureSet:InDesign-Roman><ColorTable:=<Black:COLOR:CMYK:Process:0,0,0,1><Red:COLOR:CMYK:Process:0,1,1,0.2><N Blue:COLOR:CMYK:Process:1,0.1,0,0.1><C\=0 M\=80 Y\=100 K\=10:COLOR:CMYK:Process:0,0.8,1,0.1>>
<DefineParaStyle:Header=<cSize:9>>
<DefineParaStyle:Course=<cSize:9>>
<DefineParaStyle:BrochureSubSection=<cSize:9>>
<DefineParaStyle:ActivityTitle=<cSize:9>>
<DefineParaStyle:ActivityDescription=<cSize:9>>
<DefineParaStyle:BrochureSection=<cSize:9>>
<DefineParaStyle:site=<cSize:9>>
<DefineParaStyle:numberofdates=<cSize:9>>
<DefineParaStyle:keyfeestotal=<cSize:9>>
<DefineParaStyle:activityinfo=<cSize:9>>
<ParaStyle:BrochureSection>Arts & Entertainment Programs
<ParaStyle:>
<ParaStyle:ActivityTitle>55+ Choir<0x000D><ParaStyle:ActivityDescription>Join this dynamic group and sing your heart out! The Choir is led by a professional musician and performs throughout the year at the McConaghy Centre and around the community. Please note the McConaghy Choir only accepts new members in September and January due to concert performances.<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$62.88/<ParaStyle:numberofdates>40<0x000D><ParaStyle:site>McConaghy Centre<0x000D>			<ParaStyle:Course>
Wed	Jun 4	10:00am-12:00pm	<ParaStyle:Course>40215

<ParaStyle:ActivityTitle>55+ Drawing, Sketching & More<0x000D><ParaStyle:ActivityDescription>Go from imagining a picture to making it. Learn simple  methods, skills and techniques that will give you the ability to express your  creative spirit without frustration. Create images of subjects that interest  you as you make marks in pencil, pencil crayon, ink or magic marker alone or as  mixed media. A basic supply list will be provided.<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$106.78/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>McConaghy Centre<0x000D>			<ParaStyle:Course>
Tue	Jul 1	1:00pm-3:00pm	<ParaStyle:Course>41783
        ');

        file_put_contents(__DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-2.txt','
<ASCII-WIN>
<Version:8><FeatureSet:InDesign-Roman><ColorTable:=<Black:COLOR:CMYK:Process:0,0,0,1><Red:COLOR:CMYK:Process:0,1,1,0.2><N Blue:COLOR:CMYK:Process:1,0.1,0,0.1><C\=0 M\=80 Y\=100 K\=10:COLOR:CMYK:Process:0,0.8,1,0.1>>
<DefineParaStyle:Header=<cSize:9>>
<DefineParaStyle:Course=<cSize:9>>
<DefineParaStyle:BrochureSubSection=<cSize:9>>
<DefineParaStyle:ActivityTitle=<cSize:9>>
<DefineParaStyle:ActivityDescription=<cSize:9>>
<DefineParaStyle:BrochureSection=<cSize:9>>
<DefineParaStyle:site=<cSize:9>>
<DefineParaStyle:numberofdates=<cSize:9>>
<DefineParaStyle:keyfeestotal=<cSize:9>>
<DefineParaStyle:activityinfo=<cSize:9>>
<ParaStyle:BrochureSection>Arts & Entertainment Programs
<ParaStyle:>
<ParaStyle:ActivityTitle>55+ Choir<0x000D><ParaStyle:ActivityDescription>Join this dynamic group and sing your heart out! The Choir is led by a professional musician and performs throughout the year at the McConaghy Centre and around the community. Please note the McConaghy Choir only accepts new members in September and January due to concert performances.<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$62.88/<ParaStyle:numberofdates>40<0x000D><ParaStyle:site>McConaghy Centre<0x000D>			<ParaStyle:Course>
Wed	Dec 4	10:00am-12:00pm	<ParaStyle:Course>40215

<ParaStyle:ActivityTitle>55+ Drawing, Sketching & More<0x000D><ParaStyle:ActivityDescription>Go from imagining a picture to making it. Learn simple  methods, skills and techniques that will give you the ability to express your  creative spirit without frustration. Create images of subjects that interest  you as you make marks in pencil, pencil crayon, ink or magic marker alone or as  mixed media. A basic supply list will be provided.<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$106.78/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>McConaghy Centre<0x000D>			<ParaStyle:Course>
Tue	Jan 1	1:00pm-3:00pm	<ParaStyle:Course>41783
        ');

        $this->method = new ActivenetParser(true);
    }

    protected function tearDown()
    {
        unset($_FILES);
        unset($this->method);
        @unlink(__DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-1.txt');
        @unlink(__DIR__ . '/THIS-IS-A-TEMPORARY-FILE-USED-FOR-TESTING-AND-WILL-BE-DELETED-2.txt');
    }

    /**
     * @group Unit
     */
    public function testExtractTitleContent(): void
    {
        $this->assertContains("<ParaStyle:ActivityTitle>Test Activity Title", $this->method->extractTitleContent("<ParaStyle:ActivityTitle>Test Activity Title<0x000D><ParaStyle:ActivityDescription>Description Field<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$105.74/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>Test Site<0x000D>			<ParaStyle:Course>", "<0x000D>"));
    }

    /**
     * @group Unit
     */
    public function testExtractTitleContentAndAge(): void
    {
        $this->assertContains("Age Test", $this->method->extractTitleContent("<ParaStyle:ActivityTitle>Test Activity Title<0x000D><ParaStyle:ActivityDescription>Description Field<0x000D><ParaStyle:BrochureSubSection>Age Test<0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$105.74/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>Test Site<0x000D>			<ParaStyle:Course>", "<0x000D>"));
    }

    /**
     * @group Unit
     */
    public function testExtractTitleContentAndAgeFailure(): void
    {
        $this->assertNotContains("Age Test", $this->method->extractTitleContent("<ParaStyle:ActivityTitle>Test Activity Title<0x000D><ParaStyle:ActivityDescription>Description Field<0x000D><ParaStyle:BrochureSubSectionERROR>Age Test<0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$105.74/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>Test Site<0x000D>			<ParaStyle:Course>", "<0x000D>"));
    }
    
    /**
     * @group Unit
     */
    public function testExtractTitleContentAndPrice(): void
    {
        $this->assertContains("123.45", $this->method->extractTitleContent("<ParaStyle:ActivityTitle>Test Activity Title<0x000D><ParaStyle:ActivityDescription>Description Field<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotal>$123.45/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>Test Site<0x000D>			<ParaStyle:Course>", "<0x000D>"));
    }
    
    /**
     * @group Unit
     */
    public function testExtractTitleContentAndPriceFailure(): void
    {
        $this->assertNotContains("123.45", $this->method->extractTitleContent("<ParaStyle:ActivityTitle>Test Activity Title<0x000D><ParaStyle:ActivityDescription>Description Field<0x000D><ParaStyle:BrochureSubSection><0x2003><0x2022><0x2003><ParaStyle:keyfeestotalERROR>$123.45/<ParaStyle:numberofdates>8<0x000D><ParaStyle:site>Test Site<0x000D>			<ParaStyle:Course>", "<0x000D>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingAtLeastDateRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>7 - 10 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 7 but less than 11<0x2003>"));
    }
    
    /**
     * @group Unit
     */
    public function testAgeProcessingLessThanAndYearRoundUp(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>Under 7 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>Less than 6y 11m 4w<0x2003>"));
    }
    
    /**
     * @group Unit
     */
    public function testAgeProcessingAndUp(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>4+ yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 4 and up<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingLessThan99(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>4+ yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 4 but less than 99<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingLessThan100(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>4+ yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 4 but less than 100<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingLessThanAndHalfYear(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>Under 4.5 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>Less than 4 1/2<0x2003>"));
    }
    
    /**
     * @group Unit
     */
    public function testAgeProcessingLessThan(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>Under 3 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>Less than 3<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingDateRangeWithMonths(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>12 - 14 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 11y 9m but less than 15<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingStripMonths(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>12 - 15 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 12 but less than 15y 3m<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingLessThan2(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>1.5 - 2.5 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 1 1/2 but less than 2y 6m 4w<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingStripWeeks(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>3 - 5 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 3 but less than 6y 4w<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingRoundUpYear(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>Under 7 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>Less than 6y 11m 4w<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingBabyMonths(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>2m - 11m<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 2m but less than 11m<0x2003>"));   
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingBabyMonthsAndYears(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>3m - 14m<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 3m but less than 1y 2m<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingOverTwoYearsWithMonths(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>1 - 2 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 1 but less than 2y 1m<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingBabyMonthsAndYearsRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>22m - 4 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 1y 10m but less than 5<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingMonthsAndYearsRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>3 - 5 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 2y 10m but less than 6<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingNineMonthsAndYearsRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>12 - 14 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 11y 9m but less than 15<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingTenMonthsAndYearsRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>12 - 15 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 12 but less than 15y 10m<0x2003>"));
    }

   /**
     * @group Unit
     */
    public function testAgeProcessingChildTenMonthsAndYearsRange(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSection>13 - 17 yrs<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 12y 10m but less than 18<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testAgeProcessingOutOfBoundsError(): void
    {
        $this->assertContains("<ParaStyle:BrochureSubSectionERROR>At least 9 but less than 8<0x2003>", $this->method->ageProcessing("<ParaStyle:BrochureSubSection>At least 9 but less than 8<0x2003>"));
    }

    /**
     * @group Unit
     */
    public function testCourseProcessingRemoveDateTabs(): void
    {
        $this->assertContains("Sun Apr 14	9:30am-5:30pm	<ParaStyle:Course>30000", $this->method->courseProcessing("Sun	Apr 14	9:30am-5:30pm	<ParaStyle:Course>30000"));
    }

    /**
     * @group Unit
     */
    public function testCourseProcessingDuplicateAM(): void
    {
        $this->assertContains("Sun,Fri,Sat Apr 26	6:30-10:30am	<ParaStyle:Course>29828", $this->method->courseProcessing("Sun,Fri,Sat	Apr 26	6:30am-10:30am	<ParaStyle:Course>29828"));
    }
    
    /**
     * @group Unit
     */
    public function testCourseProcessingDuplicatePM(): void
    {
        $this->assertContains("Sun,Fri,Sat Apr 26	6:30-10:30pm	<ParaStyle:Course>29828", $this->method->courseProcessing("Sun,Fri,Sat	Apr 26	6:30pm-10:30pm	<ParaStyle:Course>29828"));
    }
    
    /**
     * @group Unit
     */
    public function testCourseProcessingTopOfHourMinutes(): void
    {
        $this->assertContains("Sun,Fri,Sat Apr 26	6-10:30pm	<ParaStyle:Course>29828", $this->method->courseProcessing("Sun,Fri,Sat	Apr 26	6:00pm-10:30pm	<ParaStyle:Course>29828"));
    }

    /**
     * @group Unit
     */
    public function testCourseProcessingMonToFri(): void
    {
        $this->assertContains("Mon-Fri Jun 24	9am-6:30pm	<ParaStyle:Course>29830", $this->method->courseProcessing("Mon,Tue,Wed,Thu,Fri	Jun 24	9:00am-6:30pm	<ParaStyle:Course>29830"));
    }

    /**
     * @group Unit
     */
    public function testRenderUploadForm(): void
    {
        $this->tearDown();
        $this->method = new ActivenetParser();
        $this->expectOutputString($this->method->displayUploadForm());
    }

    /**
     * @group Integration
     */
    public function testDocumentMergeAndRender(): void
    {
        //Renders required Header Metadata
        $this->assertContains("<ASCII-WIN>", $this->method->renderedOutput());
        
        //Ensures that importData and textProcessing have run and the date fields have been transformed
        $this->assertContains("Wed Jun 4	10am-12pm	<ParaStyle:Course>40215", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Choir__62.88']['McConaghy Centre']);

        //Ensure that both Arts & Entertainment entries from the two files have merged successfully into the Master Document in this single ActivityType
        $this->assertSame(2, count($this->method->outputObject()['Arts & Entertainment Programs']['55+ Drawing, Sketching & More__106.78']['McConaghy Centre']));
        $this->assertArrayHasKey("1561986000_41783", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Drawing, Sketching & More__106.78']['McConaghy Centre']);
        
        //Ensures that the course entry from the second file was merged
        //Note: We cannot test the key because the timestamp changes as it adds a year for January since it uses the microseconds from execution time
        $this->assertContains("Tue Jan 1	1-3pm	<ParaStyle:Course>41783", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Drawing, Sketching & More__106.78']['McConaghy Centre']);

        //Ensure that both Arts & Entertainment entries from the two files have merged successfully into the Master Document for this ActivityType
        $this->assertSame(2, count($this->method->outputObject()['Arts & Entertainment Programs']['55+ Choir__62.88']['McConaghy Centre']));

        //Ensure that both Arts & Entertainment entries from the two files have merged successfully into the Master Document for this ActivityType
        $this->assertArrayHasKey("1559642400_40215", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Choir__62.88']['McConaghy Centre']);
        
        //Ensure that both Arts & Entertainment entries from the two files have merged successfully into the Master Document for this ActivityType
        $this->assertArrayHasKey("1575453600_40215", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Choir__62.88']['McConaghy Centre']);
        $this->assertContains("Wed Dec 4	10am-12pm	<ParaStyle:Course>40215", $this->method->outputObject()['Arts & Entertainment Programs']['55+ Choir__62.88']['McConaghy Centre']);
    }

}