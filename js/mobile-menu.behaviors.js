/**
 * @file
 *
 * Defines the behavior to transform sidebar to accordion on mobile.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.openy_memberships_mobile_menu = {
    attach: function (context) {
      $('#block-memberships-mobile-menu h2').once('openy_memberships_mobile_menu').click(function(e) {
        $('#block-memberships-mobile-menu h2').toggleClass('menu-open');
        $('#block-memberships-mobile-menu .wrapper-field-paragraph').toggleClass('d-block');
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
