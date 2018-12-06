<?php

class CsvWriter
{
    private $path;
    private $file;

    public function setCsvPath($path)
    {
        if (!is_dir(dirname($path)))
            throw new Exception("Directory doesn't exists");
        $this->path = $path;
        $this->file = fopen($path, 'w');
    }

    public function writeLine($line)
    {
        fwrite($this->file, ($line . PHP_EOL));
    }

    public function save()
    {
        fclose($this->file);
    }
}
