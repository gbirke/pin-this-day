<?php
/**
 * Created by PhpStorm.
 * User: gbirke
 * Date: 15.01.15
 * Time: 22:51
 */

namespace Birke\PinThisDay;

/**
 * Generate YYYY-MM-DD dates, one for each year
 * @package Birke\PinThisDay
 */
class DateGenerator {

    protected $years;

    public function __construct($years = 10) {
        $this->years = $years;
    }

    public function getYears(\DateTime $start, $includeStartYear = true){
        if (!$includeStartYear) {
            $start->modify("-1 year");
        }
        $year = $start->format("Y");
        $dateSuffix = $start->format("-m-d");
        $years = [];
        for ($i=0;$i<$this->years;$i++) {
            $years[] = sprintf("%d%s", $year, $dateSuffix);
            $year--;
        }
        return $years;
    }

} 