<?php

// Open/closed principle

// software entities (classes, modules, functions, etc.)
// should be open for extension, but closed for modification

class FileUploader
{
    public function upload(File $file)
    {
        switch ($file->getTarget()) {
            case 'aws':
                return $this->uploadToAws($file);
            case 'cloudinary':
                return $this->uploadToCloudinary($file);
            case 'filesystem':
                return $this->saveToFilesystem($file);
            default:
                throw new \InvalidArgumentException(sprintf('Unknown storage type %s', $file->getTarget()));
        }
    }

    // private function uploadToAws(File $file) {}
    // private function uploadToCloudinary(File $file) {}
    // private function saveToFilesystem(File $file) {}
}

class SFileUploader
{
    /** @var StorageInterface[] */
    private $storages;

    public function __construct(array $storages)
    {
        $this->storages = $storages;
    }

    public function upload(File $file)
    {
        foreach ($this->storages as $storage) {
            if ($storage->supports($file)) {
                return $storage->upload($file);
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown storage type %s', $file->getTarget()));
    }
}
