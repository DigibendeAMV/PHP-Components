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

namespace Digicademy;

use Digicademy\Repository\Exception\NodeNotFound;
use Digicademy\Repository\Exception\NodeInvalid;
use Digicademy\Repository\Node as Node;
use Digicademy\Repository\Node\Document as Document;
use Digicademy\Repository\Node\DocumentInterface;
// (not in use yet) use Digicademy\Repository\Node\Resource as Resource;
// (not in use yet) use Digicademy\Repository\Node\ResourceInterface;
use Digicademy\Helpers\Files as FileHelper;


/**
 * Class Item
 *
 * Represents a Digicademy Repository directory
 *
 * A Repository will contain content folders and resources for this content. A Content folder will contain
 * A) a content.*  file with the extensions txt, php, html or xhtml.
 * B) a meta.json file with additional information about athe content.
 *
 * Any other file or directory is considered to be a resource.
 *
 * You can nest as many content levels as you wish but it is recommended to use no more then 4 levels as in
 * the following example
 *
 * 0-Repository               (level 0)
 * . 1-Faculty/Category       (level 1)
 * . . 1.1-Course             (level 2)
 * . . . 1.1.1-Section        (level 3)
 * . . . . 1.1.1-Lecture      (level 4)
 * . . . . 1.1.2-Lecture
 * . . . 1.1.2-Course Section
 * . . . . 1.1.1-Lecture
 * . . . 1.1.3-Course Section
 * . . 1.2-Coursenamespace Digicademy\Exception;
 * . 2-Faculty
 *    etc
 *
 *               Node
 *         ________|________
 *        |                 |
 *     Resource         Document
 *                          |
 *                      Repository(*)
 *
 * @package Digicademy\Repository
 * @author Rob <rob@amstelveen.digibende.nl> 
 */
class Repository extends Document
{

    /**
     * @implement Node::getType()
     * @return int
     */
    public function getType(): int
    {
        return Node::TYPE_REPOSITORY | Node::TYPE_DOCUMENT;
    }

    /**
     * Internal storage for absolute path
     */
    private string $_absolutePath;

    /**
     * Repository constructor.
     *
     * @overwrite Node::__construct()
     * @param string $path The absolute path for this Repository
     * @throws NodeInvalid When given path does not qualify as a Repository Document
     */
    public function __construct(string $path)
    {
        parent::__construct($path);

        // given path is absolute path for the Repository, the relative path is the DIRECTORY_SEPARATOR
        $this->_path = DIRECTORY_SEPARATOR;
        $this->_absolutePath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        // set root to itself
        $this->_root = $this;

        // check if the location path qualifies as a Repository Document

        $reason = 0;
        if (!Repository::isDocument($this->_absolutePath, $reason)) {
            throw new NodeInvalid(sprintf('No Document found in "%s" (reason = %d).', $this->_absolutePath, $reason));
        }
    }

    /**
     * Get a Document in this Repository
     *
     * @param string $path       Relative path for the current DigiCademy Repository
     * @return DocumentInterface An Object implementing a DigiCademy Repository DocumentInterface
     * @throws NodeInvalid         When $path is not pointing to a qualified DigiCademy Repository Document
     *
     */
    public function getDocument(string $path): DocumentInterface
    {
        return Node::_initDocument($path, $this);
    }

    /* * NOT USED YET...
     * Get a Document in this Repository
     *
     * @param string $path       Relative path for the current DigiCademy Repository
     * @return ResourceInterface An Object implementing a DigiCademy Repository ResourceInterface
     * @throws NodeNotFound         When $path is not pointing to a qualified DigiCademy Repository Resource
     * /
    public function getResource(string $path): ResourceInterface
    {
        return Node::_initResource($path, $this);
    } */

