<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/7/31
 * Time: 9:45
 */

namespace common\libraries;


class ApiUserInfoSecurity
{
    private static $encrypt_config = 1;//用户信息加密配置: 0不加密, 1加密

    private static $encrypt_fields = ['mobile'];//目前需要加密的用户表字段

    private static $limit_length_config = [];//加密字段个数限制（中文算一个字符）

    //256个加密字符
    private static $encrypt_chars = [
        'ㄥ','┌','ㄒ','к','м','р','ㄇ','0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','э','┾','и','$','в','&','┠','(',')','+',',','-','.','я',':',';','<','=','>','ц','@','[','ш',']','^','_','н','{','|','}','~','§','№','☆','★','○','●','◎','◇','◆','□','℃','‰','€','■','△','▲','※','→','←','↑','↓','¤','°','＃','＆','＠','＼','︿','＿','―','♂','♀','≈','≡','≠','＝','≤','≥','＞','＜','≮','≯','±','＋','－','×','÷','ㄑ','ㄨ','∫','∮','∝','∞','∧','∨','∑','∏','∪','∩','∈','∵','∴','⊥','∥','∠','⌒','⊙','≌','∽','√','，','、','；','：','！','？','л','ы','ъ','й','～','‖','∶','д','г','｜','』','『','」','「','》','《','〉','〈','〕','〔','〖','〗','【','】','）','（','［','］','｛','｝','α','β','γ','δ','ε','ζ','η','θ','ι','κ','λ','μ','ν','ξ','ο','π','ρ','σ','τ','υ','φ','χ','ψ','ω','ā','á','ǎ','à','ō','ó','ǒ','ò','ê','ē','é','ě','è','ī','í','ǐ','ì','ū','ú','ǔ','ù','ǖ','ǘ','ǚ','ǜ','ü'
    ];


    /**
     * 加密用户信息
     * @param  string|array    $data 待加密数据(字符串或关联一维数组)
     * @param  array           $extra_fields 额外需要加密的字段,如 ['password','pass']
     * @param  int             $limit_length   超过多少字符长度不加密（中文算一个字符，0为不限制）
     *
     * @return string|array    返回加密后的数据
     */
    public static function encrypt($data, $extra_fields = [], $limit_length = 0)
    {

        if (0 === static::$encrypt_config)
        {
            return $data;
        }

        if (is_string($data) || is_numeric($data))
        {
            return static::encrypt_user_info($data, $limit_length);
        }

        if (is_array($data))
        {
            if (empty($data)) return $data;

            $encrypt_fields = static::$encrypt_fields;
            if (is_array($extra_fields) && !empty($extra_fields))
            {
                $encrypt_fields = array_merge(static::$encrypt_fields, $extra_fields);
            }

            foreach ($data as $key => $val)
            {
                if (in_array($key, $encrypt_fields))
                {
                    $limit_length = isset(static::$limit_length_config[$key]) ? static::$limit_length_config[$key] : 0;

                    $data[$key] = static::encrypt_user_info($val, $limit_length);
                }
            }
            return $data;
        }

        return $data;

    }

    /**
     * 解密用户信息
     * @param  string|array    $data 待解密数据(字符串或关联一维数组)
     * @param  array           $extra_fields 额外需要解密的字段,如 ['password','pass']
     *
     * @return string|array    返回解密后的数据
     */
    public static function decrypt($data, $extra_fields = [])
    {

        if (is_string($data))
        {
            return static::decrypt_user_info($data);
        }

        if (is_array($data))
        {
            if (empty($data)) return $data;

            $encrypt_fields = static::$encrypt_fields;
            if (is_array($extra_fields) && !empty($extra_fields))
            {
                $encrypt_fields = array_merge(static::$encrypt_fields, $extra_fields);
            }

            foreach ($data as $key => $val)
            {
                if (in_array($key, $encrypt_fields))
                {
                    $data[$key] = static::decrypt_user_info($val);
                }
            }
            return $data;

        }

        return $data;

    }

    /**
     * 用户敏感信息加密
     * @param  string $str            需要加密的字符串
     * @param  int    $limit_length   超过多少字符长度不加密（中文算一个字符，0为不限制）
     * @return string
     */
    private static function encrypt_user_info($str = '', $limit_length = 0)
    {

        if ('' === $str || is_null($str))  return $str;

        if ($limit_length > 0 && mb_strlen($str, 'UTF-8') > $limit_length) return $str;

        is_numeric($str) && $str = (string)$str;

        //判断是否已加密
        //$first = substr($str, 0, 1);
        $first = mb_substr($str, 0, 1, 'UTF-8');

        if ('$' === $first) return $str;

        $length = strlen($str);


        //1、将每个字节转成对应的8位二进制
        $binary_str = '';

        for ($i = 0; $i < $length; $i++)
        {
            $binary_str .= str_pad(base_convert(ord($str[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }

        //2、打乱顺序，偶数位的数字放左边，奇数位放右边
        $binary_str_length = strlen($binary_str);
        $left_str = $right_str = '';
        for ($i = 0; $i < $binary_str_length; $i++)
        {
            if (0 == $i % 2)
            {
                $left_str .= $binary_str[$i];
            }
            else
            {
                $right_str .= $binary_str[$i];
            }
        }

        $new_str = $left_str.$right_str;

        //3、转换加密字符
        $new_arr = str_split($new_str, 8);

        $encrypt_str = '';
        foreach ($new_arr as $val)
        {
            $encrypt_str .= static::$encrypt_chars[base_convert($val, 2, 10)];
        }

        return '$' . $encrypt_str;

    }

    /**
     * 用户敏感信息解密
     * @param  string $str 需要解密的字符串
     * @return string
     */
    private static function decrypt_user_info($str = '')
    {

        if ('' === $str || is_null($str))  return $str;

        //判断是否已加密
        $first = mb_substr($str, 0, 1,'UTF-8');

        if ('$' !== $first) return $str;

        //1、去除第一个$
        $str = mb_substr($str, 1, null,'UTF-8');

        $length = mb_strlen($str, 'UTF-8');

        $encrypt_chars = array_flip(static::$encrypt_chars);

        //2、将每个字符转成对应的十进制
        $binary_str = '';
        for ($i = 0; $i < $length; $i++)
        {
            $char = mb_substr($str, $i, 1,'UTF-8');
            $binary_str .= str_pad(base_convert($encrypt_chars[$char], 10, 2), 8, '0', STR_PAD_LEFT);
        }
        //3、偶数位和奇数位拼接
        $length = strlen($binary_str);
        $half_length = $length / 2;
        $left_binary_str  = substr($binary_str, 0, $half_length);
        $right_binary_str = substr($binary_str, $half_length);

        $new_str = '';
        for ($i = 0; $i < $half_length; $i++)
        {
            $new_str .= $left_binary_str[$i] . $right_binary_str[$i];
        }
        //4、获取ASCII码对应的字符
        $new_arr = str_split($new_str, 8);
        $decrypt_str = '';
        foreach ($new_arr as $val)
        {

            $decrypt_str .= chr(base_convert($val, 2, 10));
        }

        return $decrypt_str;
    }
}