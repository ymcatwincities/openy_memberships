/**
 * @file
 *
 * Defines the behavior to transform sidebar to accordion on mobile.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.openy_memberships_mobile_menu = {
    attach: function (context) {
      var block_id = '.ps-teasers-openy-memberships-desktop-sidebar';

      $(block_id + ' h2').click(function(e) {
        $(this).toggleClass('menu-open');
        $('.sidebar-region').toggleClass('menu-open');
        $(block_id).children('.wrapper-field-prgf-teaser-content').toggle();
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
