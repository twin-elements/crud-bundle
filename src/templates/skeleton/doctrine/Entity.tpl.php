<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?php if ($api_resource): ?>use ApiPlatform\Core\Annotation\ApiResource;
<?php endif ?>
use Doctrine\ORM\Mapping as ORM;
<?php if($isChangeable): ?>use App\Bundle\CoreAdminBundle\Entity\ChangesTracking;
<?php endif; ?>
<?php if($hasSeo): ?>use App\Bundle\CommonAdminBundle\Entity\Seo;
<?php endif; ?>

/**
<?php if ($api_resource): ?> * @ApiResource()
<?php endif ?>
 * @ORM\Table(name="<?php echo strtolower($entity_class_name); ?>")
 * @ORM\Entity(repositoryClass="<?= $repository_full_class_name ?>")
 */
class <?= $class_name."\n" ?>
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    <?php if ($isChangeable): ?>

    /**
    * @var ChangesTracking
    * @ORM\OneToOne(targetEntity="App\Bundle\CoreAdminBundle\Entity\ChangesTracking", cascade={"all"}, orphanRemoval=true)
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
    private $changes;

    <?php endif; ?>
    <?php if($isSwitchable): ?>

    /**
    * @var int
    *
    * @ORM\Column(name="active", type="boolean")
    */
    private $active = false;

    <?php endif; ?>
    <?php if($hasSeo):?>

    /**
    * @var Seo
    * @ORM\OneToOne(targetEntity="App\Bundle\CommonAdminBundle\Entity\Seo", cascade={"persist","remove","refresh"})
    * @ORM\JoinColumn(onDelete="SET NULL")
    */
    private $seo;

    <?php endif;?>
    <?php if($isSortable): ?>

    /**
    * @var int
    *
    * @ORM\Column(name="position", type="smallint")
    */
    private $position = 0;

    <?php endif;?>

}
