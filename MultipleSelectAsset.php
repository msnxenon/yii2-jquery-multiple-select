<?php

namespace yii\jquery\multipleselect;

use yii\web\AssetBundle;

class MultipleSelectAsset extends AssetBundle
{

    public $sourcePath = '@bower/multiple-select';

    public $depends = ['yii\web\JqueryAsset'];

    public $js = ['multiple-select.js'];

    public $css = ['multiple-select.css'];
}
