<?php
 /**
 * @author ...
 * @copyright ...
 * @created 10.03.2015 14:15
 */

namespace Common\Attachment\Service;

use Common\Attachment\Entity\Attachment;
use Common\Attachment\Entity\AttachmentSize;
use Common\Attachment\Mapper\AttachmentSizeMapper;
use Common\Db\Service\Traits\CrudInterface;
use Common\Db\Service\Traits\CrudTrait;
use Common\Db\Service\ServiceAbstract;
use Imagine\Image\Point;
use Phalcon\Config;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class AttachmentSizeService
 * @package Common\Attachment\Service
 */
class AttachmentSizeService extends ServiceAbstract implements CrudInterface
{
    // ...

    /**
     * @param AttachmentSize $attachmentSize
     * @param int $originalWidth
     * @param int $originalHeight
     * @param string $type
     * @param int $width
     * @param int $height
     * @param string $scaling
     * @return bool
     */
    private function scaleDimensionsFromConfig(
        $attachmentSize,
        $originalWidth,
        $originalHeight,
        $type = AttachmentSize::TYPE_SYSTEM_THUMBNAIL,
        $width = 0,
        $height = 0,
        $scaling = AttachmentSize::SCALING_FIT
    ) {
        /* if you have defined dimensions for this type of attachment, in config,
        then we overwrite the passed dimensions, if any */
        if (isset($this->attachmentConfig->imageSizes->{$type})) {
            $typeDimensions = $this->attachmentConfig->imageSizes->{$type};
            $scaling = $this->calculateScalingMode($typeDimensions->scaling, $scaling);
            $width = isset($typeDimensions->width) ? $typeDimensions->width : $width;
            $height = isset($typeDimensions->height) ? $typeDimensions->height : $height;
        }

        if ($type === AttachmentSize::TYPE_ORIGINAL || (empty($width) === true && empty($height) === true)) {
            $attachmentSize->setWidth($originalWidth);
            $attachmentSize->setHeight($originalHeight);
            $attachmentSize->setScaling(AttachmentSize::SCALING_NO_SCALE);

            return true;
        }

        $attachmentSize->setWidth($width ?: $originalWidth);
        $attachmentSize->setHeight($height ?: $originalHeight);
        $attachmentSize->setScaling($scaling);

        return true;
    }

    // ...
}
