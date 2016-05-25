<?php

namespace Common\Cms\Service;

/**
 * Class Content
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) TODO: ContentService needs refactoring (TooManyPublicMethods).
 * @SuppressWarnings(PHPMD.TooManyMethods) TODO: ContentService needs refactoring (TooManyMethods).
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) TODO: ContentService needs refactoring (ExcessiveClassComplexity).
 *
 * @package Common\Service\Cms
 */
class ContentService
{

    // ...

    /**
     * Transforms a list of usages of a media to a list with links to sources
     *
     * @param array $usages [key => [type, id]]
     * @return array
     */
    public function addLinksToMediaUsagesList(array $usages)
    {
        // ...
    }

    /**
     * Check if a key is deleted.
     *
     * @param string $key
     * @param string $module
     * @return bool
     */
    public function isKeyDeleted($key, $module = Application::MODULE_FRONTEND)
    {
        // ...
    }
}
