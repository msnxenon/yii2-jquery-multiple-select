<?php

namespace yii\jquery\multiple_select;

use yii\helpers\Html,
    yii\widgets\InputWidget,
    yii\helpers\Json,
    Yii;


class JqueryMultipleSelect extends InputWidget
{

    public $options = ['class' => 'form-control'];

    public $items = [];

    public $filter = false;

    public $clientOptions = [];

    public function run()
    {
        $inputId = $this->options['id'];
        $this->options['multiple'] = true;
        if ($this->hasModel()) {
            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
        } else {
            $output = Html::listBox($this->name, $this->value, $this->items, $this->options);
        }
        $this->clientOptions = array_merge([
            'filter' => $this->filter
        ], $this->clientOptions);
        $js = 'jQuery(\'#' . $inputId . '\').multipleSelect(' . Json::htmlEncode($this->clientOptions) . ');';
        if (Yii::$app->getRequest()->getIsAjax()) {
            $output .= Html::script($js);
        } else {
            $view = $this->getView();
            JqueryMultipleSelectAsset::register($view);
            $view->registerJs($js);
        }
        return $output;
    }
}
