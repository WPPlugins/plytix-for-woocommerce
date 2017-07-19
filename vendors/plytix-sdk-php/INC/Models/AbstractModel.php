<?php
abstract class AbstractModel {

    /**
     * @param AbstractModel $obj
     * @return mixed
     */
    abstract function modelToJson(AbstractModel $obj);

    /**
     * @return string
     */
    function toJson()
    {
        return json_encode($this->objectToArray($this));
    }

    /**
     * Object To Array
     *
     * @param $obj
     * @return mixed
     */
    function objectToArray($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = array();
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? self::objectToArray($val) : $val;
            if ($val !== null) {
                $arr[$key] = $val;
            }
        }
        return $arr;
    }
}
