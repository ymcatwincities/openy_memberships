/**
 * @file
 *
 * Defines the behavior to transform sidebar to accordion on mobile.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.button = {
    attach: function (context) {
      if ($('#block-openy-lily-content').find(".membership-app").hasClass('BranchSelectorHome')) {
        var a = $(".ps-simple_content-openy-memberships-button").children();
        a.find('p a.btn-primary').addClass("grayed-out");
      }
    }
  };

}(jQuery, Drupal, drupalSettings));
