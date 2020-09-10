<?php


namespace component;

use Cross\Exception\LogicStatusException;
use Cross\Runtime\Rules;
use Cross\Core\Helper;

use Throwable;
use Exception;
use Closure;


/**
 * Class InputFilter
 *
 * @package component
 */
class InputFilter
{

    /**
     * 待验证字符串
     *
     * @var mixed
     */
    protected $ctx;

    /**
     * 验证失败时返回的code
     *
     * @var int
     */
    protected $code = 0;

    /**
     * @var string
     */
    protected $msg;

    /**
     * 用户指定状态
     *
     * @var bool
     */
    protected $userState = false;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * InputFilter constructor.
     *
     * @param mixed $ctx
     */
    function __construct($ctx)
    {
        $this->msg = null;
        $this->ctx = trim($ctx);
    }

    /**
     * 大于0的正整数
     *
     * @return int
     * @throws LogicStatusException
     */
    function id()
    {
        $ctx = $this->uInt();
        if ($ctx == 0) {
            $this->throwMsg('参数必须是大于0的整数');
        }

        return $ctx;
    }

    /**
     * 整形
     *
     * @return int
     */
    function int()
    {
        return (int)$this->ctx;
    }

    /**
     * 限定范围
     *
     * @param int $min
     * @param int $max
     * @return int|null
     * @throws LogicStatusException
     */
    function range(int $min, int $max)
    {
        $ctx = $this->int();
        if ($ctx < $min || $ctx > $max) {
            $this->throwMsg('参数值范围 %d ~ %d', $min, $max);
        }

        return $ctx;
    }

    /**
     * 正整数
     *
     * @throws LogicStatusException
     */
    function uInt()
    {
        $ctx = $this->int();
        if ($ctx < 0) {
            $this->throwMsg('参数必须是正整数');
        }

        return $ctx;
    }

    /**
     * 限定值
     *
     * @param array $val
     * @return mixed
     * @throws LogicStatusException
     */
    function fixed(...$val)
    {
        if (!in_array($this->ctx, $val)) {
            $this->throwMsg('参数必须是指定值中的一个');
        }

        return $this->ctx;
    }

    /**
     * 正则匹配
     *
     * @param string $pattern
     * @return mixed
     * @throws LogicStatusException
     */
    function regx(string $pattern)
    {
        if (!preg_match($pattern, $this->ctx)) {
            $this->throwMsg('参数正则验证失败');
        }

        return $this->ctx;
    }

    /**
     * 自定义函数验证
     *
     * @param Closure $handler
     * @return mixed
     * @throws LogicStatusException
     */
    function closure(Closure $handler)
    {
        try {
            $v = $handler($this->ctx);
            if (false === $v) {
                $this->throwMsg('false');
            }

            return $v;
        } catch (Throwable $e) {
            $this->throwMsg('参数用户验证异常：%s', $e->getMessage());
            return false;
        }
    }

    /**
     * 规则验证
     *
     * @param string $name
     * @param mixed $val
     * @return mixed
     * @throws LogicStatusException
     */
    function rule(string $name, &$val = null)
    {
        try {
            $val = Rules::match($name, $this->ctx);
            if (false === $val) {
                $this->throwMsg('false');
            }

            return $val;
        } catch (Exception $e) {
            $this->throwMsg('规则验证异常: %s', $e->getMessage());
            return false;
        }
    }

    /**
     * 验证日期
     *
     * @param null $unixTime
     * @return false|string
     * @throws LogicStatusException
     */
    function date(&$unixTime = null)
    {
        $unixTime = strtotime($this->ctx);
        if (!$unixTime) {
            $this->throwMsg('请输入正确的日期');
        }

        return date('Y-m-d', $unixTime);
    }

    /**
     * 日期时间验证
     *
     * @param null $unixTime
     * @return false|string
     * @throws LogicStatusException
     */
    function dateTime(&$unixTime = null)
    {
        $unixTime = strtotime($this->ctx);
        if (!$unixTime) {
            $this->throwMsg('请输入正确的年月日');
        }

        return date('Y-m-d H:i:s', $unixTime);
    }

    /**
     * 验证email
     *
     * @param string $addValidExpr
     * @return mixed
     * @throws LogicStatusException
     */
    function email($addValidExpr = "/^[a-zA-Z0-9]([\w\-\.]?)+/")
    {
        if (!Helper::validEmail($this->ctx, $addValidExpr)) {
            $this->throwMsg('电子邮件地址验证失败');
        }

        return $this->ctx;
    }

    /**
     * 验证身份证
     *
     * @param bool $justCheckLength
     * @return mixed
     * @throws LogicStatusException
     */
    function idCard($justCheckLength = false)
    {
        try {
            if (!Helper::checkIDCard($this->ctx, $justCheckLength)) {
                $this->throwMsg('身份证验证失败');
            }
        } catch (Exception $e) {
            $this->throwMsg('身份证验证异常 %s', $e->getMessage());
        }

        return $this->ctx;
    }

    /**
     * 默认值
     *
     * @param mixed $val
     * @return mixed
     */
    function default($val)
    {
        if (null === $this->ctx) {
            return $val;
        }

        return $this->ctx;
    }

    /**
     * 获取原始入参
     *
     * @return mixed
     */
    function raw()
    {
        return $this->ctx;
    }

    /**
     * 转义后的原始参数
     *
     * @return string
     */
    function val()
    {
        return htmlentities(strip_tags($this->ctx), ENT_COMPAT, 'utf-8');
    }

    /**
     * 自定义验证失败时的消息
     *
     * @param int $code
     * @param string|null $msg
     * @return $this
     */
    function msg(int $code, string $msg = null)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->userState = true;
        return $this;
    }

    /**
     * 抛出异常信息
     *
     * @param null $msg
     * @param mixed ...$params
     * @throws LogicStatusException
     */
    function throwMsg($msg, ...$params)
    {
        $msgCtx = null;
        if (!$this->userState) {
            $msgCtx = sprintf($msg, ...$params);
        } elseif ($this->msg) {
            $msgCtx = $this->msg;
        }

        throw new LogicStatusException($this->code, $msgCtx);
    }

    /**
     * toString
     * @return mixed
     */
    function __toString()
    {
        return $this->val();
    }
}