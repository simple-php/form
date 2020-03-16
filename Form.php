<?php

namespace SimplePHP\Form;

use SimplePHP\Form\Field\ImgFile;

class Form
{
    static $renderTypes = [
        'default' => __NAMESPACE__.'\\Render\\DefaultRenderer',
        'bootstrapHorizontal' => __NAMESPACE__.'\\Render\\BootstrapHorizontal'
    ];

    static $fieldTypes = [
        'checklist' => __NAMESPACE__.''\\Field\\Checklist',
        'date' => __NAMESPACE__.'\\Field\\Date',
        'dateInput' => __NAMESPACE__.'\\Field\\DateInput',
        'datetime' => __NAMESPACE__.'\\Field\\Datetime',
        'duration' => __NAMESPACE__.'\\Field\\Duration',
        'file' => __NAMESPACE__.''\\Field\\Form',
        'imgFile' => __NAMESPACE__.''\\Field\\ImgFile',
        'summernote' => __NAMESPACE__.''\\Field\\SummerNote',
        'summerNote' => __NAMESPACE__.''\\Field\\SummerNote',
    ];

    /**
     * Данные не кодируются. Это значение применяется при отправке файлов.
     */
    const ENCTYPE_MULTIPART_FORM_DATA = 'multipart/form-data';

    /**
     * Вместо пробелов ставится +, символы вроде русских букв кодируются их
     * шестнадцатеричными значениями (например, %D0%90%D0%BD%D1%8F вместо Аня).
     */
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     * Пробелы заменяются знаком +, буквы и другие символы не кодируются
     */
    const ENCTYPE_TEXT_PLAIN = 'text/plain';

    static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public $fields = [];
    public $options = [
        'id' => '',
        'method' => 'POST',
        'begin' => '',
        'end' => '',
        'submit' => 'Отправить',
        'action' => '',
        'form' => true,
        'enctype' => '',
        /*'renderer' => 'default',
        'renderOptions' => null,*/
        'renderer' => 'bootstrapHorizontal',
        'renderOptions' => ['label' => 3, 'size' => 'sm'],
        'languages' => null
    ];
    public $errors = [];
    public $renderer = null;

    protected $_values = array();

    protected $_script = [
        'onload' => ''
    ];

    function __construct($attr = array())
    {
        if (isset($attr['fields'])) {
            $this->fields = $attr['fields'];
        }
        foreach ($this->options as $key => $value) {
            if (isset($attr[$key])) {
                $this->options[$key] = $attr[$key];
            }
        }
    }

    function render()
    {
        if (!isset($this->renderer)) {
            $renderer = $this->options['renderer'];
            if (isset(self::$renderTypes[$renderer]) && self::$renderTypes[$renderer]) {
                $className = self::$renderTypes[$renderer];
            } elseif (class_exists($renderer)) {
                $className = $renderer;
            } else {
                throw new Exception('Form Renderer '.$renderer.' not found!');
            }

            $this->renderer = new $className($this);
        }
        return $this->renderer->render($this);
    }

    function renderErrors() {
        $html = '';
        foreach ($this->errors as $name => &$err) {
            foreach ($err as $validatorName => &$msg) {
                $html .= '<li>'.
                    (isset($this->fields[$name]['label']) ? $this->fields[$name]['label'].' ': '')
                    .$msg.'</li>';
            }
            unset($msg);
        }
        return empty($html) ? $html : '<ul>'.$html.'</ul>';
    }

    function populate($data, $ignore = []) {
        foreach ($this->fields as $key => $el) {
            if (in_array($key, $ignore)) continue;
            if (isset($data[$key])) {
                $this->$key = $data[$key];
            }
        }
    }

    function getValue($name)
    {
        $value = null;
        if (isset($this->fields[$name]['value'])) {
            $value = $this->fields[$name]['value'];
        }
        if (isset($this->_values[$name])) {
            $value = $this->_values[$name];
        }
        if (self::isPost()) {
            if (isset($this->fields[$name]['type']) &&
                ($this->fields[$name]['type'] === 'file' ||
                    $this->fields[$name]['type'] === 'imgFile')
            )
            {
                $files = self::getFiles($name);
                foreach ($files as $key => $file) {
                    $value = $file['tmp_name'];
                }
            } else {
                if (!isset($this->fields[$name]['disabled']) ||
                    !$this->fields[$name]['disabled']) {
                    $value = @$_POST[$name];
                }
            }
        } else {
            if (isset($_GET[$name])) $value = $_GET[$name];
        }
        return $value;
    }

    function setValue($name, $value)
    {
        $this->_values[$name] = $value;
    }

    function __get($name) {
        return $this->getValue($name);
    }

    function __set($name, $value) {
       $this->setValue($name, $value);
    }

