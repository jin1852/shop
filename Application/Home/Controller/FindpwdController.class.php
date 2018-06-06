<?php
namespace Home\Controller;
use Think\Controller;
class FindpwdController extends Controller{
	/*更改密码的第一步 */
	public function index(){
		$this->display('findpwdStep1');
	}
	public function get_code(){
		$config=array(
					'fontSize'	=>30,
					'length'    =>4,
					'useNoise'  =>false,
				);
			$Verify= new \Think\Verify($config);
			$Verify->entry();
	}
	private function check_code($code,$id=''){
		$Verify=new \Think\Verify();
		return $Verify->check($code,$id);
	}
	public function check_step1(){
		$codeStatus=$this->check_code($_POST['code']);
		if(empty($codeStatus)){
			echo -1;
		}else{
			$user=M('users');
			$condition['uname']=$_POST['uname'];
			$condition['useremail']=$_POST['uname'];
			$condition['_logic']='OR';
			$result=$user->where($condition)->field('userId,uname,nickName,useremail,usertel')->find();
			if(!empty($result)){
				echo 0;
				$_SESSION['findpwd']=$result;
			}else{
				echo -2;
			}
		}
	}
	/*更改密码的第二步 */
	public function step2(){
		$findInfo=session('findpwd');
		$this->assign('findInfo',$findInfo);
		$this->display('findpwdStep2');
	}
	/*无网情况下效果*/
	public function send_check(){
		if($_POST['findmethod']==1){
			$linkpath=U('Findpwd/step3',array('userId'=>$_SESSION['findpwd']['userId'],'thing'=>'findpwd'));
			$pathInFile='<a href="'.$linkpath.'">'.$linkpath.'</a>';
			$name=$_SESSION['findpwd']['nickName'];
			$str="<script>
				var ostrong=document.getElementsByTagName('strong');
				var oa=document.getElementsByTagName('a');
				ostrong[0].innerHTML='".$name."';
				oa[5].setAttribute('href','".$linkpath."');
				oa[5].innerHTML='".$pathInFile."';
				</script>";
			file_put_contents('C:/wamp/www/JD/email_location.html',$str,FILE_APPEND);
			echo 1;
		}else if($_POST['findmethod']==2){
			//短信;
			echo 2;
		}
	}
	public function email_success(){
		$this->assign('emailpath',__ROOT__.'/email_location.html');
		$this->display('emailsuccess');
	}
	/*更改密码的第三步 */
	public function step3(){
		if(!empty($_GET)){
			$thing=$_GET['thing'];
			$userId=$_GET['userId'];
			$userData=session($thing);
			if(!empty($userData)){
				if($userData['userId']==$userId){
					$_SESSION['findpwd']=array();
					$_SESSION['userId']=$userId;
					$this->display('findpwdStep3');
				}else{
					$this->error('链接失效');
				}
			}else{
				$this->error('链接失效');
			}
		}else if(!empty($_POST)){
			//短信
			echo 2;
		}
	}
	public function do_changepwd(){
		$orders=M('orders');
		$where['userId']=$_SESSION['userId'];
		$where['tel']=$_POST['hstel'];
		$result=$orders->where($where)->find();
		if(!empty($result)){
			$users=M('users');
			$data['upwd']=$_POST['upwd'];
			$r=$users->where('userId='.$_SESSION['userId'])
				  ->data($data)
				  ->save();
			if($r>=0){
				echo 0;
				$_SESSION['userId']=array();
			}else{
				echo -2;
			}
		}else{
			echo -1;
		}
	}
	/*更改密码的第四步 */
	public function step4(){
		$this->display('findpwdStep4');
	}
}
