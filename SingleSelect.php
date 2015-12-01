<?php

namespace yii\jquery\multipleselect;

class SingleSelect extends MultipleSelect
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->clientOptions['single'] = true;
        parent::init();
    }
}
