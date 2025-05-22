<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi;

/**
 * Description of Topic.
 *
 * @author vitex
 */
class TopicManger extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'topic';
        parent::__construct($identifier, $options);
    }

    /**
     * Add Topics.
     *
     * @param array<string> $topics
     *
     * @return string<string, string> name=>color list of added
     */
    public function add(array $topics, string $color = 'C0C0C0')
    {
        $added = [];
        $allTopics = $this->listingQuery()->fetchAll('topic');

        foreach ($topics as $topic) {
            if (\array_key_exists($topic, $allTopics) === false) {
                if ((null === $this->insertToSQL(['topic' => $topic, 'color' => $color])) === false) {
                    $allTopics[$topic] = ['topic' => $topic];
                    $added[$topic] = $color;
                }
            }
        }

        return $added;
    }
}
