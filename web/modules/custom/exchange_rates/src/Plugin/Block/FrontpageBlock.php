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

    $toshow = explode(' ', empty($config['exchange_rates_frontpage_show']) ? 'EUR USD' : $config['exchange_rates_frontpage_show'] );
    $expiresAfter = empty($config['exchange_rates_frontpage_expiresAfter']) ? 600 : $config['exchange_rates_frontpage_expiresAfter'];

    $content = date('d.m.Y H:i:s')."<br>";
    $json = '';
    $cachefile = 'exchange_rates_frontpage_lastdata.json';
    $lastdata['time'] = 0;
    $rates = array();
    if(file_exists($cachefile)) {
      $_tmp = file_get_contents($cachefile);
      $lastdata = json_decode($_tmp, TRUE);
      $json = $lastdata['json'];
//      $content .= "pick json last data, expires: {$lastdata['time']}<br>";
    }

    if(empty($json) || (time()-$lastdata['time']>$expiresAfter)) {
//      $content .= "get json from url<br>";
      $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';
      // get all, because API https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=EUR&date=20190803&json sometimes got 504 Gateway Time-out
      // sometimes got [{"message":"Wrong parameters format"}] - Ukrainian Gov's IT, baby!
      $json = file_get_contents($url);
    }

    if(!empty($json)) {
      $rates = json_decode($json, TRUE);
      if(is_array($rates) && count($rates)>0 && isset($rates[0]['rate'])) {
        @file_put_contents( $cachefile, json_encode(array('json'=>$json,'time'=>time())) );
//        $content .= "set last data<br>";
      } else {
        $rates = array(); // set empty if getted error like [{"message":"Wrong parameters format"}]
      }
    }

    if(empty($rates)) {
//      $content .= "seems like error getted, try use the cached value<br>";
      if(!empty($lastdata['json'])) $rates = json_decode($lastdata['json'], TRUE);
    }

    if(empty($rates)) {
      $content .= 'shit happend';
    } else {
      foreach($rates as $rate){
        if( in_array($rate['cc'], $toshow) ) {
          $content .= "<div class='rate curr{$rate['cc']}'>
                        <span class='currId' title='{$rate['txt']}'>{$rate['cc']}</span>
                        <span class='currRate'>{$rate['rate']}</span>
                        <span class='currDate'>{$rate['exchangedate']}</span>
                       </div>";
        }
      }

    }


    return [
      '#markup' => "<div class='frontpage-exchange-rates-wrapper'>{$content}</div>",
    ];
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
    $config = $this->getConfiguration();
    $expiresAfter = $form_state->getValue('exchange_rates_frontpage_show');
    $expiresAfter = empty($expiresAfter) ? 600 : ($expiresAfter<300 ? 300 : ($expiresAfter>3600 ? 3600 : $expiresAfter) ) ;

    $this->configuration['exchange_rates_frontpage_show'] = $form_state->getValue('exchange_rates_frontpage_show');
    $this->configuration['exchange_rates_frontpage_expiresAfter'] = $expiresAfter;

  }
}
