(function ($) {

  Drupal.behaviors.liveblogTaxonomyTree = {
    attach: function (context, settings) {
      var $parentNode = $('.field--widget-liveblog-taxonomy-tree--elements--parent', context);
      // Check/uncheck the children nodes when the parent node value is changed.
      $parentNode.once('liveblog_taxonomy_tree').change(function (e) {
        var $element = $(this);
        var $elementChildren = $element.closest('.field--widget-liveblog-taxonomy-tree--node').find('.field--widget-liveblog-taxonomy-tree--item');
        $elementChildren.prop('checked', $element.prop('checked'));
      });
    }
  };

})(jQuery);
