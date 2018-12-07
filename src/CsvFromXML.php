<?php

include_once('CsvWriter.php');
/**
 * Clase para la conversión de un archivo xml con la siguiente estructura a CSV:
 * <xml>
 *  <items>
 *     <item></item>
 *     <item></item>
 *     ...
 *  </items>
 * </xml>
 * @package  Prueba Qashops
 * @author   Jesus María Luis Gil <jesusmlg@gmail.com>
 * @version  0.1
 * @access   public
 */
class CsvFromXML extends CsvWriter
{
  /**
   * cabeceras únicas del fichero csv
   * @var array
  */
  private $csvHeaders = [];

  /**
   * convierte 1 fichero xml en csv
   *
   * @param  string  $filePath ruta del documento xml que vamos a convertir
   * @return void  genera el archivo csv
   * @access public
   */
  public function convert(string $filePath, string $resultPath)
  {
    
    if (!$xmlString = @file_get_contents($filePath))
      throw new Exception('File not found in directory: ' . $filePath);

    if (!$xml = @simplexml_load_string($xmlString))
      throw new Exception('Incorrect xml structure in: ' . $filePath);

    $this->setCsvPath($resultPath);
    
    $this->csvHeaders = $this->getHeader($xml->children());

    $this->writeHeader();
    $this->writeBody($xml->children());

    $this->save();
  }

  /**
   * Guarda en la propiedad $this->csvHeaders las cabeceras del csv
   *
   * @param  SimpleXML-Object  array con los items del xml a recorrer
   * @return array  retorna un array con las cabeceras únicas que irán en el fichero csv
   * @access private
   */
  private function getHeader($items)
  {
    //Recorro todas las claves del array y elimino las duplicadas
    foreach ($items->children() as $item) {
      foreach ($item as $key => $attr) {
        array_push($this->csvHeaders, (string)$key);
      }
    }

    return array_unique($this->csvHeaders);
  }

  /**
   * Escribe la cabecera del fichero csv
   *
   * @return void  escribe en el fichero csv ls cabeceras
   * @access private
   */
  private function writeHeader()
  {
    $this->writeCsvLine(implode(';', $this->csvHeaders));
  }

  /**
   * Escribe los atributos del xml en el fichero csv linea a linea
   *
   * @param  SimpleXML-Object  array con los items del xml a recorrer
   * @return void  escribe en el fichero csv
   * @access private
   */
  private function writeBody($items)
  {
    $body = "";
    $firstItem = reset($items);

    foreach ($items->children() as $key => $item) {
      //El primer elemento es la cabecera y no la escribimos
      if ($item === $firstItem)
        continue;

      $line = "";
      /*
      Recorremos el array de cabeceras y vemos si en el item actual existe un atributo 
      con ese nombre de cabecera      
       */
      foreach ($this->csvHeaders as $header) {
        $line .= (isset($item->$header)) ? $item->$header . ";" : ";";
      }
      //Elimino el último ";" de la línea
      $line = substr($line, 0, strlen($line) - 1);
      
      $this->writeCsvLine($line);
      
    }
  }

}

$fileXml = __DIR__ . '/../assets/doc.xml';
$fileCsv = __DIR__ . '/../assets/result2.csv';

$xmlFromXML = new CsvFromXML();
$xmlFromXML->convert($fileXml, $fileCsv);
