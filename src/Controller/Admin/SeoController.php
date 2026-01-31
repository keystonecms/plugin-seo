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

namespace Keystone\Plugin\Seo\Controller\Admin;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use Keystone\Domain\User\CurrentUser;

use Keystone\Plugin\Pages\Domain\PageService;


use Keystone\Plugin\Seo\Domain\{
    SeoService,
    SeoSubject,
    SeoMetadata
};
use Keystone\Http\Controllers\BaseController;

final class SeoController extends BaseController {
    public function __construct(
        private Twig $view,
        private SeoService $seoService,
        private PageService $pageService,
        private CurrentUser $currentUser
    ) { }

 public function edit(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {


$page = $this->pageService->findById((int) $args['id']);

$subject = new SeoSubject(
            $args['type'],
            (int) $args['id']
        );

$seo = $this->seoService->getForSubject(
            subject: $subject,
            fallbackTitle: $page->title(),
            fallbackDescription: mb_substr(strip_tags($page->content_html()),0,160),
            fallbackSlug: $page->slug(),
            baseUrl: $_ENV['BASE_URL']
    );

        return $this->view->render($response, '@seo/admin/edit.twig', [
            'subject' => $subject,
            'seo' => $seo,
            'page' => $page
        ]);
    }

    public function update(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {


        $subject = new SeoSubject(
            $args['type'],
            (int) $args['id']
        );

        $data = $request->getParsedBody();

        $seo = new SeoMetadata(
            title: $data['title'] ?? '',
            description: $data['description'] ?? '',
            noIndex: isset($data['no_index']),
            canonical: $data['canonical'] ?: null,
            openGraph: [] // later uitbreidbaar
        );

        $this->seoService->update($subject, $seo);

   return $this->json($response, [
        'status'    => 'success',
        'message' => 'SEO details saved',

    ]);


    }
}

?>