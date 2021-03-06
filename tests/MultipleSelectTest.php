<?php

namespace yii\jquery\multipleselect\tests;

use yii\widgets\ActiveForm;
use Exception;
use yii\helpers\Html;
use yii\jquery\multipleselect\MultipleSelect;
use yii\jquery\multipleselect\MultipleSelectAsset;
use yii\phpunit\TestCase;
use yii\web\View;
use Yii;

class MultipleSelectTest extends TestCase
{

    const MODE_NAME_VALUE = 1;
    const MODE_NAME_VALUE_AJAX = 2;
    const MODE_MODEL_ATTRIBUTE = 3;
    const MODE_MODEL_ATTRIBUTE_AJAX = 4;
    const MODE_MODEL_ATTRIBUTE_VALUE = 5;
    const MODE_MODEL_ATTRIBUTE_VALUE_AJAX = 6;

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @param array $config
     * @return string
     */
    protected function getActual($mode, $selection, array $items, array $config = [])
    {
        switch ($mode) {
            case static::MODE_NAME_VALUE_AJAX:
            case static::MODE_MODEL_ATTRIBUTE_AJAX:
            case static::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                /* @var $request \yii\jquery\multipleselect\tests\Request */
                $request = Yii::$app->getRequest();
                $request->setIsAjax(true);
        }
        switch ($mode) {
            case static::MODE_NAME_VALUE:
            case static::MODE_NAME_VALUE_AJAX:
                MultipleSelect::$counter = 0;
                return MultipleSelect::widget(array_merge($config, [
                    'name' => 'number',
                    'value' => $selection,
                    'items' => $items
                ]));
            case static::MODE_MODEL_ATTRIBUTE_VALUE:
            case static::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $model = new TestForm;
                if (array_key_exists('options', $config)) {
                    $config['options']['value'] = $selection;
                } else {
                    $config['options'] = ['value' => $selection];
                }
            case static::MODE_MODEL_ATTRIBUTE:
            case static::MODE_MODEL_ATTRIBUTE_AJAX:
                if (!isset($model)) {
                    $model = new TestForm;
                    $model->number = $selection;
                }
                ob_start();
                ob_implicit_flush(false);
                $form = ActiveForm::begin();
                $actual = (string)$form->field($model, 'number', ['template' => '{input}'])->widget(MultipleSelect::className(), array_merge($config, ['items' => $items]));
                ActiveForm::end();
                ob_end_clean();
                return $actual;
        }
        throw new Exception;
    }

    /**
     * @param int $mode
     * @return array
     */
    protected function getIdName($mode)
    {
        switch ($mode) {
            case static::MODE_NAME_VALUE:
            case static::MODE_NAME_VALUE_AJAX:
                $id = 'w0';
                $name = 'number';
                return [$id, $name];
            case static::MODE_MODEL_ATTRIBUTE:
            case static::MODE_MODEL_ATTRIBUTE_AJAX:
            case static::MODE_MODEL_ATTRIBUTE_VALUE:
            case static::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $id = 'testform-number';
                $name = 'TestForm[number]';
                return [$id, $name];
        }
        throw new Exception;
    }

