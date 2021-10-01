<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use TwinElements\AdminBundle\Role\RoleGroupInterface;

final class <?= $class_name ?> implements RoleGroupInterface
{
    const ROLE_<?= strtoupper($entity_var_singular) ?>_FULL = 'ROLE_<?= strtoupper($entity_var_singular) ?>_FULL';
    const ROLE_<?= strtoupper($entity_var_singular) ?>_EDIT = 'ROLE_<?= strtoupper($entity_var_singular) ?>_EDIT';
    const ROLE_<?= strtoupper($entity_var_singular) ?>_VIEW = 'ROLE_<?= strtoupper($entity_var_singular) ?>_VIEW';

    public static function getRoles(): array
    {
        return [self::ROLE_<?= strtoupper($entity_var_singular) ?>_FULL, self::ROLE_<?= strtoupper($entity_var_singular) ?>_EDIT, self::ROLE_<?= strtoupper($entity_var_singular) ?>_VIEW];
    }
}
