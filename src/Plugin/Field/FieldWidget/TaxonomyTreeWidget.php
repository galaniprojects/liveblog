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

    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items);

    $data = $options;
    $tree = $this->mapTree($data);

    /*$add_elements_level = function ($parent_level, $options, $selected) {
      foreach ($options as $key => $option) {
        preg_match('/^\-+/', $option, $matches);
        $level = 0;
        if (!empty($matches[0])) {
          $level = count($matches[0]);
        }

        $element[$key] = [
          '#type' => 'container',
        ];

        $element[$key] = array(
          '#type' => 'checkbox',
          '#title' => $option,
          '#default_value' => in_array($key, $selected) ? TRUE : FALSE,
        );
      }
      return $element;
    };
    $element['values'] = $add_elements_level(0, $options, $selected);*/

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
    $tree = array();

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

      $node = array(
        'value' => $key,
        'title' => preg_replace('/^-+/', '', $val),
      );

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
      $form_state->setError($element, t('@name field is required.', array('@name' => $element['#title'])));
    }

    $values = [];
    // Massage submitted form values.
    // Drupal\Core\Field\WidgetBase::submit() expects values as
    // an array of values keyed by delta first, then by column, while our
    // widgets return the opposite.
    foreach (Element::children($element['values']) as $key) {
      if (!empty($element['values'][$key]['#value'])) {
        $values[] = $key;
      }
    }

    // Filter out the 'none' option. Use a strict comparison, because
    // 0 == 'any string'.
    $index = array_search('_none', $values, TRUE);
    if ($index !== FALSE) {
      unset($values[$index]);
    }

    // Transpose selections from field => delta to delta => field.
    $items = array();
    foreach ($values as $value) {
      $items[] = array($element['#key_column'] => $value);
    }
    $form_state->setValueForElement($element, $items);
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
