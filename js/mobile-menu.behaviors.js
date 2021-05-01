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
      if ($('#block-openy-lily-content').length > 0) {
        block_id = '#block-lily-memberships-mobile-menu';
      }
      if ($('body').hasClass('rose_based_theme')) {
        block_id = '#block-rose-memberships-mobile-menu';
      }

      $(block_id + ' h2').once('openy_memberships_mobile_menu').click(function(e) {
        $(block_id + ' h2').toggleClass('menu-open');
        $(block_id + ' .wrapper-field-paragraph').toggleClass('d-block');
      });

      // Make main-region full width for Lily theme (fixes narrow view).
      if ($('#block-openy-lily-content').length > 0 && $('.ps-block_container-openy-memberships-desktop-sidebar').length > 0) {
        $('#block-openy-lily-content .main-region')
          .removeClass('col-sm-8')
          .addClass('col-md-8');
      }
      // Make main-region full width for Rosr theme (fixes narrow view).
      if ($('#block-openy-rose-content').length > 0 && $('.ps-block_container-openy-memberships-desktop-sidebar').length > 0) {
        $('#block-openy-rose-content .main-region')
          .removeClass('col-sm-8')
          .addClass('col-md-8');
      }
    }
  };

}(jQuery, Drupal, drupalSettings));
