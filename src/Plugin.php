<?php

/*
 * Keystone CMS
 *
 * @author Constan van Suchtelen van de Haere <constan.vansuchtelenvandehaere@hostingbe.com>
 * @copyright 2026 HostingBE
 * @package   Keystone CMS
 * @author    HostingBE
 * @license   MIT
 * @link      https://keystone-cms.com
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
 * files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
 * is furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF
 * OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use function DI\autowire;
use Keystone\Core\Plugin\PluginInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\Twig;


use Keystone\Plugin\Seo\Domain\{
    SeoRepositoryInterface,
    SeoSubject,
    SeoMetadata,
    SeoService,
    SeoPolicy
};
use Keystone\Plugin\Seo\Twig\SeoExtension;
use Keystone\Plugin\Seo\Infrastructure\Persistence\PdoSeoRepository;


final class Plugin implements PluginInterface {

    public function getName(): string {
        return 'seo';
    }

    public function getVersion(): string {
        return 'v1.0.0';
    }

    public function getDescription(): string {
        return 'SEO metadata management';
    }

    public function getLoadOrder(): int {
    return 20; 
    }

    public function register(ContainerInterface $container): void {
        // repository
        $container->set(
            SeoRepositoryInterface::class,
            autowire(PdoSeoRepository::class)
        );

        // service
        $container->set(
            SeoService::class,
            autowire()
        );
    }

    public function boot(App $app, ContainerInterface $container): void {
        // twig namespace
        $twig = $container->get(Twig::class);
        $twig->getLoader()->addPath(
            __DIR__ . '/views',
            'seo'
        );

          // SEO twig extension
    $twig->addExtension(
        $container->get(SeoExtension::class)
    );

        // routes
        require __DIR__ . '/routes/admin.php';
    }
};


?>