<?php

namespace Drupal\Tests\timesup\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @coversDefaultClass \Drupal\timesup\Form\SettingsForm
 *
 * @group timesup
 * @group timesup_functional
 */
class SettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'timesup',
  ];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * Ensure the routing permissions works.
   */
  public function testAccessPermission() {
    // Create a user whitout permission for tests.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/timesup/settings');
    $this->assertSession()->statusCodeEquals(403);

    // Create another user with propre permission for tests.
    $account = $this->drupalCreateUser(['administer timesup']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/timesup/settings');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Ensure the configuration storage works as expected.
   */
  public function testConfigurationPersistance() {
    $settings = $this->container->get('config.factory')->getEditable('timesup.settings');
    $settings->set('resolvers', [
      'minutely' => FALSE,
      'hourly' => FALSE,
      'daily' => TRUE,
      'midnight' => TRUE,
      'weekly' => TRUE,
    ])->save();

    // Create another user with propre permission for tests.
    $account = $this->drupalCreateUser(['administer timesup']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/timesup/settings');
    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->checkboxNotChecked('resolvers[minutely]');
    $this->assertSession()->checkboxNotChecked('resolvers[hourly]');
    $this->assertSession()->checkboxChecked('resolvers[daily]');
    $this->assertSession()->checkboxChecked('resolvers[midnight]');
    $this->assertSession()->checkboxChecked('resolvers[weekly]');

    // Checking the minutely resolver should be presisted.
    $this->submitForm(["resolvers[minutely]" => TRUE, "resolvers[weekly]" => FALSE], 'Save');

    $this->drupalGet('admin/config/timesup/settings');
    $this->assertSession()->checkboxChecked('resolvers[minutely]');
    $this->assertSession()->checkboxNotChecked('resolvers[hourly]');
    $this->assertSession()->checkboxChecked('resolvers[daily]');
    $this->assertSession()->checkboxChecked('resolvers[midnight]');
    $this->assertSession()->checkboxNotChecked('resolvers[weekly]');
  }

}
