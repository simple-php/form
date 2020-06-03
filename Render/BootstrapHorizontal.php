<?php

namespace SimplePHP\Form\Render;

use SimplePHP\Assets;
use Nette\Utils\Html;

class BootstrapHorizontal extends DefaultRenderer {
    static $labelClass = 'col-sm-2';
    static $fieldClass = 'col-sm-10';
    static $offsetClass = 'col-sm-offset-2';
    static $groupClass = '';

    static function label($attr) {
        $label = parent::label($attr);
        if (isset($label)) {
            if (isset($attr['render']['labelClass'])) {
                $label->setClass($attr['render']['labelClass']. ' control-label');
            } else {
                $label->setClass(self::$labelClass . ' control-label');
            }
        }
        return $label;
    }

    function decorateField($content, $attr, $name = null)
    {
        //$label = isset($attr['label']) ? $attr['label'] : '';
        $type = (isset($attr['type']) ? $attr['type'] : 'text');
        $renderLabel = true;
        $renderDesc = true;

        if (isset($attr['render']['label'])) $renderLabel = $attr['render']['label'];
        if (isset($attr['render']['desc'])) $renderLabel = $attr['render']['desc'];
        if ($type == 'hidden' || $type == 'checkbox') {
            $renderLabel = false;
            $renderDesc = false;
        }

        if ($type == 'summerNote' ||
            isset($attr['render']['fullwidth']) && $attr['render']['fullwidth']) {
            $label = parent::label($attr);
            return Html::el('div')->addHtml(
                Html::el('div')->addHtml($label)->addHtml($content)
                    ->addHtml(static::desc($attr))->setClass('col-xs-12')
            )->setClass( 'form-group'.self::$groupClass);
        }

        $label = null;
        $decor = Html::el('div')->setClass((isset($attr['render']['fieldClass']) ?
        $attr['render']['fieldClass'] : self::$fieldClass));
        if ($renderLabel !== false) {
            $label = static::label($attr);
        } else {
            $decor->setClass(self::$offsetClass);
        }


        if ($type != 'hidden') {
            $decor->addHtml($content);
            if ($renderDesc) $decor->addHtml(static::desc($attr));
            $div = Html::el('div');
            if ($label) $div->addHtml($label);
            $div->addHtml($decor)
                ->setClass('form-group'.self::$groupClass);
            if (isset($this->_form->errors[$name])) $div->addClass('has-error');
            return $div;
        }
        return $content;
    }

    function initFormClasses() {
        if (isset($this->_form->options['renderOptions']['label'])) {
            $labelOptions = $this->_form->options['renderOptions']['label'];
            if (!is_array($labelOptions)) {
                if (is_int($labelOptions) && $labelOptions > 0 && $labelOptions < 12) {
                    $labelOptions = ['sm' => $labelOptions];
                } else {
                    $labelOptions = ['sm' => 2];
                }
            }
        } else {
            $labelOptions = ['sm' => 2];
        }

        self::$fieldClass = '';
        self::$offsetClass = '';
        self::$labelClass = '';
        foreach ($labelOptions as $sz => $w) {
            self::$fieldClass .= 'col-'.$sz.'-'.(12-$w).' ';
            self::$labelClass .= 'col-'.$sz.'-'.$w.' ';
            self::$offsetClass .= 'col-'.$sz.'-offset-'.$w.' ';
        }

        if (isset($this->_form->options['renderOptions']['size'])) {
            $sz = $this->_form->options['renderOptions']['size'];
            self::$groupClass = ' form-group-'.$sz;

            Assets::addStyle('height: auto','.form-group-'.$sz.' textarea.form-control');
        }
    }

    function render()
    {
        $this->initFormClasses();
        $form = [];

        if ($this->_form->options['form']) {
            $form = Html::el('form')->setClass('form-horizontal');
            if ($this->_form->options['id']) $form->id($this->_form->options['id']);
            if ($this->_form->options['action']) $form->action($this->_form->options['action']);
            if ($this->_form->options['enctype']) $form->enctype($this->_form->options['enctype']);
            if ($this->_form->options['method']) $form->method($this->_form->options['method']);
        }

        if (isset($this->_form->options['begin'])) {
            $form[] = $this->_form->options['begin'];
        }

        // TODO: CSRF Protection
        //$html .= '<input type="hidden" name="_token" value="'.$this->createToken().'">';

        foreach ($this->_form->fields as $name => &$attr)
        {
            if (is_array($attr)) {
                $form[] = $this->renderField($name, $attr);
            } else {
                $form[] = Html::el('input')->type('hidden')->name($name)->value($attr);
            }
        }
        unset($attr);

        if (isset($this->_form->options['end'])) {
            $form[] = $this->_form->options['end'];
        }

        if ($this->_form->options['submit']) {
            $form[] = Html::el('div')->addHtml(
                Html::el('div')->addHtml(
                    Html::el('input')->type("submit")
                ->setClass("btn btn-default")->value($this->_form->options['submit'])
            )->setClass(self::$offsetClass.self::$fieldClass))
                ->setClass('form-group'.self::$groupClass);
        }

        return is_array($form) ? join('', $form): $form;
    }

    function renderErrors($errors) {
        if ($errors) {
            return '<div class="alert alert-danger">'.$errors.'</div>';
        }
        return '';
    }
}