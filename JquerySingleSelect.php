<?php

namespace yii\jquery\multiple_select;


class JquerySingleSelect extends JqueryMultipleSelect
{

    public function run()
    {
        $this->clientOptions['single'] = true;
        return parent::run();
    }
}
