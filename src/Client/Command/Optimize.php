<?php
namespace Apanaj\Optimager\Client\Command;

use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Request\tCommandHelper;
use Poirot\Std\Hydrator\HydrateGetters;
use Poirot\Stream\Interfaces\iStreamable;
use Psr\Http\Message\StreamInterface;


class Optimize
    implements iApiCommand
    , \IteratorAggregate
{
    use tCommandHelper;

    protected $url;
    /** @var resource */
    protected $file;
    /** @var string Manipulation type */
    protected $type;
    /** @var string 150x150 */
    protected $size;
    /** @var int Image quality*/
    protected $quality;


    /**
     * Optimize constructor.
     *
     * @param array|null $opts
     */
    function __construct(array $opts = null)
    {

    }


    // Options

    /**
     * Optimize Image From Given Url
     *
     * @param string $url
     *
     * @return $this
     */
    function fromUrl($url)
    {
        $this->url = (string) $url;
        return $this;
    }

    /**
     * Optimize Image From Stream
     *
     * @param resource|iStreamable|StreamInterface $stream
     *
     * @return $this
     */
    function fromStream($stream)
    {
        $this->file = $stream;
        return $this;
    }

    function crop($width, $height)
    {
        $this->type('crop');
        $this->size($width, $height);

        return $this;
    }

    function resize($width, $height)
    {
        $this->type('resize');
        $this->size($width, $height);

        return $this;
    }

    function resizeForce($width, $height)
    {
        $this->type('force_resize');
        $this->size($width, $height);

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    function type($type)
    {
        $this->type = (string) $type;
        return $this;
    }

    function size($width, $height = null)
    {
        if (! $height ) {
            // 90x90
            $this->size = (string) $width;
            return $this;
        }

        $this->size = $width.'x'.$height;
        return $this;
    }

    /**
     * Set Image Quality
     *
     * @param int $q
     *
     * @return $this
     */
    function quality($q)
    {
        $this->quality = (int) $q;
        return $this;
    }


    // Arguments

    function getUrl()
    {
        return $this->url;
    }

    function getFile()
    {
        return $this->file;
    }

    /**
     * Manipulation Type
     *
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Size Of Image Manipulated By Type
     *
     * @return string
     */
    function getSize()
    {
        return $this->size;
    }

    /**
     * Quality
     *
     * @return int
     */
    function getQ()
    {
        return $this->quality;
    }


    // ..

    /**
     * @ignore
     */
    function getIterator()
    {
        $hyd = new HydrateGetters($this);
        $hyd->setExcludeNullValues();
        return $hyd;
    }
}
