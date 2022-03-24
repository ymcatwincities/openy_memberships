<?php

namespace Drupal\openy_memberships;

/**
 * Class OmPDFGenerator.
 *
 * @package Drupal\openy_memberships
 */
class OmPDFGenerator {

  /**
   * Outputs generated PDF.
   *
   * @param array $settings
   *   An array which contains content for pdf's body.
   */
  public function generatePdf($settings) {
    if (empty($settings)) {
      return;
    }
    $temporary_directory = \Drupal::service('file_system')->getTempDirectory();
    define("_MPDF_TEMP_PATH", $temporary_directory);
    ini_set("pcre.backtrack_limit", "5000000");
    $html = mb_convert_encoding(render($settings['body']), 'UTF-8', 'UTF-8');
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4', 'tempDir' => $temporary_directory]);
    $stylesheet = file_get_contents(drupal_get_path('module', 'openy_memberships') . '/css/paragraph_skins/memberships-app.css');
    $mpdf->SetTitle($settings['title']);
    $mpdf->SetHTMLFooter(render($settings['footer']));
    $mpdf->WriteHTML($stylesheet, 1);
    $mpdf->WriteHTML($html, 2);
    $mpdf->Output('summary.pdf', 'I');
    exit();
  }

}
