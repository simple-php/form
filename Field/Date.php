<?php

namespace SimplePHP\Form\Field;

use SimplePHP\Assets;
use Nette\Utils\Html;
use SimplePHP\Form\Glyphicon;
use SimplePHP\Form\Form;

class Date {
    static function assets() {
        static $assetsIncluded = false;
        if ($assetsIncluded) return;
        $assetsIncluded = true;
        Assets::add('bootstrap-daterangepicker');
        Assets::addScript('
            $(function() {
                $(".simple-php-form_field_date-clear").on("click", function(e) {
                    var $div = $(this).parent();
                    $div.find("input[type=\"hidden\"]").val("");
                    $div.find("span").text("");
                    $div.find(".simple-php-form_field_date-clear").hide();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    e.preventDefault();
                });
            });
        ','SimplePHP_Form_Field_Date');
    }

    static function getMinMax($attr) {
        $r = '';
        if (isset($attr['min'])) {
            $r .= ' minDate: "'.$attr['min'].'",';
        }
        if (isset($attr['max'])) {
            $r .= ' maxDate: "'.$attr['max'].'",';
        }
        return $r;

    }
    static function getFormat($attr) {
        return isset($attr['format']) ? $attr['format'] : 'DD.MM.YYYY';

    }

    static function renderClearButton($div, $attr) {
        if ((!isset($attr['disabled']) || !$attr['disabled'])
            && (!isset($attr['required']) || !$attr['required'])) {
            $div->addHtml(
                Html::el('a', ['class' => 'simple-php-form_field_date-clear'])->addHtml(
                    Glyphicon::remove
                )->href('#')
                ->style('float: right; margin-left: 8px;margin-top:1px;position:relative;color:#444')
            );
        }
    }

    static function render($name, $value, $attr) {
        static::assets();
        $disabled = false;
        if (isset($attr['disabled']) && $attr['disabled']) {
            $disabled = true;
        }

        $span =  Html::el('span')->style('font-size: 12px;');
        $id = $attr['id'];
        $div = Html::el('div');
        $div->id($id.'_div')->disabled($disabled)
            ->setClass('form-control simple-php-form_field_date')
            ->style('overflow: hidden; cursor: pointer; text-overflow: ellipsis')
            ->addHtml(Glyphicon::calendar);

        static::renderClearButton($div, $attr);

        $div->addHtml(
            Html::el('b', ['class' => 'caret'])->style('float: right; margin-top: 7px').
            $span
        );

        $input = Form::attr(Html::el('input')->type('hidden')->name($name), $attr);

        $input->id($id);
        if ($value) $input->value(date('d.m.Y', strtotime($value)));

        Assets::addScript('
            $(function() {
                moment.locale("ru");'.($disabled ? '' : '
                $("#'.$id.'_div").daterangepicker({
                    locale: {
                        format: "'. static::getFormat($attr) .'",
                        separator: " - ",
                        applyLabel: "Применить",
                        cancelLabel: "Сброс",
                        weekLabel: "W",
                        daysOfWeek: moment.weekdaysMin(),
                        monthNames: moment.months(),
                        firstDay: moment.localeData().firstDayOfWeek()
                    },'.($value ? '
                    startDate: "'.date('d.m.Y', strtotime($value)).'",':'').'
                    '. static::getMinMax($attr) .'
                    singleDatePicker: true,
                    showDropdowns: true,
                    alwaysShowCalendars: true,
                    showCustomRangeLabel: false
                }, function(date) {
                    var v = moment(date).format("L");
                    $("#'.$id.'_div .simple-php-form_field_date-clear").show();
                    $("#'.$id.'").val(v);
                    var $el = $("#'.$id.'_div span").text(moment(date).format("LL"));
                });').'
                
                if ($("#'.$id.'").val() != "") {
                    var v = $("#'.$id.'").val();
                    $("#'.$id.'_div .simple-php-form_field_date-clear").show();

                    var date = moment(v,"DD.MM.YYYY");

                    $("#'.$id.'_div span").text(date.format("LL"));
                } else {
                    $("#'.$id.'_div .simple-php-form_field_date-clear").hide();
                }
            });
        ',$id);

        return strval($div->addHtml($input));
    }

}