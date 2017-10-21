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
use Poirot\Stream\ResourceStream;
use Poirot\Stream\Streamable;


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
            // TODO post stream as file
            if (! is_resource($args['file']) )
                throw new \Exception('given argument for file must be stream.');

            // TODO For now convert stream that considered file into uri and post content with curl
            $fMeta = stream_get_meta_data($args['file']);
            $args['file'] = new \CURLFile( $fMeta['uri'], mime_content_type($fMeta['uri']) );
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
            $headers = isset($opts['header']) ? $opts['header'] : [];
            $headers += [
                'Content-Type: multipart/form-data',
            ];

            $content = new StreamBodyMultiPart;
            $stream  = new ResourceStream($data['file']);
            $content->addElement('file', new Streamable($stream));

            $opts['header']  = $headers;
            $opts['content'] = $content;
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


        $data   = stream_get_contents($file);
        header('Content-Type: image/jpeg');
        echo ($data);
        die;

        $response = new Response(
            $response
            , $code
            , []
            , $exception
        );

        return $response;
    }

}