    function toArray($ignoreFields = []) {
        $arr = [];
        foreach ($this->fields as $name => &$f) {
            if (in_array($name, $ignoreFields)) continue;
            $arr[$name] = $this->getValue($name);
            if ($this->fields[$name]['type'] == 'date') {
                $arr[$name] = date('Y-m-d', strtotime($arr[$name]));
            } elseif ($this->fields[$name]['type'] == 'datetime') {
                $arr[$name] = date('Y-m-d H:i:s', strtotime($arr[$name]));
            }
            if ($this->fields[$name]['type'] == 'checkbox') {
                $arr[$name] = $this->getValue($name) ? 1 : 0;
            }
        }
        unset($f);
        return $arr;
    }

    function validate()
    {
        foreach ($this->fields as $name => &$element) {
            if (!is_array($element)) {
                if ($this->getValue($name) != $element) $this->errors[$name]['value'] =
                    '<strong>'.$name.'</strong> имеет неверное значение';
                continue;
            }
            $v = $this->getValue($name);

            if (isset($element['required']) && $element['required']) {
                if (!isset($element['disabled']) || !$element['disabled']) {
                    if ($element['type'] === 'imgFile' ||
                        $element['type'] === 'file'
                    ) {
                        if ((!isset($element['src']) || $element['src'] === '') && !self::hasFile($name)) {
                            $this->errors[$name]['required'] = is_string($element['required']) ? $element['required']
                                : '<strong>' . $element['label'] . '</strong> является обязательным полем - требуется загрузить файл';
                        }
                    } else
                        if (empty($v) && strlen($v) === 0) {
                            $this->errors[$name]['required'] = is_string($element['required']) ? $element['required']
                                : '<strong>' . $element['label'] . '</strong> является обязательным полем - требуется ввести значение';
                        }
                }
            }
            if ($element['type'] === 'number') {
                $v = intval($v);
                if (isset($element['min'])) {
                    if ($v < $element['min']) {
                        $this->errors[$name]['min'] = '<strong>' . $element['label'] . '</strong> минимальное допустимое значение - ' . $element['min'];
                    }
                }
                if (isset($element['max'])) {
                    if ($v < $element['max']) {
                        $this->errors[$name]['max'] = '<strong>' . $element['label'] . '</strong> максимальное допустимое значение - ' . $element['min'];
                    }
                }
            }

            if ($element['type'] === 'imgFile' ||
                $element['type'] === 'file') {
                $isValidFile = self::validateFile($name, $this);

                if ($isValidFile && $element['type'] === 'imgFile') {
                    ImgFile::validate($name, $this);
                }
            }



            if (!empty($element['validators'])) {
                foreach ($element['validators'] as $validatorName => $validatorParams) {
                    $r = call_user_func($validatorName, $v, $validatorParams, $this);
                    if ($r !== TRUE && $r !== '') {
                        $this->errors[$name][$validatorName] = is_string($r) ? $r :
                            (is_string($validatorParams) ? $validatorParams : 'ошибка валидации '.$validatorName);
                    }
                }
            }
        }
        unset($element);
        return empty($this->errors);
    }

    function getDefaultId($name, $type) {
        $prefixes = [
            'text' => 'txt',
            'checkbox' => 'chk',
            'radio' => 'rad',
            'password' => 'psw',
            'number' => 'num',
            'datetime' => 'dtm',
            'time' => 'time',
            'month' => 'mon',
            'color' => 'cl',
            'select' => 'sel',
        ];
        $prefix = isset($prefixes[$type]) ? $prefixes[$type] : (($type !== '') ? $type : '');
        return ($this->options['id'] ? $this->options['id'].'_': '').$prefix.ucfirst($name);
    }

