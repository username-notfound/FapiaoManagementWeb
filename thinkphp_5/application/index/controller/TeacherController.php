<?php
namespace app\index\controller;
use think\Controller;   // 用于与V层进行数据传递
use think\Request;			// 引用Request
use app\common\model\Teacher;  //教师模型

class TeacherController extends Controller
{
  public function index()
  {
      //return 'hello Teacher';
      // 获取教师表中的所有数据
      //$teachers = Db::name('teacher')->select();

      // 查看获取的数据
      //var_dump($teachers[0]['name']);
      //echo $teachers[0]['name'];

      $Teacher = new Teacher;
        $teachers = $Teacher->select();

        // 向V层传数据
        $this->assign('teachers', $teachers);

        // 取回打包后的数据
        $htmls = $this->fetch();

        // 将数据返回给用户
        return $htmls;

      // // 获取第0个数据
      // $teacherName = $teachers[0];
      //
      // // 调用上述对象的getData()方法
      // //var_dump($teacher->getData('name')); //结果是string(6) "张三"
      // echo 'name of teacher: '. $teacherName->getData('name').'<br />';
      // //而echo和return效果一样 只输出张三 无双引号
      // return 'repeated the name: '. $teacherName->getData('name');
  } // action：index

  // public function insert1()
  // {
  //   //return 'hello insert';
  //   // 新建测试数据
  //    $teacher = array(); // 这种写法也可以 $teacher = [];
  //    $teacher['name'] = '王五';
  //    $teacher['username'] = 'wangwu';
  //    $teacher['sex'] = '1';
  //    $teacher['email'] = 'wangwu@yunzhi.club';
  //    //var_dump($teacher);
  //
  //    // 引用teacher数据表对应的模型
  //    $Teacher = new Teacher();
  //    // 向teacher表中插入数据并判断是否插入成功
  //    $state = $Teacher->data($teacher)->save();
  //    //var_dump($state); //不需要，因为要站在用户角度考虑问题
  //    return $teacher['name'] . '成功增加至数据表中'. $Teacher->id;
  // } // insert
  //
  public function insert()
  {
    //var_dump($_POST);
    // Request::instance()返回了一个对象，调用这个对象的post()方法，得到post数据
    $postData = Request::instance()->post();
    //think\Controller中，也可以直接使用request属性方法来获取post数据
    //$postData = $this->request->post();
    //var_dump($postData);
    //return ;    // 提前返回

    // 实例化Teacher空对象
    $Teacher = new Teacher();

    // 为对象的属性赋值
    // $Teacher->name = '王五';
    // $Teacher->username = 'wangwu';
    // $Teacher->sex = '1';
    // $Teacher->email = 'wangwu@yunzhi.club';
    // 为对象赋值
    $Teacher->name = $postData['name'];
    $Teacher->username = $postData['username'];
    $Teacher->sex = $postData['sex'];
    $Teacher->email = $postData['email'];

    // // 新增对象至数据表
    // $Teacher->save();
    //
    // // 执行对象的插入数据操作
    // return $Teacher->name . '成功增加至数据表中。新增ID为:' . $Teacher->id;
    // 新增对象至数据表
    $result = $Teacher->validate(true)->save($Teacher->getData());
    //$result = $Teacher->validate(true)->save(); //面向对象
     // 反馈结果
     if (false === $result)
     {
         return '新增失败:' . $Teacher->getError();
     } else {
         return  '新增成功。新增ID为:' . $Teacher->id;
     }
  } // insert

  public function add()
    {
      $htmls = $this->fetch();
      return $htmls;

    } // add

  public function delete()
  {
    return 'hi';
  } // delete

}
