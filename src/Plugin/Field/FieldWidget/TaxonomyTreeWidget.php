<?php

namespace Drupal\liveblog\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Plugin implementation of the 'liveblog_taxonomy_tree' widget.
 *
 * @FieldWidget(
 *   id = "liveblog_taxonomy_tree",
 *   label = @Translation("Taxonomy tree"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   multiple_values = TRUE
 * )
 */
class TaxonomyTreeWidget extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['label'] = [
      '#type' => 'item',
      '#title' => $element['#title'],
    ];
    $element['#attached']['library'][] = 'liveblog/taxonomy_tree';

    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items);
    // Copy options stack.
    $stack = $options;
    // Map options as an elements tree.
    $tree = $this->mapTree($stack);
    // Add nested form elements.
    $element['values'] = $this->prepareTreeElements($tree, $selected);

    return $element;
  }

  /**
   * Prepares nested structure of form elements according to the elements tree.
   *
   * @param array $tree
   *   The tree of elements.
   * @param string[] $selected
   *   The list of selected values.
   *
   * @return array
   *   Nested structure of form elements according to the elements tree.
   */
  protected function prepareTreeElements(array $tree, array $selected) {
    $element = [];
    foreach ($tree as $item) {
      $element[$item['value']] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['field--widget-liveblog-taxonomy-tree--node']],
      ];

      $element[$item['value']]['element'] = [
        '#type' => 'checkbox',
        '#title' => $item['title'],
        '#default_value' => in_array($item['value'], $selected) ? TRUE : FALSE,
        '#attributes' => ['class' => ['field--widget-liveblog-taxonomy-tree--item']],
      ];

      if (!empty($item['children'])) {
        $element[$item['value']]['children'] = $this->prepareTreeElements($item['children'], $selected);
        $element[$item['value']]['element']['#attributes']['class'][] = 'field--widget-liveblog-taxonomy-tree--elements--parent';
      }
    }

    return $element;
  }

  /**
   * Maps options tree.
   *
   * @param array $data
   *   The options array nested using the "-" symbol.
   * @param int $level
   *   (optional) The current tree level.
   *
   * @return array
   *   Mapped options tree.
   */
  protected function mapTree(array &$data, $level = 0) {
    $tree = [];

    $get_level = function($a) {
      $level = 0;
      if (preg_match('/^-+/', $a, $m)) {
        $level = strlen($m[0]);
      }
      return $level;
    };

    while (list($key, $val) = each($data)) {
      reset($data);
      $item_level = $get_level($val);

      $node = [
        'value' => $key,
        'title' => preg_replace('/^-+/', '', $val),
      ];

      // Same level, add sibling.
      if ($level == $item_level) {
        $next = next($data);
        unset($data[$key]);

        // More items below.
        if ($get_level($next) > $level) {
          $node['children'] = $this->mapTree($data, $level + 1);
        }

        $tree[] = $node;
      }
      // We are finished on this level, return.
      elseif ($item_level < $level) {
        return $tree;
      }
    }

    return $tree;
  }

  /**
   * Form validation handler for widget elements.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    if ($element['#required'] && $element['#value'] == '_none') {
      $form_state->setError($element, t('@name field is required.', ['@name' => $element['#title']]));
    }

    $values = [];
    $tree = [];
    // Massage submitted form values.
    // Drupal\Core\Field\WidgetBase::submit() expects values as
    // an array of values keyed by delta first, then by column, while our
    // widget returns a values tree.
    foreach (Element::children($element['values']) as $key) {
      $tree[$key] = $element['values'][$key];
    }
    // Collect values from the tree structure in the values array.
    self::collectTreeValues($tree, $values);

    // Filter out the 'none' option. Use a strict comparison, because
    // 0 == 'any string'.
    $index = array_search('_none', $values, TRUE);
    if ($index !== FALSE) {
      unset($values[$index]);
    }

    // Transpose selections from field => delta to delta => field.
    $items = [];
    foreach ($values as $value) {
      $items[] = [$element['#key_column'] => $value];
    }
    $form_state->setValueForElement($element, $items);
  }

  /**
   * Collects values from the tree structure in the values array.
   *
   * @param array $tree
   *   The elements tree.
   * @param array $values
   *   The target values array.
   */
  protected static function collectTreeValues(array $tree, array &$values) {
    foreach ($tree as $key => $item) {
      if (!empty($item['element']['#value'])) {
        $values[] = $key;
      }
      if (!empty($item['children'])) {
        self::collectTreeValues($item['children'], $values);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if (!$this->required && !$this->multiple) {
      return t('N/A');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();
    // This widget is only available for multivalued taxonomy term references.
    return $storage->getCardinality() != 1 && $storage->getSetting('target_type') == 'taxonomy_term';
  }


}
