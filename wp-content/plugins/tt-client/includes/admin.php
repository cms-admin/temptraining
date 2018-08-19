<?php

use TTClient\Client;
use TTClient\ClientModel;
use TTClient\ClientYakassa;

/*
 * TT-Client Plugin
 * ===========
 *
 * @category   Marketing
 * @package    Wordpress
 * @author     Alexander Erko <erkoam@mail.ru>
 * @copyright  2017 CMS-Admin.RU
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0.0
 * @link       https://cms-admin.ru/
 */

/**
 * Меню плагина
 */
require_once(TT_CLIENT_DIR.'includes/menu.php');

/**
 *  Подключение собственных стилей в админке
 */
require_once(TT_CLIENT_DIR.'includes/layouts.php');



/**
 * Главная страница плагина в админке
 * @return html render
 */
function tt_client_admin_index()
{
  $context  = Timber::get_context();
  $context['clients'] = Client::getInstance()->getAllClients();
  $context['coaches'] = Client::getInstance()->getAllCoaches();
  $context['total'] = Client::getInstance()->getTotalAmounts();
  $context['orders'] = Client::getInstance()->getAdminLastOrders();
  $context['debtors'] = Client::getInstance()->getAdminDebtors();
  $context['debtors_cnt'] = Client::getInstance()->getAdminDebtorsCount();

  Timber::render('admin/index.twig', $context );
}

/**
 * Настройки плагина
 * @return html render
 */
function tt_client_admin_settings(){
  $context  = Timber::get_context();
  $context['options'] = Client::getInstance()->getPluginOptions();

  Timber::render('admin/options.twig', $context );
}

/**
 * Настройки шаблонов писем
 * @return html render
 */
function tt_client_admin_templates(){
  $context  = Timber::get_context();
  $context['options'] = Client::getInstance()->getPluginOptions(false, true);

  Timber::render('admin/templates.twig', $context );
}

/**
 * Настройки клуба
 * @return html render
 */
function tt_client_admin_club(){
  $mc4wp_lists = Client::getInstance()->getSomeOptions(false, 'mc4wp_mailchimp_list_ids');
  $mc4wp_apikey = Client::getInstance()->getSomeOptions('api_key', 'mc4wp');

  $mail_list = array();

  foreach($mc4wp_lists as $list){
    $dc = substr($mc4wp_apikey,strpos($mc4wp_apikey,'-')+1);
    $url = 'https://'.$dc.'.api.mailchimp.com/3.0/lists/'.$list;
    $body = json_decode( Client::getInstance()->mailchimpCurlConnect( $url, 'GET', $mc4wp_apikey ) );

    $mail_list[] = array('id' => $list, 'name' => $body->name);
  }


  $context  = Timber::get_context();
  $context['path_icons'] = TT_CLIENT_ICONS_URL;
  $context['mail_list'] = $mail_list;
  $context['members_cnt'] = Client::getInstance()->getCountData('members');
  $context['premium_cnt'] = Client::getInstance()->getCountData('premium');
  $context['club_options'] = Client::getInstance()->getSomeOptions(false, 'tt_club_options');

  Timber::render('admin/club.twig', $context );
}

/**
 * Настройки обратной связи
 */
function tt_client_admin_feedback()
{
  $context  = Timber::get_context();

  $context['forms'] = [
    'training' => ClientModel::getInstance()->getOption('form_training', 'tt_client_feedback'),
    'director' => ClientModel::getInstance()->getOption('form_director', 'tt_client_feedback'),
  ];

  $context['emails'] = [
    'training' => ClientModel::getInstance()->getOption('emails_training', 'tt_client_feedback'),
    'director' => ClientModel::getInstance()->getOption('emails_director', 'tt_client_feedback'),
  ];
  $context['recaptcha'] = [
    'sitekey' => ClientModel::getInstance()->getOption('recaptcha_sitekey', 'tt_client_feedback'),
    'secret' => ClientModel::getInstance()->getOption('recaptcha_secret', 'tt_client_feedback'),
  ];

  Timber::render('admin/feedback.twig', $context);
}