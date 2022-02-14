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

namespace Digicademy\Repository\Node;
  
/**
 * @package Digicademy\Repository
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
interface  DocumentInterface
{

    // inherited and overwritten from Node
    public function getPath(): string;
    public function getAbsolutePath(): string;

    public function getToc(): array;
    public function getOrderedToc(): array;
    public function getChildren(bool $rebuild=false): array;
    public function getNext(): bool|array;
    public function getPrevious(): bool|array;
    public function getMeta(string $key=null, mixed $default = null): mixed;
    public function getContentFile(): string|false;
    public function getContent(): string;
    public function getDisplayName(): string;
    public function populate(): void;
}