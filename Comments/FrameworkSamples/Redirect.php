<?php

namespace Admin\Model\Form;


/**
 * Class Redirect
 * @package Admin\Model\Form
 */
class Redirect extends BaseForm
{
    // ...

    /**
     * @param int $contentId
     * @param bool $isAjax
     */
    protected function addCmsDeleteToForm($contentId, $isAjax = false)
    {
        $t = $this->translate;
        $this->get('save')->setDefault($t('redirect.save'));

        $group = $this->getGroup('redirect');
        /*$button = new Block(
            'deleteWithoutRedirect',
            $t('admin.content.delete.withoutRedirect'),
            [
                'class' => 'btn btn-danger',
                'column-element-class' => 'col-md-offset-2 col-md-10',
                'href' => $this->url->get([
                    'for' => AdminRoutes::CONTENT_PAGE_DELETE_CONFIRM,
                    'id' => $contentId,
                ]),
            ],
            'a',
            'a'
        );

        $wrapper = new Block(
            'deleteWithoutRedirectWrapper',
            $button->render(),
            [
                'class' => 'col-md-offset-2 col-md-10 btn-actions',
            ]
        );

        $buttongroup = new Block(
            'deleteWithoutRedirectGroup',
            $wrapper->render(),
            [
                'id' => 'delete-no-redirect-group',
                'class' => 'form-group row',
            ]
        );
        $group->add($buttongroup);
        $button = new Block(
            'deleteCancel',
            $t('admin.content.delete.cancel'),
            [
                'class' => 'btn btn-primary',
                'column-element-class' => 'col-md-offset-2 col-md-10 btn-actions',
                'href' => $isAjax ? '#' : $this->url->get(['for' => AdminRoutes::CONTENT_PAGES]),
                'data-dismiss' => 'modal',
            ],
            'a',
            'a'
        );
        $wrapper = new Block(
            'deleteCancelWrapper',
            $button->render(),
            [
                'class' => 'col-md-offset-2 col-md-10 btn-actions',
            ]
        );

        $buttongroup = new Block(
            'deleteCancelGroup',
            $wrapper->render(),
            [
                'id' => 'delete-cancel-group',
                'class' => 'form-group row',
            ]
        );
        $group->add($buttongroup);*/
        $this->addGroup($group);
    }
}
