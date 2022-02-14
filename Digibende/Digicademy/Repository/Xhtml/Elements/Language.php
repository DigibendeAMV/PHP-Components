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

namespace Digicademy\Repository\Xhtml\Elements;

use Digicademy\Repository\Xhtml\Element as BaseElement;  

/**
 * DigiCademy Repository XHTM tag 
 * 
 * The following XHTML structure:
 * 
 * <code>
 *    <some-programming-language>
 *       // code here
 *    </some-programming-language>
 *    <another-programming-language>
 *       // code here
 *    </another-programming-language>
 * </code>
 * 
 * Will be translated into:
 * 
 * <div class="tab-container" id="tab-container-id-%d"></div>
 *    <em>some-programming-language</em>
 *    <pre><code>
 *       // code here
 *    <pre></code>
 *    <em>another-programming-language</em>
 *    <pre><code>
 *       // code here
 *    <pre></code>
 * </code>
 * 
 * when the Element Classes "some-programming-language" and "another-programming-language" exists in this 
 * namespace as decendents this class will translate the XHTML <some-programming-language> and
 * <another-programming-language> Elements into the following HTML elements
 * 
 *    <em>some-programming-language</em>
 *    <pre class="language-some-programming-language"><code>
 *       // code here
 *    <pre></code>
 *    <em>another-programming-language</em>
 *    <pre class="language-another-programming-language"><code>
 *       // code here
 *    <pre></code>
 *  
 * @package Digicademy\Repository\Xhtml
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Language extends BaseElement
{  

    /**
     * @ignore(do not show up in the generated documentation)
     * @return string
     */
    private function _getShortName():   string
    {
        return (new \ReflectionClass($this))->getShortName();
    }


    /**
     * The caption for this Programming language. 
     * 
     * Defaults to the (shortname) name of this class. 
     * 
     * @return string
     */
    public function getCaption(): string
    {
        return $this->_getShortName();
    }


    /**
     * The Language  
     * 
     * Defaults to the (shortname) name of this class. 
     * 
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->_getShortName();
    }
     

    /**
     * @see BaseElement->onTranslateStart();
     */
    public function onTranslateStart(): string
    { 
        $html  = PHP_EOL .'<em>' . $this->getCaption() . '</em>';
        $html .= PHP_EOL .'<pre><code class="language-'. strtolower($this->getLanguage()) .'">';
        return $html;
    }

    /**
     * @see BaseElement->onTranslateEnd();
     */
    public function onTranslateEnd(): string
    {   
        return '</code></pre>';
    }

} // class