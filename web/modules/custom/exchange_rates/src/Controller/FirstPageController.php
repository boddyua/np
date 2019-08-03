<?php

/**
 * @file
 * Contains \Drupal\exchange_rates\Controller\FirstPageController.
 */

namespace Drupal\exchange_rates\Controller;

/**
 * Provides route responses for the DrupalBook module.
 */
class FirstPageController {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function content() {
    $element = array(
      '#markup' => 'Hello World!',
    );
    return $element;
  }

}
