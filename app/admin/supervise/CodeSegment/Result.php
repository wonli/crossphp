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

    public $reasone;
    public $code = 1;

    public $data = [];

    /**
     * @param mixed $reasone
     */
    public function setReasone($reasone)
    {
        $this->reasone = $reasone;
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
            'reason' => $this->reasone,
            'data' => $this->data,
        );
    }
}