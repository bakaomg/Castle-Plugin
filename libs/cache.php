<?php
/**
 * Castle Cache
 * Last Update: 2020/05/14
 */

class Castle_Plugin_Cache {
 /**
  * 创建 cache 文件夹
  */
 private static function mkdir() {
  if (!is_dir(__DIR__ .'/../cache/')) {
   mkdir(__DIR__ .'/../cache/', 0777, true);
   chmod(__DIR__ .'/../cache/', 0777);
  }
 }

 /**
  * 缓存站点配置文件
  *
  * @param  string
  * @access public
  */
 public static function siteConfig($config, $update = false) {
  self::mkdir();

  $file = __DIR__ .'/../cache/siteConfig.js';

  //如果没缓存则新建
  if (!file_exists($file) || $update === true) {
   file_put_contents($file, $config);
  }

  return Helper::options()->pluginUrl.'/Castle/cache/siteConfig.js';
 }

 /**
  * 计算站点配置缓存文件 MD5 值
  *
  * @access public
  * @param  md5_file 
  */
 public static function getSiteConfigMd5() {
  return md5_file(__DIR__ .'/../cache/siteConfig.js');
 }

 /**
  * 缓存追番信息
  */
 public static function bangumi($type, array $data) {
  if (@$data['type'] == NULL || @$type == NULL) {
   $type = 'bilibili';
   $data['type'] = 'bilibili';
  }

  file_put_contents(__DIR__ .'/../cache/'.$type.'.json', json_encode($data, JSON_UNESCAPED_SLASHES));

  return $data;
 }

}