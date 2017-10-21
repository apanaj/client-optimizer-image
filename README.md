# client-optimizer-image
php client for image optimizer service.


```php
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
```