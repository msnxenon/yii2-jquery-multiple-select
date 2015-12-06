<?php

namespace yii\jquery\multipleselect;

class SingleSelect extends MultipleSelect
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->clientOptions['single'] = true;
    }
}
