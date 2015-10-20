<?php

namespace yii\jquery\multipleselect;

use yii\helpers\Html;
use yii\widgets\InputWidget;
use yii\helpers\Json;
use Yii;

class MultipleSelect extends InputWidget
{

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var bool
     */
    public $filter = false;

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        Html::addCssClass($this->options, 'form-control');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $inputId = $this->options['id'];
        $this->options['multiple'] = true;
        if ($this->hasModel()) {
            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
        } else {
            $output = Html::listBox($this->name, $this->value, $this->items, $this->options);
        }
        $clientOptions = array_merge([
            'filter' => $this->filter
        ], $this->clientOptions);
        $js = 'jQuery(\'#' . $inputId . '\').multipleSelect(' . Json::htmlEncode($clientOptions) . ');';
        if (Yii::$app->getRequest()->getIsAjax()) {
            $output .= Html::script($js);
        } else {
            $view = $this->getView();
            MultipleSelectAsset::register($view);
            $view->registerJs($js);
        }
        return $output;
    }
}
