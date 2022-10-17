<?php
/**
 * Desc 自定义 code 提示语
 * User 韦顺隆
 * Date-Time 2022/10/14 10:25
 */

namespace customCodeMsg;

use app\exception\ThrottleException;

class CustomCodeMsg
{
    /**
     * 禁止修改的code
     * @var int[]
     * User 韦顺隆
     * Date-Time 2022/10/14 14:56
     */
    protected static $stop_code = [
        40000, 40001, 40002, 40003, 40200,
        40201, 400000, 400001, 400002, 400003,
        400004, 400006
    ];


    /**
     * 修改 个人提示语 文件
     * @param string $name
     * @param int $code
     * @param string $value
     * @return \think\response\Json
     * @throws ThrottleException
     * User 韦顺隆
     * Date-Time 2022/10/14 14:26
     */
    public static function setCode(string $name = 'share', int $code = 0, string $value = '')
    {
        if (!empty($name) && $name == 'share') throw new ThrottleException('通用 CustomCodeMsg 提示语，不允许修改！', 400004);
        if (in_array($code, self::$stop_code)) throw new ThrottleException('基础 CustomCodeMsg 提示语，不允许修改！', 400004);
        $file = self::getCodeFile($name);

        $msg = @$file[$code]; // 避免查无提示语时被中断

        if ($msg === null) throw new ThrottleException('查无 CustomCodeMsg 提示语！', 400002);

        $file[$code] = $value;

        $path = self::joinCodeFilePath($name);

        // 整理 code 顺序
        krsort($file);

        $str = "<?php
/**
 * Desc 个人 code 提示语
 * 获取个人提示语方法  get_custom_code_msg(40200, 'phpweishunlong');
 * 返回json(带参数) returnJson(40200, get_custom_code_msg(40200, 'phpweishunlong'), []);
 * 返回json() returnJson(40200, get_custom_code_msg(40200, 'phpweishunlong'));
 */

return " . var_export($file, true) . ";";

        if (!file_put_contents($path, $str)) throw new ThrottleException('修改 CustomCodeMsg 提示语，失败！', 400003);

        return json(['code' => 40000, 'message' => '修改成功！']);
    }

    /**
     * 通过 code 获取提示语
     * @param int $code key值
     * @param string $name 提示文件名
     * @return mixed
     * @throws ThrottleException
     * User 韦顺隆
     * Date-Time 2022/10/14 14:25
     */
    public static function getCode(int $code = 0, string $name = 'share')
    {
        $file = self::getCodeFile($name);

        $msg = @$file[$code]; // 避免查无提示语时被中断

        if (!$msg) throw new ThrottleException('查无 CustomCodeMsg 提示语！', 400002);

        return $msg;
    }

    /**
     * 读取文件
     * @param string $name 文件名
     * @return mixed
     * @throws ThrottleException
     * User 韦顺隆
     * Date-Time 2022/10/14 14:25
     */
    protected static function getCodeFile(string $name = '')
    {
        $dir = self::joinCodeFilePath($name);

        $file = @include $dir; // 避免查无文件时被中断

        if (!$file) throw new ThrottleException('查无 CustomCodeMsg 文件！', 400001);

        return $file;
    }

    /**
     * 拼接文件地址
     * @param string $name 文件名
     * @return string
     * @throws ThrottleException
     * User 韦顺隆
     * Date-Time 2022/10/14 14:24
     */
    protected static function joinCodeFilePath(string $name = '')
    {
        if (!$name) throw new ThrottleException('请输入需要读取的 CustomCodeMsg 文件名！', 400000);

        $name = explode('.php', $name)[0];
        if (!$name) throw new ThrottleException('请输入需要读取的 CustomCodeMsg 文件名！', 400000);

        return __DIR__.'/codeBank/'.$name.'.php';
    }
}