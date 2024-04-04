<?php

namespace Drupal\timesup\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Times'up settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timesup_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $resolvers = $this->config('timesup.settings')->get('resolvers');

    // Submitted form values should be nested.
    $form['#tree'] = TRUE;

    $form['resolvers'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Resolvers'),
      '#description' => $this->t('Please enable resolvers you actively use to avoid cache/database invalidation stress.'),
    ];

    $form['resolvers']['minutely'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Minutely resolver.'),
      '#default_value' => !empty($resolvers['minutely']),
      '#description' => $this->t('Enable this feature will invalid cache-tags <code>timesup:minutely</code> every minutes (heavy stress sensitive).'),
    ];

    $form['resolvers']['hourly'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Hourly resolver.'),
      '#default_value' => !empty($resolvers['hourly']),
      '#description' => $this->t('Enable this feature will invalid cache-tags <code>timesup:hourly</code> every hour.'),
    ];

    $form['resolvers']['daily'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Daily resolver.'),
      '#default_value' => !empty($resolvers['daily']),
      '#description' => $this->t('Enable this feature will invalid cache-tags <code>timesup:daily</code> every day.'),
    ];

    $form['resolvers']['midnight'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Midnight resolver.'),
      '#default_value' => !empty($resolvers['midnight']),
      '#description' => $this->t('Enable this feature will invalid cache-tags <code>timesup:midnight</code> every day at 00:00:00.'),
    ];

    $form['resolvers']['weekly'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Weekly resolver.'),
      '#default_value' => !empty($resolvers['weekly']),
      '#description' => $this->t('Enable this feature will invalid cache-tags <code>timesup:weekly</code> every weeks.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('timesup.settings')
      ->set('resolvers', [
        'minutely' => (bool) $form_state->getValue(['resolvers', 'minutely']),
        'hourly' => (bool) $form_state->getValue(['resolvers', 'hourly']),
        'daily' => (bool) $form_state->getValue(['resolvers', 'daily']),
        'midnight' => (bool) $form_state->getValue(['resolvers', 'midnight']),
        'weekly' => (bool) $form_state->getValue(['resolvers', 'weekly']),
      ])
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'timesup.settings',
    ];
  }

}
