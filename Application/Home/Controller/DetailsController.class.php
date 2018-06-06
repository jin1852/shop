<?php
namespace Home\Controller;
use Think\Controller;
class DetailsController extends Controller {
    /*ajax处理发货地址选择*/
   public function demo(){
        header("Content-Type:text/html;charset=utf-8");
        $area = M('areasid');
        $data = $_POST;
        $r = $area -> where("diqu = '{$data['diqu']}'") -> select();
        $pId = $r[0]['areaid'];
        $data1 = $area -> where('pId = '.$pId) -> field('diqu') ->select();
        echo json_encode($data1);
   }
    public function details(){
    	header("Content-Type:text/html;charset=utf-8");
        //S(array(
        //    'type'=>'memcache',
        //    'host'=>'127.0.0.1',
        //    'port'=>'11211',
        //    'prefix'=>'',
        //    'expire'=>300
        //    )
        //);
        $memResult=S('indexCcData');
        if(!$memResult){
            $result=\Home\Common\getCcdhData();
            S('indexCcData',$result);
        }else{
            $result=$memResult;
        }
        $this->assign('ccdhData',$result);
        $navList=\Home\Common\getNavList();
        $this->assign('navList',$navList);
        /*发货地址选择*/
        $area = M('areasid');
        $areas = $area -> where('pId = 1') -> select();
        $this -> assign('areas',$areas);
        /*展示传过来的商品详细信息*/
        $id = $_GET['id']?$_GET['id']:1;
        $products = M('products');
        $arr = $products -> where("jd_products.prodId = ".$id)
                         -> join("left join jd_prods ON jd_products.prodId = jd_prods.prodId")
                         -> join("left join jd_comments ON jd_products.prodId = jd_comments.cId")
                         -> join("left join jd_colors ON jd_products.colorId = jd_colors.colorId")
                         -> field("prodInfo,prodName,img,simimg,price1,price2,productId,jd_comments.*,count(cId),jd_products.prodId,colorName")
                     
                      -> select();
        $this -> assign('arr',$arr);
		//图片展示区
        $prods = M('prods');
		$prodimg = $prods -> where('jd_prods.prodId = '.$id)
					 -> join('left join jd_prodimg ON jd_prods.prodId = jd_prodimg.prodId')
					 -> field('image')
					 -> select();
		$this -> assign('prods',$prodimg);
        /*游戏排行榜同价位商品*/
        $products = M('products');
        $arr1 = $products -> where('jd_products.prodId = '.$id)
                          -> join('left join jd_prods ON jd_products.price > jd_prods.price2-1000 and jd_products.price < jd_prods.price2+1000')
                          -> limit(6)
                          -> select();
        $this -> assign('arr1',$arr1);
        $arr2 = array('①','②','③','④','⑤','⑥');
        $this -> assign('arr2',$arr2);
        /*同品牌商品推荐*/
        $arr3 = $products -> where('prodId = '.$id)
                          -> field('proTypeId,brandId')
                          -> select();
        $proTypeId = 0;
        $brandId = 0;
        foreach($arr3 as $v){
            $proTypeId = $v['proTypeId'];
            $brandId = $v['brandId'];
        }
        $arr4 = $products -> where('jd_products.proTypeId = '.$proTypeId.' and jd_products.brandId = '.$brandId)
                          -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                          -> field('prodName,price2,simimg')
                          -> limit(6)
                          -> select();
        $this -> assign('arr4',$arr4);
        /*同类别的商品推荐*/
        $arr5 = $products -> where('jd_products.proTypeId = '.$proTypeId)
                          -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                          -> limit(6)
                          -> select();
        $this -> assign('arr5',$arr5);
        /*相关分类商品类别展示*/
        //$protype = M('protype');
        $arr6 = $products -> where('jd_products.prodId = '.$id)
                          -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                          -> field('proName,proPid')
                          -> select();
        $proPid = 0;
        foreach($arr6 as $vo){
            $proPid = $vo['proPid'];
        }
        $protype = M('protype');
        $arr7 = $protype -> where('proTypeId =  '.$proPid) -> field('proPid,proName') -> select();
        $proPid1 = 0;
        foreach ($arr7 as $value) {
            $proPid1 = $value['proPid'];
        }
        $arr8 = $protype -> where('proPid =  '.$proPid1) -> field('proName,proPath') -> select();
        $this -> assign('arr8',$arr8);
        /*同类其他品牌*/
        $arr9 = $protype -> where('proPid = '.$proPid) -> field('proName,proPath') -> select();
        $this -> assign('arr9',$arr9);
        /*商品介绍*/
        $arr10 = $products -> where('jd_products.prodId = '.$id)
                           -> join('left join jd_brands ON jd_products.brandId = jd_brands.brandId')
                           -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                           -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                           -> join('left join jd_sizes ON jd_products.sizeId = jd_sizes.sizeId')
                           -> join('left join jd_colors ON jd_products.colorId = jd_colors.colorId')
                           -> field('colorName,sizeName,prodName,addtime,brandName,productId,proPath')
                           -> select();
        $this -> assign('arr10',$arr10);
        /*购买了此商品的用户还购买了*/
        $pro = $products -> where('jd_products.prodId = '.$id)
        				 -> join('left join jd_details ON jd_products.productId = jd_details.productId')
        				 -> join('left join jd_orders ON jd_details.orderId = jd_orders.orderId')
        				 -> field('userId')
        				 -> select();
       // echo $products -> getLastSql();
        //dump($pro);
        $order = M('orders');
        $sgoods = array();
        foreach($pro as $user){
        	//dump($user['userId']);
        	if(empty($user['userId'])){continue;}
        	$goods = $order -> where('jd_orders.userId = '.$user['userId'])
        					-> join('left join jd_details ON jd_orders.orderId = jd_details.orderId')
        					-> join('left join jd_products ON jd_details.productId = jd_products.productId')
        					-> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                  -> limit(6)
        					-> select();

        	$sgoods = $goods;
        }
        $this -> assign('sgoods',$sgoods);
        /*浏览记录*/
        $TempNum = 6;//cookie里面存储多少个浏览记录
        if(isset($_COOKIE['RecentlyGoods'])){//判断是否设置了COOKIE
          $RecentlyGoods=$_COOKIE['RecentlyGoods'];
          $RecentlyGoodsArray=explode(",", $RecentlyGoods);
          $RecentlyGoodsNum=count($RecentlyGoodsArray); //RecentlyGoodsNum 当前存储的变量个数
          if (in_array($id, $RecentlyGoodsArray)){
          //echo "已经存在,则不写入COOKIES <hr />";
          }else{
            if($RecentlyGoodsNum <$TempNum){ //如果COOKIES中的元素小于指定的大小，则直接进行输入COOKIES
              if($RecentlyGoods==""){
                setcookie("RecentlyGoods", $id, time()+3600*5, '/');
              }else{
                $RecentlyGoodsNew=$RecentlyGoods.",".$id;
                setcookie("RecentlyGoods", $RecentlyGoodsNew,time()+3600*5, '/');
              }
            }else{ //如果大于了指定的大小后，将第一个给删去，在尾部再加入最新的记录。
              $pos=strpos($RecentlyGoods,",")+1; //第一个参数的起始位置
              $FirstString=substr($RecentlyGoods,0,$pos); //取出第一个参数
              $RecentlyGoods=str_replace($FirstString,"",$RecentlyGoods); //将第一个参数删除
              $RecentlyGoodsNew=$RecentlyGoods.",".$id; //在尾部加入最新的记录g
              setcookie("RecentlyGoods", $RecentlyGoodsNew,time()+3600*5,'/');
            }
          }
        }else{
          setcookie("RecentlyGoods",$id,time()+3600*5, '/');
        }
        $prods = M('prods');
        $cookieId = explode(",", $_COOKIE['RecentlyGoods']);
        $cookieGoods = array();
        foreach($cookieId as $vId){
          $cookie = $prods -> where('prodId = '.$vId) -> select();
          $cookieGoods[] = $cookie;
        }
        // dump($cookieGoods);exit;
        $this -> assign('cookieGoods',$cookieGoods);
        /*好评比例*/
        $arr11 = $products -> where('jd_products.prodId = '.$id.' and jd_comments.is_delete = 0')
                           -> join('left join jd_comments ON jd_products.prodId = jd_comments.cId')
                           -> field('jd_comments.*')
                           -> select();
        $this -> assign('arr11',$arr11);
        /*商品评论详情*/
        $comments = M('comments');
        $count = $comments -> where('cId = '.$id." and is_delete = 0") -> count();
        $pagesize = 10;
        $page = new \Think\Page($count,$pagesize);
        $page -> setConfig('theme','%FIRST% %UP_PAGE% %HEADER% 当前第 %NOW_PAGE% 页 %LINK_PAGE% %DOWN_PAGE% %END%');
        $page -> setConfig('header','<span class="rows">共 %TOTAL_ROW% 条评论</span>');
        $page -> setConfig('prev','上一页');
        $page -> setConfig('next','下一页');
        $show = $page -> show();
        $arr13 = $comments -> where('jd_comments.cId = '.$id.' and jd_comments.is_delete = 0')
                    -> join('left join jd_users ON jd_comments.uId = jd_users.userId')
                    -> join('left join jd_userhydj ON jd_users.userHYdjId = jd_userhydj.userHYdjId')
                    -> join('left join jd_usergwxwdj ON jd_users.userGWdjId = jd_usergwxwdj.userGWdjId')
                    -> join('left join jd_orders ON jd_comments.uId = jd_orders.userId')
                    -> join('left join jd_products ON jd_comments.cId = jd_products.prodId')
                    -> join('left join jd_colors ON jd_products.colorId = jd_colors.colorId')
                    -> join('left join jd_sizes ON jd_products.sizeId = jd_sizes.sizeId')
                    -> group('jd_comments.commentsId')
                    -> field('userimg,userHYdjimg,nickName,jd_users.userHYdjId,jd_users.userGWdjId,userGwdjimg,commentsTime,mjyx,content,colorName,sizeName,orderTime,uId,commentsdj,Gcode')
                    -> order('commentsTime desc')
                    ->limit($page->firstRow.','.$page->listRows)
                   -> select();
        /*数据分页*/
        $this -> assign('commentsCount',$count);
        $this -> assign('show',$show);
        $this -> assign('arr13',$arr13);
        /*商品评论回复*/
        /*$callback = M('callback');
        $arr14 = $callback -> where('jd_callback.prodId = '.$id." and jd_callback.status = 0")
        				   -> join('left join jd_comments ON jd_callback.objectId = jd_comments.uId')
                   -> group('jd_callback.callbackId')
        				   -> select();*/
        $arr14 = $comments -> where('jd_comments.cId = '.$id.' and jd_callback.status = 0')
                           -> join('left join jd_callback ON jd_comments.cId = jd_callback.prodId')
                           -> group('callbackId')
                           -> select();
       $this -> assign('arr14',$arr14);
       // 用户回复数
       /*$num1 = 0;
       $num2 = 0;
       for($i=0;$i<count($arr14);$i++){
        if($arr14[$i]['uId'] == $arr14[$i]['objectId']){
          $num1 ++;
        }else{
          $num2 ++;
        }

       }
       echo $num1;
       echo $num2;
       dump($arr14);exit;*/
       $callback = M('callback');
       $cbCount = $callback -> where('jd_callback.prodId = '.$id." and jd_callback.status = 0")
                           //-> join('left join jd_comments ON jd_callback.objectId = jd_comments.uId')
                           -> group('jd_callback.objectId')
                           -> count('objectId');
       $this -> assign('cbCount',$cbCount);
       /*推荐搭配*/
       $arr15 = $products -> where('jd_products.prodId = '.$id)
                          -> join('left join jd_protype on jd_products.proTypeId = jd_protype.proTypeId')
                          -> field('jd_protype.proTypeId,proPid')
                          -> select();
      $pId1 = 0;
      foreach($arr15 as $v){
          $pId1 = $v['proPid'];
      }
      $arr16 = $protype -> where('proTypeId = '.$pId1) -> field('proPid,proTypeId') -> select();
      $pId2 = 0;
      foreach($arr16 as $v){
        $pId2 = $v['proPid'];
      }
      $arr17 = $protype -> where('proPid = '.$pId2.' and proTypeId != '.$pId1)
                        -> field('proTypeId,proPid')
                        -> select();
      
        $arr19 = array();
        $arr21 = array();
        $pId3 = array();
        $arr23 = array();
      foreach($arr17 as $v){
      
        $arr18 = $protype -> where('proPid = '.$v['proTypeId']) -> limit(2) -> select();
      
        $arr19[] = $arr18;
          foreach($arr18 as $vo){
            $pId3[] = $vo['proTypeId'];
            
           $arr20 = $products -> where('jd_products.proTypeId = '.$vo['proTypeId'])
                    -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                    -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                    -> limit(2)
                    -> select();
            $arr21[] = $arr20;
          }
      }
       /*按销售量分配人气组合*/
      $arr22 = $products -> where('jd_products.prodId = '.$id)
                         -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                         -> select();
      $arrr = array();
      foreach($arr22 as $vo2){
        $arr23 = $protype -> where('jd_protype.proPid = '.$vo2['proPid'])
                           -> join('left join jd_products ON jd_protype.proTypeId = jd_products.proTypeId')
                           -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                           -> order('saleNum desc')
                           -> limit(1)
                          -> select();
        $arrr[] = $arr23;
      }
  /*******************************************************/
      $prodIdCookieList=cookie('prodIdCookie');
      $prodsCookie=M('prods');
      $where['prodId']=array('in',$prodIdCookieList);
      $prodIdCookieData=$prodsCookie->where($where)
                                    ->select();
      //var_dump($prodIdCookieData);
      $prodIdCookieNum=count($prodIdCookieList)+1;
      $this->assign('prodIdCookieNum',$prodIdCookieNum);
      $this->assign('prodIdCookieData',$prodIdCookieData);
    /*******************************************************/
      // dump($arr19);
      // exit;
        $this -> assign('arr19',$arr19);
        $this -> assign('arr21',$arr21);
        $this -> assign('arr23',$arrr);
      
       $this -> display();
    }
   
