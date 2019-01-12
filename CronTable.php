<?php
/**
 */

class CronTable
{
    const TYPE_ALL = 1;
    const TYPE_EVERY = 2;
    const TYPE_EXACT_TIME = 3;
    const TYPE_RANGE = 4;
    const ITEMS_ARR = [
        1 => 'weekday',
        2 => 'month',
        3 => 'day_of_month',
        4 => 'hours',
        5 => 'minutes'
    ];
    private $arr_weekdays = [
        1 => 'Monday',
        2 => 'Thuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    ];

    private $arr_months = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ];

    private $weekday;
    private $month;
    private $day_of_month;
    private $hours;
    private $minutes;
    private $type_minutes;
    private $type_hours;
    private $type_day_of_month;
    private $type_month;
    private $type_weekday;
    private $next_start;


    public function __construct($minutes, $hours, $day_of_month, $month, $weekday)
    {
        $this->minutes = $minutes;
        $this->hours = $hours;
        $this->day_of_month = $day_of_month;
        $this->month = $month;
        $this->weekday = $weekday;
        $this->get_type('minutes');
        $this->get_type('hours');
        $this->get_type('day_of_month');
        $this->get_type('month');
        $this->get_type('weekday');
        $this->next_start = strtotime('next minutes', time());

    }

    private function get_type($property)
    {
        $type_property = 'type_'.$property;
        if($this->$property === '*'){
            $this->$type_property = self::TYPE_ALL;
        } elseif(strpos($this->$property, '/') > 0) {
            $this->$type_property = self::TYPE_EVERY;
            $this->$property = substr($this->$property, strpos($this->$property, '/') + 1);
        } elseif (strpos($this->$property, '-') > 0){
            $this->$type_property = self::TYPE_RANGE;
            $range = explode('-', $this->$property);
            $this->$property = [
                'from' => ($range[0] > $range[1]) ? $range[1] : $range[0],
                'to' => $range[1]
            ];
        }else{
            $this->$type_property = self::TYPE_EXACT_TIME;
            if($property === self::ITEMS_ARR[2] || $property === self::ITEMS_ARR[4] || $property === self::ITEMS_ARR[5]) {
                if (strlen($this->$property) == 1) {
                    $this->$property = '0' . $this->$property;
                }
            }
        };

    }

    public function calculate_next_data()
    {
        $continue = true;

        while($continue) {
            if($this->get_next_weekday()) {
                if ($this->get_next_month()) {
                    if ($this->get_next_day_of_month()) {
                        if ($this->get_next_hours()) {
                            if ($this->get_next_minutes()) {
                                $continue = false;
                            }
                        }
                    }
                }
            }
        }

        $this->next_start = strtotime(date('d.m.Y H:i', $this->next_start));
        return $this->next_start;
    }

    private function get_next_weekday()
    {
        $probable_weekday = date('w', $this->next_start);
        $done = true;

        if($this->type_weekday == self::TYPE_EVERY) {
            if(!$this->check_every($probable_weekday, self::ITEMS_ARR[1])) {
                $done = false;
            }

        }elseif($this->type_weekday == self::TYPE_EXACT_TIME) {
            if(!$this->check_exact($probable_weekday, self::ITEMS_ARR[1])) {
                $done = false;
            }

        }elseif($this->type_weekday == self::TYPE_RANGE) {

            if(!$this->check_range($probable_weekday, self::ITEMS_ARR[1])) {
                $done = false;
            }
        }

        return $done;
    }

    private function get_next_month()
    {
        $probable_month = date('m', $this->next_start);
        $done = true;

        if($this->type_month == self::TYPE_EVERY) {
            if(!$this->check_every($probable_month, self::ITEMS_ARR[2])) {
                $done = false;
            }

        }elseif($this->type_month == self::TYPE_EXACT_TIME) {
            if(!$this->check_exact($probable_month, self::ITEMS_ARR[2])) {
                $done = false;
            }

        }elseif($this->type_month == self::TYPE_RANGE) {
            if(!$this->check_range($probable_month, self::ITEMS_ARR[2])) {
                $done = false;
            }

        }

        return $done;
    }

    private function get_next_day_of_month()
    {
        $probable_day_of_month = date('j', $this->next_start);
        $done = true;

        if($this->type_day_of_month == self::TYPE_EVERY) {
            if(!$this->check_every($probable_day_of_month, self::ITEMS_ARR[3])) {
                $done = false;
            }

        }elseif($this->type_day_of_month == self::TYPE_EXACT_TIME) {
            if(!$this->check_exact($probable_day_of_month, self::ITEMS_ARR[3])) {
                $done = false;
            }

        }elseif($this->type_day_of_month == self::TYPE_RANGE) {
            if(!$this->check_exact($probable_day_of_month, self::ITEMS_ARR[3])) {
                $done = false;
            }

        }

        return $done;
    }

