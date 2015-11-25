<?php

namespace yii\jquery\multipleselect\tests;

use yii\web\Request as YiiRequest;

class Request extends YiiRequest
{

    /**
     * @var bool
     */
    private $_isAjax = false;

    /**
     * @inheritdoc
     */
    public function getIsAjax()
    {
        return $this->_isAjax;
    }

    /**
     * @param bool $isAjax
     */
    public function setIsAjax($isAjax)
    {
        $this->_isAjax = $isAjax;
    }
}
