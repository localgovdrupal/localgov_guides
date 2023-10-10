<?php

namespace Drupal\localgov_guides\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure LocalGov Guides settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'localgov_guides_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['localgov_guides.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['legacy_header'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use legacy header block on Guide pages'),
      '#description' => $this->t('When checked, Localgov Guide pages will use legacy render arrays for the header block title and lede.'),
      '#default_value' => $this->config('localgov_guides.settings')->get('legacy_header') ?? TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('localgov_guides.settings')
      ->set('legacy_header', $form_state->getValue('legacy_header'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
