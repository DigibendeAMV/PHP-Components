<?php 
/**
 * -----------------------------------------------------------------------------
 *   This program is license under MIT License.
 * 
 *   You should have received a copy of the MIT License with this program 
 *   in the file LICENSE.txt and is available through the world-wide-web 
 *   at http://license.digicademy.nl/mit-license.
 * 
 *   If you did not receive a copy of the MIT LIcense and are unable obtain 
 *   it through the world-wide-web please send a note to 
 * 
 *      Rob <rob@amstelveen.digibende.nl>
 * 
 *   so we can mail you a copy immediately.
 * 
 *   @license ~/LICENSE.txt
 * ----------------------------------------------------------------------------- 
 */ 
declare(strict_types=1);

namespace Digicademy\Repository\Xhtml;

use JetBrains\PhpStorm\Pure;

/**
 * Class Element
 *
 * When a DigiCademy Repository Document is written in xHTMl each xHTML element is translated through
 * a default element instance or one of its descendent. (Located in the Elements Folder)
 *
 * @package Digicademy\Repository\Xhtml
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Element
{
        /**
         * Element constructor.
         *
         *  Is made protected to forcing to use the factory method.
         *
         * @param string $name
         * @param array $attributes
         * @param Element|false $parent
         */
        protected function __construct(string $name, array $attributes, Element|false $parent)
        {
            $this->_name = $name;
            $this->_attributes = $attributes;
            $this->_parent = $parent;
        }



    protected string $_name;

    public function getName():string
    {
        return $this->_name;
    }



    protected array $_attributes;

    #[Pure]
    public function getAttributes(): array|string
    {
        return $this->_attributes;
    }

    #[Pure] public function getAttribute(string $name, string $default = ''): string
    {
        if ($this->hasAttribute($name)){
            return $this->_attributes[$name];
        } else {
            return $default;
        }
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->_attributes);
    }

    protected Element|false $_parent;

    public function getParent(): Element|false
    {
        return $this->_parent;
    }

    public function setParent(Element|false $parent): self
    {
        $this->_parent = $parent;
        return $this;
    }

    public function isRoot(): bool
    {
        return (false === $this->_parent);
    }

    protected array $_children = [];
    public function getChildren(): array // of Elements
    {
        return $this->_children;
    }
    public function addChild(Element $child): self
    {
        $this->_children[] = $child;
        return $this;
    }


    public static function factory(string $elementName, array $attributes = [], Element|false $parent=false): Element
    {
        $elementFile = __DIR__ . DIRECTORY_SEPARATOR . 'Elements' . DIRECTORY_SEPARATOR . ucfirst($elementName) . '.php';
        $elementClass = Element::class . 's\\' . ucfirst($elementName);
        if (is_file($elementFile)){
            return new $elementClass($elementName, $attributes, $parent);
        }
        return new Element($elementName, $attributes, $parent);;
    }



    #[Pure]
    public function onTranslateStart(): string
    {
        return '<' . $this->getName() . $this->_attributesAsString() . '>';
    }

    public function onTranslateData(string $data) : string
    {
        return $data;
    }

    #[Pure]
    public function onTranslateEnd(): string
    {
        return '</' . $this->getName() . '>';
    }




    #[Pure] protected function _attributesAsString(): string
    {
        $attribsAsString = '';
        foreach($this->getAttributes() as $attribName => $attribValue) {
            $attribsAsString .= ' ' .$attribName . '="' . $attribValue . '"';
        }
        return $attribsAsString;
    }

    protected function _deepTrim(string $data) : string
    {
        return trim($this->_stripWhiteSpaces($data));
    }

    protected function _stripWhiteSpaces(string $data): string
    {
        $data = str_replace(["\n", "\t", "\r"], ' ', $data);
        while(str_contains($data, '  ')) {
            $data = str_replace('  ', ' ', $data);
        }
        return $data;
    }

    
}