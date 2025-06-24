<?php

namespace Drupal\localgov_guides\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'GuidesPrevNextBlock' block.
 *
 * @Block(
 *  id = "localgov_guides_prev_next_block",
 *  admin_label = @Translation("Guides prev next block"),
 * )
 */
class GuidesPrevNextBlock extends GuidesAbstractBaseBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->setPages();
    $previous_url = '';
    $previous_title = '';
    $next_url = '';
    $next_title = '';

    if ($this->node->bundle() == 'localgov_guides_overview' && count($this->guidePages) > 0) {
      $next_url = $this->guidePages[0]->toUrl();
      $next_title = $this->guidePages[0]->localgov_guides_section_title->value;
    }

    if ($this->node->bundle() == 'localgov_guides_page') {
      $page_delta = array_search($this->node, $this->guidePages, TRUE);
      if (!empty($this->guidePages[$page_delta - 1])) {
        $previous_url = $this->guidePages[$page_delta - 1]->toUrl();
        $previous_title = $this->guidePages[$page_delta - 1]->title->value;
      }
      else {
        $previous_url = $this->overview->toUrl();
        $previous_title = $this->overview->localgov_guides_section_title->value;
      }
      if (!empty($this->guidePages[$page_delta + 1])) {
        $next_url = $this->guidePages[$page_delta + 1]->toUrl();
        $next_title = $this->guidePages[$page_delta + 1]->title->value;
      }
    }

    $build = [];
    $build[] = [
      '#theme' => 'guides_prev_next_block',
      '#previous_url' => $previous_url,
      '#previous_title' => $previous_title,
      '#next_url' => $next_url,
      '#next_title' => $next_title,
      '#show_title' => $this->configuration['show_title'],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['show_title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show guide title'),
      '#default_value' => $this->configuration['show_title'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();
    $this->configuration['show_title'] = $values['show_title'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'show_title' => FALSE,
    ];
  }

}
