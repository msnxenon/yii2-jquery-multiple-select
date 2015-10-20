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
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#constructor
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter1
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter2
     */
    public $filter = false;

    /**
     * @var bool
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#constructor
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-multiple-items
     */
    public $multiple = false;

    /**
     * @var int
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#constructor
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
        $options = array_merge($this->options, ['multiple' => true]);
        if ($this->hasModel()) {
            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $options);
        } else {
            $output = Html::listBox($this->name, $this->value, $this->items, $options);
        }
        $clientOptions = array_merge([
            'filter' => $this->filter,
            'multiple' => $this->multiple,
            'multipleWidth' => $this->multipleWidth
        ], $this->clientOptions);
        if (array_key_exists('placeholder', $this->options)) {
            $clientOptions['placeholder'] = $this->options['placeholder'];
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
