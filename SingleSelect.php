<?php

namespace yii\jquery\multipleselect;

class SingleSelect extends MultipleSelect
{

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->clientOptions['single'] = true;
        return parent::run();
    }
}
