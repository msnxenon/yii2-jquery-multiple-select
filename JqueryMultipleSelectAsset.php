<?php

namespace yii\jquery\multiple_select;

use yii\web\AssetBundle;


class JqueryMultipleSelectAsset extends AssetBundle
{

    public $sourcePath = '@bower/multiple-select';

    public $depends = ['yii\web\JqueryAsset'];

    public $js = ['jquery.multiple.select.js'];

    public $css = ['multiple-select.css'];
}
