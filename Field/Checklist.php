<?php

namespace SimplePHP\Form\Field;

use SimplePHP\Assets;

class Checklist {

    static function assets() {
        static $registered = false;
        if ($registered) return;
        $registered = true;
        Assets::addStyle('
            .listbox ul {
                margin-bottom: 5px;
                /*margin-right: 5px;*/
            }
            .listbox .checkbox {
                margin: 0 !important;
                padding: 3px 10px;
            }
            .listbox .checkbox:hover {
                background-color: #CDF;
            }
            .listbox label {
                display:block;
                margin: 0;
            }
            .listbox .group {
                background: #EEE;
                margin: 0;
                padding:3px 10px;
                border-bottom: 1px solid #CCC;
                clear: left;
            }
            .listbox {
                padding:0 !important;
            }
        ');

        Assets::addScript('
            function checklistClick(el) {
                var groupid = $(el).attr("data-group-id");
                var list = $(el).parents(".listbox");
                if (groupid) {
                    $("#"+groupid).prop("checked", false);
                } else {
                    groupid = $(el).attr("id");
                    list.find("input:checkbox[data-group-id=\\""+groupid+"\\"]")
                        .prop("checked", $(el).is(":checked"));
                }
                if (list.length > 0) {
                    var id = list.get(0).id;
                    $("#"+id+"_all").prop("checked", false);
                }
            }
            function checklistSelectAll(el) {
                var id = $(el).attr("id");
                if (id.substr(id.length-4,4) == "_all") {
                    id = id.substr(0, id.length - 4);
                    $("#"+id).find("input:checkbox")
                        .prop("checked", $(el).is(":checked"));
                }
            }       
        ', 'SimplePHP_Form_Field_Checklist');
    }

    static function checkbox($name, $title, $value = 1, $checked = false, $id = null, $attr = null)
    {
        if (!isset($id)) $id = 'chk'.$name;
        $html = '<label>
                    <input type="checkbox" id="'.$id.'" name="' . $name . '" value="'
            .$value.'" '.($checked ? ' checked': ''). self::renderAttr($attr) .  '> '.$title
            .'</label>';
        return $html;
    }

    static function renderAttr($attr, $excludeAttr = null) {
        $r = '';
        if (!empty($attr)) {
            foreach ($attr as $a => $v) {
                if ($a === 'type' ||
                    $a === 'label' ||
                    $a === 'name' ||
                    $a === 'value' ||
                    $a === 'id' ||
                    $a === 'options'||
                    $a === 'filter' ||
                    $a === 'after' ||
                    $a === 'before' ||
                    $a === 'checked' ||
                    $a === 'multilang' ||
                    $a === 'desc' ||
                    $a === 'inline' ||
                    $a === 'src' ||
                    $a === 'allowedExtensions' ||
                    $a === 'validators') continue;

                if (is_array($excludeAttr) && in_array($a, $excludeAttr)) continue;

                if ($a === 'required' ||
                    $a === 'readonly' ||
                    $a === 'disabled' ||
                    is_bool($v)
                ) {
                    if ($v) {
                        $r .= (($r === '') ? '' : ' ') . $a;
                    }
                    continue;
                }

                $r .= (($r === '') ? '' : ' ') . $a;
                $r .='="'.htmlspecialchars($v).'"';
            }
        }
        return empty($r) ? '' : ' '.$r;
    }

    static function render($name, $values, $attr) {
        self::assets();
        $html = '';
        $id = $attr['id'];
        $options = isset($attr['options']) ? $attr['options'] :[];
        $all = 'Выбрать все';
        if (isset($attr['all']))  $all = $attr['all'];
        if ($all) {
            $allChecked = false;
            if (isset($values['all'])) $allChecked = $values['all'];

            $html .= '<span class="checkbox" style="display:inline;margin-left:10px;">'
                . self::checkbox($name.'[all]', $all, 1, $allChecked, $id.'_all',
                    [
                        'onclick' => 'checklistSelectAll(this);'
                    ]).'</span>';
        }
        $html .= '<div id="'.$id.'" class="listbox inline form-control" 
                   style="height:auto;'.((isset($attr['height']) && $attr['height']) ? 'max-height:'.$attr['height'].';' : '').
            ((isset($attr['width']) && $attr['width'])? 'width:'.$attr['width'].';':'').'overflow:auto">
				';
        foreach ($options as $val => &$option) {
            if (is_array($option)) {
                if (isset($option['options'])) {

                    $groupId = $val;
                    $groupChecked = false;
                    if (isset($values[$groupId]['group'])) $groupChecked = $values[$groupId]['group'];
                    $label = $option['text'];
                    $groupOptions = $option['options'];
                    $groupCheck = true;
                    if (isset($attr['groupCheckboxes'])) $groupCheck = $attr['groupCheckboxes'];
                    if (isset($option['checkbox'])) $groupCheck = $option['checkbox'];
                } else {
                    $groupId = false;
                    $groupChecked = false;
                    $label = $val;
                    $groupOptions = $option;
                    $groupCheck = false;
                }
                $html .= '<ul class="nav">';
                $html.= '<li class="group">';
                if ($groupCheck) {
                    $html .= self::checkbox($name.'['.$groupId.'][group]', $label, 1,
                        $groupChecked,
                        $id.'_group'.$groupId,
                        [
                            'data-group' => $groupId,
                            'onclick' => 'checklistClick(this);'
                        ]);
                } else {
                    $html .= $label;
                }
                $html .= '</li>';

                foreach ($groupOptions as $val2 => $option2) {
                    if ($groupId !== false) {
                        $itemChecked = isset($values[$groupId][$val2]) && $values[$groupId][$val2];
                    } else {
                        $itemChecked = isset($values[$val2]) && $values[$val2];
                    }
                    $html .= '<li class="checkbox">'.
                        self::checkbox($name.'['.(($groupId !== false) ? $groupId.'][' :'') .$val2.']', $option2,
                            1, $groupChecked || $itemChecked,
                            $id.'_item'.(($groupId !== false) ? $groupId.'_' : '').$val2,
                            ($groupId !== false) ?
                                [
                                    'data-group-id' => $id.'_group'.$groupId,
                                    'onclick' => 'checklistClick(this);',
                                ]:[]).'</li>';
                }
                $html .= '</ul>';

            } else {
                $itemChecked = isset($values[$val]) && $values[$val];
                $html .= '<li class="checkbox">'.
                    self::checkbox($name.'['.$val.']', $option, 1, $itemChecked,
                        $id.'_item'.$val).'</li>';
            }
        }

        $html .= '</div>';


        if (isset($attr['inline']) && $attr['inline']) {
            Assets::addStyle('float:left','#'.$id.'.listbox .checkbox');
        }
        if (isset($attr['column-width']) && $attr['column-width']) {
            Assets::addStyle('
                width: '.$attr['column-width'].';
                float:left;
                border: 1px solid #DDD;
                margin-right: 5px;
            ', '#'.$id.'.listbox ul');
        }
        return $html;
    }
}