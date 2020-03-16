<?php

namespace SimplePHP\Form\Field;

use SimplePHP\Assets;
use Nette\Utils\Html;
use SimplePHP\Form\Helpers\Glyphicon;

class Datetime extends Date {

    static function render($name, $value, $attr) {
        static::assets();
        $disabled = false;
        if (isset($attr['disabled']) && $attr['disabled']) {
            $disabled = true;
        }
    
        $span =  Html::el('span')->style('font-size: 12px;');
        $id = $attr['id'];
        $div = Html::el('div')->id($id.'_div')
            ->disabled($disabled)
            ->setClass('form-control')
            ->style('overflow: hidden; cursor: pointer; text-overflow: ellipsis')
            ->addHtml(
                Glyphicon::calendar);


        if ($disabled || !isset($attr['required']) || !$attr['required']) {
            $div->addHtml(
                Html::el('a')->addHtml(
                    Glyphicon::remove
                )->setClass('simple-php-form_field_date-clear')
                    ->href('#')->style('float: right; margin-left: 8px;margin-top:1px;position:relative;color:#444')
            );
        }

        $div->addHtml(
            Html::el('b')->setClass('caret')->style('float: right; margin-top: 7px').
            $span
        );
        $input = Html::el('input')->type('hidden')->name($name);

        $input->id($id);
        if ($value) $input->value(date('d.m.Y H:i:s', strtotime($value)));
        Assets::addScript('
            $(function() {
                moment.locale("ru");'.($disabled ? '': '
                $("#'.$id.'_div").daterangepicker({
                    locale: {
                        format: "'. static::getFormat($attr) .'",
                        separator: " - ",
                        applyLabel: "Применить",
                        cancelLabel: "Отмена",
                        weekLabel: "W",
                        daysOfWeek: moment.weekdaysMin(),
                        monthNames: moment.months(),
                        firstDay: moment.localeData().firstDayOfWeek()
                    },'.($value ? '
                    startDate: "'.date('d.m.Y H:i:s', strtotime($value)).'",' : '').'
                    '. static::getMinMax($attr) .'
                    //cancelClass: "hidden",
                    singleDatePicker: true,
                    timePicker: true,
                    timePicker24Hour: true,
                    showDropdowns: true,
                    alwaysShowCalendars: true,
                    showCustomRangeLabel: false
                }, function(date) {
                    var v = moment(date).format("L")+" "+moment(date).format("LTS");
                    $("#'.$id.'_div .simple-php-form_field_date-clear").show();
                    $("#'.$id.'").val(v);
                    var $el = $("#'.$id.'_div span").text(moment(date).format("LLL"));
                });').'
                
                if ($("#'.$id.'").val() != "") {
                    var v = $("#'.$id.'").val();
                    $("#'.$id.'_div .simple-php-form_field_date-clear").show();
                    var date = moment(v,"DD.MM.YYYY HH:mm:ss");

                    $("#'.$id.'_div span").text(date.format("LLL"));
                } else {
                    $("#'.$id.'_div .simple-php-form_field_date-clear").hide();
                }
            });
        ',$id);

        return strval($div->addHtml($input));
    }

    static function getFormat($attr) {
        return isset($attr['format']) ? $attr['format'] : 'DD.MM.YYYY HH:mm:ss';

    }
    
}