    /**
     * @param int $mode
     * @param string $actual
     * @param string $expectedHtml
     * @param string $expectedJs
     */
    protected function checkExpected($mode, $actual, $expectedHtml, $expectedJs)
    {
        list ($id, $name) = $this->getIdName($mode);
        switch ($mode) {
            case static::MODE_MODEL_ATTRIBUTE:
            case static::MODE_MODEL_ATTRIBUTE_VALUE:
                $expectedHtml = '<input type="hidden" name="' . $name . '" value="">' . $expectedHtml;
                $expectedHtml = '<div class="form-group field-testform-number">' . "\n" . $expectedHtml . "\n" . '</div>';
            case static::MODE_NAME_VALUE:
                $this->assertEquals($expectedHtml, $actual);
                $view = Yii::$app->getView();
                $this->assertArrayHasKey(MultipleSelectAsset::className(), $view->assetBundles);
                $this->assertArrayHasKey(View::POS_READY, $view->js);
                $jsKey = md5($expectedJs);
                $this->assertArrayHasKey($jsKey, $view->js[View::POS_READY]);
                $this->assertEquals($expectedJs, $view->js[View::POS_READY][$jsKey]);
                return;
            case static::MODE_NAME_VALUE_AJAX:
                $expectedHtml .= '<script>' . $expectedJs . '</script>';
                $this->assertEquals($expectedHtml, $actual);
                return;
            case static::MODE_MODEL_ATTRIBUTE_AJAX:
            case static::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $expectedHtml .= '<script>' . $expectedJs . '</script>';
                $expectedHtml = '<input type="hidden" name="' . $name . '" value="">' . $expectedHtml;
                $expectedHtml = '<div class="form-group field-testform-number">' . "\n" . $expectedHtml . "\n" . '</div>';
                $this->assertEquals($expectedHtml, $actual);
                return;
        }
        throw new Exception;
    }

    /**
     * @return array
     */
    public function modeSelectionItemsDataProvider()
    {
        $modes = [
            static::MODE_NAME_VALUE,
            static::MODE_NAME_VALUE_AJAX,
            static::MODE_MODEL_ATTRIBUTE,
            static::MODE_MODEL_ATTRIBUTE_AJAX,
            static::MODE_MODEL_ATTRIBUTE_VALUE,
            static::MODE_MODEL_ATTRIBUTE_VALUE_AJAX
        ];
        $items = [
            'If you hide your ignorance, no one will hit you and you\'ll never learn.',
            'I don\'t talk things, sir. I talk the meaning of things.'
        ];
        $selections = array_merge([null, ''], array_keys($items));
        $data = [];
        foreach ($modes as $mode) {
            foreach ($selections as $selection) {
                $data[] = [$mode, $selection, $items];
            }
        }
        return $data;
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidget($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetClass($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'options' => ['class' => 'something']
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="something form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetPlaceholder($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'options' => ['placeholder' => 'Please choose']
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"placeholder":"Please choose"});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetPlaceholderIgnored($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'options' => ['placeholder' => 'Please choose'],
            'clientOptions' => ['placeholder' => '']
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"placeholder":""});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetDisabledTrue($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'options' => ['disabled' => true]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" disabled multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetDisabledFalse($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'options' => ['disabled' => false]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetFilterTrue($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'filter' => true
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"filter":true});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetFilterFalse($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'filter' => false
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetFilterTrueIgnored($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'filter' => true,
            'clientOptions' => ['filter' => false]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"filter":false});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleTrue($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multiple' => true
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"multiple":true});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleFalse($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multiple' => false
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleTrueIgnored($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multiple' => true,
            'clientOptions' => ['multiple' => false]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"multiple":false});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleWidth100($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multipleWidth' => 100
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"multipleWidth":100});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleWidth80($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multipleWidth' => 80
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetMultipleWidth100Ignored($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'multipleWidth' => 100,
            'clientOptions' => ['multipleWidth' => 80]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"multipleWidth":80});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeSelectionItemsDataProvider
     */
    public function testWidgetClientOptions($mode, $selection, array $items)
    {
        $actual = $this->getActual($mode, $selection, $items, [
            'clientOptions' => [
                'selectAllText' => 'Выбрать все',
                'allSelected' => 'Все выбрано',
                'countSelected' => '# из % выбрано'
            ]
        ]);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $encodedItems = array_map(function ($item) {
            return Html::encode($item);
        }, $items);
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="{$name}[]" multiple size="4">
<option value="0"$selected[0]>$encodedItems[0]</option>
<option value="1"$selected[1]>$encodedItems[1]</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect({"selectAllText":"Выбрать все","allSelected":"Все выбрано","countSelected":"# из % выбрано"});
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }
}
