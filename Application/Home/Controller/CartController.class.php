<?php
namespace Home\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class CartController extends Controller {

	//购物车商品展示
	public function index() {
		
		//获取购物车里的商品
		$list = $this->goodsList();

		//清除未知的值
		unset($list['undefined']);

		//查询同样的商品
		$product = M('Products');
		$same = $product
			->where('proTypeId=22')
			->join("__PRODS__ on __PRODS__.prodId = __PRODUCTS__.prodId")
			->limit(6)
			->select();

		//实例化地区表
        $area = M('Areasid');

        //查询数据库
        $diqu = $area->where('pId=1')->select();

		//分配变量
		$this->assign('list',$list);
		$this->assign('diqu',$diqu);
		$this->assign('sameGood',$same);

		//加载模板
		$this->display('Cart/cart');	

	}


	//添加商品到购物车
	public function addCart() {

		//获取提交信息
		$data = I('param.');
		
		//判断数据是否为空
		if(!empty($data)) {
			
			foreach($data as $key=>$val) {

				//过滤掉不是商品id的数据
				if($key != 'tsum' && $key != 'tprice'){

					//把商品数据放入到session中	
					$_SESSION['cart'][$key]['num'] = $val;

				}

				//把总价和总数放入到session中
				if($key == 'tsum' || $key == 'tprice') {
				
					$_SESSION['cart'][$key] = $val;

				}

				
			}

		}else{

			//错误返回
			$this->ajaxReturn(1);
		}

		return true;

	}



	//遍历session里商品
	private function goodsList() {

		//实例化商品表
		$prods = M('Prods');

		//定义数组存放值
		$list = array();
		
		//判断session中有没有存放商品
		if(!empty($_SESSION['cart'])) {

			//把遍历的数据存放到变量中
			foreach($_SESSION['cart'] as $k=>$v) {

				//过滤掉不是商品的值
				if(is_int($k)) {	
					
					$list[$k] = $prods
						->where('jd_prods.prodId='.$k)
						->join('__PRODUCTS__ ON __PRODUCTS__.prodId = __PRODS__.prodId')
						->find();

					$list[$k]['num'] = $v['num']; 
				}else{

					$list[$k] = $v;
					continue;
				}
			}

		}

		return $list;

	}




	//删除购物车中商品
	public function delCartGood() {

		//接收数据
		$data = I('param.');

		//遍历
		foreach($data as $val) {
			
			//删除,unset可以删除下标
			unset($_SESSION['cart'][$val]); 
		/*	$_SESSION['cart']['tsum'] -= $_SESSION['cart'][$val]['num'];
			$_SESSION['cart']['tprice'] -= $_SESSION['cart'][$val]['num']*$_SESSION['cart'][$val]['price2'];
			*/
			//判断是否删除成功
			if(in_array($val,$_SESSION['cart'][$val])) {

				$this->ajaxReturn(2);
			}

		}

	}





	//订单信息确认
	public function orderInfo() {

		//检查用户是否登陆
		if(!empty($_SESSION['user'])){

			//获取购物车商品信息
			$goods = $this->goodsList();

			//清除未知的值
			unset($goods['undefined']);

			//实例化对象表
			$users = M('Users'); //用户表
			$data = $_SESSION['user'];

			//根据用户查询地址信息
			$userAddress = $users
				->field('jd_address.*')
				->where(array('jd_users.userId'=>$data['userId'],'isDefault'=>1))
				->join('__ADDRESS__ ON __ADDRESS__.userId = __USERS__.userId')
				->find();

			//实例化地区表
        	$area = M('Areasid');

        	//查询数据库
        	$diqu = $area->where('pId=1')->select();

			//分配变量
			$_SESSION['addressId'] = $userAddress['addressId'];
			$this->assign('list',$userAddress);
			$this->assign('userId',$data['userId']);
			$this->assign('diqu',$diqu);
			$this->assign('goods',$goods);
			$this->display('Cart/orderInfo');

		}else{
	
			//没有登陆
			$this->ajaxReturn(2);
	
		}

	}


