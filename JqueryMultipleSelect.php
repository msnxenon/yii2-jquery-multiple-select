<?php

namespace yii\jquery\multiple_select;

use yii\helpers\Html,
    yii\widgets\InputWidget,
    yii\helpers\Json;


class JqueryMultipleSelect extends InputWidget
{

    public $items = [];

    public $selection = null;

    public $clientOptions = [];

    public function run()
    {
        $view = $this->getView();
        JqueryMultipleSelectAsset::register($view);
        $view->registerJs('jQuery(\'#' . $this->options['id'] . '\').multipleSelect(' . Json::htmlEncode($this->clientOptions) . ');');
        if ($this->hasModel()) {
            return Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            return Html::dropDownList($this->name, $this->selection, $this->items, $this->options);
        }
    }
}
