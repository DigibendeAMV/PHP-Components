<?php
declare(strict_types=1);

namespace Digicademy\Repository\Xhtml\Elements;

use Digicademy\Repository\Xhtml\Element as BaseElement;

class Answer extends BaseElement
{
    public static int $_counter = 0;

    private string $_elementName = '';
    private string $_id = '';
    private int $_value = 0;
    private string $_selected = '';
    private string $_status = 'unknown';


    public function onTranslateStart(): string
    {
        Answer::$_counter++;

        $this->_elementName =  'question-'. Quiz::$_counter. '-' . Question::$_counter;
        $this->_id = 'answer-'.Quiz::$_counter. '-' . Question::$_counter . '-' . Answer::$_counter ;
        $this->_value = (int) $this->getAttribute('value', '0');
        $this->_selected = '';
        if(isset($_GET[$this->_elementName])) {
            if ($_GET[$this->_elementName] == $this->_id) {
                $this->_selected = 'checked';
                if ($this->_value == 0) $this->_status = 'error';
                if ($this->_value == 1) $this->_status = 'correct';
            }
        }

        $html  = PHP_EOL.'  <div class="answer-container">';
        $html .= PHP_EOL.'    <input type="radio" '.$this->_selected.' name="'.$this->_elementName.'" class="answer" id="'.$this->_id.'" value="'. $this->_id.'">';
        $html .= PHP_EOL.'    <span class="answer-status  '.$this->_status.'">&nbsp;</span><label for="answer-'.Quiz::$_counter.'-'.Question::$_counter.'-'.Answer::$_counter.'" class="answer-data">';
        return $html;
    }

    public function onTranslateData(string $data): string
    {
        $data = $this->_deepTrim($data);
        if (empty($data)) return '';
        return  $data;
    }

    public function onTranslateEnd(): string
    {
        return  '</label>'.PHP_EOL.'  </div>';
    }


} 