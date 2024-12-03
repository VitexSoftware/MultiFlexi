<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Topic
 *
 * @author vitex
 */
class Topic extends Engine
{

    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'topic';
        parent::__construct($identifier, $options);
    }

    /**
     * Add Topics
     * 
     * @param array<string> $topics
     * @param string $color
     * 
     * @return string<string,string> name=>color list of added
     */
    public function add(array $topics, string $color = 'C0C0C0')
    {
        $added = [];
        $allTopics = $this->listingQuery()->fetchAll('topic');
        foreach ($topics as $topic){
            if(array_key_exists($topic, $allTopics) == false){
                if(is_null($this->insertToSQL(['topic'=>$topic,'color'=>$color])) === false){
                    $allTopics[$topic] = ['topic'=>$topic];
                    $added[$topic] = $color;
                } 
            }
        }
        return $added;
    }
}
