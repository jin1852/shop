<?php
namespace Home\Controller;
use Think\Controller;

class UcenterController extends Controller {
	function index(){
		if(!$_SESSION['user']['userId']){
			$this->redirect('Index/index');
		}
		$user = M('users');
		$order = M('orders');
		$prods = M('prods');
		$address = M('address');
		$message = M('messages');

		$uid = $_SESSION['user']['userId'];
		$history = cookie('RecentlyGoods');
		$payments = array('货到付款','在线支付','分期付款');

		//获取用户的相关信息，判断条件使用用户的id，这里暂时设为1，后期可以从cookie或者session中获得
		$userInfo = $user->where('userId='.$uid)->field('userId,nickName,uname,userimg,userHYdjId')->find(); 

		$rank = array('普通会员','铁牌会员','铜牌会员','银牌会员','金牌会员','SVIP'); //定义会员等级对应的名称斌分配
		$states = array('等待发货','已发货','已收货','已经取消');
		$actions = array('取消订单','确认收货','评价','');

		//会员图片旁边显示的问候语，会根据时间的不同而不同
		$greeting = array( 
			'据说早起的人有艳遇~~我反正是信了',
			'一天之计在于晨，我要好好把握','一定要对自己好一点，所以中午要吃顿好的','哈尼，喝杯下午茶，精神精神~~~',
			'忙碌了一天，赶紧犒劳下自己吧~~',
			'夜深了，注意身体哟~~'
			);

		$hour = localtime(time(),true)['tm_hour'];
		if($hour < 8){
			$hint = 0;
		}else if($hour < 10){
			$hint = 1;
		}else if($hour < 12){
			$hint = 2;
		}else if($hour < 18){
			$hint = 3;
		}else if($hour < 20){
			$hint = 4;
		}else{
			$hint = 5;
		}

		//判断用户是否上传了图片，如果没有上传的话就使用默认的图片
		$userInfo['userimg'] = $userInfo['userimg']?$userInfo['userimg']:'/Images/u_pic.jpg'; 

		//分页相关数据
		$count = $order->where('userId='.$uid)
			->join('jd_details on jd_orders.orderId = jd_details.orderId')
			->join('jd_prods on jd_details.productId = jd_prods.prodId')
			->count();

		$page = new \Think\Page($count, 8);    //分页
		$show = $page->show();
		$page->setConfig('prev','上一页');
		$page->setConfig('next','下一页');

		//获得用户的订单信息
		$orderData = $order->where('userId='.$uid)
			->join('jd_details on jd_orders.orderId = jd_details.orderId')
			->join('jd_prods on jd_details.productId = jd_prods.prodId')
			// ->join('jd_address on jd_address.userId = jd_orders.userId')
			->field('jd_orders.*, jd_prods.*,jd_details.*')
			->order('orderTime desc')
			->select();
		
		$payment = $address->where("userId=".$uid." AND isDefault=1")->field('payment')->find();

		$historymap['prodId'] = array('in',$history);
		$historyGoods = $prods->field('prodId, img, price2, prodName')->where($historymap)->limit(10)->select();

		$caremap['userId'] = $uid;
		$careGids= $user->field('userCare')->where($caremap)->find();
		$goodsMap['prodId'] = array('in', $careGids['userCare']);		
		$careGoods = $prods->field('prodId, prodName, price2, img')->where($goodsMap)->limit(10)->select();

		//获取用户的站内新闻
		$messData['userId'] = $uid;
		$messData['statue'] = 1;
		$mess = $message->where($messData)->limit(5)->order('sendTime DESC')->field('title, messageId')->select();

		$result=\Home\Common\getCcdhData();
	    $this->assign('ccdhData',$result);
	    $navList = \Home\Common\getNavList();
	    $this->assign('navList',$navList);

		$this->assign('rank',$rank);
		$this->assign('userInfo',$userInfo);
		$this->assign('uid',$uid);
		$this->assign('orderData',$orderData);
		$this->assign('greeting',$greeting);
		$this->assign('hint',$hint);
		$this->assign('page',$show);
		$this->assign('payments', $payments);
		$this->assign('states',$states);
		$this->assign('actions',$actions);
		$this->assign('careGoods',$careGoods);
		$this->assign('historyGoods',$historyGoods);
		$this->assign('payment',$payment);
		$this->assign('mess',$mess);
		$this->display();
	}

	function confirm(){
		$orderModel = M('orders');
		$where['orderId'] = I('get.orderId');
		$data['orderState'] = 2;
		$orderRes = $orderModel->where($where)->save($data);
		if($orderRes){
			$this->success("成功确认");
		}else{
			$this->error("确认收货失败");
		}
	}


