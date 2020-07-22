<?php
namespace app\index\controller;
use think\Controller;   // 用于与V层进行数据传递
use think\Request;			// 引用Request
use app\common\model\Invoice;  //发票模型

// 定义常量
const APP_ID = '21409065';
const API_KEY = 'lah2nV3Ukx2ntuLjfwmbbFeK';
const SECRET_KEY = 'umwXhe02TQDeRywdGUdbwdscQWwni9Tn';

class InvoiceController extends Controller
{
  /**
* 发起http post请求(REST API), 并获取REST请求的结果
* @param string $url
* @param string $param
* @return - http response body if succeeds, else false.
*/
public function request_post($url = '',
                    $param = '')
{
    if (empty($url) || empty($param)) {
        return false;
    }

    $postUrl = $url;
    $curlPost = $param;
    // 初始化curl
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $postUrl);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 要求结果为字符串且输出到屏幕上
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // post提交方式
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    $header  = array(
          'Content-Type:'.'application/x-www-form-urlencoded');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    // 运行curl
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
} // request_post: copy from baidu api


  public function index()
  {
    $Invoice = new Invoice;
    $invoices = $Invoice->select();

    // 向V层传数据
    $this->assign('invoices', $invoices);

    // 取回打包后的数据
    $htmls = $this->fetch();

    // 将数据返回给用户
    return $htmls;

  } // index

  public function insert()
  {
    // phpinfo();exit;
    // 获取access token -- 每30天会失效
    /*
    ** TODO: 记住第一次请求的时间， 并计算出失效日期
    **       等失效后再次请求新的access token
    **       更新：百度官方回复获取access_token是不限制次数的
    */
    $url = 'https://aip.baidubce.com/oauth/2.0/token';
    $post_data['grant_type']       = 'client_credentials';
    $post_data['client_id']      = 'lah2nV3Ukx2ntuLjfwmbbFeK';
    $post_data['client_secret'] = 'umwXhe02TQDeRywdGUdbwdscQWwni9Tn';
    $o = "";
    foreach ( $post_data as $k => $v )
    {
    	$o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);

    $token = $this->request_post($url, $post_data);
    // 把得到的数据解码为string，并且保留其中access_token的值
    $access_token = json_decode($token,true)['access_token'];
    //halt($access_token);

    /*
    ** TODO: 对上传文件进行验证，包括文件大小、文件类型和后缀
    **       百度要求文件大小小于4MB，文件类型支持jpg jpeg png bmp
    **       4MB = 4*1024*1024byte = 4194304
    **       更新： php不支持bmp位图上传，则在移动图片文件前
    **             应先考虑是否超过php上传限制，如未超过再按照需求用代码判断
    */
    $file = request()->file('file');     // 获取已经上传的图片文件
    //halt($file);
    if(empty($file)){
      $this->error("未上传文件或超出服务器上传限制。请重新选择图片！");
    }  else {
      // 移动到框架应用根目录/public/uploads/ 目录下
      // 在此不必判断文件的大小和文件格式，已在前段做了限制
      // 并对大小大于4MB且小于等于8MB的图片进行了压缩，确保该大小范围内的图片可以正常上传
      //$info =  $file->validate(['size'=>2097152,'ext'=>'jpg,jpeg,png,bmp'])->move(ROOT_PATH . 'public' . DS . 'uploads');
      $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');

      if($info){
         // 成功上传后 获取上传信息
         // 输出 jpg
         //echo $info->getExtension();
         //echo $info->getSaveName()
         //echo $info->getFilename();
         //halt($image);
       }else{
          //echo $file->getError();
          // 上传失败则转跳回upload页面并提示失败原因
          $this->error($file->getError().'。请重新选择图片！');
       } // else
   } // else

    //var_dump(json_decode($res,true));
    $url2 = 'https://aip.baidubce.com/rest/2.0/ocr/v1/vat_invoice?access_token=' . $access_token;
    if($info->getSize() > 4194304)
    {
      $image = \think\Image::open(ROOT_PATH.'public/uploads/'.$info->getSaveName());
      // 50代表的是质量、压缩图片容量大小， 压缩上传的图片使其小于4mb
      $image->save(ROOT_PATH.'public/uploads/'.$info->getSaveName(),$info->getExtension(), 50);
    }
    // if($image->getSize() > 4194304)
    // {
    //   $this->error('所选的照片过大，请重新上传小于8MB的图片');
    // }

    //halt($image);
    $img = file_get_contents(ROOT_PATH.'public/uploads/'.$info->getSaveName());
    $img = base64_encode($img);
    $bodys = array(
    'image' => $img);
    $res = $this->request_post($url2, $bodys);

    //var_dump($res);
    $res = json_decode($res,true);
    //halt($res);
    //存储数据
    $Invoice = new Invoice();
    $Invoice->InvoiceCode = $res["words_result"]['InvoiceCode'];
    $Invoice->InvoiceNum = $res["words_result"]['InvoiceNum'];
    $Invoice->AmountInFiguers = $res["words_result"]['AmountInFiguers'];
    $Invoice->InvoiceDate = $res["words_result"]['InvoiceDate'];
    $Invoice->SellerName = $res["words_result"]['SellerName'];

    /*
    ** TODO: 判断扫描数据是否已经存在，若不存在则插入数据库
    */
    // 由于发票号是唯一的，所以如果发票号重复则说明该组数据已经存在
    $Invoice2 = new Invoice();
    $invoice2 = $Invoice2->where(['InvoiceNum' => $Invoice->InvoiceNum])->find();
    if ($invoice2){
      // 如果重复则直接返回主页
      $this->success('该发票已经存在', 'Invoice/index');
    } else {
      // 如果不重复再加入数据
      $insert = $Invoice->save($Invoice->getData());
      // 重定向，转跳回主页
      if($insert){
        //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
        $this->success('新增成功', 'Invoice/index');
      } else {
        //错误页面的默认跳转页面是返回前一页，通常不需要设置
        $this->error('新增失败');
      } // else
    }
  } // insert

  public function upload()
  {
    $htmls = $this->fetch();
    return $htmls;

  } // upload


  } // InvoiceController
