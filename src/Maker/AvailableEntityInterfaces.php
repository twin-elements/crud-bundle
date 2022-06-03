<?php

namespace TwinElements\CrudBundle\Maker;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use TwinElements\AdminBundle\Entity\Traits\AttachmentsInterface;
use TwinElements\AdminBundle\Entity\Traits\ContentInterface;
use TwinElements\AdminBundle\Entity\Traits\EnableInterface;
use TwinElements\AdminBundle\Entity\Traits\ImageAlbumInterface;
use TwinElements\AdminBundle\Entity\Traits\ImageInterface;
use TwinElements\SortableBundle\Entity\PositionInterface;
use TwinElements\FormExtensions\Type\AttachmentsType;
use TwinElements\FormExtensions\Type\FileWithTitleType;
use TwinElements\FormExtensions\Type\TECollectionType;
use TwinElements\FormExtensions\Type\TEUploadType;
use TwinElements\FormExtensions\Type\TinymceType;
use TwinElements\FormExtensions\Type\ToggleChoiceType;
use TwinElements\SeoBundle\Form\Admin\SeoType;
use TwinElements\SeoBundle\Model\SeoInterface;
use TwinElements\AdminBundle\Entity\Traits\TitleInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;

class AvailableEntityInterfaces
{
    private $translatable = false;
    private $seo = false;
    private $position = false;
    private $attachment = false;
    private $image = false;
    private $imageAlbum = false;
    private $enable = false;
    private $title = false;
    private $timestampable = false;
    private $blameable = false;
    private $content = false;

    private $usedClassList = [];

    public function __construct(string $entityFullName, DoctrineHelper $doctrineHelper)
    {
        /**
         * @var ClassMetadata $entityMetadata
         */
        $entityMetadata = $doctrineHelper->getMetadata($entityFullName);
        $entityReflectionClass = $entityMetadata->getReflectionClass();

        $this->translatable = $entityReflectionClass->implementsInterface(TranslatableInterface::class);

        if ($this->translatable) {
            /**
             * @var ClassMetadata $translationEntityDoctrineDetails
             */
            $translationEntityDoctrineDetails = $doctrineHelper->getMetadata($entityFullName . 'Translation');
            $this->checkInterfacesWithTranslations($entityReflectionClass, $translationEntityDoctrineDetails->getReflectionClass());
        } else {
            $this->checkInterfaces($entityReflectionClass);
        }
    }

    private function checkInterfaces(\ReflectionClass $entity)
    {
        if (self::checkSeo($entity)) {
            $this->seo = true;
            $this->addUsedClass('seo', SeoType::class);
        }

        if (self::checkImage($entity)) {
            $this->image = true;
            $this->addUsedClass('upload', TEUploadType::class);
        }

        if (self::checkImageAlbum($entity)) {
            $this->imageAlbum = true;
            $this->addUsedClass('collection', TECollectionType::class);
            $this->addUsedClass('file_with_title', FileWithTitleType::class);
        }

        if (self::checkAttachments($entity)) {
            $this->attachment = true;
            $this->addUsedClass('attachments', AttachmentsType::class);
        }

        if (self::checkEnable($entity)) {
            $this->enable = true;
            $this->addUsedClass('toggle', ToggleChoiceType::class);
        }

        $this->position = self::checkPosition($entity);

        if (self::checkTitle($entity)) {
            $this->title = true;
            $this->addUsedClass('text', TextType::class);
        }

        $this->timestampable = self::checkTimestampable($entity);
        $this->blameable = self::checkBlameable($entity);

        if (self::checkContent($entity)) {
            $this->content = true;
            $this->addUsedClass('tinymce', TinymceType::class);
        }
    }

    private function checkInterfacesWithTranslations(\ReflectionClass $entity, \ReflectionClass $entityTranslation)
    {
        if (self::checkSeo($entity) || self::checkSeo($entityTranslation)) {
            $this->seo = true;

            $this->addUsedClass('seo', SeoType::class);
        }
        if (self::checkImageAlbum($entity) || self::checkImageAlbum($entityTranslation)) {
            $this->imageAlbum = true;
            $this->addUsedClass('collection', TECollectionType::class);
            $this->addUsedClass('file_with_title', FileWithTitleType::class);
        }
        if (self::checkImage($entity) || self::checkImage($entity)) {
            $this->image = true;
            $this->addUsedClass('upload', TEUploadType::class);
        }
        if (self::checkAttachments($entity) || self::checkAttachments($entityTranslation)) {
            $this->attachment = true;
            $this->addUsedClass('attachments', AttachmentsType::class);
        }
        if (self::checkEnable($entity) || self::checkEnable($entityTranslation)) {
            $this->enable = true;
            $this->addUsedClass('toggle', ToggleChoiceType::class);
        }
        if (self::checkPosition($entity) || self::checkPosition($entityTranslation)) {
            $this->position = true;
        }
        if (self::checkTitle($entity) || self::checkTitle($entityTranslation)) {
            $this->title = true;
            $this->addUsedClass('text', TextType::class);
        }
        if (self::checkTimestampable($entity) || self::checkTimestampable($entityTranslation)) {
            $this->timestampable = true;
        }
        if (self::checkBlameable($entity) || self::checkBlameable($entityTranslation)) {
            $this->blameable = true;
        }
        if (self::checkContent($entity) || self::checkContent($entityTranslation)) {
            $this->content = true;
            $this->addUsedClass('tinymce', TinymceType::class);
        }
    }

    private function addUsedClass($key, $usedClass)
    {
        if (!array_key_exists($key, $this->usedClassList)) {
            $this->usedClassList[$key] = $usedClass;
        }
    }

    private static function checkBlameable(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(BlameableInterface::class);
    }

    private static function checkTimestampable(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(TimestampableInterface::class);
    }

    private static function checkTitle(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(TitleInterface::class);
    }

    private static function checkPosition(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(PositionInterface::class);
    }

    private static function checkEnable(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(EnableInterface::class);
    }

    private static function checkAttachments(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(AttachmentsInterface::class);
    }

    private static function checkSeo(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(SeoInterface::class);
    }

    private static function checkImageAlbum(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(ImageAlbumInterface::class);
    }

    private static function checkImage(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(ImageInterface::class);
    }

    private static function checkContent(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->implementsInterface(ContentInterface::class);
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    /**
     * @return bool
     */
    public function isSeo(): bool
    {
        return $this->seo;
    }

    /**
     * @return bool
     */
    public function isPosition(): bool
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function isAttachment(): bool
    {
        return $this->attachment;
    }

    /**
     * @return bool
     */
    public function isImageAlbum(): bool
    {
        return $this->imageAlbum;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @return bool
     */
    public function isTitle(): bool
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isTimestampable(): bool
    {
        return $this->timestampable;
    }

    /**
     * @return bool
     */
    public function isBlameable(): bool
    {
        return $this->blameable;
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->image;
    }

    /**
     * @return bool
     */
    public function isContent(): bool
    {
        return $this->content;
    }

    /**
     * @return array
     */
    public function getUsedClassList(): array
    {
        return $this->usedClassList;
    }
}
