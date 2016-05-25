<?php

class Pager
{
    // ...

    /**
     * Calculates/recalculates pages, limits, offsets, all needed stuff
     */
    protected function buildPaging()
    {
        $start = ($this->getFirstPage() > $this->getCurrentPage() - $this->range) ?
            $this->getFirstPage() :
            ($this->getCurrentPage() - $this->range);
        if ($this->getLastPage() < ($this->getCurrentPage() + $this->range)) {
            $this->pages = range($start, max($this->getLastPage(), 1));
            return;
        }

        $range = range($start, ($this->getCurrentPage() + $this->range - 1));
        $range[] = $this->getLastPage();
        $this->pages = $range;
    }

    // ...
}