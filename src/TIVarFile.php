<?php
/*
 * Part of tivars_lib
 * (C) 2015-2017 Adrien 'Adriweb' Bertrand
 * https://github.com/adriweb/tivars_lib
 * License: MIT
 */

// TODO: Handle multiple varEntries

namespace tivars;

require_once 'BinaryFile.php';

class TIVarFile extends BinaryFile
{
    const headerLength      = 55;   // 8+3+42+2, see $header array below
    const dataSectionOffset = 55;   // == headerLength
    const varEntryOldLength = 0x0B; // 2+1+8     (if $calcFlags <  TIFeatureFlags::hasFlash)
    const varEntryNewLength = 0x0D; // 2+1+8+1+1 (if $calcFlags >= TIFeatureFlags::hasFlash)

    private $header = [
        'signature'     => null, //  8 bytes
        'sig_extra'     => null, //  3 bytes
        'comment'       => null, // 42 bytes
        'entries_len'   => null  //  2 bytes
    ];

    private $varEntry = [
        'entryMetaLen'  => null, //  2 bytes (byte count of the next 3 or 5 fields (== 11 or 13) depending on $calcFlags, see below)
        'data_length'   => null, //  2 bytes
        'typeID'        => null, //  1 byte
        'varname'       => null, //  8 bytes
        'version'       => null, //  1 byte (present only if $calcFlags >= TIFeatureFlags::hasFlash)
        'archivedFlag'  => null, //  1 byte (present only if $calcFlags >= TIFeatureFlags::hasFlash)
        'data_length2'  => null, //  2 bytes, same as data_length
        'data'          => null  //  n bytes
    ];

    /** @var TIVarType */
    private $type;

    /** @var TIModel */
    private $calcModel;

    private $computedChecksum;
    private $inFileChecksum;
    private $isFromFile;

    private $corrupt;

    /*** Constructors ***/

    /**
     * Internal constructor, called from loadFromFile and createNew.
     * @param   string  $filePath
     * @throws  \Exception
     */
    protected function __construct($filePath = '')
    {
        if ($filePath !== '')
        {
            $this->isFromFile = true;
            parent::__construct($filePath);
            if ($this->fileSize < 76) // bare minimum for header + a var entry
            {
                throw new \RuntimeException('This file is not a valid TI-[e]z80 variable file');
            }
            $this->makeHeaderFromFile();
            $this->makeVarEntryFromFile();
            $this->computedChecksum = $this->computeChecksumFromFileData();
            $this->inFileChecksum = $this->getChecksumValueFromFile();
            if ($this->computedChecksum !== $this->inFileChecksum) {
                //echo "[Warning] File is corrupt (read and calculated checksums differ)\n";
                $this->corrupt = true;
            }
            $this->type = TIVarType::createFromID($this->varEntry['typeID']);
            $this->close(); // let's free the resource up as early as possible
        } else {
            $this->isFromFile = false;
        }
    }

    public static function loadFromFile($filePath = '')
    {
        if ($filePath !== '')
        {
            return new self($filePath);
        } else {
            throw new \InvalidArgumentException('No file path given');
        }
    }

    public static function createNew(TIVarType $type = null, $name = '', TIModel $model = null)
    {
        if ($type !== null)
        {
            $instance = new self();
            $instance->type = $type;
            $instance->calcModel = ($model !== null) ? $model : TIModel::createFromName('84+'); // default

            $name = $instance->fixVarName($name);

            if (!$instance->calcModel->supportsType($instance->type))
            {
                throw new \RuntimeException('This calculator model (' . $instance->calcModel->getName() . ') does not support the type ' . $instance->type->getName());
            }

            $instance->header = [
                'signature'     =>  $instance->calcModel->getSig(),
                'sig_extra'     =>  [ 0x1A, 0x0A, 0x00 ],
                'comment'       =>  str_pad('Created by tivars_lib on ' . date('M j, Y'), 42, "\0"),
                'entries_len'   =>  0 // will have to be overwritten later
            ];

            // Default cases for >= TIFeatureFlags::hasFlash. It's fixed right after if necessary.
            // This is done that way because the field order is important - the ones that change can't simply be added afterwards.
            $instance->varEntry = [
                'entryMetaLen'  =>  [ self::varEntryNewLength, 0x00 ],
                'data_length'   =>  0, // will have to be overwritten later
                'typeID'        =>  $type->getId(),
                'varname'       =>  str_pad($name, 8, "\0"),
                'version'       =>  0, // present for >= hasFlash ; may be removed after
                'archivedFlag'  =>  0, // present for >= hasFlash ; may be removed after // TODO: check when that needs to be 1.
                'data_length2'  =>  0, // will have to be overwritten later
                'data'          =>  [] // will have to be overwritten later
            ];

            // Deal with the hasFlash flag "issue" mentioned above.
            if ($instance->calcModel->getFlags() < TIFeatureFlags::hasFlash)
            {
                $instance->varEntry['entryMetaLen'] = [ self::varEntryOldLength, 0x00 ];
                unset($instance->varEntry['version'], $instance->varEntry['archivedFlag']);
            }

            return $instance;
        } else {
            throw new \InvalidArgumentException('No type given');
        }
    }


