<?php


namespace App\Service;

use App\Entity\File;
use App\Entity\FileCreate;
use App\Entity\FileInterface;
use App\Entity\FileMetadata;
use Doctrine\ORM\EntityManagerInterface;
use MSDev\DoctrineFMDataAPIDriver\Utility\ContainerAccess;
use Symfony\Component\HttpFoundation\JsonResponse;

class FileService
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var ContainerAccess */
    protected $containerAccess;

    /** @var string */
    protected $projectDir;

    /**
     * FileService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerAccess $containerAccess, string $projectDir)
    {
        $this->entityManager = $entityManager;
        $this->containerAccess = $containerAccess;
        $this->projectDir = $projectDir;
    }

    /**
     * @param FileInterface $file
     * @param array $upload
     * @return JsonResponse
     */
    public function processUpload(FileInterface $file, array $upload)
    {
        $dir = $this->projectDir."/public/upload/";
        $destination = $dir . basename($upload["name"]);

        try {
            if (move_uploaded_file($upload["tmp_name"], $destination)) {

                $metadata = $this->getFileEntityMetadata();
                $this->containerAccess->performContainerInsert($metadata->getLayout(), $file->getRecordId(), $metadata->getFileField(), $destination);
                unlink($destination);

                return new JsonResponse(['success' => true, 'fileID' => $file->getUuid(), 'recID' => $file->getRecordId()]);
            }
        } catch(\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'debug' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse(['success' => false, 'debug' => 'No file received'], 500);
    }


    /**
     * @return FileInterface
     */
    public function createFile(): FileInterface
    {
        $fileCreate = new FileCreate();
        $this->entityManager->persist($fileCreate);
        $this->entityManager->flush();

        /** @var File $file */
        $file = $this->entityManager->getRepository('App:File')
            ->find($fileCreate->getUuid());
        return $file;
    }

    /**
     * @return FileMetadata
     */
    private function getFileEntityMetadata()
    {
        $metadata = $this->entityManager->getClassMetadata('App:File');

        return new FileMetadata($metadata->table['name'], $metadata->fieldMappings['container']['columnName']);
    }
}