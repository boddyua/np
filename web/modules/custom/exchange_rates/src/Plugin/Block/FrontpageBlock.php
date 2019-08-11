<?php

namespace Drupal\exchange_rates\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a exchange_rates for front page.
 *
 * @Block(
 *   id = "exchange_rates_frontpage_block",
 *   admin_label = @Translation("exchange_rates for front page"),
 * )
 */
class FrontpageBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $toshow = explode(' ', empty($config['exchange_rates_frontpage_show']) ? 'EUR USD' : $config['exchange_rates_frontpage_show']);
    $expiresAfter = empty($config['exchange_rates_frontpage_expiresAfter']) ? 600 : $config['exchange_rates_frontpage_expiresAfter'];

    $dbg[] = \Drupal::service('date.formatter')->format(time(), 'date_text');

    $json = '';
    $data = [];

    $cid = 'exchange_rates_frontpage_lastdata';
    $lastdata['time'] = 0;
    $rates = [];
    if ($cache = \Drupal::cache()->get($cid)) {
      $lastdata = $cache->data;
      $json = $lastdata['json'];
      $rates = json_decode($json, TRUE);
      // $dbg[] = "pick json last data, expires: {$lastdata['time']}";
    }

    $_age = time() - $lastdata['time'];
    if (empty($json) || ($_age > $expiresAfter)) {
      /*
      if (empty($json)) {
        $dbg[] = "empty json";
      }
      if ($_age > $expiresAfter) {
        $dbg[] = "age: {$_age}, lasttime: {$lastdata['time']}, expiresAfter: {$expiresAfter}";
      }
      $dbg[] = "get json from url";
      */
      $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';
      // Get all, because API https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=EUR&date=20190803&json
      // sometimes got 504 Gateway Time-out
      // sometimes got [{"message":"Wrong parameters format"}]
      // - Ukrainian Gov's IT, baby!
      $json = file_get_contents($url);
      if (!empty($json)) {
        $rates = json_decode($json, TRUE);
        if (is_array($rates) && count($rates) > 0 && isset($rates[0]['rate'])) {
          \Drupal::cache()->set($cid, ['json' => $json, 'time' => time()]);
          // $dbg[] = "set last data";
        }
        else {
          // Set empty if get error like [{"message":"Wrong parameters format"}] .
          $rates = [];
        }
      }
    }

    if (empty($rates)) {
      // $dbg[] = "seems like error getted, try use the cached value";
      if (!empty($lastdata['json'])) {
        $rates = json_decode($lastdata['json'], TRUE);
      }
    }

    if (empty($rates)) {
      $dbg[] = 'shit happend';
    }
    else {
      foreach ($rates as $rate) {
        if (in_array($rate['cc'], $toshow)) {
          $data[] = $rate;
        }
      }
    }

    $output['dbg'] = $dbg;
    $output['data'] = $data;
    $output['#attached']['library'][] = 'exchange_rates/exchange_rates';

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    $form['exchange_rates_frontpage_show'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Codes of currencies to show, separated by space, e.g. USD EUR'),
      '#default_value' => !empty($config['exchange_rates_frontpage_show']) ? $config['exchange_rates_frontpage_show'] : '',
    ];

    $form['exchange_rates_frontpage_expiresAfter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Getting interval'),
      '#description' => $this->t('Interval in second, to prevent flood of service by often reloads'),
      '#default_value' => !empty($config['exchange_rates_frontpage_expiresAfter']) ? $config['exchange_rates_frontpage_expiresAfter'] : 600,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $expiresAfter = intval($form_state->getValue('exchange_rates_frontpage_expiresAfter'));
    $expiresAfter = empty($expiresAfter) ? 600 : ($expiresAfter < 300 ? 300 : ($expiresAfter > 3600 ? 3600 : $expiresAfter));

    $this->configuration['exchange_rates_frontpage_show'] = $form_state->getValue('exchange_rates_frontpage_show');
    $this->configuration['exchange_rates_frontpage_expiresAfter'] = $expiresAfter;

  }

}
