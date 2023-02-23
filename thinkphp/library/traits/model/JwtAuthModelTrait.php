<?php


namespace traits\model;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use \Exception;
use think\Env;

trait JwtAuthModelTrait
{
    public static $sign_id = 'lXXing';
    /**
     * @param array $params
     * @return string
     */
    public function getToken(array $params = [])
    {

        $id = $this->{$this->getPk()};
        $host = request()->host();
//        $base_url = Env::get('base_url');
        $sign = config('sign_md5');
        $login_expiration = Env::get('login_expiration',1);

        $signer = new Sha256();
        $token = (new Builder())->setIssuer($host)
//            ->setAudience($base_url)
            ->setId(self::$sign_id, true) //自定义标识
            ->setIssuedAt(time()) //当前时间
            ->setExpiration(time() + (86400 * $login_expiration)) //token有效期时长
            ->set('uid', $id);
        if($params){
            foreach ($params as $k => $v){
                $token->set($k,$v);
            }
        }
        $token = $token->sign($signer, $sign)
            ->getToken();
        //这里可以做一些其它的操作，例如把Token放入到Redis内存里面缓存起来。

        return (String) $token;
    }

    /**
     * @param string $jwt
     * @return array
     *
     * @throws UnexpectedValueException     Provided JWT was invalid
     * @throws SignatureInvalidException    Provided JWT was invalid because the signature verification failed
     * @throws BeforeValidException         Provided JWT is trying to be used before it's eligible as defined by 'nbf'
     * @throws BeforeValidException         Provided JWT is trying to be used before it's been created as defined by 'iat'
     * @throws ExpiredException             Provided JWT has since expired, as defined by the 'exp' claim
     *
     */
    public static function parseToken(string $token): array
    {
        $user_id = 0;
        $token = (new Parser())->parse((string)$token);

        //验证token
        $data = new ValidationData();
        $data->setIssuer(ENV::get('api_url',''));//验证的签发人
//        $data->setAudience(ENV::get('base_url',''));//验证的接收人
        $data->setId(self::$sign_id);//验证token标识

        if (!$token->validate($data)) {
            //token验证失败
            return [[]];
        }

        //验证签名
        $signer = new Sha256();
        if (!$token->verify($signer, config('sign_md5'))) {
            //签名验证失败
            return [[]];
        }

        //从token中获取用户id
        $user_id = $token->getClaim('uid');

        $model = new self();

        return [$model->where($model->getPk(), $user_id)->find()];
    }
}