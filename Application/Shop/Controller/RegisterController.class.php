<?php
namespace Shop\Controller;
use Admin\Controller\EmailController;
use Shop\Model\CountryModel;
use Shop\Model\UserModel;
use Think\Controller;
use Shop\Controller\Image;
class RegisterController extends BaseController{
    //注册界面
    public function index(){
        $Country = new CountryModel();
        $country_list = $Country ->get_list();
        $this->assign('country_list',$country_list);
        $this->assign('head_title','Register');
        $this->display("User/register");
    }
		//验证邮箱，用户名是否唯一
	public	function check_name_email(){
			$users=M('users');
			$r=$users->where($_POST)->cache(false)->find();
			if(!empty($r)){
				echo 1;
			}else{
				echo 0;
			}
		}
	public function get_code(){
		$Login=new LoginController();
		$Login->setCode();
	}

	public function check_code(){
		$code = $_POST['code'];
		$id='';
		$Verify=new \Think\Verify();
		echo $Verify->check($code,$id);
	}
		//对注册成功的数据进行入库
//	public	function do_register(){
//		$code=I("post.code");
//		if($code){
//			$uname=I("post.uname");
//			$upwd=I("post.upwd");
//			$rupwd=I("post.rupwd");
//			$email=I("post.useremail");
//			if($uname && $upwd && $rupwd && $email) {
//				if($upwd == $rupwd) {
//					$User=new UserModel();
//					$res=$User->user_reg($uname,$upwd,$email);
//					if ($res > 0) {
//						$Email = new EmailController();
//						$to[0]['address'] = $email;
//						$result = $Email->send_email($to, $cc=null, '系统管理员', $User->email_content($res,$uname,$upwd,$email));
//						if($result==1) {
//							echo 10;
//						}else{
//							echo 20;
//						}
//					} else {
//						echo 0;
//					}
//				}else{
//					echo -3;
//				}
//			}else{
//				echo -2;
//			}
//		}else{
//			echo -1;
//		}
//	}

    public function do_register(){
        $business_card = I('post.business_card');

        $company = I("post.company");
        $company_phone = I("post.company_phone");
        $email = I("post.email");
        $contact = I("post.contact");

        $phone = I("post.phone");
        $zy_title = I("post.zy_title");
        $address = I("post.address");
        $country = I("post.country");
        $website = I("post.website");
        $business_type = I("post.business_type");

        $Cookie = new Cookie();
        if ($company && $company_phone && $email && $contact) {
            if (preg_match($this->E, $email)) {
                $has=$this->check_email($email);
                if($has){
                    $this->list_json_result(-10,'This email has been register','');
                }else {
                    if ($business_card) {
                        $result = $this->SaveAjaxUpload('/Public/Uploads/business_card/img/', $business_card);
                        if ($result['status'] == 1) {
                            $name = explode(".", $result['url']);
                            $mini_img = $this->img_thumb_fn($result['url'], md5(time()) . "." . $name[1], '/Public/Uploads/business_card/mini_img/');
                            if ($mini_img) {
                                $data['business_card'] = $result['url'];
                                $data['business_card_mini'] = $mini_img;
                            } else {
                                $this->list_json_result(-19, 'thumb error', '');
                            }
                        } else {
                            echo json_encode($result);
                            die();
                        }
                    } else {
                        $data['business_card'] = '';
                        $data['business_card_mini'] = '';
                    }
                    $data['uname'] = $email;
                    $data['userCompany'] = $company;
                    $data['userCompanyPhone'] = $company_phone;
                    $data['useremail'] = $email;
                    $data['userContact'] = $contact;
                    if ($zy_title) {
                        $data['zy_title'] = $zy_title;
                    } else {
                        $data['zy_title'] = '';
                    }
                    if ($phone) {
                        $data['usertel'] = $phone;
                    } else {
                        $data['usertel'] = '';
                    }
                    if ($address) {
                        $data['address'] = $address;
                    } else {
                        $data['address'] = '';
                    }
                    if ($country) {
                        $data['country'] = $country;
                    } else {
                        $data['country'] = '';
                    }
                    if ($website) {
                        $data['website'] = $website;
                    } else {
                        $data['website'] = '';
                    }
                    if ($business_type) {
                        $data['business_type'] = $business_type;
                    } else {
                        $data['business_type'] = '';
                    }
                    $data['reg_time'] = time();
                    $data['reg_ip'] = $this->get_ip();
                    $data['login_count'] = 0;
                    $data['login_time'] = 0;
                    $data['login_ip'] = 0;
                    $data['status'] = 1;
                    $data['reviewed'] = 0;
                    $data['userHYdjId'] = 0;
                    $data['userJF'] = 0;
                    $data['userGCS'] = 0;
                    $data['userCZ'] = 0;
                    $openid = $Cookie->get('openid');
                    $type = $Cookie->get('type');
                    if ($type && $openid) {
                        switch ($type) {
                            case 'FB':
                                $data['FB_openid'] = $openid;
                                $data['TW_openid'] = '';
                                break;
                            case 'TW':
                                $data['TW_openid'] = $openid;
                                $data['FB_openid'] = '';
                                break;
                            default:
                                $data['TW_openid'] = '';
                                $data['FB_openid'] = '';
                                break;
                        }
                    } else {
                        $data['TW_openid'] = '';
                        $data['FB_openid'] = '';
                    }
                    $res = M('users', 'jd_')->add($data);
                    if ($res) {
                        $Email = new EmailController();
                        $to = 'gala@galison.co';
                        $title = 'Gala have a new register';
                        $content = 'Gala have a new register is : ' . $email;
                        $re = $Email->send_email($to, '', $title, $content);
                        if ($re == 1) {
                            $this->list_json_result(1, 'reg success', '');
                        } else {
                            $this->list_json_result(0, 'reg fail', '');
                        }
                    } else {
                        $this->list_json_result(-1, 'reg fail', '');
                    }
                }
            } else {
                $this->list_json_result(-3, 'email format error', '');
            }
        } else {
            $this->list_json_result(-20, 'post error', '');
        }
    }


