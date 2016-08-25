<?php

namespace yii\jquery\multipleselect\tests;

use yii\web\Request as WebRequest;

class Request extends WebRequest
{

    /**
     * @var bool
     */
    private $isAjax = false;

    /**
     * @inheritdoc
     */
    public function getIsAjax()
    {
        return $this->isAjax;
    }

    /**
     * @param bool $isAjax
     */
    public function setIsAjax($isAjax)
    {
        $this->isAjax = $isAjax;
    }
}