    private function get_next_hours()
    {
        $probable_hours = date('H', $this->next_start);
        $done = true;
        if($this->type_hours == self::TYPE_EVERY) {
            if(!$this->check_every($probable_hours, self::ITEMS_ARR[4])) {
                $done = false;
            }

        }elseif($this->type_hours == self::TYPE_EXACT_TIME) {
            if(!$this->check_exact($probable_hours, self::ITEMS_ARR[4])) {
                $done = false;
            }

        }elseif($this->type_hours == self::TYPE_RANGE) {
            if(!$this->check_range($probable_hours, self::ITEMS_ARR[4])) {
                $done = false;
            }

        }

        return $done;
    }

    private function get_next_minutes()
    {
        $probable_minutes = date('i', $this->next_start);
        $done = true;

        if($this->type_minutes == self::TYPE_EVERY) {
            if(!$this->check_every($probable_minutes, self::ITEMS_ARR[5])) {
                $done = false;
            }

        }elseif($this->type_minutes == self::TYPE_EXACT_TIME) {
            if(!$this->check_exact($probable_minutes, self::ITEMS_ARR[5])) {
                $done = false;
            }

        }elseif($this->type_minutes == self::TYPE_RANGE) {
            if(!$this->check_range($probable_minutes, self::ITEMS_ARR[5])) {
                $done = false;
            }

        }

        return $done;
    }

    private function check_exact($probable_number, $item)
    {
        $exact_number = $this->$item;
        if($probable_number === $exact_number) {
            return true;
        }else {
            $this->next_start = strtotime('next minute', $this->next_start);
        }
    }

    private function check_every($probable_number, $item)
    {
        $every_number = $this->$item;
        if($this->helper_every($every_number, $item, $probable_number)) {
            return true;
        }else {
            return false;
        }
    }

    private function check_range($probable_number, $item)
    {
        $range = $this->$item;

        if($probable_number >= $range['from'] && $probable_number<= $range['to']) {
            return true;
        } else {
            $this->next_start = strtotime("next minute", $this->next_start);
            return false;
        }
    }

    private function helper_every($every_number, $item, $probable_number)
    {
        if($item === self::ITEMS_ARR[1]) {
            $start = 1;
            $end = 7;
            if($probable_number == $start) {
                return true;
            }

            for($i = $every_number; $i <= $end; $i++) {
                if($probable_number == $i) {
                    return true;
                }elseif($probable_number < $i) {
                    $this->next_start = strtotime("next {$this->arr_weekdays[$i]}", $this->next_start);
                    return false;
                }
            }
            $this->next_start = strtotime('next Monday', $this->next_start);
            return false;
        }elseif($item === self::ITEMS_ARR[2]) {
            $start = 1;
            $end = 12;

            if($probable_number == $start) {
                return true;
            }

            for($i = $every_number; $i <= $end; $i += $every_number) {

                if($probable_number == $i) {
                    return true;
                } elseif ($probable_number < $i) {
                    $this->next_start = strtotime("next minute", $this->next_start);
                    return false;
                }
            }
            $this->next_start = strtotime('next minute', $this->next_start);
            return false;
        }elseif ($item === self::ITEMS_ARR[3]) {

            $start = 1;
            $end = date('t', $this->next_start);

            if ($probable_number == $start) {
                return true;
            }

            for($i = $every_number; $i <= $end; $i += $every_number) {
                if($probable_number == $i) {
                    return true;
                }elseif($probable_number < $i) {
                    $this->next_start = strtotime('next minute', $this->next_start);
                    return false;
                }
            }
            $this->next_start = strtotime('next minute', $this->next_start);
            return false;

        }elseif ($item === self::ITEMS_ARR[4]) {
            $start = 0;
            $end = 59;

            if($probable_number == $start) {
                return true;
            }

            for ($i = $every_number; $i <= $end; $i += $every_number) {
                if ($probable_number == $i) {
                    return true;
                } elseif ($probable_number < $i) {
                    $this->next_start = strtotime('next minute', $this->next_start);
                    return false;
                }
            }
            $this->next_start = strtotime('next minute', $this->next_start);
            return false;


        } else {
            $start = 0;
            $end = 59;

            if($probable_number == $start) {
                return true;
            }

            for ($i = $every_number; $i <= $end; $i += $every_number) {
                if ($probable_number == $i) {
                    return true;
                } elseif ($probable_number < $i) {
                    $this->next_start = strtotime('next minute', $this->next_start);
                    return false;
                }
            }
            $this->next_start = strtotime('next minute', $this->next_start);
            return false;
        }
    }
}