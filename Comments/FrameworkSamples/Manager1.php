<?php

namespace Common\AttachmentManager;


/**
 * Class AttachmentManager
 * @package Common\AttachmentManager
 */
class AttachmentManager
{
    // ....

    /**
     * Save new file to the database and storage
     *
     * @param File $file - file interface
     * @param string $groupCode - thumbnail group type
     * @param null|integer $attachmentId
     * @throws UsedException in case that attachment id exists in a db
     * @return Attachment
     */
    public function create(File $file, $groupCode = 'catalog', $attachmentId = null)
    {
        // check if the attachment is unique
        if (!is_null($attachmentId) && $this->attachmentService->count(['id' => $attachmentId]) != 0) {
            throw new UsedException(sprintf('attachment with id %d already exists', $attachmentId));
        }
        // build attachment object with all the thumbnail sizes objects
        $attachment = $this->buildAttachmentFromUploadedFile($file, $groupCode, $attachmentId);
        // save files in a file system, get FileAttachment[] interface in response
        $fileAttachments = $this->fileAttachmentFactory->getStrategy($attachment)->create();
        // save to the database
        return $this->saveAttachmentAfterSavingFile($attachment, $fileAttachments);
    }

    // ....

}
