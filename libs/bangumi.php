<?php
/**
 * Castle Plugin Bangumi
 * Last Update: 2020/05/14
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Castle_Bangumi {
 
 /**
  * 从 BGM.tv 获取在看番剧
  *
  * @access private
  */
 public static function __getBGMWatchingList($uid) {
  $apiUrl = 'https://api.bgm.tv/user/'.$uid.'/collection';
  $data = curl::get($apiUrl, ['cat' => 'watching'], [
   'Referer: https://bgm.tv/',
   'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36'
  ]);

  //如果数据为空
  if ($data == null) {
   return false;
  }

  $data = json_decode($data, true);
  $bangumiArray = [];

  foreach($data as $bangumi) {
   if ($bangumi['subject']['type'] == 2) {
    $bangumiArray[] = [
     'name'        =>  $bangumi['name'],
     'name_cn'     =>  $bangumi['subject']['name_cn'],
     'cover'       =>  [
      'large'  => preg_replace('/http/', 'https', $bangumi['subject']['images']['large']),
      'square' => preg_replace('/http/', 'https', $bangumi['subject']['images']['common'])
     ],
     'url'         =>  preg_replace('/http/', 'https', $bangumi['subject']['url']),
     'status'      =>  $bangumi['ep_status'],
     'count'       =>  ($bangumi['subject']['eps_count'] == null) ? '总集数未知' : $bangumi['subject']['eps_count'],
     'progress'    =>  ($bangumi['subject']['eps_count'] == null) ? '0' : 100/$bangumi['subject']['eps_count']*$bangumi['ep_status']
    ];
   }
  }
  
  return $bangumiArray;
 }

 /**
  * 从 哔哩哔哩 获取追番列表
  *
  * @access private
  */
 private static function __getBiliBangumiWatchingList($uid, $token = NULL) {
  $apiUrl = 'https://api.bilibili.com/x/space/bangumi/follow/list';
  $SESSDATA = ($token) ? 'Cookie: SESSDATA='.$token.';' : '';
  $header = [
   $SESSDATA,
   'Referer: https://space.bilibili.com/'.$uid.'/bangumi',
   'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36'
  ];
  $getData = [
   'type'          => '1',
   'follow_status' => '0',
   'pn'            => '1',
   'ps'            => '50',
   'vmid'          => $uid
  ];

  $data = curl::get($apiUrl, $getData, $header);

  $data = json_decode($data, true);

  if ($data['data']['list'] == NULL) {
   return false;
  }
  $bangumiArray = [];

  foreach($data['data']['list'] as $key => $bangumi) {
   //获取观看进度
   preg_match_all('/第(\d+)话/', $bangumi['progress'], $getProgress);

   $bangumiArray[] = [
    'name'     =>  $bangumi['title'],
    'cover'    =>  [
     'large'  => preg_replace('/http/', 'https', $bangumi['cover'])."@188w_251h.jpg",
     'square' => preg_replace('/http/', 'https', $bangumi['square_cover'])
    ],
    'count'    =>  ($bangumi['total_count'] != '-1' && $bangumi['total_count'] != '0' && isset($bangumi['total_count'])) ? $bangumi['total_count'] : 'unknown',
    'url'      =>  $bangumi['url'],
    'status'   =>  (isset($getProgress[1][0])) ? $getProgress[1][0] : '0',
    'progress' =>  (isset($getProgress[1][0]) && $bangumi['total_count'] != '-1' && $bangumi['total_count'] != '0' && isset($bangumi['total_count'])) ? 100/$bangumi['total_count']*$getProgress[1][0] : '0',
    'info'     =>  [
     'area' => $bangumi['areas'][0]['name'],
     'type' => $bangumi['season_type_name'],
     'show' => (isset($bangumi['new_ep']['index_show'])) ? $bangumi['new_ep']['index_show'] : '即将开播',
     'evaluate' => $bangumi['evaluate']
    ]
   ];
  }

  return $bangumiArray;
 }
 
 /**
  * 获取更新
  *
  * @access private
  */
 private static function getUpdate() {
  //获取更新类型
  $type = (Helper::options()->PluginBangumiType) ? Helper::options()->PluginBangumiType : false;
  //获取用户 ID
  $uid = (Helper::options()->PluginBangumiUID) ? Helper::options()->PluginBangumiUID : false;

  //判断缓存目录是否存在
  if (!is_dir(__DIR__ .'/../cache/')) {
   mkdir(__DIR__ .'/../cache/', 0777, true);
   chmod(__DIR__ .'/../cache/', 0777);
  }

  //判断文件是否存在
  if (file_exists(__DIR__ .'/../cache/'.$type.'.json')) {
   $list = json_decode(file_get_contents(__DIR__ .'/../cache/'.$type.'.json'), true);
  }else{
   $list = [
    'last_update' => false,
    'type' => $type,
    'data' => []
   ];
  }

  //判断是否需要更新
  if ((time() - $list['last_update']) < 86400 && $list['last_update'] != false) {
   return $list;
  }

  if ($type != false && $type == 'bgm') {
  //Bangumi(bgm.tv)
   //如果 UID 不为空
   if ($uid != false) {
    $getBGM = self::__getBGMWatchingList($uid);
    
    //如果返回不为空
    if ($getBGM != false && $getBGM != NULL) {
     //替换原先内容
     $list['data'] = $getBGM;

     //更新时间
     $list['last_update'] = time();
    }else{
     //如果更新失败
     $list['last_update'] = time();
    }
   }
  }elseif ($type != false && $type == 'bilibili') {
   //Bilibili(bilibili.com)
   //如果 UID 不为空
   if ($uid != false) {
    $getBili = self::__getBiliBangumiWatchingList($uid,
     (Helper::options()->PluginBangumiBiliSESSDATA) ? Helper::options()->PluginBangumiBiliSESSDATA : NULL
    );
    
    //如果返回不为空
    if ($getBili != false && $getBili != NULL) {
     //替换原先内容
     $list['data'] = $getBili;

     //更新时间
     $list['last_update'] = time();
    }else{
     //如果更新失败
     $list['last_update'] = time();
    }
   }
  }

  //存入数据
  file_put_contents(__DIR__ .'/../cache/'.$type.'.json', json_encode($list, JSON_UNESCAPED_SLASHES));

  return $list;
 }

 /**
  * 输出追番列表
  *
  * @access public
  * @return array
  */
 public static function getList() {
  $get = self::getUpdate();
  return ['type' => $get['type'], 'data' => $get['data']];
 }
 
}
