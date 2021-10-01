<?= "<?php\n" ?>

namespace <?= $namespace; ?>;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use TwinElements\AdminBundle\Entity\AdminUser;
use TwinElements\AdminBundle\Role\AdminUserRole;
use <?= $entity_full_class_name ?>;
use <?= $role_full_class_name ?>;

class <?= $class_name ?> extends Voter
{
    const FULL = 'full';
    const VIEW = 'view';
    const EDIT = 'edit';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::FULL, self::VIEW, self::EDIT]) && $subject instanceof <?= $bounded_class_name ?>;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof AdminUser) {
            return false;
        }

        switch ($attribute) {
            case self::FULL;
            return $this->isFull();

            case self::EDIT;
            return $this->canEdit();

            case self::VIEW;
            return $this->canView();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function isFull()
    {
        if ($this->security->isGranted(AdminUserRole::ROLE_ADMIN)) {
            return true;
        }

        if ($this->security->isGranted(<?= $role_short_class_name ?>::ROLE_<?= strtoupper($entity_var_singular) ?>_FULL)) {
            return true;
        }
    }

    private function canEdit()
    {
        if ($this->isFull()) {
            return true;
        }

        if ($this->security->isGranted(<?= $role_short_class_name ?>::ROLE_<?= strtoupper($entity_var_singular) ?>_EDIT)) {
            return true;
        }
    }

    private function canView()
    {
        if ($this->canEdit()) {
            return true;
        }

        if ($this->security->isGranted(<?= $role_short_class_name ?>::ROLE_<?= strtoupper($entity_var_singular) ?>_VIEW)) {
            return true;
        }
    }
}
