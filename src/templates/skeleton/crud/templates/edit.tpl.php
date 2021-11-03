{% extends '@TwinElementsAdmin/core/details_with_sidebar.html.twig' %}
{% set back_button_link = path('<?= $route_name ?>_index') %}

{% block title %}
    {% if entity.title %}
        {{ entity.title }}
    {% else %}
        {{ entity.translate(default_locale, false).title }}<br>
        {{ block('title_no_translation_badge') }}
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
