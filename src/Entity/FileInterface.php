<?php


namespace App\Entity;


interface FileInterface
{
    public function getRecordId(): int;
    public function getUuid(): string;
}