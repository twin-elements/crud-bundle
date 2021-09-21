{% extends '@TwinElementsAdmin/core/details.html.twig' %}

{% set back_button_link = path('<?= $route_name ?>_index') %}

{% block title %}{{ "<?= $entity_twig_var_singular ?>.creating_a_new_<?= $entity_twig_var_singular ?>"|translate_admin }}{% endblock %}
{% block buttons %}
    {{ block('back_button') }}
{% endblock %}
