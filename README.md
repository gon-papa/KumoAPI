# kumoAPI

ルーティングとDIコンテナの使い方をまとめています。エントリポイントは`src/public/index.php`で、ここからコンテナを立ち上げてルーティングを読み込みます。

## ルーティング
- ルータ本体は`Framework\Router\Router`、シンタックスシュガーとして`Framework\Router\Route`を利用します。`Route::setRouter($router)`で実体を渡してから登録を始めます。
- パスは`Route::route()`でプレフィックスをネストできます。`Route::get()`/`Route::post()`でHTTPメソッドごとのハンドラを配列形式(`[Controller::class, 'method']`)で登録します。
- パスパラメータは`/users/{id}`のように書き、`UserController::show(Request $request, string $id)`のようにメソッド引数名で受け取れます。
- `src/App/Route/route.php`に実際の登録例があります。

```php
use App\Domains\User\Presentation\UserController;
use Framework\Router\Route;

Route::setRouter($router);

// /api/v1/user にマウント
Route::route('api/v1', function () {
    Route::get('user', [UserController::class, 'index']);

    // ネストも可能 -> /api/v1/admin/users/{id}
    Route::route('admin', function () {
        Route::post('users/{id}', [AdminUserController::class, 'update']);
    });
});
```

## DIコンテナ
- `Framework\Container\Container`はシンプルなDIコンテナです。コンストラクタの型ヒントから自動解決（オートワイヤリング）されます。
- バインド方法
  - `bind($abstract, $concrete)`: 都度インスタンスを生成する通常バインド。
  - `singleton($abstract, $concrete)`: 1度だけ生成し、以降同じインスタンスを返す。
  - `instance($abstract, $object)`: 既存インスタンスをそのまま登録（例: `Request`をグローバルから生成して登録）。
- 生成方法
  - `make(Foo::class)`: バインドがあればそれを、なければクラスをリフレクションして依存を再帰的に解決します。
  - `call([$obj, 'method'], $params)`: メソッド/関数の引数に対して、パラメータ配列→コンテナ解決→デフォルト値の順で注入します。ルートパラメータや`Request`を渡したいときに使います。
- 利用例

```php
use Framework\Container\Container;
use Framework\Request\Request;

$container = new Container();

// 既存インスタンスを登録（リクエストは自前で作る）
$container->instance(Request::class, Request::fromGlobals());

// 実装を差し替えたいとき
$container->bind(UserRepositoryInterface::class, UserRepository::class);

// シングルトン登録（クロージャでもOK）
$container->singleton(LoggerInterface::class, fn() => new FileLogger('/tmp/app.log'));

// コントローラやサービスはオートワイヤリングされる
$controller = $container->make(UserController::class);
$response = $container->call([$controller, 'index'], ['id' => 123]);
```

`UserController`では`Request`と`UserService`が自動で解決され、`UserService`から`UserRepository`も再帰的に解決されます。
