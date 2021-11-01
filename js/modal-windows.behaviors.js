/**
 * @file
 *
 * Defines the behavior to added text to modal windows.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.openy_modal_windows = {
    attach: function (context) {
      $(document).ajaxStop(function () {
        var $modal_block = $('.ui-dialog.learn_more');
        if($modal_block.is(':visible')) {
          var $article = $modal_block.children('.ui-dialog-content').children('article');
          var height = $article.height();
          if (height === 0) {
            $article.html("<p class='wait-information'>We are still waiting for information..</p>" );
          }
        }
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