    /*** Makers ***/

    private function makeHeaderFromFile()
    {
        rewind($this->file);
        $this->header = [];
        $this->header['signature']   = $this->get_string_bytes(8);
        $this->header['sig_extra']   = $this->get_raw_bytes(3);
        $this->header['comment']     = $this->get_string_bytes(42);
        $this->header['entries_len'] = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        $this->calcModel = TIModel::createFromSignature($this->header['signature']);
    }

    private function makeVarEntryFromFile()
    {
        fseek($this->file, self::dataSectionOffset);
        $this->varEntry = [];
        $this->varEntry['entryMetaLen'] = $this->get_raw_bytes(2);
        $this->varEntry['data_length']  = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        $this->varEntry['typeID']       = $this->get_raw_bytes(1)[0];
        $this->varEntry['varname']      = $this->get_string_bytes(8);
        if ($this->calcModel->getFlags() >= TIFeatureFlags::hasFlash)
        {
            $this->varEntry['version']      = $this->get_raw_bytes(1)[0];
            $this->varEntry['archivedFlag'] = $this->get_raw_bytes(1)[0];
        }
        $this->varEntry['data_length2'] = $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        $this->varEntry['data']         = $this->get_raw_bytes($this->varEntry['data_length']);
    }


    /*** Getters ***/

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


    /*** Private actions ***/

    public function computeChecksumFromFileData()
    {
        if ($this->isFromFile)
        {
            fseek($this->file, self::dataSectionOffset);
            $sum = 0;
            for ($i = self::dataSectionOffset; $i < $this->fileSize - 2; $i++)
            {
                $sum += $this->get_raw_bytes(1)[0];
            }
            return $sum & 0xFFFF;
        } else {
            throw new \RuntimeException('No file loaded to compute checksum from');
        }
    }

    private function computeChecksumFromInstanceData()
    {
        $sum = 0;
        $sum += array_sum($this->varEntry['entryMetaLen']);
        $sum += 2 * (($this->varEntry['data_length'] & 0xFF) + (($this->varEntry['data_length'] >> 8) & 0xFF));
        $sum += $this->varEntry['typeID'];
        $sum += array_sum(array_map('ord', str_split($this->varEntry['varname'])));
        $sum += array_sum($this->varEntry['data']);
        if ($this->calcModel->getFlags() >= TIFeatureFlags::hasFlash)
        {
            $sum += $this->varEntry['version'];
            $sum += $this->varEntry['archivedFlag'];
        }
        return ($sum & 0xFFFF);
    }

    private function getChecksumValueFromFile()
    {
        if ($this->isFromFile)
        {
            fseek($this->file, $this->fileSize - 2);
            return $this->get_raw_bytes(1)[0] + ($this->get_raw_bytes(1)[0] << 8);
        } else {
            throw new \RuntimeException('No file loaded to compute checksum from');
        }
    }

    /**
     *  Updates the length fields in both the header and the var entry, as well as the checksum
     */
    private function refreshMetadataFields()
    {
        // todo : recompute correctly for multiple var entries
        $this->varEntry['data_length2'] = $this->varEntry['data_length'] = count($this->varEntry['data']);

        // The + 2 + 2 is because of the length of both length fields themselves
        $this->header['entries_len'] = 2 + 2 + $this->varEntry['data_length']
                                     + ($this->calcModel->getFlags() >= TIFeatureFlags::hasFlash ? self::varEntryNewLength
                                                                                                 : self::varEntryOldLength);

        $this->computedChecksum = $this->computeChecksumFromInstanceData();
    }

