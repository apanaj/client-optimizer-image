<?php
namespace Apanaj\Optimager\Client;

use Apanaj\Optimager\Client\Command\Optimize;
use Poirot\ApiClient\aPlatform;
use Poirot\ApiClient\Exceptions\exHttpResponse;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Http\HttpMessage\Request\StreamBodyMultiPart;
use Poirot\Std\ErrorStack;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamBridgeFromPsr;
use Poirot\Stream\ResourceStream;
use Poirot\Stream\Streamable;
use Psr\Http\Message\StreamInterface;


class PlatformRest
    extends aPlatform
    implements iPlatform
{
    /** @var iApiCommand */
    protected $Command;

    // Options:
    protected $usingSsl  = false;
    protected $serverUrl = null;


    // Alters

    /**
     * @param Optimize $command
     *
     * @return iResponse
     * @throws \Exception
     */
    protected function _Optimize(Optimize $command)
    {
        $headers = [];
        $method  = 'GET';
        $args    = iterator_to_array($command);

        if ( isset($args['file']) ) {
            $method = 'POST';
        }

        $url      = $this->serverUrl;
        $response = $this->_sendViaPhpContext($method, $url, $args, $headers);
        return $response;
    }




    // Options

    /**
     * Set Server Url
     *
     * @param string $url
     *
     * @return $this
     */
    function setServerUrl($url)
    {
        $this->serverUrl = (string) $url;
        return $this;
    }

    /**
     * Server Url
     *
     * @return string
     */
    function getServerUrl()
    {
        return $this->serverUrl;
    }


    // ..

    protected function _sendViaPhpContext($method, $url, array $data, array $headers = [])
    {
        $opts = [
            'method'  => $method,
            'timeout'    => 60 * 3,
            'user-agent' => 'PHP-Optimager_Client',
        ];

        if (! empty($headers) ) {
            $h = [];
            foreach ($headers as $key => $val)
                $h[] = $key.': '.$val;
            $headers = $h;

            $opts = [
                'header'  => $headers,
            ];
        }

        if ($method === 'POST') {
            $content  = new StreamBodyMultiPart;
            $resource = $data['file'];
            $stream   = $this->_makeStreamFromGivenResource($resource);

            $filename = basename( $stream->resource()->meta()->getUri() );
            if ('' === $ext = pathinfo($filename, PATHINFO_EXTENSION))
                $filename = uniqid().'.jpg';

            $content->addElement(
                'file'
                , $stream
                , [
                    'Content-Disposition' => 'form-data; name="file"; filename="'.$filename.'"',
                    'Content-Type'        => 'image/jpeg',
                ]
            );

            $content->addElementDone();

            $headers = isset($opts['header']) ? $opts['header'] : [];
            $headers += [
                'Content-Type: multipart/form-data; boundary='.$content->getBoundary(),
            ];

            $opts['header']  = $headers;
            $opts['content'] = $content->read();

            unset($data['file']);
            $url .= '?'.http_build_query($data);
        }

        if ($method === 'GET') {
            $url .= '?'.http_build_query($data);
        }

        $context = stream_context_create(['http' => $opts]);


        $code = 200;
        $exception = null;
        ErrorStack::handleError( E_ALL );
        if (false === $response = $file = fopen($url, 'rb', false, $context)) {
            $code      = 400;
            $exception = new exHttpResponse('Error While Retrieve Resource', $code);
        }
        if ( $ex = ErrorStack::handleDone() )
            throw $ex;


        $response = new Response(
            $response
            , $code
            , []
            , $exception
        );

        return $response;
    }

    // ..

    function _makeStreamFromGivenResource($resource)
    {
        if ( is_resource($resource) ) {
            $resource = new ResourceStream($resource);
            $resource = new Streamable($resource);
        }

        if ($resource instanceof StreamInterface)
            $resource = new StreamBridgeFromPsr($resource);

        if (! $resource instanceof iStreamable)
            throw new \Exception(sprintf(
                'Invalid resource (%s) given for optimize.'
                , gettype($resource)
            ));


        ## Rewind Resource:
        #
        if ( $resource->resource()->isSeekable() )
            $resource->rewind();

        return $resource;
    }
}
