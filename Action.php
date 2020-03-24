<?php
/**
 * Castle Plugin Action
 * Last Update: 2020/03/23
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
require_once('libs/curl.php');

class Castle_Action extends Typecho_Widget implements Widget_Interface_Do {
 
 public function action() {
  $this->on($this->request->isGet())->api();
 }

 /**
  * API
  */
 public function api() {
  //请求类型
  $type = $this->request->get('type');
  //请求Auth
  $auth = $this->request->get('auth');

  if (!isset($type) || !isset($auth)) {
   http_response_code(403);
   die();
  }

  if ($type == 'bangumi') {
   $this->bangumi($auth);
  }else{
   http_response_code(404);
   die();
  }
 }

 /**
  * 追番列表获取
  */
 public function bangumi($auth) {
  //检查 Auth 是否正确
  self::checkAuth($auth, 'bangumi', Helper::options()->PluginBangumiUID);

  //引入文件
  require_once('libs/bangumi.php');
  
  header('Content-type: application/json');

  $offset = ($this->request->get('offset')) ? $this->request->get('offset') : '0';
  $list = Castle_Bangumi::getList();
  
  if ($offset >= count($list['data'])) {
   $newArray = ['type' => $list['type'], 'data' => [], 'status' => false];
  }else{
   $newArray = ['type' => $list['type'], 'data' => [], 'status' => true];
  }
  
  for ($i=$offset; $i<$offset+5; $i++) {
   if (!isset($list['data'][$i])) { continue; }
   $newArray['data'][] = $list['data'][$i];
  }

  echo json_encode($newArray);
 }

 /**
  * AUTH 验证
  *
  * @access private
  */
 private function checkAuth($auth, $type, $uid = NULL) {
  $getAuth = (Helper::options()->PluginAPIAuth) ? Helper::options()->PluginAPIAuth : '';
  
  if ($type == 'bangumi') {
   $token = md5($type.$getAuth.date('Y-m-d H').$uid.$type);
  }else{
   $token = md5($type.$getAuth.date('Y-m-d H').$type);
  }

  if ($token != $auth) {
   http_response_code(403);
   die();
  }
 }
}