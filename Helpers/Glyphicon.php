<?php

namespace SimplePHP\Form\Helpers;

use Nette\Utils\Html;

class Glyphicon {
    const bookmark = '<i class="glyphicon glyphicon-bookmark"></i>';
    const calendar = '<i class="glyphicon glyphicon-calendar"></i>';
    const check = '<i class="glyphicon glyphicon-check"></i>';
    const edit = '<i class="glyphicon glyphicon-edit"></i>';
    const flag = '<i class="glyphicon glyphicon-flag"></i>';
    const flash = '<i class="glyphicon glyphicon-flash"></i>';
    const globe = '<i class="glyphicon glyphicon-globe"></i>';
    const hourglass = '<i class="glyphicon glyphicon-hourglass"></i>';
    const infoSign = '<i class="glyphicon glyphicon-info-sign"></i>';
    const play = '<i class="glyphicon glyphicon-play"></i>';
    const playCircle = '<i class="glyphicon glyphicon-play-circle"></i>';
    const plus = '<i class="glyphicon glyphicon-plus"></i>';
    const plusSign = '<i class="glyphicon glyphicon-plus-sign"></i>';
    const off = '<i class="glyphicon glyphicon-off"></i>';
    const ok = '<i class="glyphicon glyphicon-ok"></i>';
    const okCircle = '<i class="glyphicon glyphicon-ok-circle"></i>';
    const okSign = '<i class="glyphicon glyphicon-ok-sign"></i>';
    const questionSign = '<i class="glyphicon glyphicon-question-sign"></i>';
    const refresh = '<i class="glyphicon glyphicon-refresh"></i>';
    const remove = '<i class="glyphicon glyphicon-remove"></i>';
    const removeCircle = '<i class="glyphicon glyphicon-remove-circle"></i>';
    const removeSign = '<i class="glyphicon glyphicon-remove-sign"></i>';
    const share = '<i class="glyphicon glyphicon-share"></i>';
    const stats = '<i class="glyphicon glyphicon-stats"></i>';
    const tags = '<i class="glyphicon glyphicon-tags"></i>';


    static function icon($name) {
        return Html::el('i', ['class' => 'glyphicon glyphicon-'.$name]);
    }
}