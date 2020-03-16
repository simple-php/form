<?php

namespace SimplePHP\Form\Field;

use Nette\Utils\Html;
use SimplePHP\Form\Form;

class ImgFile {

    static function render($name, $value, $attr = null) {
        $html = '';
        $id = $attr['id'];
        $src = isset($attr['src']) ? $attr['src'] : '';
        if ($src != '') {
            $html .= '<a href="'.$src.'" target="_blank" title="Открыть в полный размер, в новом окне">
            <img src="' . $src . '" id="' . $id . '_image" style="max-width: 300px; max-height: 300px;"></a>';
            if (!isset($attr['required']) || !$attr['required']) {
                $html .= ' <label><input type="checkbox" name="' . $name . '_clear" id='
                    . $id . '_clear" value="yes">Удалить</label>';
            }
        }
        $exclAttr = [];
        if ($src !== '' && isset($attr['required']) && $attr['required']) $exclAttr = ['required'];
        $html .= '<div class="input-group"><div class="input-group-addon">Загрузить '.
            (($src != '') ? 'другое ': '').'изображение: </div>'
            . Form::attr(Html::el('input')->type('file')->setClass('form-control')->name($name), $attr, $exclAttr) . '</div>';
        return $html;
    }

    static function validate($name, $form)
    {
        if (Form::hasFile($name)) {
            if (@is_array($size = getimagesize($form->getValue($name)))) {
                if (isset($form->fields[$name]['max'])) {
                    list($w, $h) = explode($form->fields[$name]['max']);
                    if ($size[0] > $w || $size[0] > $h) {
                        $form->errors[$name]['max'] = '<strong>' . $form->fields[$name]['label'] .
                            ':</strong> Выбранное изображение имеет больший размер чем ' . $w . 'x' . $h;
                    }
                }
                if (isset($form->fields[$name]['min'])) {
                    list($w, $h) = explode($form->fields[$name]['min']);
                    if ($size[0] < $w || $size[0] < $h) {
                        $form->errors[$name]['min'] = '<strong>' . $form->fields[$name]['label'] .
                            ':</strong> Выбранное изображение имеет меньший размер чем ' . $w . 'x' . $h;
                    }
                }
            } else {
                $form->errors[$name]['imgFile'] = '<strong>' . $form->fields[$name]['label'] .
                    ':</strong> Выбранный файл не является изображением';
            }
        }
    }

}