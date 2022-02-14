<?php
declare(strict_types=1);

namespace Digicademy\Repository\Xhtml\Elements;

use Digicademy\Repository\Xhtml\Element as BaseElement;

class Question extends BaseElement
{
    public function validate(string &$message=''): bool
    {
        if ($this->getParent()->getName() != 'quiz'){
            $message = 'Question must be inside a Quiz Element, currently in : ' . $this->getParent()->getName();
            return false;
        }
        return true;
    }



    public static int $_counter = 0;

    public function onTranslateStart(): string
    {
        Question::$_counter++;
        return PHP_EOL.'<div class="question" id="question-'.Quiz::$_counter.'-' . Question::$_counter . '">';
    }


    public function onTranslateData(string $data): string
    {
        $data = $this->_deepTrim($data);
        if (empty($data)) return '';
        return PHP_EOL.'  <div class="question-data">' . $data . '</div>';
    }

    public function onTranslateEnd(): string
    {
        return PHP_EOL.'</div>'. PHP_EOL;
    }

} 