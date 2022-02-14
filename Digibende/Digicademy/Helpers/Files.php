<?php
declare(strict_types=1);

namespace Digicademy\Helpers;

use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;


/**
 * Helper functions for file manipulation for the DigiCademy Repository
 */
abstract class Files
{

    /**
     * Ads a trailing Directory Separator if not empty
     *
     * @param string $path
     * @return string
     */
    public static function addTrailingDirectorySeparator(string $path): string
    {
        if(empty($path)) return '';
        return rtrim($path, DIRECTORY_SEPARATOR)  . DIRECTORY_SEPARATOR;
    }



    /**
     * Whether the name can be used in an URL slug.
     *
     * Only alphanumeric character, hyphen and underscore are valid
     *
     * @param string $subject
     * @return bool TRUE when valid to be used in an URL, false otherwise;
     */
    public static function isUrlSluggable(string $subject): bool
    {
        $pattern = '/[^a-zA-Z0-9_-]/';
        $matches =[];
        $result = preg_match($pattern, $subject,  $matches);
        if(false === $result) return false;
        return($result == 0);
    }


    /**
     * Adds the local stored Uri offset to the Http Reference if
     * it is a local reference (no "HTTP.*" found).
     *
     * @param string $httpReference
     * @param string $uriOffset
     * @return string
     */
    #[Pure]
    public static function getLink(string $httpReference, string $uriOffset = ''): string
    {
        if(str_starts_with($httpReference, 'http'))  return $httpReference;
        return $uriOffset . $httpReference;
    }



    /**
     * Send correct header, send data if possible and exit
     *
     * @param string $resourceFile
     * @return void
     */
    #[NoReturn]
    public static function sendFile(string $resourceFile): void
    {
        if(!is_file($resourceFile)) {
            header('HTTP/1.0 404 Not Found ');
            exit($resourceFile . ' Not found');
        }

        // check the extension to set the correct content type header
        $extension = pathinfo($resourceFile, PATHINFO_EXTENSION);


        switch($extension) {

            // php files are special ...
            case 'php'  :
                header('Content-Type: text/html');
                include $resourceFile;
                exit();

            // textual files
            case 'css'  : header('content-type: text/css'); break;
            case 'js'   : header('content-type: text/javascript'); break;

            // video's
            case 'avi'  : header('content-type: video/x-msvideo'); break;
            case 'mpeg' : header('content-type: video/mpeg'); break;
            case 'mp4'  : header('content-type: video/mp4'); break;
            case 'mov'  : header('content-type: video/quicktime'); break;

            // images
            case 'svg'  : header('Content-Type: image/svg+xml');break;
            //case 'bmp'  : header('content-type: image/bmp'); break;
            case 'gif'  : header('content-type: image/gif'); break;
            case 'ico'  : header('content-type: image/vnd.microsoft.icon'); break;
            case 'jpeg' : // no break;
            case 'jpg'  : header('content-type: image/jpeg'); break;
            case 'png'  : header('content-type: image/png'); break;

            // not supported extensions
            default:
                header('HTTP/1.0 415 Media-type not supported');
                exit($extension . ' Media-type not supported');


        } // switch

        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Length: " . filesize( $resourceFile));
        readfile($resourceFile);
        exit(1);
    }



} // class