<?php

namespace Admin\Model\Form;

use Common\Authorization\Entity\AclResource;
use Common\Authorization\Entity\Role as RoleEntity;
use Common\Form\BaseForm;
use Common\Form\FieldSet;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\PresenceOf;

/**
 * Class Role
 * @package Admin\Model\Form
 */
class Role extends BaseForm
{
    // ...

    private function createResourceGroup($groupName, $ruleSetName)
    {
        /** @var AclResource[] $groupResources */
        $groupResources = $this->aclResource
            ->find(['module' => 'admin', 'group' => $groupName, 'ruleset' => $ruleSetName])
            ->order(['name' => 'ASC', 'action' => 'ASC']);

        // ...
    }

    // ...
}
