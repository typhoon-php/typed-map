<?php

declare(strict_types=1);

use DragonCode\Benchmark\Benchmark;
use Typhoon\OPcache\TyphoonOPcache;
use Typhoon\Reflection\Cache\FreshCache;
use Typhoon\Reflection\Cache\NullCache;
use Typhoon\Reflection\TyphoonReflector;

require_once __DIR__ . '/../../vendor/autoload.php';

$typhoonNoCache = TyphoonReflector::build(cache: new NullCache());

$typhoonInMemoryCache = TyphoonReflector::build();

$opcache = new TyphoonOPcache(__DIR__ . '/../../var/benchmark/cache');
$opcache->clear();
$typhoonOpcache = TyphoonReflector::build(cache: $opcache);

$freshOpcache = new FreshCache(new TyphoonOPcache(__DIR__ . '/../../var/benchmark/fresh'));
$freshOpcache->clear();
$typhoonFreshOpcache = TyphoonReflector::build(cache: $freshOpcache);

// warmup class autoloading
$typhoonNoCache->reflectClassLike(AppendIterator::class)->methods()['append'];

Benchmark::start()
    ->withoutData()
    ->compare([
        'native reflection' => static fn(): mixed => (new ReflectionClass(AppendIterator::class))->getMethod('append'),
        'typhoon, no cache' => static fn(): mixed => $typhoonNoCache->reflectClassLike(AppendIterator::class)->methods()['append'],
        'typhoon, in-memory cache' => static fn(): mixed => $typhoonInMemoryCache->reflectClassLike(AppendIterator::class)->methods()['append'],
        'typhoon, OPcache' => static fn(): mixed => $typhoonOpcache->reflectClassLike(AppendIterator::class)->methods()['append'],
        'typhoon, fresh OPcache' => static fn(): mixed => $typhoonFreshOpcache->reflectClassLike(AppendIterator::class)->methods()['append'],
    ]);
