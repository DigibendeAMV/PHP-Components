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

namespace Digicademy\Repository;
 
use Digicademy\Repository as Repository;
use Digicademy\Repository\Exception\NodeNotFound;
use Digicademy\Repository\Exception\NodeInvalid;
use Digicademy\Repository\Node\Document;
use Digicademy\Repository\Node\DocumentInterface;
use Digicademy\Repository\Node\Resource;
use Digicademy\Repository\Node\ResourceInterface;

use JetBrains\PhpStorm\Pure;

/**
 * A Repository Node
 *
 *               Node(*)
 *         ________|________
 *        |                 |
 *     Resource          Document
 *                          |
 *   
 * @package Digicademy\Repository
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
abstract class Node
{

    //const TYPE_NONE = 0;
    const TYPE_RESOURCE = 1;
    const TYPE_DOCUMENT = 2;
    const TYPE_REPOSITORY = 4;

    abstract public function getType(): int;
    abstract public function sendContent(): bool;
    abstract public function getLastModified(): string;

    /**
     * Node constructor.
     *
     * @param string $path The relative path for this DigiCademy Repository Node
     */
    protected function __construct(string $path)
    {
        $this->_path = $path;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * @return string The dirname of the absolute path
     */
    #[Pure]
    public function getName(): string
    {
        return basename($this->getAbsolutePath());
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (do not show up in generated Documentation)
         * @var string $_path Internal storage of the (relative) path for this Node
         */
        protected string $_path;

    /**
     * @return string The relative path of this Node
     */
    public function getPath(): string
    {
        return  $this->_path;
    }

    /**
     * @return string The absolute path of this Node
     */
    #[Pure] public function getAbsolutePath(): string
    {
        return $this->getRoot()->getAbsolutePath() . $this->getPath();
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * @ignore (do not show up in generated Documentation)
         * @var Repository|null $_root Internal reference to the root Document
         */
        protected ?Repository $_root = null;

    /**
     * Get the root document
     *
     * @return Repository|null DigiCademy Repository Document when not Root, NULL otherwise
     */
    public function getRoot(): ?Repository
    {
        return $this->_root;
    }

        /**
         * Set Root
         *
         * This can only be set internal by the _init* functions
         *
         * @ignore (do not show up in generated Documentation)
         * @param Repository $root
         * @return void
         */
        protected function setRoot(Repository $root): void
        {
            $this->_root = $root;
        }

        /**
         * @ignore (do not show up in generated Documentation)
         * @var DocumentInterface|null $_parent Internal reference to the Parent of this Node
         */
        protected ?DocumentInterface $_parent = null;

    /**
     * Returns the Parent Document for this Node
     *
     * The Parent is always a Document or when this Node is Root NULL is returned
     *
     * @return DocumentInterface|null
     * @throws NodeInvalid
     */
    public function getParent(): ?DocumentInterface
    {
        // check if we are root, it has no parent :-)
        if ($this->getPath() == $this->getRoot()->getPath()) {
            return null;
        }

        if(!isset($this->_parent)) {

            // the parent is the first document directory. So we loop upwards to find this
            $directories = explode(DIRECTORY_SEPARATOR, rtrim($this->getPath(), DIRECTORY_SEPARATOR));
            $foundParent = false;
            $possibleParentPath = '';

            while (!$foundParent) {

                // There should always be a parent, just in case ...
                if (count($directories) == 0) break;

                array_pop($directories);
                $possibleParentPath = implode(DIRECTORY_SEPARATOR, $directories);

                if (Repository::isDocument($possibleParentPath)) {
                    $foundParent = true;
                }
            }
            if ($foundParent) {
               $this->_parent = $this->getRoot()->getDocument($possibleParentPath);
            }
        }
        return $this->_parent;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Replace all Directory separators wit a URL separator (forward slash) in
     * the relative path
     *
     * @return string The Uri for this Node
     */
    public function getUri(): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/' , $this->getPath());
    }

    // -----------------------------------------------------------------------------------------------------------

        /**
         * Returns a DigiCademy Document Node for the given Repository
         *
         * @ignore (do not show up in generated Documentation)
         * @param string $path Relative path for The given Repository $root
         * @param Repository $root The Repository
         * @param bool $validate Whether to check the path is pointing to a qualified DigiCademy Repository Document
         * @return DocumentInterface An Object implementing a DigiCademy Repository DocumentInterface
         * @throws NodeInvalid When $validate = TRUE and the path is not pointing to a qualified DigiCademy Repository Document         *
         */
        protected static function _initDocument(string $path, Repository $root, bool $validate=true): DocumentInterface
        {
            // All Document path starts with directory separator
            // and is removed at the end. So make the path windows save and remove last slash
            $path = DIRECTORY_SEPARATOR . trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
            if ($root->getPath() == $path) return $root;

            // check if the location path qualifies as a Repository Document
            if ($validate) {
                if (!Repository::isDocument($root->getAbsolutePath() . $path)) {
                    throw new NodeInvalid(sprintf('No Document found in Repository "%s" where path is "%s".', $root->getName(), $path));
                }
            }

            // initialize Node and return;
            $Node =  new Document($path);
            $Node->setRoot($root);
            return $Node;
        }

        /**
         * Returns a DigiCademy Resource Node for the given Repository
         *
         * @ignore (do not show up in generated Documentation)
         * @param string $path Relative path for the given Repository $root
         * @param Repository $root The Repository
         * @param bool $validate Whether to check the path is pointing to a qualified DigiCademy Repository Resource
         * @return ResourceInterface An Object implementing a DigiCademy Repository ResourceInterface
         * @throws NodeNotFound When $validate = TRUE and the path is not pointing to a qualified DigiCademy Repository Resource
         */
        protected static function _initResource(string $path, Repository $root, bool $validate = true): ResourceInterface
        {
            // make windows save
            $path = DIRECTORY_SEPARATOR . trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

            // check if the location path qualifies as a Repository Resource
            if ($validate) {
                if (!Repository::isResource($root->getAbsolutePath() . DIRECTORY_SEPARATOR . $path)) {
                    throw new NodeNotFound(sprintf('No Resource found in "%s".', $path));
                }
            }

            // initialize Node and return;
            $Node = new Resource($path);
            $Node->setRoot($root);
            return $Node;
        }

} // class