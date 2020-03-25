<?php
/**
 * Castle Plugin Libs
 * Last Update: 2020/03/25
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Castle_Plugin_Libs {
 
 /**
  * 登录界面 Header 回调
  *
  * @access public
  * @return void
  */
 public static function LoginHeaderRender($head) {
  if (!Typecho_Widget::widget('Widget_User')->hasLogin()) {
   if (Helper::options()->PluginLoginSwitch && in_array('style', Helper::options()->PluginLoginSwitch)) {
    $BGURL = (Helper::options()->PluginLoginBG) ? Helper::options()->PluginLoginBG : Helper::options()->pluginUrl.'/Castle/static/bg.jpg';
    $head .= '<link rel="stylesheet" href="'.Helper::options()->pluginUrl.'/Castle/static/login.min.css">';
    $head .= '<script src="'.Helper::options()->pluginUrl.'/Castle/static/login.min.js"></script>';
    $head .= '<style>.i-logo, .i-logo-s{background-image:url(\''.Helper::options()->pluginUrl.'/Castle/static/cat.png\')}#moe-typecho-login-bg{background-image:url(\''.$BGURL.'\')}</style>';
    $head .= '<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no">';
   }
  }

  return $head;
 }
 
}