<?php

namespace yii\jquery\multiple_select;


class JquerySelect extends JqueryMultipleSelect
{

    public function run()
    {
        $this->clientOptions['single'] = true;
        return parent::run();
    }
}