	function cancel(){
		$orderModel = M('orders');
		$where['orderId'] = I('get.orderId');
		$data['orderState'] = 3;
		$orderRes = $orderModel->where($where)->save($data);
		if($orderRes){
			$this->success("成功取消订单");
		}else{
			$this->error("取消订单失败");
		}
	}

	//获取用户的新闻
	function getNews(){
		$mess = M('messages');
		$mid = I('mid');
		$map['statue'] = 2;
		$data['messageId'] = $mid;
		$mess->where($data)->save($map);
		$news = $mess->where($data)->field('title, message')->find();
		echo json_encode($news);
	}


	function getUserInfo(){
		$uid = I('uid');
		$type = I('type');
		$area = M('areasid');
		
		$userInfo = M('users');
		$userData = $userInfo
					->where(array('userId'=>$uid))
					->field('userId,nickName,usertel,userdizhi,userday')
					->find();

		$province = '';
		$city = '';
		$county = '';
		$year = '';
		$month = '';
		$day = '';

		//如果地址栏不存在值的话，那么获取省份的值并分配过去
		if(empty($userData['userdizhi'])){
			$provinceData = $area->field('areaid, diqu')->where('pId=1')->select();
			$province .= '<option value="0" disabled="" selected="selected">请选择：</option>';
			foreach ($provinceData as $key => $v) {
				$province .= '<option value="'.$v['areaid'].'">'.$v['diqu'].'</option>';
			}

		}else{
			//如果有值的话，分别获得省、市、县三级的id编号
			list($proid, $cityid, $countyid) = explode('/', $userData['userdizhi']);

			//获得所有的省份数据
			$provinceData = $area->field('areaid, diqu')->where('pId=1')->select();

			//获得所有的市相关数据
			$cityData = $area->field('areaid, diqu')->where('pId='.$proid)->select();

			//获得所有的县城相关数据
			$countyData = $area->field('areaid, diqu')->where('pId='.$cityid)->select();

			//将省份数据转化成对应的option数据
			foreach ($provinceData as $key => $v) {
				$prochk = ''; //用于统计当前option是否被选中
				if($proid == $v['areaid']){
					$prochk = "selected";
				}
				$province .= '<option value="'.$v['areaid'].'" '.$prochk.'>'.$v['diqu'].'</option>';
			}

			foreach ($cityData as $key => $v) {
				$citychk = ''; //用于记录当前的option是否被选中
				if($cityid == $v['areaid']){
					$citychk = 'selected';
				}
				$city .= '<option value="'.$v['areaid'].'" '.$citychk.'>'.$v['diqu'].'</option>';
			}

			foreach ($countyData as $key => $v) {
				$countychk = ''; //用于记录当前的option是否被选中
				if($countyid == $v['areaid']){
					$countychk = "selected";
				}
				$county .= '<option value="'.$v['areaid'].'" '.$countychk.'>'.$v['diqu'].'</option>';
			}
			

		}

		//分配具体的月日年过去
		if(!empty($userData['userday'])){
			list($y, $m, $d) = explode('/', $userData['userday']);
		}

		for($i=2014;$i>=1930;$i--){
			if($y == $i){
				$ychk = "selected";
			}
			$year .= '<option value="'.$i.'" '.$ychk.'>'.$i.'</option>';
			$ychk = '';

		}

		//获得月份相关数据
		for($i=1;$i<=12;$i++){
			if($m == $i){
				$mchk = "selected";
			}
			$month .= '<option value="'.$i.'" '.$mchk.'>'.$i.'</option>';
			$mchk = '';

		}

		//获得日期相关数据
		for($i=1;$i<=30;$i++){
			if($d == $i){
				$dchk = "selected";
			}
			$day .= '<option value="'.$i.'" '.$dchk.'>'.$i.'</option>';
			$dchk = '';
		}

		$this->assign('province',$province);
		$this->assign('city',$city);
		$this->assign('county',$county);
		$this->assign('year',$year);
		$this->assign('month',$month);
		$this->assign('day',$day);
		$this->assign('userData',$userData);
		//根据不同的type值指定不同的模板/返回不同的值
		switch ($type) {
			case 1:
				$this->display('ajax_uinfo');
				break;
			case 2:
				$this->display('ajax_img');
			default:
				# code...
				break;
		}
	}

	//处理上传图片
	function multiupload(){
		  $user = M('users');
		  $id = I('post.uid');

		  $upload = new \Think\Upload(); //实例化tp的上传类
		  $upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
		  $upload->rootPath = './Public/Uploads/'; //相对于站点根目录jd
		  $upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)
		  //定义空数组，用于存储上传的图片地址
		  $datalist = array();

