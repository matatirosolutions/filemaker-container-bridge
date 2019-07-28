<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 2019-04-24
 * Time: 15:18
 */

namespace App\Controller;

use App\Entity\File;
use App\Service\FileService;
use MSDev\DoctrineFMDataAPIDriver\Utility\ContainerAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class FilesController extends AbstractController
{
    /**
     * @Route("/api/file/{id}/upload", name="api_file_upload")
     *
     * @param File $file
     * @param FileService $fileService
     *
     * @return JsonResponse
     */
    public function uploadFileAction(File $file, FileService $fileService)
    {
        return $fileService->processUpload($file, $_FILES["file"]);

    }

    /**
     * @Route("/api/file/upload/new", name="api_file_upload_new")
     *
     * @param FileService $fileService
     *
     * @return JsonResponse
     */
    public function uploadNewFileAction(FileService $fileService)
    {
        $file = $fileService->createFile();

        return $fileService->processUpload($file, $_FILES["file"]);
    }


    /**
     * @Route("/api/file/{id}/download", name="api_file_download")
     * @ParamConverter("file", class="App:File")
     *
     * @param File $file
     *
     * @param ContainerAccess $container
     * @return StreamedResponse
     */
    public function downloadAction(File $file, ContainerAccess $container)
    {
        $response = new StreamedResponse(function() use($container, $file)
        {
            echo $container->getStreamedContainerContent($file->getContainer());
        });

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->headers->set('Content-Disposition', 'inline; filename="'.$file->getFileName().'"');

        return $response;
    }
}