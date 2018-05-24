<?php
$widgets = temptraining_get_widget_data_for('plans');

foreach($widgets as $key => $widget) :
  require get_template_directory() . '/widgets/plans-' . $key . '.php';
endforeach;
?>