    private function fixVarName($name = '')
    {
        if ($name === '')
        {
            $name = 'FILE' . ((count($this->type->getExts()) > 0) ? $this->type->getExts()[0] : '');
        }
        $newName = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        if ($newName !== $name || strlen($newName) > 8 || $newName === '' || is_numeric($newName[0]))
        {
            throw new \InvalidArgumentException('Invalid name given. 8 chars (A-Z, 0-9) max, starting by a letter');
        }
        $name = strtoupper(substr($name, 0, 8));

        return $name;
    }

    /*** Public actions **/

    /**
     * @param   array $data The array of bytes
     * @throws  \Exception
     */
    public function setContentFromData(array $data = [])
    {
        if ($data !== [])
        {
            $this->varEntry['data'] = $data;
            $this->refreshMetadataFields();
        } else {
            throw new \InvalidArgumentException('No data given');
        }
    }

    public function setContentFromString($str = '', array $options = [])
    {
        $handler = $this->type->getTypeHandler();
        $this->varEntry['data'] = $handler::makeDataFromString($str, $options);
        $this->refreshMetadataFields();
    }

    public function setCalcModel(TIModel $model = null)
    {
        if ($model !== null)
        {
            $this->calcModel = $model;
            $this->header['signature'] = $model->getSig();
        } else {
            throw new \InvalidArgumentException('No model given');
        }
    }

    public function setVarName($name = '')
    {
        $name = TIVarFile::fixVarName($name);
        $this->varEntry['varname'] = str_pad($name, 8, "\0");
        $this->refreshMetadataFields();
    }

    public function setArchived($flag)
    {
        if ($this->calcModel->getFlags() >= TIFeatureFlags::hasFlash)
        {
            $this->varEntry['archivedFlag'] = ($flag === true) ? 1 : 0;
            $this->refreshMetadataFields();
        } else {
            throw new \RuntimeException('Archived flag not supported on this calculator model');
        }
    }

    public function getRawContent()
    {
        return $this->varEntry['data'];
    }

    public function getReadableContent(array $options = [])
    {
        $handler = $this->type->getTypeHandler();
        return $handler::makeStringFromData($this->varEntry['data'], $options);
    }

    /**
     * Writes a variable to an actual file on the FS
     * If the variable was already loaded from a file, it will be used and overwritten,
     * except if a specific directory and name are provided.
     *
     * @param   string $directory Directory to save the file to
     * @param   string $name      Name of the file, without the extension
     * @return  string
     *
     * @throws \Exception (if output file can't be written to)
     */
    public function saveVarToFile($directory = '', $name = '')
    {
        $fullPath = '';
        if ($this->isFromFile && $directory === '')
        {
            $fullPath = $this->filePath;
        } else {
            if ($name === '')
            {
                $name = $this->varEntry['varname'];
            }
            $extIndex = $this->calcModel->getOrderId();
            if ($extIndex < 0)
            {
                $extIndex = 0;
            }
            $fileName = str_replace("\0", '', $name) . '.' . $this->getType()->getExts()[$extIndex];
            if ($directory === '')
            {
                $directory = './';
            }
            $directory = rtrim($directory, '/');
            $fullPath = realpath($directory) . '/' . $fileName;
        }

        $handle = fopen($fullPath, 'wb');
        if (!$handle) {
            throw new \RuntimeException('Could not open destination file: ' . $fullPath);
        }

        $this->refreshMetadataFields();

        $bin_data = '';
        foreach ([$this->header, $this->varEntry] as $whichData)
        {
            foreach ($whichData as $key => $data)
            {
                // fields not used for this calc version, for instance.
                if ($data === null)
                {
                    continue;
                }
                switch (gettype($data))
                {
                    case 'integer':
                        // The length fields are the only ones on 2 bytes.
                        if ($key === 'entries_len' || $key === 'data_length' || $key === 'data_length2')
                        {
                            $bin_data .= chr($data & 0xFF) . chr(($data >> 8) & 0xFF);
                        } else {
                            $bin_data .= chr($data & 0xFF);
                        }
                        break;
                    case 'string':
                        $bin_data .= $data;
                        break;
                    case 'array':
                        foreach ($data as $subData)
                        {
                            $bin_data .= chr($subData & 0xFF);
                        }
                        break;
                }
            }
        }

        fwrite($handle, $bin_data);
        fwrite($handle, chr($this->computedChecksum & 0xFF) . chr(($this->computedChecksum >> 8) & 0xFF));

        fclose($handle);

        $this->corrupt = false;

        return $fullPath;
    }

}