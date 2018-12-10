<?php

/**
 * Clase para escribir en un fichero CSV
 * @package  Prueba Qashops
 * @author   Jesus María Luis Gil <jesusmlg@gmail.com>
 * @version  0.1
 * @access   public
 */
class CsvWriter
{
    /**
     * ruta del fichero csv
     * @var string
     */
    private $path;
    /**
     * fichero donde se escribirán los datos
     * @var resource
     */
    private $file;

    /**
     * convierte 1 fichero xml en csv
     *
     * @param  string $path ruta del fichero que vamos a utilizar
     * @return void    abre el fichero en modo escritura
     * @access public
     * @throws Exception si el directorio no existe
     */
    public function setCsvPath($path)
    {
        if (!is_dir(dirname($path)))
            throw new Exception("Directory doesn't exists");
        $this->path = $path;
        $this->file = fopen($path, 'w');
    }

    /**
     * convierte 1 fichero xml en csv
     *
     * @param  string  $line de datos a escribir en el fichero
     * @return void    escribe los datos proporcionados en el fichero
     * @access public
     */
    public function writeCsvLine($line)
    {
        fwrite($this->file, ($line . PHP_EOL));
    }

    /**
     * cierra el puntero
     *
     * @return void    Cierra el puntero
     * @access public
     */
    public function save()
    {
        fclose($this->file);
    }
}
