<?php

namespace addons\cms\controller\api;

use addons\cms\library\IntCode;
use addons\cms\model\Collection as CollectionModel;
use addons\cms\model\Modelx;
use think\Db;
use think\Exception;

/**
 * 会员收藏
 */
class Collection extends Base
{
    protected $layout = 'default';
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 我的收藏
     */
    public function index()
    {
        $collection = new CollectionModel;
        $model = null;
        $type = (int)$this->request->param('type');
        $q = $this->request->param('q');
        $config = ['query' => []];

        // 指定模型
        if ($type) {
            $model = Modelx::get($type);
            if ($model) {
                $collection->where('type', $type);
                $config['query']['type'] = $type;
            }
        }
        // 搜索关键字
        if ($q) {
            $collection->where('title|keywords|description', 'like', '%' . $q . '%');
            $config['query']['q'] = $q;
        }

        $config = get_addon_config('cms');
        $user_id = $this->auth->id;
        $collectionList = $collection->where('user_id', $user_id)
            ->order('id', 'desc')
            ->paginate(10, null, $config);
        foreach ($collectionList as $index => $item) {
            if ($item['type'] == 'archives') {
                $item->aid = isset($config['archiveshashids']) && $config['archiveshashids'] ? IntCode::encode($item->aid) : $item->aid;
            }
        }
        $collectionList = $collectionList->toArray();

        return $this->success('获取成功', [
            'collectionList' => $collectionList,
            'model'          => $model
        ]);
    }

    /**
     * 添加收藏
     */
    public function create()
    {
        $type = $this->request->post("type");
        if (!in_array($type, ['archives', 'page', 'special', 'diyform'])) {
            $this->error("参数不正确");
        }
        if ($type == 'archives') {
            //检测ID是否加密
            $this->hashids('aid');
        }
        $aid = $this->request->post("aid/d");
        $model = call_user_func_array(['\\addons\\cms\\model\\' . ucfirst($type), "get"], [$aid]);
        if (!$model) {
            $this->error("未找到指定数据");
        }
        Db::startTrans();
        try {
            $collection = CollectionModel::lock(true)->where(['type' => $type, 'aid' => $aid, 'user_id' => $this->auth->id])->find();
            if ($collection) {
                throw new \think\Exception("请勿重复收藏");
            }
            $title = $model->title;
            $url = $model->fullurl;
            $image = $model->image;
            $data = [
                'user_id' => $this->auth->id,
                'type'    => $type,
                'aid'     => $aid,
                'title'   => $title,
                'url'     => $url,
                'image'   => $image
            ];
            CollectionModel::create($data);
            Db::commit();
        } catch (\think\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            Db::rollback();
            $this->error("收藏失败");
        }
        $this->success("收藏成功");
    }


    /**
     * 删除收藏
     */
    public function delete()
    {
        $id = (int)$this->request->post('id/d');
        if (!$id) {
            $this->error("参数不正确");
        }
        $collection = \addons\cms\model\Collection::where('id', $id)->where('user_id', $this->auth->id)->find();
        if (!$collection) {
            $this->error("未找到指定的收藏");
        }
        Db::startTrans();
        try {
            $collection->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("移除收藏失败");
        }
        $this->success("移除收藏成功");
    }
}
