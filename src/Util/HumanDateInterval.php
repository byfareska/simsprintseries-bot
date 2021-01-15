<?php declare(strict_types=1);

namespace App\Util;

use DateInterval;
use DateTime;

final class HumanDateInterval
{
    private DateInterval $interval;
    private DateTime $now;
    private DateTime $date;
    private ?DateInterval $diffByDateWithoutTime = null;

    public function __construct(DateTime $dateTime, ?DateTime $now = null)
    {
        $this->now = $now === null ? new DateTime() : $now;
        $this->date = $dateTime;
        $this->interval = $this->now->diff($dateTime);
    }

    public function getInterval(): DateInterval
    {
        return $this->interval;
    }

    public function toString(): string
    {
        $methods = [
            "years", "months", "days"
        ];

        $i = -1;
        $result = null;

        while ($result === null) {
            $method = $methods[++$i];
            $result = $this->$method();
        }

        return $result;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function years(): ?string
    {
        if ($this->interval->y === 0)
            return null;

        $years = $this->interval->y;
        $prefix = $this->interval->m >= 4 ? "ponad " : "";

        return "{$prefix}{$years} lata";
    }

    private function months(): ?string
    {
        if ($this->interval->m === 0)
            return null;

        $months = $this->interval->m === 1 ? "" : $this->interval->m;
        $prefix = $this->interval->d >= 10 ? "ponad " : "";
        $suffix = $this->interval->m === 1 ? "miesiąc" : "miesiące";

        return "{$prefix}{$months}{$suffix}";
    }

    private function getDiffByDateWithoutTime(): DateInterval
    {
        if ($this->diffByDateWithoutTime === null) {
            $now = new DateTime($this->now->format("Y-m-d"));
            $date = new DateTime($this->date->format("Y-m-d"));
            $this->diffByDateWithoutTime = $now->diff($date);
        }

        return $this->diffByDateWithoutTime;
    }

    private function days(): ?string
    {
        $day = $this->day();

        if ($day === null)
            return "{$this->getDiffByDateWithoutTime()->d} dni";

        return "{$day}, {$this->date->format("H:i")}";
    }

    private function day(): ?string
    {
        if ($this->getDiffByDateWithoutTime()->d === 0)
            return "dziś";

        if ($this->getDiffByDateWithoutTime()->d === 1)
            return "jutro";

        if ($this->getDiffByDateWithoutTime()->d === 2)
            return "pojutrze";

        if ($this->getDiffByDateWithoutTime()->d <= 7)
            return self::translateDayName((int)$this->date->format("w"));

        return null;
    }

    public static function translateDayName(int $day): string
    {
        switch ($day) {
            case 0:
                return "Niedziela";
            case 1:
                return "Poniedziałek";
            case 2:
                return "Wtorek";
            case 3:
                return "Środa";
            case 4:
                return "Czwartek";
            case 5:
                return "Piątek";
        }

        return "Sobota";
    }

    public static function translateMonthName(int $month): string
    {
        switch ($month) {
            case 1:
                return "Stycznia";
            case 2:
                return "Lutego";
            case 3:
                return "Marca";
            case 4:
                return "Kwietnia";
            case 5:
                return "Maja";
            case 6:
                return "Czerwca";
            case 7:
                return "Lipca";
            case 8:
                return "Sierpnia";
            case 9:
                return "Września";
            case 10:
                return "Października";
            case 11:
                return "Listopada";
        }

        return "Grudnia";
    }
}