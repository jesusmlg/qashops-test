<?php

use PHPUnit\Framework\TestCase;

final class CsvMergeTest extends TestCase
{
 
  protected $csv1;

  protected function setUp(){
    $this->csv1 = new CsvMerge();    
  }

  public function testFileExistsAndEqualsToResult(){
    $fileCsv1 = __DIR__ . '/../assets/csv1.csv';
    $fileCsv2 = __DIR__ . '/../assets/csv2.csv';
    $fileCsvMerged= __DIR__ . '/result1.csv';
    $fileCsvResult = __DIR__  .'/result1OK.csv';
    
    $this->csv1->merge($fileCsv1, $fileCsv2,$fileCsvMerged);

    $this->assertFileExists($fileCsvMerged);
    $this->assertFileEquals($fileCsvMerged,$fileCsvResult);
  }
  
  
}