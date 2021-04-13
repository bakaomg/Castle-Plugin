<?php
/**
 * Castle 主题配套插件
 * 
 * @package Castle
 * @author ohmyga
 * @version 0.1.5
 * @link https://ohmyga.cn/
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

//设置时区 [上海]
//如果时间显示有误请注解
date_default_timezone_set("Asia/Shanghai");

define('CASTLE_PLUGIN_VERSION', '0.1.2');
require_once('libs/libs.php');

class Castle_Plugin extends Castle_Plugin_Libs implements Typecho_Plugin_Interface {
 
 /**
  * 激活插件方法,如果激活失败,直接抛出异常
  * 
  * @access public
  * @return void
  * @throws Typecho_Plugin_Exception
  */
 public static function activate() {
  // 检查 PHP 版本
  if (substr(PHP_VERSION,0,3) < '7.0') {
   throw new Typecho_Plugin_Exception('启用失败，PHP 版本必须大于或等于 7.0 。');
  }

  // 检查是否存在 OpenSSL
  if (!extension_loaded('openssl')) {
   throw new Typecho_Plugin_Exception('启用失败，PHP 需启用 OpenSSL 扩展。');
  }

  // 检查是否存在 CURL
  if (!extension_loaded('curl')) {
   throw new Typecho_Plugin_Exception('启用失败，PHP 需启用 CURL 扩展。');
  }

  //注册 Action
  Helper::addAction('castle', 'Castle_Action');

  //登录页面 Header 回调
  Typecho_Plugin::factory('admin/header.php')->header = array('Castle_Plugin', 'LoginHeaderRender');

  //评论者 IP 地址地理位置解析
  Typecho_Plugin::factory('Widget_Comments_Admin')->callIp = array('Castle_Plugin', 'commentLocation');
 }

 /**
  * 禁用插件方法,如果禁用失败,直接抛出异常
  *
  * @static
  * @access public
  * @return void
  * @throws Typecho_Plugin_Exception
  */
 public static function deactivate() {
  Helper::removeAction("castle");
 }

 /**
  * 获取插件配置面板
  *
  * @access public
  * @param Typecho_Widget_Helper_Form $form 配置面板
  * @return void
  */
 public static function config(Typecho_Widget_Helper_Form $form) { }

 /**
  * 个人用户的配置面板
  * 
  * @access public
  * @param Typecho_Widget_Helper_Form $form
  * @return void
  */
 public static function personalConfig(Typecho_Widget_Helper_Form $form){ }

 /**
  * 插件实现方法
  * 
  * @access public
  * @param Typecho_Widget $comments 评论
  * @return void
  */
  public static function commentLocation($comments) {
   if (Helper::options()->PluginCommentSwitch && in_array('showIP', Helper::options()->PluginCommentSwitch)) {
    require_once('libs/IP.php');

    $location = IPLocation_IP::locate($comments->ip);
    echo $comments->ip . '<br>' . $location;
   }else{
    echo $comments->ip;
   }
  }

 public static function getAuth($type) {
  $getAuth = (Helper::options()->PluginAPIAuth) ? Helper::options()->PluginAPIAuth : '';
  return md5($type.$getAuth.date('Y-m-d H').Helper::options()->PluginBangumiUID.$type);
 }

 public static function LoginHeaderRender($head) {
  return Castle_Plugin_Libs::LoginHeaderRender($head);
 }
}