    /**
     * Get a Document or Resource in this Repository
     *
     * @param string $path Relative path for the current DigiCademy Repository
     * @return bool|Node   A valid Node(Document|Resource) FALSE otherwise
     * @throws NodeNotFound|NodeInvalid   When $path is not pointing to a qualified DigiCademy Repository Node
     */
    public function get(string $path): bool|Node
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (Repository::isResource($this->getAbsolutePath() . $path)){
            $node = Node::_initResource($path, $this, false);
        } else {
            if (Repository::isDocument($this->getAbsolutePath() . $path)){
                $node = Node::_initDocument($path, $this, false);
            } else {
                throw new NodeNotFound(sprintf(
                    'Node "%s" not found in Repository "%s"',
                    $path,
                    $this->getName()
                ));
            }
        }
        return $node;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * The absolute path differs with the absolute path of a Document. The absolute path of a Repository is set in
     * the Constructor by the passed $path parameter.
     *
     * @ignore (do not show in generted documentation)
     * @overwrite Node::getAbsolutePath()
     * @return string
     */
    public function getAbsolutePath(): string
    {
        return $this->_absolutePath;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * @var array|string[]
     */
    protected static array $_validResourcesExtensions = ['php', 'js', 'css', 'png', 'svg', 'jpg', 'jpeg'];
    //public static function setValidResourceExtensions(array $validResourcesExtensions): void
    //{
    //    self::$_validResourcesExtensions = $validResourcesExtensions;
    //}
    public static function getValidResourcesExtensions() : array
    {
        return self::$_validResourcesExtensions;
    }

    // -----------------------------------------------------------------------------------------------------------

    /**
     * Whether a directory qualifies as a DigiCademy document.
     *
     * To qualify as a DigiCademy document __$documentPath__ must:
     *
     * a) be an existing directory,
     * b) be readable,
     * c) has a valid name to be converted into an url slug
     * f) contain a valid content file,
     * e) contain a meta.json file.
     *
     * @param string $documentPath
     * @param int $reason
     * @return bool TRUE when a valid document is found, FALSE otherwise
     */
    public static function isDocument(string $documentPath, int &$reason = 0): bool
    {
        // check path
        if (!is_dir($documentPath)) { $reason= 1; return false;}
        if (!is_readable($documentPath)) { $reason= 2; return false;}

        // check if the document has a valid name to be converted into an url slug
        if (!FileHelper::isUrlSluggable(basename($documentPath))) {$reason= 3; return false;}

        // add directory separator
        $documentPath = FileHelper::addTrailingDirectorySeparator($documentPath);

        // check content
        if (!file_exists($documentPath . 'content.xhtml') &&
            !file_exists($documentPath . 'content.html') &&
            !file_exists($documentPath . 'content.php')) { $reason= 4; return false;}
        if (!file_exists($documentPath . 'meta.json')) { $reason= 5; return false; }

        // yep valid
        return true;
    }

    /**
     * Whether a file qualifies as a DigiCademy Repository Resource.
     *
     * To qualify as a resource __$resourcePath__ must:
     * a) exist,
     * b) be a file,
     * c) be readable,
     * d) has a valid extension,
     * e) be stored in a valid DigiCademy Repository Document directory.
     *
     * @param string $resourcePath
     * @return bool TRUE when a valid resource is found, FALSE otherwise
     */
    public static function isResource(string $resourcePath): bool
    {
        // check existence
        if (!is_file($resourcePath)) return false;
        if (!is_readable($resourcePath)) return false;

        // check extension
        $extension = pathinfo($resourcePath, PATHINFO_EXTENSION);
        if(!in_array($extension, Repository::getValidResourcesExtensions())){
            return false;
        }

        // check whether the resource is stored in a valid DigiCademy Repository Document directory.
        $directories = explode(DIRECTORY_SEPARATOR,  $resourcePath);
        $found = false;
        while (!$found) {
            if (count($directories) == 0) break; // be safe
            $repoPath = implode(DIRECTORY_SEPARATOR, $directories);
            if (Repository::isDocument($repoPath)) {
                $found = true;
            }
            array_pop($directories);
        }

        // return TRUE when a valid resource is found, FALSE otherwise
        return $found;
    }
    // -----------------------------------------------------------------------------------------------------------

    /*
     * @param string $path
     * @return DocumentInterface|null
     * @throws NodeNotFound
     * /
     public static function getRootFor(string $path): ?DocumentInterface
     {
         // The (default) root is the directory with no other Document directories above. So we loop upwards to find this
         $directories = explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR));
         $foundRoot = false;
         $rootPath = $path;
         while (!$foundRoot) {
             if (count($directories) == 0) break; // be safe
                 $rootPath = implode(DIRECTORY_SEPARATOR, $directories);
                  array_pop($directories);
                  $possibleRootPath = implode(DIRECTORY_SEPARATOR, $directories);
                  if (!Repository::isDocument($possibleRootPath)) {
                      $foundRoot = true;
                  }
              }
          }
          if ($foundRoot) {
              return new Document($rootPath);
          }
         return null;
     } //*/

} // class