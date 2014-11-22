<?php

namespace Plummer\Calendarful\Recurrence\Type;

use Plummer\Calendarful\Recurrence\RecurrenceInterface;

class Monthly implements RecurrenceInterface
{
	protected $label = 'monthly';

	protected $limit = '+25 year';

	public function getLabel()
	{
		return $this->label;
	}

	public function getLimit()
	{
		return $this->limit;
	}

	public function generateOccurrences(Array $events, \DateTime $fromDate, \DateTime $toDate, $limit = null)
	{
		$return = [];

		$monthlyEvents = array_filter($events, function ($event) {
			return $event->getRecurrenceType() === $this->getLabel();
		});

		foreach ($monthlyEvents as $monthlyEvent) {

			$startMarker = $fromDate > new \DateTime($monthlyEvent->getStartDate())
				? $fromDate
				: new \DateTime($monthlyEvent->getStartDate());

			$endMarker = $monthlyEvent->getRecurrenceUntil()
				? min(new \DateTime($monthlyEvent->getRecurrenceUntil()), $toDate)
				: $toDate;

			// The DatePeriod class does not actually include the end date so you have to increment it first
			$endMarker->modify('+1 day');

			$dateInterval = new \DateInterval('P1M');
			$datePeriod = new \DatePeriod($startMarker, $dateInterval, $endMarker);

			foreach($datePeriod as $date) {
				$newMonthlyEvent = clone($monthlyEvent);
				$newStartDate = $date;
				$duration = $newMonthlyEvent->getDuration();

				$newMonthlyEvent->setStartDate($newStartDate);
				$newStartDate->add($duration);
				$newMonthlyEvent->setEndDate($newStartDate);
				$newMonthlyEvent->setRecurrenceType();

				$return[] = $newMonthlyEvent;
			}
		}

		return $return;
	}
}