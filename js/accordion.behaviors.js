/**
 * @file
 *
 * Defines the behavior to initialize accordion.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.openy_memberships_accordion = {
    attach: function (context) {
      $(".ps-block_container-openy-memberships-accordion .field-fc-title")
        .once('openy_memberships_accordion').on("click", function() {

        if ($(this).hasClass("active")) {
          $(this).removeClass("active");
          $(this)
            .siblings(".field-fc-body")
            .slideUp(200);
        } else {
          $(".ps-block_container-openy-memberships-accordion .field-fc-title").removeClass("active");
          $(this).addClass("active");
          $(".field-fc-body").slideUp(200);
          $(this)
            .siblings(".field-fc-body")
            .slideDown(200);
        }
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
