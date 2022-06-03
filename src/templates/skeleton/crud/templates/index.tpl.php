{% extends '@TwinElementsAdmin/core/list.html.twig' %}
{% use '@TwinElementsSortable/blocks.html.twig' %}

{% block title %}{{ "<?= $entity_twig_var_singular ?>.<?= $entity_twig_var_plural ?>"|translate_admin }}{% endblock %}
{% block buttons %}
    {% set link = path('<?= $route_name ?>_new') %}
    {{ block('add_action_link') }}
{% endblock %}

<?php
$isChangesIsset = false;
if ($availableInterfaces->isBlameable() and $availableInterfaces->isTimestampable()){
    $isChangesIsset = true;
}

$isPositionIsset = false;
if($availableInterfaces->isPosition()){
    $isPositionIsset = true;
}
?>

{% block list %}
        {{ block('list_ul_begin') }}
            {{ block('list_li_header_begin') }}
                    {{ block('id_header') }}

                    <?php if ($availableInterfaces->isTitle()): ?>
                        <div class="col">{{ "<?= $entity_twig_var_singular ?>.title"|translate_admin }}</div>
                    <?php endif ?>

                    <?php if ($isChangesIsset) : ?>
                    {{ block('list_changes_header') }}
                    <?php endif ?>

                    <?php if ($availableInterfaces->isEnable()): ?>
                    {{ block('circle_header') }}
                    <?php endif; ?>

                    <?php if ($isPositionIsset): ?>
                        {{ block('move_header') }}
                    <?php endif; ?>
            {{ block('list_li_header_end') }}

            {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
            {% set id = <?= $entity_twig_var_singular ?>.id %}
            <?php if($isChangesIsset): ?>
                {% set createdAt = <?= $entity_twig_var_singular ?>.createdAt %}
                {% set createdBy = <?= $entity_twig_var_singular ?>.createdBy %}
                {% set updatedAt = <?= $entity_twig_var_singular ?>.updatedAt %}
                {% set updatedBy = <?= $entity_twig_var_singular ?>.updatedBy %}
            <?php endif; ?>
            <?php if ($availableInterfaces->isEnable()): ?>
                {% set circle_active = <?= $entity_twig_var_singular ?>.enable %}
            <?php endif; ?>

                {{ block('list_li_begin') }}
                    {{ block('id') }}
                    <?php if ($availableInterfaces->isTitle()): ?>
                        <div class="col">
                            <div class="title">
                                {% if is_granted(constant('App\\Security\\<?= $entity_class_name ?>Voter::EDIT'), <?= $entity_twig_var_singular ?>) %}
                                    {% set editUrl = path('<?= $route_name ?>_edit', { 'id': <?= $entity_twig_var_singular ?>.id }) %}
                                {% endif %}
                                <a {% if editUrl is defined %}href="{{ editUrl }}"{% endif %}>
                                    <span>{{ <?= $entity_twig_var_singular ?>.title }}</span>
                                    {% if <?= $entity_twig_var_singular ?>.title is null %}
                                    <span class="badge badge-warning">{{ "admin.no_translation"|translate_admin }}</span>
                                    {% endif %}
                                </a>
                            </div>
                        </div>
                    <?php endif ?>

                    <?php if ($isChangesIsset): ?>
                        {{ block('changes_box_in_list') }}
                    <?php endif ?>
                    <?php if ($availableInterfaces->isEnable()): ?>
                        {{ block('circle') }}
                    <?php endif; ?>
                    <?php if ($isPositionIsset): ?>
                        {{ block('move') }}
                    <?php endif; ?>

                {{ block('list_li_end') }}
            {% else %}
                {{ block('no_elements') }}
            {% endfor %}
        {{ block('list_ul_end') }}

{% endblock %}

{% block head_js %}
    {{ block('sortable') }}
{% endblock %}
