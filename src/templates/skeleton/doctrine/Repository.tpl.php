<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use <?= $entity_full_class_name; ?>;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query;
use App\Bundle\CoreAdminBundle\Repository\EntityRepositoryTranslatable;

/**
 * @method <?= $entity_class_name; ?>|null find($id, $lockMode = null, $lockVersion = null)
 * @method <?= $entity_class_name; ?>|null findOneBySlug($slug, $inDefaultLocale = false)
 * @method <?= $entity_class_name; ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?= $entity_class_name; ?>[]    findAll()
 * @method <?= $entity_class_name; ?>[]|<?= $entity_class_name; ?>|null    findById($id, $inDefaultLocale = false, $orderByField = false)
 * @method <?= $entity_class_name; ?>[]|null    findAllActiveTranslated(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method Query    findAllActiveTranslatedQuery(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method <?= $entity_class_name; ?>[]|null    findAllTranslated(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method Query    findAllTranslatedQuery(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method <?= $entity_class_name; ?>[]|null    findEverything(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method Query    findEverythingQuery(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method <?= $entity_class_name; ?>[]|null    findAllActive(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method Query    findAllActiveQuery(array $orderBy = array('position' => 'asc'), array $byParent = null, $limit = null, $offset = null)
 * @method <?= $entity_class_name; ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class <?= $class_name; ?> extends EntityRepositoryTranslatable
{
    public function __construct(RegistryInterface $registry)
    {
        $this->setClass(<?= $entity_class_name; ?>::class);
        parent::__construct($registry);
    }
}