	//删除收货人信息
	public function delAddress() {

		//接收数据
		$data = I('param.');
		
		//实例化地址表
		$address = M('Address');

		//删除
		$list = $address->where($data)->delete();

		//判断删除是否成功
		if($list === false) {

			$this->ajaxReturn(2);
		}else{

			$this->ajaxReturn($list);
		}


	}

	//修改收货信息
	public function editAddress() {

		//实例化地址表
		$address = M('Address');

		//接收数据
		$data = I('param.');
		
		//根据主键判断是否有默认地址
		if(!$data['addressId']){

			//判断之前有多少个地址
			$user = $_SESSION['user'];
			$addressNum = $address->where('userId='.$user['userId'])->count();

			if($addressNum > 3) {

				$this->ajaxReturn('num');
			}

			if(!empty($_SESSION['addressId'])){

				$default['isDefault'] = 0;
				//修改旧默认地址
				$address->where('addressId='.$_SESSION['addressId'])->save($default);
			}

			//添加默认地址
			$addressId = $address->data($data)->add();

			$_SESSION['addressId'] = $addressId;

			$this->ajaxReturn($addressId);
			
		}else {

			//更新
			$list = $address->save($data);

			//判断是否更新成功
			if($list === false){
				$this->ajaxReturn('false');

			}


		}

	}




	//订单提交成功
	public function orderOk() {

		//判断用户有没有登录
		if(!empty($_SESSION['user'])) {
			if(!empty($_SESSION['cart'])){
				//实例化表
				$address = M('Address');
				$order = M('Orders');
				$detail = M('Details');
				$product = M('Products');
				$user = M('Users');

				//查询
				$addressList = $address
					->field('receiver,tel,hometel,address,email,payment,addressbie')
					->where('addressId='.$_SESSION['addressId'])
					->find();


				//取出session中的值
				//订单
				$data = array();
				foreach($addressList as $key=>$val) {

					if($key == payment) {
						$payment = $val;
					}else{
						$data[$key] = $val;
					}
					
				}

				$data['userId'] = $_SESSION['user']['userId'];
				$data['orderTime'] = time();

				//生成订单数据,返回订单号
				$orderId = $order->data($data)->add();

				//订单详情
				foreach($_SESSION['cart'] as $k=>$v) {

					//过滤不是商品的键值
					if(is_int($k)) {

						//查询商品编号
						$productList = $product
							->where('jd_prods.prodId='.$k)
							->join('__PRODS__ ON __PRODS__.prodId = __PRODUCTS__.prodId')
							->find();

						$detailData['productId'] = $productList['productId'];
						$detailData['orderId'] = $orderId;
						$detailData['amount'] = $v['num'];
						$detailData['price'] = $productList['price2'];
						$otherInfo = $v['num'] * $productList['price2'];

						//购买数量，减去库存						
						$product->where('productId='.$productList['productId'])->setInc('saleNum',$v['num']);
						$product->where('productId='.$productList['productId'])->setDec('amount',$v['num']);

						$detail->data($detailData)->add();

					}else{

						continue;
					}
				}

				//总价格
				$tprice = $_SESSION['cart']['tprice'];

				//积分，购买次数
				$user->where('userId='.$data['userId'])->setInc('userJF',floor($tprice));
				$user->where('userId='.$data['userId'])->setInc('userGCS');				
				//分配变量
				$this->assign('payment',$addressList['payment']);
				$this->assign('orderId',$orderId);
				$this->assign('tprice',$tprice);

				//加载模板
				$this->display('Cart/orderOk');
				
				//订单成功后清除session中购买的商品信息
				unset($_SESSION['cart']);
				unset($_SESSION['addressId']);

			}else{

				$this->display('Index/index');
			}

		}else{

			$this->display('Login/index');

		}

		
	}




	//省市区三级联动查询
	public function getRegion(){

	 	//接收数据
	 	$data = I('param.');

	 	//实例化地区表
        $area = M('Areasid');

        //查询数据库
        $list = $area->where('pId='.$data['pId'])->select();
       
        if($list) {

        	$this->ajaxReturn($list);

        }else{

        	$this->ajaxReturn(2);
        }

   }



}



