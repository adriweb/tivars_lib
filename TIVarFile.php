<?php
/*
 * Part of tivars_lib
 * (C) 2015 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

namespace tivars;

use tivars\TIVarType;

include_once "BinaryReadableFile.php";

class TIVarFile extends BinaryReadableFile
{

    private $header = null;
    private $varEntry = null;
    private $type = null;
    private $computedChecksum = null;
    private $inFileChecksum = null;


    public function __construct($filePath = null)
    {
        parent::__construct($filePath);
        $this->makeHeader();
        $this->makeVarEntry();
        $this->computedChecksum = $this->computeChecksum();
        $this->inFileChecksum = $this->getInFileChecksum();
        $this->type = $this->determineType();
    }

    private function makeHeader()
    {
        rewind($this->file);
        $this->header = [];
        $this->header['signature']   = $this->get_string_bytes(8);
        $this->header['sig_extra']   = $this->get_raw_bytes(3);
        $this->header['comment']     = $this->get_string_bytes(42);
        $this->header['entries_len'] = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
    }

    private function makeVarEntry()
    {
        $dataSectionOffset = (8+3+42+2); // after header
        fseek($this->file, $dataSectionOffset);
        $this->varEntry = [];
        $this->varEntry['constBytes']   = $this->get_raw_bytes(2);
        $this->varEntry['data_length']  = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        $this->varEntry['typeID']       = $this->get_raw_bytes(1)[0];
        $this->varEntry['varname']      = $this->get_string_bytes(8);
        $this->varEntry['version']      = $this->get_raw_bytes(1)[0];
        $this->varEntry['archivedFlag'] = $this->get_raw_bytes(1)[0];
        $this->varEntry['data_length2'] = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        $this->varEntry['data']         = $this->get_raw_bytes($this->varEntry['data_length']);
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getVarEntry()
    {
        return $this->varEntry;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSubType()
    {
        rewind($this->file);
        // TODO
    }

    public function isValid()
    {
        return $this->computedChecksum === $this->inFileChecksum;
    }

    // To override in subclasses (especially for programs, to detokenize)
    public function getFormattedData()
    {
        rewind($this->file);
        // TODO
    }

    public function fixChecksum()
    {
        if (!$this->isValid())
        {
            fseek($this->file, $this->fileSize - 2);
            fwrite($this->file, chr($this->computedChecksum & 0xFF) . chr($this->computedChecksum >> 8));
            $this->inFileChecksum = $this->getInFileChecksum();
        }
    }

    public function computeChecksum()
    {
        $dataSectionOffset = (8+3+42+2); // after header
        fseek($this->file, $dataSectionOffset);
        $sum = 0;
        for ($i = $dataSectionOffset; $i < $this->fileSize - 2; $i++)
        {
            $sum += $this->get_raw_bytes(1)[0];
        }
        return $sum & 0xFFFF;
    }

    public function getInFileChecksum()
    {
        fseek($this->file, $this->fileSize - 2);
        return $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
    }

    /**
     * @return \tivars\TIVarType
     */
    private function determineType()
    {
        return TIVarType::createFromID($this->varEntry['typeID']);
    }

}