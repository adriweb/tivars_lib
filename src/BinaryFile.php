<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

date_default_timezone_set('UTC');

class BinaryFile
{
    protected $file;
    protected $filePath;
    protected $fileSize;

    /**
     * @param string|null $filePath
     * @throws \Exception
     */
    protected function __construct($filePath = null)
    {
        if ($filePath !== null)
        {
            if (file_exists($filePath))
            {
                $filePath = realpath($filePath);
                $this->file = fopen($filePath, 'rb+');
                if ($this->file === false)
                {
                    throw new \RuntimeException("Can't open the input file");
                }
                $this->filePath = $filePath;
                $this->fileSize = fstat($this->file)['size'];
            } else {
                throw new \RuntimeException('No such file');
            }
        } else {
            throw new \InvalidArgumentException('No file path given');
        }
    }

    /**
     * Returns an array of $bytes bytes read from the file
     *
     * @param   int $bytes
     * @return  array
     * @throws  \Exception
     */
    public function get_raw_bytes($bytes = -1)
    {
        if ($this->file !== null)
        {
            if ($bytes !== -1)
            {
                return array_merge(unpack('C*', fread($this->file, $bytes)));
            } else {
                throw new \InvalidArgumentException('Invalid number of bytes to read');
            }
        } else {
            throw new \RuntimeException('No file loaded');
        }
    }

    /**
     * Returns a string of $bytes bytes read from the file (doesn't stop at NUL)
     *
     * @param   int $bytes The number of bytes to read
     * @return  string
     * @throws  \Exception
     */
    public function get_string_bytes($bytes = -1)
    {
        if ($this->file !== null)
        {
            if ($bytes !== -1)
            {
                return fread($this->file, $bytes);
            } else {
                throw new \InvalidArgumentException('Invalid number of bytes to read');
            }
        } else {
            throw new \RuntimeException('No file loaded');
        }
    }

    public function close()
    {
        if ($this->file !== null)
        {
            fclose($this->file);
            $this->file = null;
        }
    }

    public function size()
    {
        if ($this->file !== null)
        {
            return $this->fileSize;
        } else {
            throw new \RuntimeException('No file loaded');
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}