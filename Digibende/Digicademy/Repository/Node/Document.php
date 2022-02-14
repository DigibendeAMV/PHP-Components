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

use Digicademy\Repository as Repository;
use Digicademy\Repository\Node as Node;
use Digicademy\Helpers\Files as FileHelper;
use Digicademy\Config as Meta;
use Digicademy\Repository\Xhtml\Parser as XhtmlParser;

use DirectoryIterator;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class Document
 *
 * A Document in the DigiCademy.
 * Documents contain 0 or more documents
 *
 *               Node
 *         ________|________
 *        |                 |
 *     Resource          Document(*)
 *                  ________|________
 *                 |                 |
 *             Repository          Quiz
 *
 * 
 * @package Digicademy\Repository
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Document extends Node implements DocumentInterface
{


    /**
     * @overwrite Node::getType()
     * @return int
     */
    public function getType(): int
    {
        return Node::TYPE_DOCUMENT;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * @overwrite Node::sendContent()
     * @return bool (!) Execution is terminated; return value can not be used
     */
    #[NoReturn]
    public function sendContent(): bool
    {
        $resourceFile =   $this->getRoot()->getPath() . $this->getPath() . DIRECTORY_SEPARATOR . $this->getContentFile();
        FileHelper::sendFile($resourceFile);
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Recursively creates the table of content for this document and its children
     *
     * @return array
     * @throws Repository\Exception\NodeInvalid
     */
    #[ArrayShape([
        'caption' => "string",
        'uri' => "string",
        'children' => "array"
    ])]
    public function getToc(): array
    {
        $toc = [
            'caption' => $this->getDisplayName(),
            'uri' => $this->getUri(),
            'children' => []
        ];
        foreach($this->getChildren() as $child) {
            $toc['children'][]= $child->getToc();
        }
        return $toc;
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (do not show up in generated documentation))
         * @var array Cache for orderedToc items
         */
        protected array $_orderedTocCache;

        /**
         * @ignore (do not show up in generated documentation))
         * @return array
         * @throws Repository\Exception\NodeInvalid
         */
        protected function _orderedTocCache(): array
        {
            $list[] = ['caption' => $this->getDisplayName(), 'uri' => $this->getUri()];
            foreach($this->getChildren() as $child) {
                $list = array_merge( $list, $child->_orderedTocCache());
            }
            return $list;
        }

    /**
     * @return array
     * @throws Repository\Exception\NodeInvalid
     */
    #[ArrayShape([
        'caption' => "string",
        'uri' => "string",
        'children' => "array"
    ])]
    public function getOrderedToc(): array
    {
        if(!isset($this->_orderedTocCache)) {
            $this->_orderedTocCache = $this->_orderedTocCache();
        }
        return $this->_orderedTocCache;
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (do not show up in generated documentation))
         * @var array $_children Internal list of child documents
         */
        protected array $_children;

    /**
     * Lists the child documents
     *
     * @param bool $rebuild
     * @return array Of child documents
     * @throws Repository\Exception\NodeInvalid
     */
    public function getChildren(bool $rebuild=false): array
    {
        if(!isset($this->_children) || $rebuild) {
            $this->_children = [];
            $dir = new DirectoryIterator($this->getAbsolutePath());
            foreach ($dir as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()) continue;
                if (Repository::isDocument($fileInfo->getPathname())) {
                    $this->_children[$fileInfo->getBasename()] = $this->getRoot()->getDocument($this->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFileName());
                }
            }
            asort($this->_children);
        }
        return $this->_children;
    }

        /**
         * @ignore (do not show up in generated documentation))
         * @var bool|array Toc Array entry if not already last node, FALSE otherwise
         */
        protected bool|array $_next;

    /**
     * Returns T.O.C. Array entry if not already last node, FALSE otherwise
     *
     * @return bool|array
     * @throws Repository\Exception\NodeInvalid
     */
    #[ArrayShape([
        'caption' => "string",
        'uri' => "string",
        'children' => "array"
    ])]
    public function getNext(): bool|array
    {
        if(!isset($this->_next)) {
            $list = $this->getRoot()->getOrderedToc();
            $this->_next = false;
            $isMe = false;
            foreach($list as  $doc) {
                if ($isMe) {
                    $this->_next =  $doc;
                    break;
                }
                $isMe =($doc['uri'] == $this->getUri());
            }
        }
        return $this->_next;
    }

        /**
         * @ignore (do not show up in generated documentation))
         * @var bool|array Toc Array entry if not first node, FALSE otherwise
         */
        protected bool|array $_prev;

    /**
     * Returns T.O.C. Array entry if not first node, FALSE otherwise
     *
     * @return bool|array
     * @throws Repository\Exception\NodeInvalid
     */
    #[ArrayShape([
        'caption' => "string",
        'uri' => "string",
        'children' => "array"
    ])]
    public function getPrevious(): bool|array
    {
        if(!isset($this->_prev)) {
            $list = $this->getRoot()->getOrderedToc();
            $this->_prev = false;
            $isMe = false;
            foreach(array_reverse($list) as  $doc) {
                if ($isMe) {
                    $this->_prev =  $doc;
                    break;
                }
                $isMe =($doc['uri'] == $this->getUri());
            }
        }
        return $this->_prev;
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (Do not show up in generated documenation)
         * @var Meta $_meta Internal reference of meta information
         */
        protected Meta $_meta;

    /**
     * Contains the data in the meta.json file
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     * @throws Meta\Exception
     *@see Meta
     */
    public function getMeta(string $key = null, mixed $default = null): mixed
    {
        if(!isset($this->_meta)) {
            $this->_meta = new Meta(
                json_decode(
                    file_get_contents(
                        $this->getAbsolutePath() . DIRECTORY_SEPARATOR . 'meta.json'
                    ),
                    true
                )
            );
        }
        if(isset($key)) {
            return $this->_meta->getOption($key, $default);
        } else {
            return $this->_meta;
        }
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (Do not show up in generated documenation)
         * @var string $_contentFile Internal storage for the name of the content file
         */
        protected string $_contentFile;

    /**
     * Returns the name of the content file
     *
     * @return string The name of the Content File
     */
    public function getContentFile(): string
    {
        if(!isset($this->_contentFile)) {
            $path = $this->getAbsolutePath() . DIRECTORY_SEPARATOR;
            if (is_file($path . 'content.xhtml'))
                $this->_contentFile =  'content.xhtml';
            if (is_file($path . 'content.html'))
                $this->_contentFile =  'content.html';
            if (is_file($path . 'content.php'))
                $this->_contentFile = 'content.php';

            // NOTE:
            //     Document object cannot be instantiated without a content file present,
            //     so there's no need to test for its existence.
            //     @see Repository::getDocument()
        }
        return $this->_contentFile;
    }

    /**
     * @throws \Digicademy\Exception
     */
    public function getContent(): string
    {
        $contentFile = $this->getAbsolutePath() . DIRECTORY_SEPARATOR . $this->getContentFile();
        $extension = pathinfo($contentFile, PATHINFO_EXTENSION);

        if ($extension == 'php') {
            include $contentFile;

        }else if ($extension == 'xhtml') {

            // parse the file
            //echo file_get_contents($contentFile);
            //echo PHP_EOL;
            //echo PHP_EOL;
            //echo PHP_EOL;
            //echo PHP_EOL;

            $parser = new XhtmlParser($contentFile);
            return $parser->startParse();
        } else {
            return file_get_contents($contentFile);
        }
        return '';
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Loops through all children to fetch locations
     * Created for debugging purposes
     *
     * @throws Repository\Exception\NodeInvalid
     */
    public function populate(): void
    {
        $this->getParent();
        foreach($this->getChildren() as $doc) {
            $doc->populate();
        }
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * @return string The last modification date in human readable form
     */
    public function getLastModified(): string
    {
        $lastModifiedTimestamp = filemtime($this->getAbsolutePath() . DIRECTORY_SEPARATOR . $this->getContentFile());
        return  date("d M Y H:i:s", $lastModifiedTimestamp);
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * Private cache for the Human friendly form of the directory name
         * @ignore (do not show up in generated documentation))
         * @var string $_displayName
         */
        protected string $_displayName;

    /**
     * A human friendly form of the directory name
     * @return string
     */
    public function getDisplayName(): string
    {
        if (!isset($this->_displayName)) {

            // remove all before the first letter
            $pattern = '/^[^a-zA-Z]*/';
            $replacement = '';
            $subject = $this->getName();
            $this->_displayName = (string) preg_replace($pattern, $replacement, $subject);

            // replace hyphens and underscores with a space
            $this->_displayName = str_replace(['_', '-'], ' ', $this->_displayName);

            // remove double spaces
            while (str_contains($this->_displayName, '  ')) {
                $this->_displayName = str_replace('  ', ' ', $this->_displayName);
            }

            // remove all exotic chars
            //$pattern = '/[^a-zA-Z ]/';
            //$replacement = '';
            //$subject = $this->getName();
            //$this->_displayName = (string) preg_replace($pattern, $replacement, $subject);

            // capitalize first character
            $this->_displayName = ucfirst($this->_displayName);
        }
        return $this->_displayName;
    }



} // class