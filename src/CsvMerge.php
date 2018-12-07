<?php

include_once('CsvWriter.php');

/**
 * Clase para la convinación de 2 ficheros csv en 1
 * @package  Prueba Qashops
 * @author   Jesus María Luis Gil <jesusmlg@gmail.com>
 * @version  0.1
 * @access   public
 */
class CsvMerge extends CsvWriter
{
  /**
   * cabeceras únicas del fichero csv
   * @var array
  */
  private $header = [];
  /**
   * líneas del fichero csv que vamos a escribir sin la cabecera
   * @var array
  */
  private $body = [];
  /**
   * combina dos ficheros csv en uno único
   *
   * @param  string  $file1Path primer fichero
   * @param  string  $file2Path segundo fichero
   * @param  string  $resultPath ruta del fichero donde se guardará el merge
   * @return void    genera el archivo csv
   * @access public
   */
  public function merge($file1Path, $file2Path, $resultPath)
  {
    if (!file_exists($file1Path) || !file_exists($file2Path))
      throw new Exception("Bad path in file1 or file2");

    $this->setCsvPath($resultPath);

    $file1InArray = array_map('str_getcsv', file($file1Path));
    $file2InArray = array_map('str_getcsv', file($file2Path));

    $this->createHeader(array($file1InArray, $file2InArray));
    $this->createBody(array($file1InArray, $file2InArray));


    echo $this->writeMergedCsv();

    $this->save();

  }


  /**
   * busca las cabeceras comunes en los dos ficheros y elimina duplicados
   *
   * @param  array  $filesInArrays contine un array con los ficheros csv mapeados en arrays
   * @return void    guarda en un array las cabecerás
   * @access private
   */
  private function createHeader(array $filesInArrays)
  {
    $row = [];
    //leo de los archivos la primera fila que son las cabeceras
    foreach ($filesInArrays as $fileInArray) {
      foreach ($fileInArray[0] as $header) {
        //sólo guardo las que no estén repetidas
        if (!in_array($header, $row))
          array_push($row, $header);
      }
    }

    array_push($this->header, $row);
  }

  /**
   * escribe la cabecera y el body
   *
   * @return void    escribe los datos en el fichero csv
   * @access private
   */
  private function writeMergedCsv()
  {
    $this->writeLineInCSV($this->header);
    $this->writeLineInCSV($this->body);
  }

  /**
   * escribe las lineas en el fichero csv
   *
   * @param  array  $csvInArray array con los datos mapeados del fichero csv
   * @return void    escribe las lineas en el fichero csv
   * @access private
   */
  private function writeLineInCSV(array $csvInArray)
  {
    foreach ($csvInArray as $value) {
      $this->writeCsvLine(implode(",", $value));
    }
  }

  /**
   * busca el indice de la cabecera cuyo nombre se le pasa por parámetro
   *
   * @param  string  $headerName nombre de la cabecera para buscar su índice
   * @return int    devuelve la posición de la cabecera
   * @access private
   */
  private function findHeaderIndex($headerName)
  {
    return array_search($headerName, $this->header[0]);
  }

  /**
   * recorre los dos ficheros y aplica la función createBodyFile
   *
   * @param  array  $csvFilesArr ficheros csv convertirdos a array
   * @return void    devuelve la posición de la cabecera
   * @access private
   */
  private function createBody($csvFilesArr)
  {
    array_map(array($this, 'createBodyFile'), $csvFilesArr);
  }

  /**
   * escribe los datos de cada array con los datos del csv
   *
   * @param  array  $csvInArray Datos mapeados del fichero csv
   * @return void    escribe los datos del body en el fichero csv
   * @access private
   */
  public function createBodyFile($csvInArray)
  {
    $firstItem = reset($csvInArray);
    //Si son las cabeceras paso a la siguiente iteración
    foreach ($csvInArray as $item) {
      if ($firstItem === $item)
        continue;

      $csvLine = [];
      //recorro todas los elementos que corresponden a las líneas del csv
      foreach ($item as $key => $row) {
        //guardo el nombre de la cabecera que corresponde a este valor
        $nameOfHeader = $csvInArray[0][$key];
        //busco el índice que le corresponde con respecto a la posición en los header
        $indexOfHeader = $this->findHeaderIndex($nameOfHeader);
        //lo almaceno en la posición correspondiente
        $csvLine[$indexOfHeader] = $row;
      }
      //relleno de vacíos los índices que no se han utilizado y ordeno
      $csvLine = $this->fillWithBlanks($csvLine);
      ksort($csvLine);
      array_push($this->body, $csvLine);
    }
  }

  /**
   * escribe datos en blanco en los índices que no existen para que todos
   * las líneas tengan el mismo número de elementos que elementos tiene
   * la cabecera final
   *
   * @param  array  $csvLine linea con los datos de cada línea del csv
   * @return void    escribe los datos del body en el fichero csv
   * @access private
   */
  private function fillWithBlanks($csvLine)
  {
    for ($i = 0; $i < count($this->header[0]); $i++) {
      //si no existe ese índice lo creo que valor vacío
      $csvLine[$i] = (!isset($csvLine[$i])) ? "" : $csvLine[$i];
    }

    return $csvLine;
  }
}
$file1 = __DIR__  . '/../assets/csv1.csv';
$file2 = __DIR__  . '/../assets/csv2.csv';
$fileCsv= __DIR__  . '/../assets/result.csv';

$xml = new CsvMerge();
$xml->merge($file1, $file2, $fileCsv);
