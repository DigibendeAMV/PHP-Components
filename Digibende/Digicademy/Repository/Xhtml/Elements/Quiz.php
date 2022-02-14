<?php
declare(strict_types=1);

namespace Digicademy\Repository\Xhtml\Elements;

use Digicademy\Repository\Xhtml\Element as BaseElement;

class Quiz extends BaseElement
{

    public static int $_counter = 0;

    public function onTranslateStart(): string
    {
        Quiz::$_counter++;
        return PHP_EOL . '<form method="get"  class="quiz" id="quiz-'.Quiz::$_counter.'">'.PHP_EOL;
    }

    public function onTranslateData(string $data): string
    {
        $data = $this->_deepTrim($data);
        if (empty($data)) return '';
        return PHP_EOL. '<div class="quiz-data">' . $data  . '</div>' . PHP_EOL;
    }

    public function onTranslateEnd(): string
    {
        $html = '<div class="buttons"><button>Verstuur</button></div>';
        return $html . '</form>';
    }


}