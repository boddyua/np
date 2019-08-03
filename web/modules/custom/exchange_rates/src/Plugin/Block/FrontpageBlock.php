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


    // cached data:
    // $config['exchange_rates_frontpage_lastdata']
    // $config['exchange_rates_frontpage_lasttime']

    $content = 'lasttime: '.$config['exchange_rates_frontpage_lasttime'];
    $json = false;
    $cachefile = 'exchange_rates_frontpage_lastdata.json';
    if(file_exists($cachefile)) {
      $_tmp = file_get_contents($cachefile);
      $lastdata = json_decode($_tmp, TRUE);
      $json = $lastdata['json'];
      $lasttime = $lastdata['time'];
      $content .= "pick json last data, expires: {$lasttime}<br>";
    }

    if(empty($json) || (time()-$lasttime>$expiresAfter)) {
      $json = $config['exchange_rates_frontpage_lastdata'];
    } else {
      $content .= "get json from url<br>";
      $url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json';
      // get all, because API https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?valcode=EUR&date=20190803&json sometimes got 504 Gateway Time-out
      //      $json = file_get_contents($url);
      $json = false;
      if(!$json)  {
        $json = "[
{ 
\"r030\":36,\"txt\":\"Австралійський долар\",\"rate\":17.331,\"cc\":\"AUD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":124,\"txt\":\"Канадський долар\",\"rate\":19.296627,\"cc\":\"CAD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":156,\"txt\":\"Юань Женьмiньбi\",\"rate\":3.680628,\"cc\":\"CNY\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":191,\"txt\":\"Куна\",\"rate\":3.842333,\"cc\":\"HRK\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":203,\"txt\":\"Чеська крона\",\"rate\":1.100888,\"cc\":\"CZK\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":208,\"txt\":\"Данська крона\",\"rate\":3.798846,\"cc\":\"DKK\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":344,\"txt\":\"Гонконгівський долар\",\"rate\":3.262871,\"cc\":\"HKD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":348,\"txt\":\"Форинт\",\"rate\":0.0867451,\"cc\":\"HUF\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":356,\"txt\":\"Індійська рупія\",\"rate\":0.3667207,\"cc\":\"INR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":360,\"txt\":\"Рупія\",\"rate\":0.00179821,\"cc\":\"IDR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":364,\"txt\":\"Іранський ріал\",\"rate\":0.000608,\"cc\":\"IRR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":376,\"txt\":\"Новий ізраїльський шекель\",\"rate\":7.312479,\"cc\":\"ILS\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":392,\"txt\":\"Єна\",\"rate\":0.239202,\"cc\":\"JPY\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":398,\"txt\":\"Теньге\",\"rate\":0.066201,\"cc\":\"KZT\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":410,\"txt\":\"Вона\",\"rate\":0.0212699,\"cc\":\"KRW\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":484,\"txt\":\"Мексіканський песо\",\"rate\":1.321987,\"cc\":\"MXN\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":498,\"txt\":\"Молдовський лей\",\"rate\":1.431261,\"cc\":\"MDL\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":554,\"txt\":\"Новозеландський долар\",\"rate\":16.658159,\"cc\":\"NZD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":578,\"txt\":\"Норвезька крона\",\"rate\":2.861832,\"cc\":\"NOK\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":643,\"txt\":\"Російський рубль\",\"rate\":0.39506,\"cc\":\"RUB\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":682,\"txt\":\"Саудівський рiял\",\"rate\":6.810056,\"cc\":\"SAR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":702,\"txt\":\"Сінгапурський долар\",\"rate\":18.549498,\"cc\":\"SGD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":710,\"txt\":\"Ренд\",\"rate\":1.737123,\"cc\":\"ZAR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":752,\"txt\":\"Шведська крона\",\"rate\":2.645158,\"cc\":\"SEK\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":756,\"txt\":\"Швейцарський франк\",\"rate\":25.946557,\"cc\":\"CHF\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":818,\"txt\":\"Єгипетський фунт\",\"rate\":1.547909,\"cc\":\"EGP\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":826,\"txt\":\"Фунт стерлінгів\",\"rate\":30.995226,\"cc\":\"GBP\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":840,\"txt\":\"Долар США\",\"rate\":25.537711,\"cc\":\"USD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":933,\"txt\":\"Бiлоруський рубль\",\"rate\":12.4253,\"cc\":\"BYN\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":944,\"txt\":\"Азербайджанський манат\",\"rate\":15.022183,\"cc\":\"AZN\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":946,\"txt\":\"Румунський лей\",\"rate\":5.990534,\"cc\":\"RON\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":949,\"txt\":\"Турецька ліра\",\"rate\":4.558297,\"cc\":\"TRY\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":960,\"txt\":\"СПЗ(спеціальні права запозичення)\",\"rate\":35.066798,\"cc\":\"XDR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":975,\"txt\":\"Болгарський лев\",\"rate\":14.501576,\"cc\":\"BGN\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":978,\"txt\":\"Євро\",\"rate\":28.362182,\"cc\":\"EUR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":985,\"txt\":\"Злотий\",\"rate\":6.593709,\"cc\":\"PLN\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":12,\"txt\":\"Алжирський динар\",\"rate\":0.209821,\"cc\":\"DZD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":50,\"txt\":\"Така\",\"rate\":0.297265,\"cc\":\"BDT\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":51,\"txt\":\"Вiрменський драм\",\"rate\":0.0525856,\"cc\":\"AMD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":368,\"txt\":\"Іракський динар\",\"rate\":0.021089,\"cc\":\"IQD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":417,\"txt\":\"Сом\",\"rate\":0.358702,\"cc\":\"KGS\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":422,\"txt\":\"Ліванський фунт\",\"rate\":0.016634,\"cc\":\"LBP\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":434,\"txt\":\"Лівійський динар\",\"rate\":17.849019,\"cc\":\"LYD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":458,\"txt\":\"Малайзійський ринггіт\",\"rate\":6.06338,\"cc\":\"MYR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":504,\"txt\":\"Марокканський дирхам\",\"rate\":2.608774,\"cc\":\"MAD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":586,\"txt\":\"Пакистанська рупія\",\"rate\":0.157116,\"cc\":\"PKR\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":704,\"txt\":\"Донг\",\"rate\":0.00107848,\"cc\":\"VND\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":764,\"txt\":\"Бат\",\"rate\":0.814071,\"cc\":\"THB\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":784,\"txt\":\"Дирхам ОАЕ\",\"rate\":6.812447,\"cc\":\"AED\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":788,\"txt\":\"Туніський динар\",\"rate\":8.692591,\"cc\":\"TND\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":860,\"txt\":\"Узбецький сум\",\"rate\":0.002887,\"cc\":\"UZS\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":901,\"txt\":\"Новий тайванський долар\",\"rate\":0.805095,\"cc\":\"TWD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":934,\"txt\":\"Туркменський новий манат\",\"rate\":7.148787,\"cc\":\"TMT\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":936,\"txt\":\"Ганських седі\",\"rate\":4.655371,\"cc\":\"GHS\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":941,\"txt\":\"Сербський динар\",\"rate\":0.237682,\"cc\":\"RSD\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":972,\"txt\":\"Сомонi\",\"rate\":2.650588,\"cc\":\"TJS\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":981,\"txt\":\"Ларi\",\"rate\":8.419394,\"cc\":\"GEL\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":959,\"txt\":\"Золото\",\"rate\":36673.43,\"cc\":\"XAU\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":961,\"txt\":\"Срiбло\",\"rate\":408.731,\"cc\":\"XAG\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":962,\"txt\":\"Платина\",\"rate\":21553.828,\"cc\":\"XPT\",\"exchangedate\":\"05.08.2019\"
 }
,{ 
\"r030\":964,\"txt\":\"Паладiй\",\"rate\":37055.219,\"cc\":\"XPD\",\"exchangedate\":\"05.08.2019\"
 }
]";

      }

      if($json!==false) {
        $this->setConfigurationValue('exchange_rates_frontpage_lastdata', $json);
        $this->setConfigurationValue('exchange_rates_frontpage_lasttime', time());
        $content .= "set last data<br>";
      }

    }



    if($json===false) {
      $content .= 'shit happend';
    } else {
      $rates = json_decode($json, TRUE);

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
