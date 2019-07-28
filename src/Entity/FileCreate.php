<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 09/07/2018
 * Time: 16:16
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * File
 *
 * @ORM\Table(name="File")
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class FileCreate implements FileInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="rec_id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="'__pk_FileID'", type="string", length=255)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="MSDev\DoctrineFMDataAPIDriver\FMIdentityGenerator")
     */
    private $uuid;

    /**
     * @return int
     */
    public function getRecordId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

}