<?php

namespace Drupal\openy_memberships_front\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Render Activities/Strategies pages.
 */
class Memberships extends ControllerBase {
  
  /**
   * Activity route method for proxying client request.
   */
  public function page($filepath) {
    $attachments['library'][] = 'openy_memberships_front/openy_memberships_front';
    return [
      '#id' => 'app',
      '#theme' => 'openy_memberships_front',
      '#attached' => $attachments,
    ];
  }

}
