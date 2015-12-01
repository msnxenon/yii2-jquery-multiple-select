<?php

namespace yii\jquery\multipleselect;

use yii\helpers\Html;
use yii\widgets\InputWidget;
use yii\helpers\Json;
use yii\base\NotSupportedException;
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
        $this->options['multiple'] = true;
        $this->clientOptions = array_merge(array_diff_assoc([
            'filter' => $this->filter,
            'multiple' => $this->multiple,
            'multipleWidth' => $this->multipleWidth
        ], get_class_vars(__CLASS__)), $this->clientOptions);
        if (array_key_exists('placeholder', $this->options)) {
            if (!array_key_exists('placeholder', $this->clientOptions)) {
                $this->clientOptions['placeholder'] = $this->options['placeholder'];
            }
            unset($this->options['placeholder']);
        }
        parent::init();
    }

    /**
     * @inheritdoc
     * @throw NotSupportedException
     */
    public function run()
    {
        $inputId = $this->options['id'];
        if ($this->hasModel()) {
            if (array_key_exists('value', $this->options)) {
                if (!isset($this->model->{$this->attribute})) {
                    throw new NotSupportedException("Unable to set value of the property '{$this->attribute}'.");
                }
                $buffer = $this->model->{$this->attribute};
                $this->model->{$this->attribute} = $this->options['value'];
                unset($this->options['value']);
            }
            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);
            if (isset($buffer)) {
                $this->model->{$this->attribute} = $buffer;
            }
        } else {
            $output = Html::listBox($this->name, $this->value, $this->items, $this->options);
        }
        $js = 'jQuery(\'#' . $inputId . '\').multipleSelect(' . Json::htmlEncode($this->clientOptions) . ');';
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
