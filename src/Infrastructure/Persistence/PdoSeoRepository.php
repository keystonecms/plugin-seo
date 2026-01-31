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

namespace Keystone\Plugin\Seo\Infrastructure\Persistence;

use PDO;
use Keystone\Plugin\Seo\Domain\{
    SeoRepositoryInterface,
    SeoSubject,
    SeoMetadata
};

final class PdoSeoRepository implements SeoRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function find(SeoSubject $subject): ?SeoMetadata
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM seo_metadata
             WHERE subject_type = :type
             AND subject_id = :id'
        );

        $stmt->execute([
            'type' => $subject->type(),
            'id'   => $subject->id(),
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new SeoMetadata(
            $row['title'],
            $row['description'],
            (bool) $row['no_index'],
            $row['canonical'],
            $row['open_graph']
                ? json_decode($row['open_graph'], true)
                : []
        );
    }

    public function save(
        SeoSubject $subject,
        SeoMetadata $metadata
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO seo_metadata (
                subject_type,
                subject_id,
                title,
                description,
                no_index,
                canonical,
                open_graph,
                created_at,
                updated_at
            ) VALUES (
                :type,
                :id,
                :title,
                :description,
                :no_index,
                :canonical,
                :open_graph,
                NOW(),
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                description = VALUES(description),
                no_index = VALUES(no_index),
                canonical = VALUES(canonical),
                open_graph = VALUES(open_graph),
                updated_at = NOW()
        ');

        $stmt->execute([
            'type'        => $subject->type(),
            'id'          => $subject->id(),
            'title'       => $metadata->title(),
            'description' => $metadata->description(),
            'no_index'    => (int) $metadata->noIndex(),
            'canonical'   => $metadata->canonical(),
            'open_graph'  => json_encode($metadata->openGraph()),
        ]);
    }
}

?>