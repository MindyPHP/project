<?php

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';

use App\Kernel;
use Mindy\Application\App;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

if (class_exists(Dotenv::class)) {
    (new Dotenv())->load(__DIR__ . '/../.env');
}

$env = substr_count(__DIR__, 'stage') > 0 ? 'stage' : 'prod';
$env = $_ENV['APP_ENV'] ?? $env;

$debug = getenv('APP_DEBUG') ? getenv('APP_DEBUG') : 'prod' !== $env;

if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$app = App::createInstance(Kernel::class, $env, $debug);
$request = Request::createFromGlobals();
$kernel = $app->getKernel();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
