<?php
set_include_path(get_include_path().":".dirname(__FILE__)."/3rdparty/");
//error_reporting(E_ALL);
//date_default_timezone_set('Europe/Amsterdam');
//setlocale(LC_TIME, array('nl_NL', 'nl_NL.utf8'));

require_once('Zend/Gdata/Calendar.php');
require_once('Zend/Gdata/ClientLogin.php');

class PracticeSchedulerAPI {

    /**
     * @var Zend_Gdata_Calendar
     */
    private $gdataCal;

    public function __construct($email, $password) {
        $this->gdataCal = $this->getCalendar($email, $password);
    }

    /**
     * Retrieves a list of appointments for a given date.
     * @param unknown_type $date
     * @param string $user
     * @param string $password
     * @throws PracticeSchedulerRequestFailedException
     */
    public function getAppointmentsForDate($date) {
        $startDate = $date . 'T00:00:00';
        $endDate = $date . 'T23:59:59';
        //        $query = new Zend_Gdata_Calendar_EventQuery();
        $query = $this->gdataCal->newEventQuery();
        $query->setProjection('full');
        $query->setStartMin($startDate);
        $query->setStartMax($endDate);
        $query->setUser('default');
        $query->setVisibility('private');
        $query->setProjection('full');
        $query->setOrderby('starttime');

        $appointments = array();
        // appointments come in "2011-08-22T12:00:00.000+02:00" format
        try {
            $eventFeed = $this->gdataCal->getCalendarEventFeed($query);
            foreach ($eventFeed as $event) {
                $when = $event->when[0];
                $startTime = substr($when->getStartTime(), 0, 19); // strip off TZ
                $endTime = substr($when->getEndTime(), 0, 19);
                $appointments []= array(strtotime($startTime)+$offset,strtotime($endTime)+$offset);
            }
        } catch (Zend_Gdata_App_InvalidArgumentException $e) {
            // send mail? try again? no availability?
            throw new PracticeSchedulerRequestFailedException($e->getMessage());
        }
        return $appointments;
    }

    /**
     * Add appointment to Google Calendar
     * @param int $startTime
     * @param int $endTime
     * @param string $title
     * @param string $description
     * @param string $location
     */
    public function addAppointmentToCalendar($startTime, $endTime, $title, $description, $location) {
        // verify that the appointment is still valid
        $appointments = $this->getAppointmentsForDate(strftime('%Y-%m-%d', $startTime));
        if (!$this->isSlotAvailable($startTime, $endTime, $appointments)) {
            throw new PracticeSchedulerNoAvailabilityException("The selected timeframe is not available");
        }
        $newEvent = $this->gdataCal->newEventEntry();

        $newEvent->title = $this->gdataCal->newTitle($title);
        $newEvent->content = $this->gdataCal->newContent($description);
        $newEvent->where = array($this->gdataCal->newWhere($location));

        $when = $this->gdataCal->newWhen();
        $startTimeStr = strftime('%Y-%m-%dT%H:%M:00.000', $startTime);
        $endTimeStr = strftime('%Y-%m-%dT%H:%M:00.000', $endTime);
        $when->startTime = $startTimeStr;
        $when->endTime = $endTimeStr;
        $newEvent->when = array($when);

        $createdEvent = $this->gdataCal->insertEvent($newEvent);
        return $createdEvent->id->text;
    }

    /**
     * Get a calendar.
     * @param string $username
     * @param string $password
     * @return Zend_Gdata_Calendar
     */
    private function getCalendar($username, $password) {
        $service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME; // predefined service name for calendar
        $client = Zend_Gdata_ClientLogin::getHttpClient($username, $password, $service);
        return new Zend_Gdata_Calendar($client);
    }

    public static function isSlotAvailable($startTime, $endTime, $appointments) {
        foreach ($appointments as $a) {
            if ($a[0] < $endTime && $a[1] > $startTime) {
                return false;
            }
        }
        return true;
    }
}

class PracticeSchedulerRequestFailedException extends Exception {}
class PracticeSchedulerNoAvailabilityException extends Exception {}
