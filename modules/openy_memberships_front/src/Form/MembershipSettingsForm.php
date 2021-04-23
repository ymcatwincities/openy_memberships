<?php
namespace Drupal\openy_memberships_front\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class MembershipSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'openy_memberships_front.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_memberships_front_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['steps'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Steps'),
      '#default_value' => $config->get('steps'),
    ];

    $form['all_branches_btn'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('All Branches Button'),
      '#default_value' => $config->get('all_branches_btn'),
      '#description' => $this->t('Activates "-All Branches-" button on Branch Selector step.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('steps', $form_state->getValue('steps'))
      ->set('all_branches_btn', $form_state->getValue('all_branches_btn'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
