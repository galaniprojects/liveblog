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

    /*$element['values'] = [
      '#type' => 'container',
    ];
    foreach ($options as $key => $option) {
      $element['values'][$key] = array(
        '#type' => 'checkbox',
        '#title' => $option,
        '#default_value' => in_array($key, $selected) ? TRUE : FALSE,
      );
    }*/

    $tree = [0 => []];
    $levels = [];
    foreach ($options as $key => $option) {
      preg_match('/^\-+/', $option, $matches);
      $level = 0;
      if (!empty($matches[0])) {
        $level = count($matches[0]);
      }

      if (empty($levels[$level])) {
        // Create a sub-item of level - 1.
        $tree[$level];
      }
      else {
        // Create a sub-item of level -1.
      }
      $levels[$level] = $key;
    }

    $tree_source = [
      1 => 'title1', // 0 1
      2 => '-title2', // 0 1, 1 2
      3 => '--title3', // 0 1, 1 2, 2 3
      4 => '--title4', // 0 1, 1 2, 2 4
      5 => 'title5', // 0 5
      6 => '-title6', // 0 5, 1 6
      7 => 'title7', // 0 7
    ];

    $tree_text = $this->mapTree($tree_source);

    $tree_test = [
      [
        'value' => 1,
        'title' => 'title1',
        'children' => [
          [
            'value' => 2,
            'title' => 'title2',
            'children' => [
              [
                'value' => 3,
                'title' => 'title3',
              ],
              [
                'value' => 4,
                'title' => 'title4',
              ]
            ],
          ],
        ],
      ],
      [
        'value' => 5,
        'title' => 'title5',
      ],
      [
        'value' => 6,
        'title' => 'title6',
      ]
    ];

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

  protected function mapTree(array $data, $parentKey = NULL) {
    $tree = array();

    /*$data = [
      1 => 'title1', // 0 1
      2 => '-title2', // 0 1, 1 2
      3 => '--title3', // 0 1, 1 2, 2 3
      4 => '--title4', // 0 1, 1 2, 2 4
      5 => 'title5', // 0 5
      6 => '-title6', // 0 5, 1 6
      7 => 'title7', // 0 7
    ];*/

    $processChildren = FALSE;

    foreach ($data as $key => $val) {
      if ($parentKey) {
        if ($key == $parentKey) {
          $processChildren = TRUE;
        }
      }

      preg_match('/^\-+/', $val, $m);
      $level = 0;

      if (preg_match('/^\-+/', $val, $m)) {
        $level = count($m) - 1;
        $level2 = count($m[0]);

        if ($level != $level2) {
          throw new \Exception('level');
        }
      }

      $node = array(
        'value' => $key,
        'title' => str_replace('-', '', $val),
        'children' => mapTree($data, $key),
      );

      $tree[] = $node;
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
