<?php

declare(strict_types=1);

namespace Framework\Container;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;

class Container
{
    /**
     * @var array<string, array{concrete: Closure|string, singleton: bool}>
     */
    protected array $bindings = [];
    /**
     * @var array<string, object>
     */
    protected array $instances = [];

    public function bind(string $abstract, Closure|string $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, Closure|string $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * 既存インスタンスをそのまま登録
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract): object
    {
        // 生成済みの場合
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // バインドされている場合
        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];

            $object = $this->build($concrete);

            // シングルトンの場合はinstancesに格納
            if ($binding['singleton']) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        // 何もバインドされていない場合はオートワイヤリング(自動解決)
        return $this->build($abstract);
    }

    protected function build(Closure|string $concrete): object
    {
        // クロージャなら実行して返す
        if ($concrete instanceof Closure) {
            $object = $concrete($this);
            if (!is_object($object)) {
                throw new InvalidArgumentException('Container closure must return object.');
            }
            return $object;
        }

        // クラスの名の場合
        if (!class_exists($concrete)) {
            throw new InvalidArgumentException("Class {$concrete} does not exist.");
        }

        $refClass = new ReflectionClass($concrete);

        // インスタンス化が可能か確認(abstractClassやinterfaceは例外とする)
        if (!$refClass->isInstantiable()) {
            throw new InvalidArgumentException("Class {$concrete} is not instantiable.");
        }

        // リフレクションからコンストラクタ情報を取得
        $constructor = $refClass->getConstructor();
        // コンストラクタがない場合はそのままnewして返す
        if ($constructor === null) {
            return new $concrete();
        }

        $deps = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            // 型指定がない or 組み込み型（int/string 等）はエラー
            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $deps[] = $param->getDefaultValue();
                    continue;
                }

                $name = $param->getName();
                $className = $concrete;
                throw new InvalidArgumentException(
                    "Cannot resolve parameter \${$name} of {$className} (no class type hint or default)."
                );
            }
            // クラス/インターフェイスを再帰的に解決
            $deps[] = $this->make($type->getName());
        }

        return $refClass->newInstanceArgs($deps);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    /**
     * 任意の callable に依存解決付きで呼び出し
     *
     * @param callable $callable [obj, 'method'] / 'function_name' / 'Class::method'
     * @param array<string,mixed> $parameters 名前付き引数（ルートパラメータ等）
     */
    public function call(callable $callable, array $parameters = []): mixed
    {
        // Reflection オブジェクトを作る
        if (is_array($callable)) {
            [$objectOrClass, $method] = $callable;
            if (!is_object($objectOrClass) && !is_string($objectOrClass)) {
                throw new InvalidArgumentException('Callable array must have object or class-string as first element.');
            }
            if (!is_string($method)) {
                throw new InvalidArgumentException('Callable array second element must be method name.');
            }
            $ref = new ReflectionMethod($objectOrClass, $method);
        } elseif (is_string($callable) && str_contains($callable, '::')) {
            [$class, $method] = explode('::', $callable, 2);
            $ref = new ReflectionMethod($class, $method);
        } else {
            $ref = new ReflectionFunction(Closure::fromCallable($callable));
        }

        $args = [];

        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();

            // 1. 名前付きで渡されているならそれを優先（ルート param / Request 等）
            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
                continue;
            }

            $type = $param->getType();

            // 2. クラス / インターフェイスならコンテナから解決
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();

                // 明示的にインスタンスを渡していないが、
                // コンテナから解決できる（UserRepository / Logger など）
                $args[] = $this->make($className);
                continue;
            }

            // 3. デフォルト値があればそれを使う
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            // 4. 解決不能
            throw new InvalidArgumentException(
                "Cannot resolve parameter \${$name} for callable {$ref->getName()}"
            );
        }

        // 実際の呼び出し
        if ($ref instanceof ReflectionMethod) {
            // インスタンス or クラスメソッドかで分岐
            if ($ref->isStatic()) {
                return $ref->invokeArgs(null, $args);
            }

            // $callable が [obj, 'method'] の場合のみここに来る想定
            $object = is_array($callable) ? $callable[0] : null;

            if (!is_object($object)) {
                throw new InvalidArgumentException('Non-static method call requires object.');
            }

            return $ref->invokeArgs($object, $args);
        }

        // 通常の関数
        return $ref->invokeArgs($args);
    }
}
