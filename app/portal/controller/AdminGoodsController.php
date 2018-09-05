<?php
/*
 * 商品相关
 * By: Phpstorm
 * Author: XiaoJie
 * Datetime: 2018/7/6 16:05
 */
namespace app\portal\controller;

use app\portal\model\CateModel;
use app\portal\model\GoodsAttkeyModel;
use app\portal\model\GoodsAttvalueModel;
use app\portal\model\GoodsCommentModel;
use app\portal\model\GoodsModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Loader;
use think\Validate;

class AdminGoodsController extends AdminBaseController
{
    public function index(){
        $where = [];
        $cate_id = $this->request->param('cate_id','','intval');
        $keyword = $this->request->param('keyword','');
        if(!empty($cate_id)){
            $where['c.id'] = $cate_id;
        }
        if(!empty($keyword)){
            if($keyword == '限时特价'){
                $where['g.if_tejia'] = 1;
            }elseif($keyword == '兑换商品'){
                $where['g.if_only_exchange'] = 1;
            }elseif($keyword == '推荐商品'){
                $where['g.if_recommend'] = 1;
            }else{
                $where['g.goods_name'] = ['like',"%$keyword%"];
            }
        }
        $cate = new CateModel();
        $goods = new GoodsModel();
        $list = $goods
            ->alias('g')
            ->field('g.*,c.cate_name')
            ->join('__CATE__ c','g.cid = c.id')
            ->order('g.id DESC')
            ->where($where)
            ->paginate(20);
//        foreach ($list->items() as $k=>$v){
//            $list->items()[$k]['goods_name'] = 3312334;
//        }
        $page = $list->render();
        $catelist = $cate->select();

        $this->assign('catelist',$catelist);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('cate_id',empty($cate_id)?'':$cate_id);
        $this->assign('keyword',empty($keyword)?'':$keyword);
        return $this->fetch();
    }

    //添加
    public function add(){
        $cate = new CateModel();
        $catelist = $cate->select();
        $this->assign('catelist',$catelist);
        return $this->fetch();
    }

    //添加提交
    public function addPost()
    {
        $post = $this->request->param();
        $is_one = $post['if_tejia'] + $post['if_only_exchange'] + $post['if_recommend'];
        if($is_one > 1){
            $this->error('商品属性只能选其一');
        }
        $data_goods = [
            'goods_name' => $post['goods_name'],
            'cid' => $post['cate'],
            'goods_thumb' => $post['goods_thumb'],
            'goods_banner' => empty($post['photo_urls'])?'':json_encode($post['photo_urls']),
            'shortcontent' => $post['shortcontent'],
            'content' => empty($post['content'])?'':$post['content'],
            'goods_now_price' => $post['goods_now_price'],
            'goods_ago_price' => $post['goods_ago_price'],
            'if_tejia' => $post['if_tejia'],
            'if_only_exchange' => $post['if_only_exchange'],
            'if_recommend' => $post['if_recommend'],
            'if_shelf' => $post['if_shelf'],
            'create_time' => $post['create_time'],
        ];
        $validate = Loader::validate('Goods');
        if(!$validate->check($data_goods)){
            $this->error($validate->getError());
        }
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        Db::startTrans();
        $res1 = $goods_id = $goods->insertGetId($data_goods);
        foreach ($post['attkey_name'] as $k=>$v){
            $res2 = $goods_attkey_id = $attkey->insertGetId([
                'good_id' => $goods_id,
                'attkey_name' => $v,
                'goods_attkey_now_price' => $post['goods_attkey_now_price'][$k],
                'goods_attkey_ago_price' => $post['goods_attkey_ago_price'][$k]
            ]);
            if(!$res2){
                Db::rollback();
                $this->error('信息不正确');
            }
            $valuelist = explode(',',$post['attvalue_name'][$k]);
            $valuenowprice = explode(',',$post['goods_attkey_now_price'][$k]);
            $valueagoprice = explode(',',$post['goods_attkey_ago_price'][$k]);
            foreach ($valuelist as $kk=>$vv){
                $res3 = $attvalue-> insert([
                    'goods_attkey_id' => $goods_attkey_id,
                    'attvalue_name' => $vv,
                    'goods_attvalue_now_price' => $valuenowprice[$kk],
                    'goods_attvalue_ago_price' => $valueagoprice[$kk]
                ]);
                if(!$res3){
                    Db::rollback();
                    $this->error('信息不正确');
                }
            }
        }
        if(!$res1){
            Db::rollback();
            $this->error('信息不正确');
        }
        Db::commit();
        $this->success('添加成功');
    }

