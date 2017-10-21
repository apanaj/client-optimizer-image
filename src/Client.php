<?php
namespace Apanaj\Optimager;

use Apanaj\Optimager\Client\Command\Optimize;
use Apanaj\Optimager\Client\PlatformRest;
use Apanaj\Optimager\Client\Response;
use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;


/*

// Resize Image:
// from url
$c = new \Apanaj\Optimager\Client('http://apanaj_optimizer-image');
$img = $c->optimize(
    (new \Apanaj\Optimager\Client\Command\Optimize())
        ->fromUrl('https://helloworld.co.nz/images/helloworld-logo.jpg')
        ->crop(90, 90)
        ->quality(100)
);

header('Content-Type: image/jpeg');
echo stream_get_contents($img);
die;


// Resize Image:
// from stream or file
$c = new \Apanaj\Optimager\Client('http://apanaj_optimizer-image');
$img = $c->optimize(
    (new \Apanaj\Optimager\Client\Command\Optimize())
        ->fromStream(fopen('https://helloworld.co.nz/images/helloworld-logo.jpg', 'rb'))
        ->resize(90, 90)
        ->quality(100)
);

*/

class Client
    extends aClient
{
    /** @var string */
    protected $serverUrl;
    /** @var */
    protected $platform;


    /**
     * Image Optimizer Client constructor.
     *
     * @param string $serverUrl
     */
    function __construct($serverUrl)
    {
        $this->serverUrl = rtrim( (string) $serverUrl, '/' );
    }


    /**
     * Optimize
     *
     * @param Optimize $command
     *
     * @return resource
     */
    function optimize(Optimize $command)
    {
        $response = $this->call($command);
        if ( $ex = $response->hasException() )
            throw $ex;

        return $response->expected();
    }


    // Implement aClient

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return iPlatform
     */
    protected function platform()
    {
        if (! $this->platform )
            $this->platform = new PlatformRest;


        # Default Options Overriding
        $this->platform->setServerUrl( $this->serverUrl );
        return $this->platform;
    }


    // ..

    /**
     * @override handle token renewal from server
     *
     * @inheritdoc
     *
     * @return Response
     */
    protected function call(iApiCommand $command)
    {
        $response = \Poirot\Std\reTry(function() use ($command) {
            return parent::call($command);
        }, 3);


        return $response;
    }
}
