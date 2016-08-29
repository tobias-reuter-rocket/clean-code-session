<?php

namespace CleanCode\SimpleRefactoring;

class Invoices
{
    private $invoiceDates;

    public function __construct($invoiceDates)
    {
        $this->invoiceDates = $invoiceDates;
    }

    /**
     * Problems: cannot cover method with tests, cannot use another date
     * @return int|string
     */
    public function getNextProgressiveNumber()
    {
        $currentDate = date('Y-m-d');
        foreach ($this->invoiceDates as $number => $date) {
            if ($date > $currentDate) {
                $nextNumber = $number;
                break;
            }
        }
        if (!isset($nextNumber)) {
            $nextNumber = count($this->invoiceDates) + 1;
        }
        return $nextNumber;
    }
}

/** Refactoring */
class InvoicesRefactoredSimple
{
    private $invoiceDates;

    public function __construct($invoiceDates)
    {
        $this->invoiceDates = $invoiceDates;
    }

    /**
     * Problems: cannot cover method with tests, cannot use another date
     * @return int|string
     */
    public function getNextProgressiveNumber()
    {
        $currentDate = date('Y-m-d');
        // instead of the hardcoded date, we introduced the parameter
        return $this->getNextProgressiveNumberByDate($currentDate);
    }

    /**
     * Now this method can be covered with tests
     * @param $currentDate
     * @return int|string
     */
    public function getNextProgressiveNumberByDate($currentDate)
    {
        foreach ($this->invoiceDates as $number => $date) {
            if ($date > $currentDate) {
                $nextNumber = $number;
                break;
            }
        }
        if (!isset($nextNumber)) {
            $nextNumber = count($this->invoiceDates) + 1;
        }
        return $nextNumber;
    }
}
