<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private string $uploadDirectory,
        private ImageConverter $imageConverter,

    ) {}

    public function uploadImage(UploadedFile $file, FormInterface $form): void
    {
        $fileName = uniqid() . '.webp';
        $this->imageConverter->toWebp($file);
        try {
            $file->move($this->getUploadDirectory(), $fileName);
            $form->getData()->setImage($fileName);
        } catch (FileException $e) {
            echo "Erreur" .  $e->getMessage();
        }
    }


    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }
}
