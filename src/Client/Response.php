<?php
namespace Apanaj\Optimager\Client;

use Poirot\ApiClient\ResponseOfClient;
use Poirot\TenderBinClient\Exceptions\exServerError;


class Response
    extends ResponseOfClient
{
    /**
     * Has Exception?
     *
     * @return \Exception|false
     */
    function hasException()
    {
        return $this->exception;
    }

    /**
     * Process Raw Body As Result
     *
     * :proc
     * mixed function($originResult, $self);
     *
     * @param callable $callable
     *
     * @return mixed
     */
    function expected(/*callable*/ $callable = null)
    {
        if ( $callable === null )
            // Retrieve Json Parsed Data Result
            $callable = $this->_getDataParser();


        return parent::expected($callable);
    }


    // ...

    function _getDataParser()
    {
        if ( false !== strpos($this->getMeta('content_type'), 'image') )
            // Retrieve Json Parsed Data Result
            return function () {
                return $this->rawBody;
            };


        if ($this->responseCode == 204) {
            return function() {
                return null;
            };
        }

        throw new exServerError($this->rawBody);
    }
}
