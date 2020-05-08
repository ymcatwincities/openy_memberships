/**
 * @file
 *
 * Defines the behavior to transform sidebar to accordion on mobile.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.openy_memberships_mobile_menu = {
    attach: function (context) {
      var block_id = '#block-memberships-mobile-menu';
      if ($('body').hasClass('rose_based_theme')) {
        block_id = '#block-rose-memberships-mobile-menu';
      }

      $(block_id + ' h2').once('openy_memberships_mobile_menu').click(function(e) {
        $(block_id + ' h2').toggleClass('menu-open');
        $(block_id + ' .wrapper-field-paragraph').toggleClass('d-block');
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
