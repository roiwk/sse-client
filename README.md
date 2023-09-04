# sse-client
Server Sent Event client  
SSE客户端


# Install 安装

```sh
composer require roiwk/sse-client
```

# Usage 使用

## Client  客户端

```php

include 'path/to/autoload.php';

$client = new Roiwk\SSEClient\Client('http://127.0.0.1:8888');
$client->addEventListener('ping', function ($data) {
    echo "Received ping event: $data\n";
});
$client->start();
```


## Server (demo)  服务端

You can use a test server to facilitate the use of this demo client, or use another server. Here is a demo:  
你可以使用一个测试服务器,以方便使用此演示客户端,或着使用别的服务端:  

[server.php](./example/server.php)

```sh
php -S 127.0.0.1 server.php
```


# Advanced 高级用法

```php

// construct usage
$client = new Roiwk\SSEClient\Client('http://127.0.0.1:8888', [
    'retryInterval' => 3,
    'onmessage' => function(string $data){},
    'streamContextOptions' => [
        'http' => [
            'method' => 'GET',
        ]
    ],
]);

// method usage
$client = new Roiwk\SSEClient\Client('http://127.0.0.1:8888');
$client->onmessage(function(string $data){
    echo $data.PHP_EOL;
});
$client->setRetryInterval(1);



```






