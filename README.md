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

## Parameters and methods 参数与方法

更多http context选项, 详见[php.net](https://www.php.net/manual/zh/context.http.php)

```php
// construct usage
$client = new Roiwk\SSEClient\Client('http://127.0.0.1:8888', [
    'retryInterval' => 3,
    'onmessage' => function(string $data){},
    'streamContextOptions' => [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
            ],
        ],
        'content' => json_encode(['test' => 1]),
    ],
]);

// method usage
$client->addEventListener('ping', function ($data) {
    echo "Received ping event: $data\n";
});

$client->onmessage(function(string $data) use ($client){
    echo $data.PHP_EOL;
    $iWantClose = true;
    if ($iWantClose) {
        $client->close();
    }
});

$client->setRetryInterval(1);

$client->start();

```

## Tips
>1. If the server sends messages without an event field, those messages are treated as message events.
>1. 如果服务器发送的消息中没有 event 字段，则这些消息会被视为 message 事件。

>2. The close method can be executed in the callback, for example: addEventListener and onmessage, onerror.
>2. close方法可以在回调中执行, 例如:addEventListener和onmessage,onerror.








