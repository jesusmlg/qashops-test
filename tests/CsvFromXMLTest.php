<?php

use PHPUnit\Framework\TestCase;

final class CsvFromXMLTest extends TestCase
{
 
  protected $csv;

  protected function setUp(){
    $this->csv = new CsvFromXML();    
  }

  public function testFileExistsAndEqualsToResult(){
    $fileXml = __DIR__. '/../assets/doc.xml';
    $fileCsv = __DIR__ . '/result2.csv';
    $fileCsvResult = __DIR__ . '/result2OK.csv';
    
    $this->csv->convert($fileXml, $fileCsv);

    $this->assertFileExists($fileCsv);
    $this->assertFileEquals($fileCsv,$fileCsvResult);
  }
  
  
}