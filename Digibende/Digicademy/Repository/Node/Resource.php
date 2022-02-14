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

use Digicademy\Repository\Exception\NodeInvalid;
use Digicademy\Repository\Node as Node;
use Digicademy\Helpers\Files as FileHelper;

use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;

/**
 * Repository Resource
 *
 *               Node
 *         ________|________
 *        |                 |
 *     Resource(*)       Document
 *                          |
 *                      Repository
 *
 * @package Digicademy\Repository
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Resource extends Node implements ResourceInterface
{

    /**
     * @overwrite Node::getType()
     * @return int
     */
    public function getType(): int
    {
        return Node::TYPE_RESOURCE;
    }

    /**
     * @overwrite Node::sendContent()
     * @return bool (!) Execution is terminated; return value can not be used
     */
    #[NoReturn] public function sendContent(): bool
    {
        $resourceFile =   $this->getRoot()->getAbsolutePath() . $this->getPath();
        FileHelper::sendFile($resourceFile);
    }

    /**
     * @overwrite Node::getName()
     * @return string The dirname of the absolute path
     */
    #[Pure]
    public function getName(): string
    {
        return basename($this->getAbsolutePath());
    }

    /**
     * Replace all Directory separators wit a forward slash
     *
     * @overwrite Node::getUri()
     * @return string
     * @throws NodeInvalid
     */
    public function getUri(): string
    {
        return $this->getParent()->getPath() . str_replace(DIRECTORY_SEPARATOR, '/' , $this->getPath());
    }


    /**
     * @overwrite Node::getLastModified()
     * @return string The last modification date in human readable form
     * @throws NodeInvalid
     */
    public function getLastModified(): string
    {
        $lastModifiedTimestamp = filemtime($this->getParent()->getPath() . str_replace(DIRECTORY_SEPARATOR, '/' , $this->getPath()));
        return  date("d M Y H:i:s", $lastModifiedTimestamp);
    }


} // class