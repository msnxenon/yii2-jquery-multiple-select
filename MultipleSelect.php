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
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter1
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter2
     */
    public $filter = false;

    /**
     * @var bool
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-multiple-items
     */
    public $multiple = false;

    /**
     * @var int
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-multiple-items
     */
    public $multipleWidth = 80;

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
        $hasModel = $this->hasModel();
        if (array_key_exists('value', $this->options)) {
            $value = $this->options['value'];
        } elseif ($hasModel) {
            $value = Html::getAttributeValue($this->model, $this->attribute);
        } else {
            $value = $this->value;
        }
        $options = array_merge($this->options, [
            'multiple' => true,
            'value' => $value
        ]);
        if ($hasModel) {
            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $options);
        } else {
            $output = Html::listBox($this->name, $this->value, $this->items, $options);
        }
        $clientOptions = array_merge([
            'filter' => $this->filter,
            'multiple' => $this->multiple,
            'multipleWidth' => $this->multipleWidth
        ], $this->clientOptions);
        if (!array_key_exists('placeholder', $clientOptions) && array_key_exists('placeholder', $options)) {
            $clientOptions['placeholder'] = $options['placeholder'];
        }
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
