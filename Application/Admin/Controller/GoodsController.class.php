<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class GoodsController extends CommonController {

    private $name='商品';

    public function _initialize(){
        parent::_initialize();
    }
    public function batchadd(){
        if(IS_POST){
            $form_type=I("form_type",'');
            if($form_type=='review_excel'){
                $upload = new \Think\Upload(); //实例化tp的上传类
                $upload->exts = array('xls','xlsx'); //设置附件上传类型
                $upload->rootPath = './'; //相对于站点根目录jd
                $upload->savePath = '/Public/Uploads/excel/'; //设置附件上传目录,地址是相对于根目录(rootPath的)
                $info = $upload->uploadOne($_FILES['excelfile']); //开始上传
                $fullpath=$info['savepath'].$info['savename'];
                $data=loadExcelContent($fullpath);
                if($data){
                    $i=1;
                    foreach($data as &$one){
                        $one['temp_id']=$i;
                        $one['paking']=$this->explain_text($one['paking']);
                        $one['prodInfo']=$this->explain_text($one['prodInfo']);
                        $one['wbi']=$this->explain_text($one['wbi']);

                        $protype=M('protype')->where("proTypeId=".$one['proTypeId'])->cache(true,1200)->find();
                        //var_dump($protype);
                        if(M("prods")->where("prodNo='".$one['prodNO']."'")->find()){
                            $this->error('产品编号为：'.$one['prodNO'].'已经存在商品库重复');
                            die();
                        }
                        if($protype==null||M("protype")->where("proPid=".$protype['proTypeId'])->count()>0){
                            $this->error('产品编号为：'.$one['prodNO'].'的记录中所设置的proTypeId正确。');
                            die();
                        }
                        $i++;
                    }
                    session("batch_data",$data);
                    unlink($_SERVER['DOCUMENT_ROOT'].$fullpath);
                    $this->assign("batch_data",$data);
                    $this->display("Goods/batchadd_result");

                }else{
                    unlink($_SERVER['DOCUMENT_ROOT'].$fullpath);
                    $this->error("excel文件无效，请确认格式");
                }

//                if(!$info){
//                    $this->error($upload->getError());
//                }else{
//                    $pic['file'] = $info['savepath'].$info['savename']; //获取文件名
//                    //ori是根据需要进行更改的
//                    $pic['simimg'] = $info['savepath'].'50_'.$info['savename']; //获取缩略图的文件名
//                    $pic['img'] = $upload->rootPath.$info['savepath'].$info['savename']; //获取完整的图片地址
//
//                    return $pic; //返回相关信息数组
//                }
                return;
            }elseif($form_type=='commit_excel'){
                $tempid=I("tempid",0);
                $canInsert=array();
                $inserted=0;
                $session_data=session("batch_data");
//                var_dump($tempid);
//                var_dump($session_data);
//                die;
                foreach($tempid as &$one){

                    foreach($session_data as &$data){
                        if(intval($data['temp_id'])==intval($one)){
                            unset($data['temp_id']);
                            if($data['isNew']==null){
                                $data['isNew']=0;
                            }
                            if($data['isHot']==null){
                                $data['isHot']=0;
                            }
                            if($data['totalSale']==null){
                                $data['totalSale']=0;
                            }
                            if($data['totalSale']==null){
                                $data['totalSale']=0;
                            }
                            M("prods")->data($data)->add();

                            $inserted++;
                        }
                    }
                }
                session("batch_data",null);
//                echo "添加了".$inserted."条";die;
                $this->success("添加了".$inserted."条",U('Goods/index'));
                return;
            }
        }
        $this->display();
    }
    private function explain_text($text){
        $str = str_replace(array("\r\n", "\r", "\n"), "<br/>", $text);
        $str="<p style='font-family: Arial;font-size:16px;'>".$str."</p>";
        return $str;
    }
    //商品列表页
    public function index(){
        $prodName = I('prodName');
        $typeName=I('typeName');
        $styleId=I('styleselect');
        $proTypeId=I('proTypeId');
        $brandId=I('brandId');
        $prodNO=I('prodNO');
        $Hot=I('Hot');
        $New=I('New');
        $where['status'] = array('lt',2);
        if(!empty($Hot)){
            switch($Hot){
                case 1:$where['isHot']=1;break;
                case 2:$where['isHot']=0;break;
            }
        }
        if(!empty($New)){
            switch($New){
                case 1:$where['isNew']=1;break;
                case 2:$where['isNew']=0;break;
            }
        }
        if(!empty($prodNO)){
            $where['prodNO'] = array('like','%'.$prodNO.'%');
        }
        if(!empty($prodName)){
            $where['prodName'] = array('like','%'.$prodName.'%');
        }
        if(!empty($proTypeId)){
            $where['p.proTypeId'] = array('in',$this->intypepId($proTypeId));
        }
        if(!empty($brandId) or $brandId==0){
            if($brandId!=0){
                $where['brandId']=$brandId;
            }
        }
        //按系列分类搜索
        if(!empty($typeName)){
            if(!empty($styleId)){
                //找到指定的系列
                $where['a.styleId']=$styleId;
            }else{
                //in 当前分类下所有的系列
                $in=$this->get_table("products_style")->where("typeName='%s'",$typeName)->select();
                $wherein='';
                foreach($in as &$i){
                    if(!empty($wherein)){
                        $wherein.=",".$i['styleId'];
                    }else{
                        $wherein.=$i['styleId'];
                    }
                }
                $where['a.styleId']=array('in',$wherein);
            }
            $res=$this->select_join_prods_styles($where);
            $data=$res['data'];
            $show=$res['show'];
        }else{
            if(!empty($styleId)){
                //找到指定的系列
                $where['a.styleId']=$styleId;
                $res=$this->select_join_prods_styles($where);
                $data=$res['data'];
                $show=$res['show'];
            }else{
                //不搜索系列分类
                $res=$this->select_notjoin($where);
                $data=$res['data'];
                $show=$res['show'];
            }
        }
        foreach($data  as &$one){
            $one['prodName']=$this->string_Handle($one['prodName']);
            $one['proName']=$this->string_Handle($one['proName']);
            $one['simimg']=$this->full_url().$one['simimg'];
            $one['status_name']=$this->format_status($one['status']);
            $one['isHot_name']=$this->is_not($one['isHot']);
            $one['isNew_name']=$this->is_not($one['isNew']);
        }
        //分类option
        $r = $this->get_table('protype')->where("proLive=1")->order('proPath')->select();
        $brandArr=$this->get_table('brands')->where('status=1')->select();
        $typeArr=$this->get_table('products_style')->group('typeName')->field('typeName')->where('status=1')->select();
        $type = $this->demo($r, 0);
        $option = $this->option($type,true,$proTypeId);
        $this->typeArr=$typeArr;
        $this->brandArr=$brandArr;
        $this->brandId=$brandId;
        $this->prodName=$prodName;
        $this->New=$New;
        $this->Hot=$Hot;
        $this->proTypeId=$proTypeId;
        $this->option=$option;
        $this->page=$show;
        $this->data=$data;
        $this->title=$this->name;
        $this->display();
    }
    public function select_notjoin($where){
        $count=$this->get_table('prods')
            ->alias('p')
            ->join("jd_protype AS t ON p.proTypeId=t.proTypeId")
            ->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('prods')
            ->alias('p')
            ->join("jd_protype AS t ON p.proTypeId=t.proTypeId")
            ->field("p.*,t.proName")
            ->limit($page->firstRow.','.$page->listRows)->where($where)->order('p.prodId desc')->select();
        $res['show']=$show;
        $res['data']=$data;
        return $res;
    }
    public function select_join_prods_styles($where){
        $count=$this->get_table('prods')->alias('p')
            ->join("jd_prods_styles AS a ON a.prodId=p.prodId")
            ->join("jd_protype AS t ON p.proTypeId=t.proTypeId")
            ->group('a.prodId')
            ->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('prods')
            ->alias('p')
            ->join("jd_prods_styles AS a ON a.prodId=p.prodId")
            ->join("jd_protype AS t ON p.proTypeId=t.proTypeId")
            ->field("p.*,a.styleId")
            ->field("p.*,t.proName,a.styleId")
            ->group('a.prodId')
            ->limit($page->firstRow.','.$page->listRows)->where($where)->order('p.prodId desc')->select();
        $res['show']=$show;
        $res['data']=$data;
        return $res;
    }


    //添加商品页
    public function add(){
        $r = $this->get_table('protype')->order('proPath')->where('proLive=1')->select();
        $brandsArr = $this->get_table('brands')->where('status=1')->select();
        $business=$this->get_table('authentication')->where('status=1')->select();
        $r = $this->demo($r, 0);
        $options = $this->option($r);
        $this->assign('options',$options);
        $this->brandsArr=$brandsArr;
        $this->business=$business;
        $this->title=$this->name;
        $this->display();
    }
    //编辑商品信息页
    public function edit(){
        $id=I('get.gid');
        if($id){
            $url= $_SERVER['HTTP_REFERER'];
            $data=$this->get_table('prods')->where("prodId=%d",$id)->find();
            $brandsArr = $this->get_table('brands')->where('status=1')->select();
            $business=$this->get_table('authentication')->where('status=1')->select();
            $data['img']=$this->full_url().$data['img'];
            if($data['banner']){
                $data['banner']=$this->full_url()."banner/".$data['banner'];
            }
            if($data['banner_link']=='javascript:void(0);' or $data['banner_link']=='#'){
                $data['banner_link']='';
            }
            $data['business']=explode(",",$data['businessId']);
            $r = $this->get_table('protype')->order('proPath')->where('proLive=1')->select();
            $r = $this->demo($r, 0);
            $options = $this->option($r,'',$data['proTypeId']);
            $this->data=$data;
            $this->url=$url;
            $this->business=$business;
            $this->title=$this->name;
            $this->brandsArr=$brandsArr;
            $this->options=$options;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }


    //商品详情表
    function detail(){
        $id=I('get.gid');
        if($id){
            $goodData=$this->get_table('prods')
                ->alias('p')
                ->join("jd_protype AS t ON p.proTypeId=t.proTypeId")
                ->field('p.*,t.proName')
                ->where('p.prodId=%d',$id)->find();
            if($goodData){
                if($goodData['brandId']){
                    $brands= $this->get_table('brands')->where('brandID=%d',$goodData['brandId'])->field('brandName')->find();
                    $goodData['brandName']=$brands['brandName'];
                }else{
                    $goodData['brandName']='未选择风格';
                }
                $authenti=$this->get_table('authentication')->where("id in (".$goodData['businessId'].")")->select();
                if($authenti){
                    foreach($authenti as &$au){
                        $au['image']=$this->full_url()."authentication/".$au['image'];
                    }
                    $this->authenti=$authenti;
                }
                $goodData['isNew']=$this->format_is_show($goodData['isNew']);
                $goodData['isHot']=$this->is_not($goodData['isHot']);
                $goodData['create_time']=$this->time_format($goodData['create_time']);
                $goodData['updata_time']=$this->time_format($goodData['updata_time']);
                $goodData['status']=$this->format_status($goodData['status']);
                $goodData['simimg']=$this->full_url().$goodData['simimg'];
                $count=$this->get_table('products')->alias('p')->join('jd_colors AS c ON c.colorId=p.colorId')->join('jd_sizes AS s ON s.sizeId=p.sizeId')->where('prodId=%d',$id)->count();
                $page=$this->get_page($count,15);
                $show = $page->show(); //分页显示输出
                $products=$this->get_table('products')->alias('p')
                    ->join('jd_colors AS c ON c.colorId=p.colorId')
                    ->join('jd_sizes AS s ON s.sizeId=p.sizeId')
                    ->field("p.productId,p.status,p.saleNum,p.price,p.commentNum,c.colorName,s.sizeName,p.isDefault,p.productNO,p.prodId")
                    ->where('p.prodId=%d and p.status<2',$id)
                    ->limit($page->firstRow.','.$page->listRows)
                    ->order('productId desc')
                    //                    ->order('isDefault desc,productId desc')默认的显示靠前
                    ->select();

                if($products){
                    foreach($products as &$one){
                        $one['status_name']=$this->format_status($one['status']);
                        $goodData['saleNum']+=$one['saleNum'];
                    }
                    $this->products=$products;
                }else{
                    $goodData['saleNum']=0;
                }
                $this->title1='商品基础信息';
                $this->title2='商品详细列表';
                $this->page=$show;
                $this->goodData=$goodData;
                $this->display();
            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //商品添加操作
    function add_doit(){
        $wbi=I('post.wbi');
        $paking=I('post.paking');
        $keyword=I('post.keyword');
        $mqq=I('post.mqq');
        $prodInfo=I('post.prodInfo');
        $prodNO=I('post.prodNO');
        $prodName=I('post.prodName');
        $banner_link=I('post.banner_link');
        $businessId=I('post.businessId');
        $price1=I("post.price1");
        $data['price1']=I('post.price1');
        $brandId=I('post.brandId');
        $isHot=I('post.isHot');
        $isNew=I('post.isNew');
        $proTypeId=I('post.TypeId');
        $totalSale=I('post.totalSale');
        if($isHot==0 or $isHot==1){
            $data['isHot']=$isHot;
        }
        if($isNew==0 or $isNew==1){
            $data['isNew']=$isNew;
        }
        if($data['isNew']+$data['isHot']==2){
            $this->error("热门和New只能选择一项");
        }
        if($mqq>0){
            $data['mqq']=$mqq;
        }
        if($brandId){
            $data['brandId']=$brandId;
        }
        if($banner_link){
            $data['banner_link']=$banner_link;
        }else{
            $data['banner_link']='javascript:void(0);';
        }
        if($keyword){
            $data['keyword']=rawurldecode($keyword);
        }
        if($businessId){
            $data['businessId']=implode(',',$businessId);
        }
        if($brandId){
            $data['brandId']=$brandId;
        }else{
            $data['brandId']=0;
        }
        if($_FILES['image']['name']){
            $data['banner']= $this->upFile();
        }
        if($prodInfo){
            $data['prodInfo']=htmlspecialchars_decode(html_entity_decode($prodInfo));
        }
        if($paking){
            $data['paking']=htmlspecialchars_decode(html_entity_decode($paking));
        }
        if($wbi){
            $data['wbi']=htmlspecialchars_decode(html_entity_decode($wbi));
        }
        if($totalSale){
            $data['totalSale']=$totalSale;
        }
        if(!$proTypeId){
            $this->error("请选择商品分类");
        }else{
            $data['proTypeId']=$proTypeId;
        }
        if(!$prodName){
            $this->error("未填写商品名称");
        }else{
            $data['prodName']=$prodName;
        }
        if(!$prodNO){
            $this->error("未填写商品唯一编号");
        }else{
            $data['prodNO']=$this->check_field(null,$prodNO,"prods","prodNO");
        }
        if(!$price1){
            $this->error("未填写商品价格");
        }else{
            $data['price1']=$price1;
        }
        if($_FILES['img']['name']){ //如果图片上传
            $pic = $this->upload();
            $data['img'] = $pic['file'];
            $data['simimg'] = $pic['simimg'];
        }else{
            $this->error("请上传商品图片");
        }
        $data['create_time']=time();
        $add=$this->get_table('prods')->add($data);
        if($add){
            $this->write_log('添加商品','添加商品,商品名称:'.$prodName."编号".$prodNO);
            $this->success('添加成功',U('Goods/index'));
        }else{
            $this->error('添加失败');
        }
    }

    //操作修改商品信息
    public function edit_doit(){
        $id=I('post.prodId');
        $prodName=I('post.prodName');
        $mqq=I('post.mqq');
        $url=I('post.url');
        $prodInfo=I('post.prodInfo');
        $wbi=I('post.wbi');
        $paking=I('post.paking');
        $prodNO=I('post.prodNO');
        $banner_link=I('post.banner_link');
        $businessId=implode(',',I('post.businessId'));
        $price1=I('post.price1');
        $status=I('post.status');
        $keyword=I('post.keyword');
        $isNew=I('post.isNew');
        $isHot=I('post.isHot');
        $brandId=I('post.brandId');
        $totalSale=I('post.totalSale');
        if($id){
            if($isHot+$isNew>=2){
                $this->error("热门和New只能选择其中一个显示");
            }
            $list=$this->get_table('prods')->where("prodId=%d",$id)->find();
            if($list){
                $old_list=$list;
                $log = "编辑商品 id为：" .$id;
                if(($isHot==0 or $isHot==1) && $list['isHot']!=$isHot){
                    $list['isHot']=$isHot;
                    $log.=$this->save_log('是否热门',$old_list['isHot'],$isHot);
                }
                if(($isNew==0 or $isNew==1) && $list['isNew']!=$isNew){
                    $list['isNew']=$isNew;
                    $log.=$this->save_log('是否NEW',$old_list['isNew'],$isNew);
                }
                if($_SESSION['admin']['levelId']!=4){
                    if(!$status){$status=0;}
                    if(($status==0 or $status==1) && $list['status']!=$status){
                        $list['status']=$status;
                        $log.=$this->save_log('状态',$old_list['status'],$status);
                    }
                }
                if(!$totalSale){$totalSale=0;}
                if($list['totalSale']!=$totalSale){
                    if($totalSale) {
                        $list['totalSale']=$totalSale;
                    }else{
                        $list['totalSale']=0;
                    }
                    $log.=$this->save_log('总售出数量',$old_list['totalSale'],$list['totalSale']);
                }

                if($_FILES['img']['error'] === 0){ //如果图片上传
                    $pic = $this->upload();
                    $img = $pic['file'];
                    $simimg = $pic['simimg'];
                    $list['img']=$img;
                    $list['simimg']=$simimg;
                    $log.='更新了商品图片';
                }
                if($_FILES['image']['error'] === 0){ //如果图片上传
                    $banner= $this->upFile();
                    $list['banner']=$banner;
                    $log.='更新了广告图片';
                }
                if($list['keyword']!=$keyword){
                    if($keyword){
                        $list['keyword']=$keyword;
                    }else{
                        $list['keyword']='';
                    }
                    $log.=$this->save_log('关键字',$old_list['keyword'],$list['keyword']);
                }
                if($list['mqq']!=$mqq){
                    if($mqq>0){
                        $list['mqq']=$mqq;
                    }else{
                        $list['mqq']=0;
                    }
                    $log.=$this->save_log('MOQ',$old_list['mqq'],$list['mqq']);
                }

                if(!$banner_link){$banner_link='javascript:void(0);';}
                if($list['banner_link']!=$banner_link){
                    if($banner_link){
                        $list['banner_link']=$banner_link;
                    }else{
                        $list['banner_link']='javascript:void(0);';
                    }
                    $log.=$this->save_log('广告图链接',$old_list['banner_link'],$list['banner_link']);
                }
                if(!$businessId){$businessId='';}
                if($list['businessId']!=$businessId){
                    if($businessId){
                        $list['businessId']=$businessId;
                        $log.='更新了商品图片';
                    }else{
                        $list['businessId']='';
                    }
                    $log.=$this->save_log('认证id',$old_list['businessId'],$list['businessId']);
                }
                if(!$wbi){$wbi='';}else{$wbi=htmlspecialchars_decode(html_entity_decode($wbi));}
                if($list['wbi']!=$wbi){
                    if($wbi){
                        $list['wbi']=$wbi;
                        $log.='更新了Why Buy It';
                    }else{
                        $list['wbi']='';
                        $log.='清除了Why Buy It';
                    }
                }
                if(!$paking){$paking='';}else{$paking=htmlspecialchars_decode(html_entity_decode($paking));}
                if($list['paking']!=$paking){
                    if($paking){
                        $list['paking']=$paking;
                        $log.='更新了paking';
                    }else{
                        $list['paking']="";
                        $log.='清除了paking';
                    }
                }
                if(!$prodInfo){$prodInfo='';}else{$prodInfo=htmlspecialchars_decode(html_entity_decode($prodInfo));}
                if($list['prodInfo']!=$prodInfo) {
                    if ($prodInfo) {
                        $list['prodInfo']=$prodInfo;
                        $log.='更新了商品描述';
                    } else {
                        $list['prodInfo'] = "";
                        $log.='清除了商品描述';
                    }
                }
                if($list['brandId']!=$brandId){
                    if($brandId){
                        $list['brandId']=$brandId;
                    }else{
                        $list['brandId']=0;
                    }
                    $log.=$this->save_log('风格id',$old_list['brandId'],$list['brandId']);
                }

                if($prodName){
                    $prodName=$this->filter_str($prodName);
                    if($list['prodName']!=$prodName){
                        $list['prodName']=$prodName;
                        $log.=$this->save_log('商品名称',$old_list['prodName'],$list['prodName']);
                    }
                }else{
                    $this->error("未填写商品名称");
                }
                if($prodNO){
                    if($list['prodNO']!=$prodNO){
                        $has2=$this->get_table('prods')->where("prodId!=%d and prodNO='%s'",$id,$prodNO)->select();
                        if($has2){
                            $this->error($prodNO."已存在");
                        }else{
                            $list['prodNO']=$prodNO;
                            $log.=$this->save_log('商品唯一编号',$old_list['prodNO'],$list['prodNO']);
                        }
                    }
                }else{
                    $this->error("未填写商品唯一编号");
                }
                if($price1){
                    if($list['price1']!=$price1){
                        $list['price1']=$price1;
                        $log.=$this->save_log('商品价格',$old_list['price1'],$list['price1']);
                    }
                }else{
                    $this->error("未填写商品价格");
                }
                if(array_diff_assoc($list,$old_list)){
                    $list['updata_time']=time();
                    $re=$this->get_table("prods")->save($list);
                    if($re){
                        if($img && $simimg){
                            unlink('./Public/Uploads/'.$old_list['img']);
                            unlink('./Public/Uploads/'.$old_list['simimg']);
                        }
                        if($banner){
                            unlink('./Public/Uploads/banner/'.$old_list['banner']);
                            unlink('./Public/Uploads/banner/m_'.$old_list['banner']);
                            unlink('./Public/Uploads/banner/l_'.$old_list['banner']);
                        }
                        $this->write_log('修改商品',$log);
                        $this->success('数据更新成功',$url);
                    }else{
                        $this->error("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }
            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("非法操作",U('Goods/index'));
        }
    }


    //添加商品详细信息列表页
    public function products(){
        $colorsArr=$this->get_table('colors')->where('status=1')->select();
        $gendarsArr = $this->get_table('gendars')->where('status=1')->select();
        $sizeArr = $this->get_table('sizes')->where('status=1')->select();
        $this->sizeArr=$sizeArr;
        $this->colorsArr=$colorsArr;
        $this->gendarsArr=$gendarsArr;
        $this->prodID=I('get.gid');
        $this->title='添加商品详细';
        $this->display();
    }

    //添加商品详细信息操作
    public function add_products(){
//        $productNO=I('post.productNO');
        $prodId=I('post.prodId');
        $colorId=I('post.colorId');
        $price=I('post.price');
        $sizeId=I('post.sizeId');
        //        $amount=I('post.amount');
        //        $gendarId=I('post.gendarId');
        if($prodId){
            $true=$this->get_table('products')->where('prodId=%d and isDefault=1 and status!=2',$prodId)->count();
            if($true<=0) {
                $data['isDefault']= 1;
            }
            $data['prodId']=$prodId;
//            if($productNO){
//                $data['productNO']=$this->check_field(null,$productNO,'products',"productNO");
//            }else{
//                $this->error("未填写商品唯一编号");
//            }
            if($colorId){
                $data['colorId']=$colorId;
            }else{
                $this->error("请选择颜色");
            }
            if($price){
                $data['price']=$price;
            }else{
                $this->error('请输入价格');
            }
            if($sizeId){
                $data['sizeId']=$sizeId;
            }else{
                $this->error("请选择尺寸");
            }
            //            if($amount){
            //                $data['amount']=$amount;
            //            }
            //            if($gendarId){
            //                $data['gendarId']=$gendarId;
            //            }
            $res=$this->get_table('products')->where("prodId=%d and status!=2 and sizeId=%d and colorId=%d",$data['prodId'],$data['sizeId'],$data['colorId'])->select();
            if(!$res){
                $data['addtime']=time();
                $add=$this->get_table('products')->add($data);
                if($add){
                    $this->write_log('添加商品详细','商品id'.$data['prodId'].',尺寸id'.$sizeId.',颜色id'.$colorId.',价格'.$price);
                    $this->success('添加成功',U('Goods/detail',array('gid'=>$data['prodId'])));
                }else{
                    $this->error('添加失败');
                }
            }else{
                $this->error("此颜色尺寸已存在");
            }
        }else{
            $this->error("非法操作");
        }
    }


    //修改商品详细页
    public function productsEdit(){
        $id=I('get.id');
        if($id){
            $colorsArr=$this->get_table('colors')->where('status=1')->select();
            $gendarsArr = $this->get_table('gendars')->where('status=1')->select();
            $sizeArr = $this->get_table('sizes')->where('status=1')->select();
            $data=$this->get_table('products')->where("productId=%d",$id)->find();
            $this->data=$data;
            $this->sizeArr=$sizeArr;
            $this->colorsArr=$colorsArr;
            $this->gendarsArr=$gendarsArr;
            $this->productID=$id;
            $this->title='副商品详细修改';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }


    //do修改商品详细
    public function Doedit_products(){
        $colorId=I('post.colorId');
        $price=I('post.price');
        $sizeId=I('post.sizeId');
//        $productNO=I('post.productNO');
        $productID=I('post.productID');
        //        $amount=I('post.amount');
        //        $gendarId=I('post.gendarId');
        if(!$colorId){
            $this->error("请选择颜色");
        }
        if(!$price){
            $this->error("请输入价格");
        }
//        if(!$productNO){
//            $this->error("未填写商品唯一编号");
//        }
        if($productID){
            $list=$this->get_table('products')->where("productId=%d",$productID)->find();
            if($list){
                $res=$this->get_table('products')->where("productId!=%d and prodId=%d and sizeId=%d and colorId=%d",$productID,$list['prodId'],$sizeId,$colorId)->select();
                if(!$res){
                    $old_list=$list;
                    $log = "编辑商品详细 id为：" .$productID;
                    //                    if(isset($gendarId) && $list['gendarId']!=$gendarId){
                    //                        $list['gendarId']=$gendarId;
                    //                    }
                    //                    if($amount && $list['amount']!=$amount){
                    //                        $list['amount']=$amount;
                    //                    }
                    if($list['colorId']!=$colorId){
                        $list['colorId']=$colorId;
                        $log.=$this->save_log('颜色',$old_list['colorId'],$list['colorId']);
                    }
                    if($list['price']!=$price){
                        $list['price']=$price;
                        $log.=$this->save_log('价格',$old_list['price'],$list['price']);
                    }
                    if($list['sizeId']!=$sizeId){
                        $list['sizeId']=$sizeId;
                        $log.=$this->save_log('尺寸',$old_list['sizeId'],$list['sizeId']);
                    }
//                    if($list['productNO']!=$productNO){
//                        $has=$this->get_table('products')->where("prodId=%d and productNO='%s'",$list['prodId'],$productNO)->select();
//                        if($has){
//                            $this->error($productNO."已存在");
//                        }else{
//                            $list['productNO']=$productNO;
//                        }
//                    }
                    if(array_diff_assoc($list,$old_list)){
                        $list['updata_time']=time();
                        $re=$this->get_table("products")->save($list);
                        if($re){
                            $this->write_log('修改商品详细',$log);
                            $this->success('数据更新成功',U('Goods/detail',array('gid'=>$list['prodId'])));
                        }else{
                            $this->error("保存失败");
                        }
                    }else{
                        $this->error("新旧数据一致，不需要保存");
                    }
                }else{
                    $this->error("此颜色尺寸已存在");
                }
            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("非法操作");
        }
    }


    //设置商品属性页面
    public function style(){
        $this->title='系列管理';
        $id=I('get.gid');
        if($id){
            $Path=$this->get_table('prods')->alias('p')->join('jd_protype AS r ON r.proTypeId=p.proTypeId')->where('p.prodId=%d',$id)->field('r.proPath')->find();
            $Path=explode(',',$Path['proPath']);
            $Path=$Path[1];
            $protype= $this->get_table('protype')->where('proTypeId=%d',$Path)->find();
            $styleinId=$protype['styleId'];
            $style=$this->get_table('products_style')->where("styleId in(".$styleinId.") and status=1")->order('top desc')->select();
            $html='';
            foreach($style as $one){
                $html.="<option value='".$one['styleId']."_".$one['child']."'>".$one['styleName']."</option>";
            }
            $this->styleinId=$styleinId;
            $this->proName=$protype['proName'];
            $this->html=$html;
            $this->path=$Path;
            $this->pid=$id;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //拼接上级ID
    public function next_id($one,$proTypeId){
        if($one['pid']>0){
            $next_pid_where='styleId='.$one['pid'];
            if($one['lv']==3){
                $next_pid_where.=" and proTypeId=".$proTypeId;
            }
            $next_pid=$this->get_table('products_style')->where($next_pid_where)->find();
            if($next_pid['pid']){
                $one['one_id']=$next_pid['pid'];
            }
        }
        return $one;
    }

    //option 拼接
    public function option_html($where,$one,$lv){
        $html='';
        $two_select=$this->get_table('products_style')->where($where)->select();
        foreach($two_select as $select2){
            if($lv==2){
                if($one['one_id']>0){
                    $selected=$this->selected_html($one['pid'],$select2['styleId']);
                }else{
                    $selected=$this->selected_html($one['styleId'],$select2['styleId']);

                }
            }else{
                $selected=$this->selected_html($one['styleId'],$select2['styleId']);
            }
            $html.="<option ".$selected." value='".$select2['styleId']."_".$select2['child']."'>".$select2['styleName']."</option>";
        }
        $html.="</select>";
        return $html;
    }

    //返回选择状态
    public function selected_html($one,$two){
        if($one==$two){
            return 'selected';
        }else{
            return '';
        }
    }

    //初始话设置属性
    public function initial(){
        $prodId=I('post.prodId');
        $proTypeId=I('post.proTypeId');
        $styleinId=I('post.styleinId');
        if($prodId>0 && $proTypeId>0 && $styleinId){
            $data=$this->get_table('prods_styles')->where('prodId=%d and status=1',$prodId)->find();
            if($data){
                $one_select=$this->get_table('products_style')->where("styleId in(".$styleinId.") and status=1")->order('top desc')->select();
                $style=$this->get_table('products_style')->where('styleId in('.$data['styleId'].') and status=1')->order('styleId desc')->select();
                $html='';
                $num=0;
                foreach($style as &$one){
                    $num++;
                    $one=$this->next_id($one,$proTypeId);
                    //一级select
                    $html.="<div class='mws-form-item'> 系列分类 <select class='select_width selectone' id='one_".$num."'>";
                    foreach($one_select as &$select1){
                        if($one['one_id']>0){
                            $selected=$this->selected_html($one['one_id'],$select1['styleId']);
                        }elseif($one['one_id']==0 && $one['pid']>0){
                            $selected=$this->selected_html($one['pid'],$select1['styleId']);
                        }else{
                            $selected=$this->selected_html($one['styleId'],$select1['styleId']);
                        }
                        $html.="<option ".$selected."  value='".$select1['styleId']."_".$select1['child']."'>".$select1['styleName']."</option>";
                    }
                    $html.="</select><lable id='lable_two_".$num."'>";
                    if($one['pid']>0){
                        $select_where_pid=$one['pid'];
                        if($one['one_id']>0){
                            $select_where_pid=$one['one_id'];
                        }
                        //二级select
                        $html.="&nbsp;&nbsp;系列 <select class='select_width selecttwo' id='two_".$num."' data-existed='true'>";
                        $where="pid=".$select_where_pid." and proTypeId=".$proTypeId." and status=1";
                        $html.=$this->option_html($where,$one,2);

                    }
                    $html.="</lable><lable id='lable_three_".$num."'>";
                    if($one['one_id']>0){
                        //三级select
                        $html.="&nbsp;&nbsp;系列选项 <select class='select_width selectthree' id='three_".$num."' data-existed='true'>";
                        $where="pid=".$one['pid']." and proTypeId=".$proTypeId." and status=1";
                        $html.=$this->option_html($where,$one,3);
                    }
                    //最后平均按钮
                    $html.="</lable> <input type='button' class='save btn' id='save_".$num."' data-oldId='".$one['styleId']."' value='修改'> </div><br>";
                }
                die($this->list_json_result(1,"加载成功",$html));
            }else{
                die($this->list_json_result(1,"暂未添加",'暂未添加'));
            }
        }else{
            die($this->list_json_result(-1,"异常",''));
        }
    }

    //改变一级系列分类
    public function selectone(){
        $styleId=I("post.styleId");
        $id=I('post.id');
        $proTypeId=I('post.proTypeId');
        $this->change_lv($styleId, $id, $proTypeId,2);
    }

    //改变二级系列
    public function selecttwo(){
        $styleId=I("post.styleId");
        $id=I('post.id');
        $proTypeId=I('post.proTypeId');
        $this->change_lv($styleId, $id, $proTypeId,3);
    }

    public function change_lv($styleId,$id,$proTypeId,$lv){
        if($styleId>0 && $proTypeId>0 && $id && $lv>0){
            $style = $this->get_table('products_style')->where("proTypeId=%d and pid=%d and lv=%d and status=1", $proTypeId, $styleId, $lv)->order('top desc')->select();
            switch ($lv) {
                case 2:$name = 'two';$selectname='系列';break;
                case 3:$name = 'three';$selectname='系列选项';break;
                default :die($this->list_json_result(-20, "级别未定义", ''));break;
            }
            $html = "&nbsp;&nbsp;".$selectname." <select class='select_width select" . $name . "' id='" . $name . "_" . $id . "' data-existed='true'>";
            if ($style) {
                $html .= "<option value=''>请选择</option>";
                foreach ($style as $two) {
                    $html .= "<option value='" . $two['styleId'] . "_" . $two['child'] . "'>" . $two['styleName'] . "</option>";
                }
            } else {
                $html .= "<option value=''>当前".$selectname."未添加系列选项</option>";
            }
            $html .= "</select>";
            die($this->list_json_result(1,"成功更改",$html));
        }else{
            die($this->list_json_result(-1,"数据异常",''));
        }
    }

    //添加商品系列
    public function addstyle(){
        $prodId=I('post.prodId');
        $styleId=I('post.styleId');
        if($prodId>0 && $styleId>0){
            $prods=$this->get_table('prods_styles')->where("prodId=".$prodId.' and status=1')->find();
            if($prods){
                if(strpos($prods['styleId'],$styleId)===false){
                    $prods['styleId'].=",".$styleId;
                    $sql="UPDATE `jd_prods_styles` SET `styleId`='".$prods['styleId']."' WHERE `id` = ".$prods['id'];
                    $res=M()->execute($sql);
                }else{
                    die($this->list_json_result(-10,"已存在,请勿重复添加",''));
                }
            }else{
                $data['prodId']=$prodId;
                $data['styleId']=$styleId;
                $res=$this->get_table('prods_styles')->add($data);
            }
            $prods=$this->get_table('prods_styles')->where("prodId=".$prodId.' and status=1')->find();
            if($res){
                $this->write_log('商品添加系列','商品id:'.$prodId.'添加系列id:'.$styleId.'   数据id:'.$prods['id']);
                die($this->list_json_result(1,"添加成功",''));
            }else{
                die($this->list_json_result(-999,"添加失败",''));
            }
        }else{
            die($this->list_json_result(-1,"数据异常",''));
        }
    }



    //修改商品系列
    public function savestyle(){
        $prodId=I('post.prodId');
        $styleId=I('post.styleId');
        $oldid=I('post.old_id');
        if($prodId>0 && $styleId>0 && $oldid){
            $prods=$this->get_table('prods_styles')->where("prodId=".$prodId.' and status=1')->find();
            $log = "编辑商品系列 id为：" .$prods['id'];
            if(!strpos($prods['styleId'],$styleId)){
                $prods['styleId']=str_replace($oldid,$styleId,$prods['styleId']);
                $sql="UPDATE `jd_prods_styles` SET `styleId`='".$prods['styleId']."' WHERE `id` = ".$prods['id'];
                $res= M()->execute($sql);
                $log.=$this->save_log('系列id',$oldid,$styleId);
            }else{
                die($this->list_json_result(-10,"已存在,请勿重复添加",''));
            }
            if($res){
                $this->write_log('修改商品系列',$log);
                die($this->list_json_result(1,"修改成功",''));
            }else{
                die($this->list_json_result(-999,"修改失败",''));
            }
        }else{
            die($this->list_json_result(-1,"数据异常",''));
        }
    }

    //删除商品系列
    public function deletestyle(){
        $this->check_1or3();
            $prodId=I('post.prodId');
            if($prodId>0){
                $prods=$this->get_table('prods_styles')->where("prodId=".$prodId.' and status=1')->find();
                if($prods){
                    $prods['status']=0;
                    $save=$this->get_table('prods_styles')->save($prods);
                    if($save){
                        $this->write_log('删除商品系列','删除系列id：'.$prods['id']);
                        die($this->list_json_result(1,"删除成功",''));
                    }else{
                        die($this->list_json_result(-999,"修改失败",''));
                    }
                }else{
                    die($this->list_json_result(-10,"此商品未添加系列",''));
                }
            }else{
                die($this->list_json_result(-1,"数据异常",''));
            }
    }

//    //开启功能分类
//    public function onstyle(){
//        $id=I('post.value')['id'];
//        if($id>0){
//            $res=$this->get_table('prods_styles')->where('id=%d',$id)->find();
//            if($res){
//                if($res['status']==0){
//                    $res['status']=1;
//                    $save=$this->get_table('prods_styles')->where('id=%d',$id)->save($res);
//                    if($save){
//                        die($this->list_json_result(1,"开启成功",''));
//                    }else{
//                        die($this->list_json_result(-2,"开启失败",''));
//                    }
//                }else{
//                    die($this->list_json_result(-11,"已是开启状态",''));
//                }
//            }else{
//                die($this->list_json_result(-111,"数据不存在",''));
//            }
//        }else{
//            die($this->list_json_result(-1,"数据异常",''));
//        }
//    }
//    //禁用功能分类
//    public function offstyle(){
//        $id=I('post.value')['id'];
//        if($id>0){
//            $res=$this->get_table('prods_styles')->where('id=%d',$id)->find();
//            if($res){
//                if($res['status']==1){
//                    $res['status']=0;
//                    $save=$this->get_table('prods_styles')->where('id=%d',$id)->save($res);
//                    if($save){
//                        die($this->list_json_result(1,"禁用成功",''));
//                    }else{
//                        die($this->list_json_result(-2,"禁用失败",''));
//                    }
//                }else{
//                    die($this->list_json_result(-12,"已是禁用状态",''));
//                }
//            }else{
//                die($this->list_json_result(-111,"数据不存在",''));
//            }
//        }else{
//            die($this->list_json_result(-1,"数据异常",''));
//        }
//    }
//    //首页改变type
//    public function indexchangetype(){
//        $typeName=I('post.value')['typeName'];
//        $proTypeId=I('post.value')['proTypeId'];
//        $styleselect=I('post.value')['styleselect'];
//        $where=1;
//        if(!empty($proTypeId)){
//            $Path=$this->get_table('protype')->where('proTypeId=%d',$proTypeId)->find();
//            $proTypeId=explode(',',$Path['proPath'])[1];
//            $where.=" and a.proTypeId='".$proTypeId."'";
//        }
//        if(!empty($typeName)){
//            $where.=" and a.typeName='".$typeName."'";
//        }
//        $data=$this->get_table('products_style')->alias("a")->join("jd_protype AS p ON a.proTypeId=p.proTypeId")->field("a.*,p.proName")->where($where)->select();
//        if($data){
//            $option="<option value=''>全部</option>";
//            foreach($data as &$one){
//                if($styleselect==$one['styleId']){
//                    $selected='selected';
//                }else{
//                    $selected='';
//                }
//                $option.="<option ".$selected." value='".$one['styleId']."'>".$one['proName']."====".$one['typeName']."====".$one['styleName']."</option>";
//            }
//        }else{
//            $option="<option value=''>未添加系列</option>";
//        }
//        die($this->list_json_result(1,"改变成功",$option));
//    }
//    //改变changeproType
//    public function changeproType(){
//        $proTypeId=I('post.value')['proTypeId'];
//        $where=1;
//        if(!empty($proTypeId)){
//            $Path=$this->get_table('protype')->where('proTypeId=%d',$proTypeId)->find();
//            $proTypeId=explode(',',$Path['proPath'])[1];
//            $where.=" and proTypeId='".$proTypeId."'";
//        }
//        $data=$this->get_table('products_style')->where($where)->group('typeName')->select();
//        if($data){
//            $option="<option value=''>全部</option>";
//            foreach($data as &$one){
//                $option.="<option value='".$one['typeName']."'>".$one['typeName']."</option>";
//            }
//        }else{
//            $option="<option value=''>未添加</option>";
//        }
//        die($this->list_json_result(1,"改变成功",$option));
//    }
    //json
    public function list_json_result($status, $msg, $result){
        return json_encode(array('status' => $status, 'msg' => $msg, 'result' => $result));
    }




    //删除商品操作
    public function del(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && $status==2){
            $data['prodId']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result = $this->get_table('prods')->save($data);
            if($result){
                $this->status_log($status,$id,'商品');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //开关商品详细操作
    public function status(){
        $this->check_1to3();
        $id=I('get.productId');
        $status=I('get.status');
        $this->do_status($id,$status);
    }

    //删除商品详细操作
    public function del_detail(){
        $this->check_1or3();
        $id=I('get.id');
        $status=I('get.status');
        $this->do_status($id,$status);
    }

    public function do_status($id,$status){
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['productId']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result = $this->get_table('products')->save($data);
            if($result){
                $this->status_log($status,$id,'商品详细');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }




    //设置为默认状态
    public function isDefault(){
        $data['prodId']=I('get.pId');
        $data['productId']=I('get.id');
        $data['isDefault']=I('get.isid');
        if(($data['isDefault']==0 or $data['isDefault']==1) &&  $data['productId']>0 && $data['prodId']>0){
            if($data['isDefault']==1){
                $save['isDefault']=0;
                $this->get_table('products')->where('prodId=%d and isDefault=1',$data['prodId'])->save($save);
            }
            $change=$this->get_table('products')->where('productId=%d',$data['productId'])->save($data);
            if($change){
                switch($data['isDefault']){
                    case 0 : $text ="取消";break;
                    case 1:  $text ="设置";break;
                    default: $text ="未知";break;
                }
                $this->write_log('默认状态',$text.' id为: '.$data['prodId'].' 商品详细的状态');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    protected function upload($url=null){ //上传图片，如果有传递$url参数的话就删除对应的图片
        $upload = new \Think\Upload(); //实例化tp的上传类
        $upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
        $upload->rootPath = './Public/Uploads/'; //相对于站点根目录jd
        $upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)
        $info = $upload->uploadOne($_FILES['img']); //开始上传
        if(!$info){
            $this->error($upload->getError());
        }else{
            $pic['file'] = $info['savepath'].$info['savename']; //获取文件名
            //ori是根据需要进行更改的
            $pic['simimg'] = $info['savepath'].'50_'.$info['savename']; //获取缩略图的文件名
            $pic['img'] = $upload->rootPath.$info['savepath'].$info['savename']; //获取完整的图片地址
            $image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
            $image->open($pic['img']);
            $image->thumb(50, 50)->save($upload->rootPath.$info['savepath'].'50_'.$info['savename']);
            return $pic; //返回相关信息数组
        }
    }


    public function upFile() {
        //实例化上传类
        $upload = new \Think\Upload();
        //设置上传大小
        $upload->maxSize = 3145728;
        //设置上传类型
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        // 设置上传目录
        $upload->rootPath= './Public/Uploads/banner/';
        $upload->savePath = '';
        $upload->autoSub = false;
        //设置保存的文件名
        $upload->saveName = 'time';
        //上传文件
        $info = $upload->uploadOne($_FILES['image']);
        if(!$info) {
            //上传失败
            $this->error($upload->getError());
        }else{
            //上传成功
            //图片处理
            $image = new \Think\Image();
            $image->open($upload->rootPath.$info['image']['savepath'].$info['savename']);
            $image->thumb(50, 50)->save($upload->rootPath.$info['savepath'].'m_'.$info['savename']);
            $image->thumb(50, 50)->save($upload->rootPath.$info['savepath'].'m_'.$info['savename']);
            //返回上传的文件名
            return $info['savename'];
        }
    }
    //递归获取分类信息
    protected function demo($data, $pid=0){
        $arr = array();
        foreach($data as $v){
            if($v['proPid'] == $pid){
                $v['child'] = $this->demo($data, $v['proTypeId']);
                $arr[] = $v;
            }
        }
        return $arr;
    }

    //根据传入的数组生成对应的option
    private function option($r,$notdis=null,$id=null){
        $selected = ''; // 判断分类是否选中，如果被选中，其值为select，没有选中的话其值为空
        $options = '';  //用来存储我们要生成的option信息
        if($notdis){
            $disabled='';
        }else{
            $disabled=' disabled';
        }
        //通过遍历获得我们需要的格式组成的option
        foreach($r as $v){ //第一层的遍历
            if($v['proTypeId'] == $id){
                $selected = 'selected';
            }
            $options .= '<option value="'.$v['proTypeId'].'" '.$selected.$disabled.'>'.$v['proTypeId'].':'.$v['proName'].'.</option>';
            $selected = ''; //及时的将$selected的值清空，保证后面的状态

            if($v['child']){
                foreach ($v['child'] as $v2) { //第二层分类
                    if($v2['proTypeId'] == $id){
                        $selected = "selected";
                    }
                    if($v2['child']){
                        //如果仍然有子级的话，就加上disabled
                        $options.= '<option value="'.$v2['proTypeId'].'" '.$selected.$disabled.' >&nbsp;&nbsp;&nbsp;&nbsp;'.$v2['proTypeId'].':'.$v2['proName'].'</option>';
                        $selected = '';

                    }else{ //如果没有子级的话
                        $options.= '<option value="'.$v2['proTypeId'].'" '.$selected.'>&nbsp;&nbsp;&nbsp;&nbsp;'.$v2['proTypeId'].':'.$v2['proName'].'</option>';
                        $selected = '';
                    }
                    if($v2['child']){ //第三层分类
                        foreach ($v2['child'] as $v3) {
                            if($v3['proTypeId'] == $id){
                                $selected = "selected";
                            }

                            $options.= '<option value="'.$v3['proTypeId'].'" '.$selected.'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$v3['proTypeId'].':'.$v3['proName'].'</option>';
                            $selected = '';
                        }
                    }
                }
            }
        }
        return $options;
    }


    private function order($data, $parentid=0) {
        static $arr = array();
        foreach($data as $volist) {
            if($volist['parentid'] == $parentid) { // 得到所有的一级分类
                $arr[] = $volist;
                // 继续调用当前方法  - 递归函数
                $this->order($data, $volist['id']);
            }
        }
        return $arr;
    }


    function demo2() {
        $kinds = M('lamp86_shop.kinds','tb_');
        // 将表中所有的数据都查到
        $data = $kinds -> select();
        // 获取所有的一级分类
        $r=$this->order2($data);
        foreach($r as $v) {
            echo $v['name'].'<br>';
            foreach($v['child'] as $v1) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$v1['name'].'<br>';
                foreach($v1['child'] as $v2) {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$v2['name'].'<br>';

                }
            }
        }
    }

    private function order2($data, $parentid=0) {
        $arr = array();
        foreach($data as $volist) {
            if($volist['parentid'] == $parentid) { // 得到所有的一级分类
                $volist['child'] = $this->order2($data, $volist['id']);
                $arr[] = $volist;
                // 继续调用当前方法  - 递归函数
            }
        }
        return $arr;
    }
    //type 拼接styleId
    public function type_in($res){
        $in='0';
        if($res){
            foreach($res as &$one){$in.=",".$one['styleId'];}
        }
        return $in;
    }
    //拼接分类ID
    public function intypepId($id){
        $pieces = explode(",", $id);
        $where['proPid']=array('in',$id);
        $where['proLive']=array('eq',1);
        $cdata=$this->get_table('protype')->where($where)->select();
        if (count($pieces) == count(array_unique($pieces))) {
            if($cdata){
                foreach($cdata as &$one){
                    $id.=",".$one['proTypeId'];
                }
                $id=$this->intypepId($id);
            }
        }
        return $id;
    }
    //end
}