    //压缩图片
    public function img_thumb_fn($link,$name,$path){
        $fullpath = dirname(THINK_PATH) . $path;
        //检测文件夹是否存在 不存在则创建
        if (!is_dir($fullpath)) {
            mkdir($fullpath, 0777, true);
        }
        $Img = new Image();
        $m_name = "m_" . $name;
        $thumbname = $fullpath . $m_name;
        $image = $Img->thumb(dirname(THINK_PATH).$link, $thumbname, '', $maxWidth = 338, $maxHeight = 208, $interlace = true);
        if($image) {
            return $path.$m_name;
        }else{
            return false;
        }
    }


    function SaveAjaxUpload($basedir, $img, $types=array()){
        if($img) {
            $fullpath = dirname(THINK_PATH) . $basedir;
            //检测文件夹是否存在 不存在则创建
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }
            //定义类型
            $types = empty($types) ? array('jpg', 'gif', 'png', 'jpeg') : $types;
            //修改字符
            $img = str_replace(array('_', '-'), array('/', '+'), $img);
            $b64img = substr($img, 0, 100);
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $b64img, $matches)) {
                $type = $matches[2];
                //判断是否在定义的类型里面
                if (!in_array($type, $types)) {
                    return array('status' => -5, 'info' => '图片格式不正确', 'url' => '');
                } else {
                    if ($type == 'jpeg') {
                        $type = 'jpg';
                    }
                    //清除头
                    $img = str_replace($matches[1], '', $img);
                    //base64解码
                    $img = base64_decode($img);
                    //定义文件名
                    $photo = '/' . md5(date('YmdHis') . rand(1000, 9999)) . '_' . uniqid() . '.' . $type;
                    //写入文件
                    $result = file_put_contents($fullpath . $photo, $img);
                    if ($result) {
                        return array('status' => 1, 'info' => '保存图片成功', 'url' => $basedir . $photo);
                    } else {
                        return array('status' => -1, 'info' => '保存图片失败', 'url' => '');
                    }
                }
            }else{
                return array('status' => -2, 'info' => '图片异常', 'url' => '');
            }
        }else {
            return array('status' => -10, 'info' => '请选择要上传的图片', 'url' => '');
        }
    }

    public function check_email($email){
        $sql = "select * from jd_users where useremail='".$email."' limit 1";
        $has = M()->query($sql);
        if($has){
            return true;
        }else{
            return false;
        }
    }

    public function email(){
		$to=array();
		$to[0]['address']='422536329@qq.com';
		$subject='测试';
		$content='测试测试测试';
		$Email=new EmailController();
		$res=$Email->send_email($to,$cc=null,$subject,$content);
		echo $res;
	}
    //end
}