    //删除
    public function delete()
    {
        $id = $this->request->param('id','','intval');//goods表id
<<<<<<< HEAD
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        $attkey_ids = $attkey->where('good_id',$id)->column('id');
        Db::startTrans();
        $res1 = $goods->where('id',$id)->delete();
        $res2 = $attkey->where('good_id',$id)->delete();
        $res3 = $attvalue->where('goods_attkey_id','in',$attkey_ids)->delete();
        if(!$res1 or !$res2 or !$res3){
            Db::rollback();
            $this->error('删除失败！');
        }
        Db::commit();
        $this->success('删除成功！');
=======
        $goodsModel = new GoodsModel();
        $attkeyModel = new GoodsAttkeyModel();
        $attvalueModel = new GoodsAttvalueModel();
        $attkey=$attkeyModel->where('good_id',$id)->column('id');
        Db::startTrans();
        $res=$attvalueModel->where('goods_attkey_id','in',$attkey)->delete();
        $key_res=$attkeyModel->where('good_id',$id)->delete();
        $goods_res=$goodsModel->where('id',$id)->delete();
        if (!$res||!$key_res||!$goods_res){
            Db::rollback();
            $this->error('删除失败');
        }
        Db::commit();
        $this->success('删除成功');

//        $res = $goodsModel->delete($id);
//        if(!$res){
//            $this->error('删除失败！');
//        }
//        $this->success('删除成功！');
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
    }

    //编辑
    public function edit()
    {
        $id = $this->request->param('id','','intval');//goods表id
        $goods = new GoodsModel();
        $cate = new CateModel();
        $info = $goods->find($id)->toArray();
        $info['content'] = htmlspecialchars_decode($info['content']);
<<<<<<< HEAD
=======
        //$info['goods_banner'] = json_decode($info['goods_banner']);
>>>>>>> 9c781cc2cf65170209d1936be01b7acac8aa5bb9
        $catelist = $cate->select();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        $list = $attkey->where('good_id',$info['id'])->select()->toArray();
        foreach ($list as $k=>$v){
            $info_val = $attvalue->where('goods_attkey_id',$v['id'])->column('attvalue_name');
            $list[$k]['str_attvalue_name'] = implode(',',$info_val);
        }


        $this->assign('catelist',$catelist);
        $this->assign('info',$info);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //编辑提交
    public function editPost()
    {
        $post = $this->request->param();
        $is_one = $post['if_tejia'] + $post['if_only_exchange'] + $post['if_recommend'];
        if($is_one > 1){
            $this->error('商品属性只能选其一');
        }
        $data_goods = [
            'id' => $post['id'],
            'goods_name' => $post['goods_name'],
            'cid' => $post['cate'],
            'goods_thumb' => $post['goods_thumb'],
            'goods_banner' => empty($post['goods_banner'])?'':json_encode($post['goods_banner']),
            'shortcontent' => $post['shortcontent'],
            'content' => empty($post['content'])?'':$post['content'],
            'goods_now_price' => $post['goods_now_price'],
            'goods_ago_price' => $post['goods_ago_price'],
            'if_tejia' => $post['if_tejia'],
            'if_only_exchange' => $post['if_only_exchange'],
            'if_recommend' => $post['if_recommend'],
            'if_shelf' => $post['if_shelf'],
            'create_time' => $post['create_time'],
        ];
        $validate = Loader::validate('Goods');
        if(!$validate->check($data_goods)){
            $this->error($validate->getError());
        }
        $goods = new GoodsModel();
        $attkey = new GoodsAttkeyModel();
        $attvalue = new GoodsAttvalueModel();
        Db::startTrans();
        $arr_goods_attkey_id = $attkey->where('good_id',$post['id'])->column('id');
        $arr_goods_attvalue_id = $attvalue->where('goods_attkey_id','in',$arr_goods_attkey_id)->column('id');
        $res1 = $attkey->where('id','in',$arr_goods_attkey_id)->delete();
        $res2 = $attvalue->where('id','in',$arr_goods_attvalue_id)->delete();
        $res3 = $goods->update($data_goods);
        if(!$res1 or !$res2 or !$res3){
            Db::rollback();
            $this->error('信息填写错误');
        }
        foreach ($post['attkey_name'] as $k=>$v){
            $res4 = $goods_attkey_id = $attkey->insertGetId([
                'good_id' => $post['id'],
                'attkey_name' => $v,
                'goods_attkey_now_price' => $post['goods_attkey_now_price'][$k],
                'goods_attkey_ago_price' => $post['goods_attkey_ago_price'][$k]
            ]);
            if(!$res4){
                Db::rollback();
                $this->error('信息填写不正确');
            }
            $valuelist = explode(',',$post['attvalue_name'][$k]);
            $valuenowprice = explode(',',$post['goods_attkey_now_price'][$k]);
            $valueagoprice = explode(',',$post['goods_attkey_ago_price'][$k]);

            foreach ($valuelist as $kk=>$vv){
                $res5 = $attvalue-> insert([
                    'goods_attkey_id' => $goods_attkey_id,
                    'attvalue_name' => $vv,
                    'goods_attvalue_now_price' => $valuenowprice[$kk],
                    'goods_attvalue_ago_price' => $valueagoprice[$kk]
                ]);
                if(!$res5){
                    Db::rollback();
                    $this->error('信息填写不正确');
                }
            }
        }
        Db::commit();
        $this->success('更新成功');
    }

    /**
     * 批量推荐商品
     */
    public function recommend()
    {
        $post = $this->request->param();
        $goods = new GoodsModel();
        $res = $goods->where('id','in',$post['ids'])->data([
            'if_only_exchange' => 0,
            'if_tejia' => 0,
            'if_recommend' => 1
        ])->update();
        if($res){
            $this->success('批量推荐设置成功');
        }else{
            $this->error('您选择的商品都是推荐，无需设置');
        }
    }

}
