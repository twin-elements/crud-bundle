{% extends '@TwinElementsAdmin/core/details_with_sidebar.html.twig' %}
{% set back_button_link = path('<?= $route_name ?>_index') %}

{% block title %}
    {% if entity.title %}
        {{ entity.title }}
    {% else %}
        {{ entity.translate(default_locale, false).title }}<br>
        <span class="badge badge-warning small">{{ "cms.no_translation_for_this_locale"|trans({},null,admin_locale) }}</span>
    {% endif %}
{% endblock %}

{% block buttons %}
    {{ block('back_button') }}
{% endblock %}

{% block right_sidebar %}
<?php
if($availableInterfaces->isTimestampable() || $availableInterfaces->isBlameable()){
    echo '{{ block(\'changes_details\') }}';
}
?>
    {{ block('delete_form') }}
{% endblock %}