    /*回复处理*/
    public function do_callback(){
        header("Content-type:text/html;charset=utf-8");
        $data = $_POST;
        $data['userName'] = $_SESSION['user']['uname'];
        $users = M('users');
        $arr = $users -> where("uname = '{$data['objectName']}'") ->field('userId') -> select();
        foreach($arr as $v){
            $objectId = $v['userId'];
        }
        $data['objectId'] = $objectId;
        $data['cbTime'] = time();
        $callback = M('callback');
        if(!empty($data['cbContent'])){
          if($callback -> add($data)){
            $this -> success('回复成功！');
          }else{
            $this -> error('回复失败！');
          }
        }else{
          $this -> error("回复内容不能为空！");
        }
        
    }
    /*查看全部评论*/
    public function allComments(){
    header("Content-type:text/html;charset=utf-8");
    /*获取橱窗导航栏的数据*/
        /*memCache设定与存取*/
//        S(array(
//            'type'=>'memcache',
//            'host'=>'192.168.150.45',
//            'port'=>'11211',
//            'prefix'=>'',
//            'expire'=>300
//            )
//        );
        
        $memResult=S('indexCcData');
        if(!$memResult){
            $result=\Home\Common\getCcdhData();
            S('indexCcData',$result);
        }else{
            $result=$memResult;
        }
        $this->assign('ccdhData',$result);
        /**横条导航数据**/
        $navList=\Home\Common\getNavList();
        $this->assign('navList',$navList);

      $id = $_GET['id']?$_GET['id']:1;
     
        /*好评比例*/
      $products = M('products');
      $arr11 = $products -> where('jd_products.prodId = '.$id.' and jd_comments.is_delete = 0')
                           -> join('left join jd_comments ON jd_products.prodId = jd_comments.cId')
                           -> field('jd_comments.*')
                           -> select();
        $this -> assign('arr11',$arr11);
        /*商品评论详情*/
        $comments = M('comments');
        $count = $comments -> where('cId = '.$id) -> count();
        $pagesize = 10;
        $page = new \Think\Page($count,$pagesize);
        $page -> setConfig('theme','%FIRST% %UP_PAGE% %HEADER% 当前第 %NOW_PAGE% 页 %LINK_PAGE% %DOWN_PAGE% %END%');
        $page -> setConfig('header','<span class="rows">共 %TOTAL_ROW% 条评论</span>');
        $page -> setConfig('prev','上一页');
        $page -> setConfig('next','下一页');
        $show = $page -> show();
        $arr13 = $comments -> where('jd_comments.cId = '.$id.' and jd_comments.is_delete = 0')
                    -> join('left join jd_users ON jd_comments.uId = jd_users.userId')
                    -> join('left join jd_userhydj ON jd_users.userHYdjId = jd_userhydj.userHYdjId')
                    -> join('left join jd_usergwxwdj ON jd_users.userGWdjId = jd_usergwxwdj.userGWdjId')
                    -> join('left join jd_orders ON jd_comments.uId = jd_orders.userId')
                    -> join('left join jd_products ON jd_comments.cId = jd_products.prodId')
                    -> join('left join jd_colors ON jd_products.colorId = jd_colors.colorId')
                    -> join('left join jd_sizes ON jd_products.sizeId = jd_sizes.sizeId')
                    -> group('jd_comments.commentsId')
                    -> field('userimg,userHYdjimg,nickName,jd_users.userHYdjId,jd_users.userGWdjId,userGwdjimg,commentsTime,mjyx,content,colorName,sizeName,orderTime,uId,commentsdj,Gcode')
                    -> order('commentsTime desc')
                    ->limit($page->firstRow.','.$page->listRows)
                   -> select();
        /*数据分页*/
        $this -> assign('arr13',$arr13);
        $this -> assign('show',$show);
        /*商品评论回复*/
        $callback = M('callback');
        $arr14 = $callback -> where('jd_callback.prodId = '.$id.' and jd_callback.status = 0')
                   -> join('left join jd_comments ON jd_callback.objectId = jd_comments.uId')
                    -> group('jd_callback.callbackId')
                   -> select();
       $this -> assign('arr14',$arr14);
       $cbCount = $callback -> where('jd_callback.prodId = '.$id.' and jd_callback.status = 0')
                           //-> join('left join jd_comments ON jd_callback.objectId = jd_comments.uId')
                           -> group('jd_callback.objectId')
                           -> count();
       $this -> assign('cbCount',$cbCount);
       /*商品信息展示处理*/
    $db = M('prods');
    $arr = $db -> table('jd_prods as P,jd_comments as C')
           -> where('P.prodId=C.cId and P.prodId = '.$id)
           -> field('img,prodInfo,price2,Gcode,count(cId),prodId')
           -> select();
    $this -> assign('arr',$arr);
    $this -> display();
    }
    /*推荐列*/
    public function fenlei(){
      $data = $_POST;
       $products = M('products');
     
      $arr1 = $products -> where('jd_products.proTypeId = '.$data['typeId'])
                       -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                       -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                       -> field('price2,prodName,img,jd_products.prodId,jd_protype.proName')
                       -> limit(6)
                       -> select();
      echo json_encode($arr1);
    }
    public function accessories(){
      $data = $_POST;
      $products = M('products');
       $arr15 = $products -> where('jd_products.prodId = '.$data['prod'])
                          -> join('left join jd_protype on jd_products.proTypeId = jd_protype.proTypeId')
                          -> field('jd_protype.proTypeId,proPid')
                          -> select();
      $pId1 = 0;
      foreach($arr15 as $v){
          $pId1 = $v['proPid'];
      }
      $protype = M('protype');
      $arr16 = $protype -> where('proTypeId = '.$pId1) -> field('proPid,proTypeId') -> select();
      $pId2 = 0;
      foreach($arr16 as $v){
        $pId2 = $v['proPid'];
      }
        $arr17 = $protype -> where('proPid = '.$pId2.' and proTypeId != '.$pId1)
                        -> field('proTypeId,proPid')
                        -> select();
      $arr21 = array();
      foreach($arr17 as $v){
       $arr18 = $protype -> where('proPid = '.$v['proTypeId']) -> limit(1) -> select();
          foreach($arr18 as $vo){
             $arr20 = $products -> where('jd_products.proTypeId = '.$vo['proTypeId'])
                    -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                    -> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
                    -> limit(6)
                    -> select();
            if(!empty($arr20)){
              $arr21[] = $arr20;
            }
          }
      }
        echo json_encode($arr21);
    }
}