    static function attr($html, $attr, $excludeAttr = null) {
        if (!empty($attr)) {
            foreach ($attr as $a => $v) {
                if ($a === 'type' ||
                    $a === 'render' ||
                    $a === 'label' ||
                    $a === 'name' ||
                    $a === 'value' ||
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


                $html->$a($v);
            }
        }
        return $html;
    }

    const FILESAVE_ERROR = 999;

    static protected $_files = [];
    static protected $_fileErrors = [];

    static protected function _addFileError($name, $key, $error)
    {
        if (isset($key) && !isset(self::$_fileErrors[$name])) self::$_fileErrors[$name] = [];
        $errMsg = 'Неизвестная ошибка';
        switch ($error) {
            case UPLOAD_ERR_NO_FILE: return;
            //$errMsg = 'Файл не загружен'; break;
            case UPLOAD_ERR_CANT_WRITE:
                $errMsg = 'Не могу записать файл'; break;
            case UPLOAD_ERR_EXTENSION:
                $errMsg = 'Недопустимое расширение файла'; break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errMsg = 'Превышен размер файла'; break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errMsg = 'Отсутствует временный каталог'; break;
            case UPLOAD_ERR_PARTIAL:
                $errMsg = 'Файл не загружен полностью'; break;
            case self::FILESAVE_ERROR:
                $errMsg = 'Не удалось сохранить файл'; break;
        }
        if (isset($key)) {
            self::$_fileErrors[$name][$key] = $errMsg;
        } else {
            self::$_fileErrors[$name] = $errMsg;
        }
    }

    static function getFiles($name) {
        if (isset(self::$_files[$name])) return self::$_files[$name];

        $files = [];
        if (isset($_FILES[$name])) {
            if (is_array($_FILES[$name]['tmp_name'])) {
                foreach ($_FILES[$name]['error'] as $key => $error) {
                    if ($error == UPLOAD_ERR_OK && is_uploaded_file($_FILES[$name]['tmp_name'][$key])) {
                        $files[$key] = [
                            'tmp_name' => $_FILES[$name]['tmp_name'][$key],
                            'name' => $_FILES[$name]['name'][$key],
                            'size' => $_FILES[$name]['size'][$key],
                        ];
                    } else {
                        self::_addFileError($name, $key, $error);
                    }
                }
            } else {
                if ($_FILES[$name]['error'] == UPLOAD_ERR_OK &&
                    is_uploaded_file($_FILES[$name]['tmp_name'])) {
                    $files[] = $_FILES[$name];
                } else {
                    self::_addFileError($name, null, $_FILES[$name]['error']);
                }
            }
        };
        self::$_files[$name] = $files;
        return $files;
    }

    static function hasFile($name) {
        $f = self::getFiles($name);
        return !empty($f);
    }

    /*function getFile($name) {
        return $_FILES[$name];
    }

    function saveFile($name, $path) {
        $files = self::getFiles($name);
        $saved = [];
        foreach ($files as $key => $file) {
            move_uploaded_file($file['tmp_name'], $path)
        }
        return $saved;
    }*/

    static function hasFileErrors() {
        return !empty(self::$_fileErrors);
    }

    static function renderFileErrors($form) {
        $html = '';
        foreach (self::$_fileErrors as $name => &$err) {
            if (is_array($err)) {
                foreach ($err as $key => $msg) {
                    $html .= '<li><strong>' .
                        ((isset($form->fields[$name]['label'])) ? $form->fields[$name]['label'] : $name)
                        . '</strong> ' . $msg . '</li>';
                }
            } else {
                $html .= '<li><strong>' .
                    ((isset($form->fields[$name]['label'])) ? $form->fields[$name]['label'] : $name)
                    . '</strong> ' . $err . '</li>';
            }
        }
        return empty($html) ? $html : '<ul>'.$html.'</ul>';
    }

    static function saveFiles($name, $path) {
        $uploadDir =  realpath($_SERVER['DOCUMENT_ROOT']) . $path;
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $files = self::getFiles($name);
        $savedFiles = [];
        foreach ($files as $key => &$file) {
            $originalFilename = urldecode($file['name']);
            $pathinf = pathinfo($originalFilename);
            $filename = md5($pathinf['filename']);
            $fileext = $pathinf['extension'];
            $i = 1;
            $savename = $filename . '.' . $fileext;

            while (file_exists($uploadDir . DIRECTORY_SEPARATOR . $savename)) {
                $i++;
                $savename = $filename . $i . '.' . $fileext;
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $savename)) {
                $savedFiles[] = $path . '/' . $savename;
            } else {
                self::_addFileError($name, $key, self::FILESAVE_ERROR);
            }
        }
        unset($file);
        if (empty($savedFiles)) {
            return false;
        } elseif (count($savedFiles) == 1) {
            return $savedFiles[0];
        } else {
            return $savedFiles;
        }
    }

    static function getFileExtension($file_name){
        $ext = explode('.', $file_name);
        $ext = array_pop($ext);
        return strtolower($ext);
    }

    static function validateFile($name, $form)
    {
        if (self::hasFile($name)) {
            $files = self::getFiles($name);
            if (count($files) > 1 && !$form->fields[$name]['multiple']) {
                $form->errors[$name]['multiple'] = 'Можно загрузить только один файл';
            }
            $allowed_ext = (isset($form->fields[$name]['allowedExtensions']) ?
                $form->fields[$name]['allowedExtensions'] : null);

            if (isset($allowed_ext) && !is_array($allowed_ext)) {
                $allowed_ext = explode(',', $allowed_ext);
            }
            if (!empty($allowed_ext)) {
                foreach ($files as $file) {
                    if (!in_array(self::getFileExtension($file['name']), $allowed_ext)) {
                        $form->errors[$name]['allowedExtensions'] = 'Разрешена загрузка следующих форматов: ' . implode(',', $allowed_ext);
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
       

}