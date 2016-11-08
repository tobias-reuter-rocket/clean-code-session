<?php
namespace Common\AttachmentManager;

class AttachmentManager
{
    // ....

    /**
     * @param File $file
     * @param string $groupCode
     * @param int|null $attachmentId
     * @throws DuplicateException
     * @return Attachment
     */
    public function createAndSave(File $file, $groupCode = 'catalog', $attachmentId = null)
    {
        if ($this->attachmentService->isDuplicate($attachmentId)) {
            throw new DuplicateException(sprintf('attachment with id %d already exists', $attachmentId));
        }

        $attachment = $this->buildAttachmentFromUploadedFile($file, $groupCode, $attachmentId);

        $fileAttachments = $this->fileAttachmentFactory->getStrategy($attachment)->saveToFile();

        return $this->saveAttachmentAfterSavingFile($attachment, $fileAttachments);
    }

    // ....

}

class AttachmentService
{
    /**
     * @param int|null $attachmentId
     * @return bool
     */
    public function isDuplicate($attachmentId)
    {
        if (is_null($attachmentId)) {
            return false;
        }

        return $this->count(['id' => $attachmentId]) > 0;
    }
}

