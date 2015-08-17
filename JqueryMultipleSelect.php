<?php

namespace yii\jquery\multiple_select;

use yii\helpers\Html,
    yii\widgets\InputWidget,
    yii\helpers\Json;


class JqueryMultipleSelect extends InputWidget
{

    public $options = ['class' => 'form-control'];

    public $clientOptions = [];

    public $items = [];

    public $selection = null;

    public function run()
    {
        $view = $this->getView();
        JqueryMultipleSelectAsset::register($view);
        $view->registerJs('jQuery(\'#' . $this->options['id'] . '\').multipleSelect(' . Json::htmlEncode($this->clientOptions) . ');');
        $this->options['multiple'] = true;
        if ($this->hasModel()) {
            return Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
        } else {
            return Html::listBox($this->name, $this->selection, $this->items, $this->options);
        }
    }
}
