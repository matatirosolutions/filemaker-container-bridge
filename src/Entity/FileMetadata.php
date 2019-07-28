<?php


namespace App\Entity;


class FileMetadata
{
    /** @var string */
    protected $layout;

    /** @var string */
    protected $fileField;

    /**
     * FileMetadata constructor.
     * @param string $layout
     * @param string $uuidField
     * @param string $fileField
     */
    public function __construct(string $layout, string $fileField)
    {
        $this->layout = $layout;
        $this->fileField = $fileField;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @return string
     */
    public function getFileField(): string
    {
        return $this->fileField;
    }
}