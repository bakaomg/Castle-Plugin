<?php

/**
 * Castle Plugin Action
 * Last Update: 2022/04/04
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
require_once('libs/curl.php');

/**
 * 大声喵喵：
 * 
 * 别吐槽这代码太 shit 了
 * 我自己看着都快趋势了））
 * 等主题重构再一并重构插件
 * 这一年来学到了许多的奇奇怪怪的魔法 到时或许会用得上（
 */

class Castle_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $_db;

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);

        $this->_db = Typecho_Db::get();
    }


    public function action()
    {
        $this->on($this->request->isGet())->api();
        $this->on($this->request->isPost())->search();
    }

    /**
     * 搜索接口
     */
    public function search()
    {
        //请求类型
        $type = $this->request->get('type');

        //搜索内容
        $search = !empty($_POST["search"]) ? $_POST['search'] : null;

        if ($type != "search") {
            http_response_code(404);
            die();
        }

        if ($search == null && $search == '') {
            http_response_code(404);
            die();
        }

        $select = $this->_db
            ->select('cid', 'title', 'created', 'modified', 'slug', 'commentsNum', 'text', 'type')
            ->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->where('created < ?', time())
            ->where('password IS NULL')
            ->limit(5)
            ->order('created', Typecho_Db::SORT_DESC);
        $searchQuery = '%' . str_replace(' ', '%', $search) . '%';
        $select->where('title LIKE ? OR text LIKE ?', $searchQuery, $searchQuery);
        $result = $this->_db->fetchAll($select);

        $res = [
            'data' => [],
        ];

        foreach ($result as $key => $value) {
            $value = $this->filter($value);
            $res['data'][] = [
                'title'    =>  $value['title'],
                'link'     =>  $value['permalink'],
                'text'     =>  $value['text'],
                'excerpt'  =>  $value['excerpt']
            ];
        }

        $res['has'] = (count($res['data']) <= 0) ? false : true;

        header("content-type: application/json");

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    private function filter($result)
    {
        $widget = $this->widget('Widget_Abstract_Contents');

        $result['text'] = (!empty($result['text'])) ? $result['text'] : "";
        $result['digest'] = (!empty($result['digest'])) ? $result['digest'] : "";

        $result['password'] = '';

        $result = $widget->filter($result);
        $result['text'] = $widget->markdown($result['text']);
        $result['digest'] = $widget->markdown($result['digest']);
        $result['excerpt'] = Typecho_Common::subStr(strip_tags($result['text']), 0, 100, "...");

        return $result;
    }

    /**
     * API
     */
    public function api()
    {
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
        } else {
            http_response_code(404);
            die();
        }
    }

    /**
     * 追番列表获取
     */
    public function bangumi($auth)
    {
        //检查 Auth 是否正确
        self::checkAuth($auth, 'bangumi', Helper::options()->PluginBangumiUID);

        //引入文件
        require_once('libs/bangumi.php');

        header('Content-type: application/json');

        $offset = ($this->request->get('offset')) ? $this->request->get('offset') : '0';
        $list = Castle_Bangumi::getList();

        if ($offset >= count($list['data'])) {
            $newArray = ['type' => $list['type'], 'data' => [], 'status' => false];
        } else {
            $newArray = ['type' => $list['type'], 'data' => [], 'status' => true];
        }

        for ($i = $offset; $i < $offset + 5; $i++) {
            if (!isset($list['data'][$i])) {
                continue;
            }
            $newArray['data'][] = $list['data'][$i];
        }

        echo json_encode($newArray);
    }

    /**
     * AUTH 验证
     *
     * @access private
     */
    private function checkAuth($auth, $type, $uid = NULL)
    {
        $getAuth = (Helper::options()->PluginAPIAuth) ? Helper::options()->PluginAPIAuth : '';

        if ($type == 'bangumi') {
            $token = md5($type . $getAuth . date('Y-m-d H') . $uid . $type);
        } else {
            $token = md5($type . $getAuth . date('Y-m-d H') . $type);
        }

        if ($token != $auth) {
            http_response_code(403);
            die();
        }
    }
}
