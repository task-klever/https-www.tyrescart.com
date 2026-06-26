<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SearchBuilderInterface
{
    /**
     * @param $searchTerm
     * @return null|SearchInterface
     */
    public function getSearchEvent($searchTerm);
}
