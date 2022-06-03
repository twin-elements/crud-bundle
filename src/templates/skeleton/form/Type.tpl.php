<?= "<?php\n" ?>
<?php

unset($form_fields['id']);

if (key_exists('slug', $form_fields)) {
    unset($form_fields['slug']);
}

if ($availableInterfaces->isTitle()) {
    unset($form_fields['title']);
}

if ($availableInterfaces->isPosition()) {
    unset($form_fields['position']);
}

if ($availableInterfaces->isBlameable()) {
    unset($form_fields['createdBy']);
    unset($form_fields['updatedBy']);
    unset($form_fields['deletedBy']);
}

if ($availableInterfaces->isTimestampable()) {
    unset($form_fields['createdAt']);
    unset($form_fields['updatedAt']);
}

if ($availableInterfaces->isEnable()) {
    unset($form_fields['enable']);
}
if ($availableInterfaces->isSeo()) {
    unset($form_fields['seo']);
}
if ($availableInterfaces->isAttachment()) {
    unset($form_fields['attachments']);
}
if ($availableInterfaces->isImageAlbum()) {
    unset($form_fields['imageAlbum']);
}
if ($availableInterfaces->isImage()) {
    unset($form_fields['image']);
}
if ($availableInterfaces->isContent()) {
    unset($form_fields['content']);
}
?>
namespace <?= $namespace ?>;

<?php if (isset($bounded_full_class_name)): ?>
    use <?= $bounded_full_class_name ?>;
<?php endif ?>

use TwinElements\FormExtensions\Type\SaveButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TwinElements\Component\AdminTranslator\AdminTranslator;
<?php
foreach ($availableInterfaces->getUsedClassList() as $key => $fullClassName) {
    echo 'use ' . $fullClassName . ';' . PHP_EOL;
}
?>


class <?= $class_name ?> extends AbstractType
{
/**
* @var AdminTranslator $translator
*/
private $translator;

public function __construct(AdminTranslator $translator)
{
$this->translator = $translator;
}

public function buildForm(FormBuilderInterface $builder, array $options)
{
$builder
<?php if ($availableInterfaces->isTitle()) : ?>
    ->add('title', TextType::class, [
    'label' => $this->translator->translate('admin_type.title')
    ])
<?php endif; ?>

<?php foreach ($form_fields as $form_field => $typeOptions): ?>
    ->add('<?= $form_field ?>', null, [
        'label' => $this->translator->translate('<?= $entity_twig_var_singular ?>.<?= $form_field ?>')
    ])
<?php endforeach; ?>

<?php if ($availableInterfaces->isContent()): ?>
    ->add('content', TinymceType::class)
<?php endif; ?>

<?php if ($availableInterfaces->isImage()) : ?>
    ->add('image', TEUploadType::class, [
    'file_type' => 'image',
    'label' => $this->translator->translate('admin_type.image'),
    'required' => false
    ])
<?php endif; ?>

<?php if ($availableInterfaces->isImageAlbum()) : ?>
    ->add('imageAlbum', TECollectionType::class, [
    'entry_type' => FileWithTitleType::class,
    'entry_options' => [
    'file_type' => 'image',
    'label' => $this->translator->translate('admin_type.image_album.choose_image')
    ],
    'min' => 0,
    'label' => $this->translator->translate('admin_type.image_album.image_album')
    ])
<?php endif; ?>
<?php if ($availableInterfaces->isAttachment()) : ?>->add('attachments', AttachmentsType::class)<?php endif; ?>
<?php if ($availableInterfaces->isSeo()): ?>->add('seo', SeoType::class)<?php endif; ?>
<?php if ($availableInterfaces->isEnable()) : ?>->add('enable', ToggleChoiceType::class)<?php endif; ?>
->add('buttons', SaveButtonsType::class);
}

public function configureOptions(OptionsResolver $resolver)
{
$resolver->setDefaults([
<?php if (isset($bounded_full_class_name)): ?>
    'data_class' => <?= $bounded_class_name ?>::class,
<?php else: ?>
    // Configure your form options here
<?php endif ?>
]);
}
}