		  $info = $upload->upload(); //开始上传
		  if(!$info){
		    $this->error($upload->getError());
		  }else{
		    foreach($info as $v) {
		    	//获取文件的地址
		      $pic['img'] = $upload->rootPath.$v['savepath'].$v['savename']; 

		      //商品id获取完整的图片地址
		      $image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
		      $image->open($pic['img']);
		      
		      $image->thumb(100, 100)->save($upload->rootPath.$v['savepath'].'user_'.$v['savename']); //根据网站需要生成100*100的缩略图
		      $datalist['userimg'] = $imgpath = $v['savepath'].'user_'.$v['savename'];
		      unlink($pic['img']);

		      if($user->create($datalist)){
		          $res = $user->where('userId='.$id)->save();
		        if($res){
		          echo $imgpath;
		        }else{
		          echo "0";
		        }
		      }
		  }
		}
	}


	//处理用户密码的更改
	function addAddress(){
		$users  = M('users');
		$addressData = I('post.');
		$oriPass = I('oripass');
		$newPass = I('upwd');
		$newedPass = I('upwded');
		$userId = I('userId');

		$defaultPass = $users->where('userId='.$userId)->getField('upwd');
		if(md5($oriPass) == $defaultPass){
			if($newPass == $newedPass){
				$data['upwd'] = md5($newPass);
				$res = $users->where('userId='.$userId)->save($data);
				if($res){
					echo "1";
				}else{
					echo "2";
				}
			}
		}else{
			echo "0";
		}

	}



	//处理省市县三级联动，用于获得下一级的相关数据
	function getNext(){
		$area = M('areasid');
		$pid = I('get.pid');
		$map = array('pId'=>$pid);

		$next['city'] = '';
		$next['county'] = '';
		
		$nextCity = $area->field('areaid, diqu')->where($map)->select();
		$deepid = $nextCity[0]['areaid'];

		foreach ($nextCity as $key => $v) {
				$next['city'] .= '<option value="'.$v['areaid'].'" '.$citychk.'>'.$v['diqu'].'</option>';
			}

		$nextCounty = $area->field('areaid, diqu')->where('pId='.$deepid)->select();

		foreach ($nextCounty as $key => $v) {
				$next['county'] .= '<option value="'.$v['areaid'].'" '.$citychk.'>'.$v['diqu'].'</option>';
			}
		echo json_encode($next);
	}


	//添加用户的相关信息到数据库
	function addUserInfo(){
		$user = M('users');
		$data = I('POST.');
		if($user->create($data)){
			$res = $user->save();
			if($res){
				echo "添加成功";
			}else{
				echo "添加失败";
			}
		}
	}


	function orderlist(){
		$uid = I('uid');
		$order = M('orders');
		$orderlist = $order->where('userId='.$uid)
			->join('jd_details on jd_orders.orderId=jd_details.orderId')
			->join('jd_prods on jd_details.productId = jd_prods.prodId')
			->order('orderTime')
			->select();
		$orderstates = array('等待发货','已经发货','已经确认收货');

		$this->assign("orderstates",$orderstates);
		$this->assign('orderlist',$orderlist);
		$this->display();
	}

	function orderDetail(){
		if(!$_SESSION['user']['userId']){
			$this->redirect('Index/index', array(), 2, '非法访问~~页面跳转中...');
		}

		$orderId = I('get.id');
		$pid = I('get.pid');
		$order = M('orders');
		$orderlist = $order
			->where('jd_orders.orderId='.$orderId)
			->join('jd_details on jd_orders.orderId=jd_details.orderId and jd_details.productId='.$pid)
			->join('jd_address on jd_address.userId =' .$_SESSION['user']['userId'].' AND jd_address.isDefault = 1')
			->join('jd_prods on jd_prods.prodId='.$pid)
			->order('orderTime')
			->field('jd_orders.*, jd_address.payment, jd_address.delivery, jd_prods.*, amount,price')
			->find();
			// echo $order->getLastSql();
			// dump($orderlist);return;
		
		$states = array('等待发货','已发货','完成','已经取消');
		$actions = array('取消订单','确认收货','评价','');
		$payments = array('货到付款','在线支付','分期付款');
		$delivery = array('京东快递','上门自提');

		$result=\Home\Common\getCcdhData();
		$this->assign('ccdhData',$result);
		$navList = \Home\Common\getNavList();
		$this->assign('navList',$navList);

		$this->assign('payments',$payments);
		$this->assign('delivery',$delivery);
		$this->assign('orderInfo',$orderlist);
		$this->assign('states',$states);
		$this->assign('actions',$actions);
		$this->display();
	}
}