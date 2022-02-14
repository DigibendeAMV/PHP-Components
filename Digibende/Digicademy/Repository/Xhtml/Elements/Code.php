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
 * This class will translate the XHTML <code>  element into 
 * 
 * <div class="tab-container" id="tab-container-id-%d"></div>
 *
 * @package Digicademy\Repository\Xhtml
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Code extends BaseElement
{
    public static int $counter = 0;
 
    public function onTranslateStart(): string
    {
        Code::$counter++;
        //return '<div class="tabs" id="tabs'. Code::$counter . '">';
        return '<div class="tab-container" id="tab-container-id-'. Code::$counter . '"></div>';
    }
 
    public function onTranslateEnd(): string
    {
        return '' ; //'</div>';
    }
}