<?php

namespace yii\jquery\multipleselect\tests;

use yii\widgets\ActiveForm;
use Exception;
use yii\jquery\multipleselect\MultipleSelect;
use yii\jquery\multipleselect\MultipleSelectAsset;
use yii\codeception\TestCase;
use yii\web\View;
use Yii;

class MultipleSelectTest extends TestCase
{

    public $appConfig = '@yii/jquery/multipleselect/tests/config.php';

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
    protected function getActual($mode, $selection, array $items = [], array $config = [])
    {
        switch ($mode) {
            case self::MODE_NAME_VALUE_AJAX:
            case self::MODE_MODEL_ATTRIBUTE_AJAX:
            case self::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                /* @var $request \yii\jquery\multipleselect\tests\Request */
                $request = Yii::$app->getRequest();
                $request->setIsAjax(true);
        }
        switch ($mode) {
            case self::MODE_NAME_VALUE:
            case self::MODE_NAME_VALUE_AJAX:
                MultipleSelect::$counter = 0;
                $actual = MultipleSelect::widget(array_merge($config, [
                    'name' => 'text',
                    'value' => $selection,
                    'items' => $items
                ]));
                return $actual;
            case self::MODE_MODEL_ATTRIBUTE_VALUE:
            case self::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $model = new TestForm;
                $model->text = '';
                if (array_key_exists('options', $config)) {
                    $config['options']['value'] = $selection;
                } else {
                    $config['options'] = ['value' => $selection];
                }
            case self::MODE_MODEL_ATTRIBUTE:
            case self::MODE_MODEL_ATTRIBUTE_AJAX:
                if (!isset($model)) {
                    $model = new TestForm;
                    $model->text = $selection;
                }
                ob_start();
                ob_implicit_flush(false);
                $form = ActiveForm::begin();
                $actual = (string)$form->field($model, 'text', ['template' => '{input}'])->widget(MultipleSelect::className(), array_merge($config, ['items' => $items]));
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
            case self::MODE_NAME_VALUE:
            case self::MODE_NAME_VALUE_AJAX:
                $id = 'w0';
                $name = 'text';
                return [$id, $name];
            case self::MODE_MODEL_ATTRIBUTE:
            case self::MODE_MODEL_ATTRIBUTE_AJAX:
            case self::MODE_MODEL_ATTRIBUTE_VALUE:
            case self::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $id = 'testform-text';
                $name = 'TestForm[text]';
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
            case self::MODE_MODEL_ATTRIBUTE:
            case self::MODE_MODEL_ATTRIBUTE_VALUE:
                $expectedHtml = '<input type="hidden" name="' . $name . '" value="">' . $expectedHtml;
                $expectedHtml = '<div class="form-group field-testform-text">' . "\n" . $expectedHtml . "\n" . '</div>';
            case self::MODE_NAME_VALUE:
                $this->assertEquals($expectedHtml, $actual);
                $view = Yii::$app->getView();
                $this->assertArrayHasKey(MultipleSelectAsset::className(), $view->assetBundles);
                $this->assertArrayHasKey(View::POS_READY, $view->js);
                $jsKey = md5($expectedJs);
                $this->assertArrayHasKey($jsKey, $view->js[View::POS_READY]);
                $this->assertEquals($expectedJs, $view->js[View::POS_READY][$jsKey]);
                return;
            case self::MODE_NAME_VALUE_AJAX:
                $expectedHtml .= '<script>' . $expectedJs . '</script>';
                $this->assertEquals($expectedHtml, $actual);
                return;
            case self::MODE_MODEL_ATTRIBUTE_AJAX:
            case self::MODE_MODEL_ATTRIBUTE_VALUE_AJAX:
                $expectedHtml .= '<script>' . $expectedJs . '</script>';
                $expectedHtml = '<input type="hidden" name="' . $name . '" value="">' . $expectedHtml;
                $expectedHtml = '<div class="form-group field-testform-text">' . "\n" . $expectedHtml . "\n" . '</div>';
                $this->assertEquals($expectedHtml, $actual);
                return;
        }
        throw new Exception;
    }

    /**
     * @return array
     */
    public function modeValueDataProvider()
    {
        $modes = [
            self::MODE_NAME_VALUE,
            self::MODE_NAME_VALUE_AJAX,
            self::MODE_MODEL_ATTRIBUTE,
            self::MODE_MODEL_ATTRIBUTE_AJAX,
            self::MODE_MODEL_ATTRIBUTE_VALUE,
            self::MODE_MODEL_ATTRIBUTE_VALUE_AJAX
        ];
        $items = [
            'So, we\'ll go no more a roving',
            'So late into the night,',
            'Though the heart be still as loving,',
            'And the moon be still as bright.'
        ];
        $data = [];
        foreach ($modes as $mode) {
            foreach (array_keys($items) as $selection) {
                $data[] = [$mode, $selection, $items];
            }
        }
        return $data;
    }

    /**
     * @param int $mode
     * @param string $selection
     * @param array $items
     * @dataProvider modeValueDataProvider
     */
    public function testWidget($mode, $selection, array $items = [])
    {
        $actual = $this->getActual($mode, $selection, $items);
        list ($id, $name) = $this->getIdName($mode);
        $selected = array_fill_keys(array_keys($items), '');
        $selected[$selection] = ' selected';
        $expectedHtml = <<<EXPECTED_HTML
<select id="$id" class="form-control" name="${name}[]" multiple size="4">
<option value="0"$selected[0]>So, we&#039;ll go no more a roving</option>
<option value="1"$selected[1]>So late into the night,</option>
<option value="2"$selected[2]>Though the heart be still as loving,</option>
<option value="3"$selected[3]>And the moon be still as bright.</option>
</select>
EXPECTED_HTML;
        $expectedJs = <<<EXPECTED_JS
jQuery('#$id').multipleSelect([]);
EXPECTED_JS;
        $this->checkExpected($mode, $actual, $expectedHtml, $expectedJs);
    }
}
