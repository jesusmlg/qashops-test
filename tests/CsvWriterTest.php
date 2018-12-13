<?php

use PHPUnit\Framework\TestCase;

final class CsvWriterTest extends TestCase
{
  public function testWriteLine(){
    $filePath = './hello.csv';
    $str = "hello world";
    $csv = new CsvWriter();

    $csv->setCsvPath($filePath);        
    $csv->writeCsvLine($str);
    $csv->save();
    unset($csv);
    
    $file = fopen($filePath,'r');

    $result = file_get_contents($filePath);

    $this->assertEquals($str. PHP_EOL,$result);

    
  }
  
  
}