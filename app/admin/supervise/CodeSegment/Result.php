<?php
/**
 * @author wonli <wonli@live.com>
 * Result.php
 */


namespace app\admin\supervise\CodeSegment;


class Result
{
    const DATA_CURL = 'curl';
    const DATA_FLUTTER = 'flutter';
    const DATA_JAVA = 'java';

    public $reason;
    public $code = 1;

    public $data = [];

    /**
     * @param mixed $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function addData($key, $data)
    {
        $this->data[$key] = $data;
    }

    public function getResult()
    {
        return array(
            'code' => $this->code,
            'reason' => $this->reason,
            'data' => $this->data,
        );
    }
}