<?php

namespace Drupal\bhcc_guide\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LabelsWidget
 *
 * Display the entity references without an autocomplete field. This is used in
 * cases where the entities are added to this field from elsewhere but the
 * delta/weight is still configurable.
 *
 * @package Drupal\bhcc_guide\Field\FieldWidget
 *
 * @FieldWidget(
 *   id = "entity_reference_labels",
 *   label = @Translation("Labels"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class LabelsWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Remove the add more link.
    if (key_exists('add_more', $elements)) {
      unset($elements['add_more']);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    if ($items[$delta]->entity) {
      // Set the node label as the field text.
      $element['#markup'] = $items[$delta]->entity->label();

      // Set target id as a hidden field so we can save the order.
      $element['target_id'] = [
        '#type' => 'hidden',
        '#default_value' => $items[$delta]->getValue()['target_id']
      ];

      return $element;
    }

    if ($delta === 0 && !$items[$delta]->entity) {
      // No results.
      $element['#markup'] = $this->t('There are currently no referenced pages');
      return $element;
    }

    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      if (array_key_exists('target_id', $value) && is_array($value['target_id'])) {
        unset($values[$key]['target_id']);
        $values[$key] += $value['target_id'];
      }
    }

    return $values;
  }
}
