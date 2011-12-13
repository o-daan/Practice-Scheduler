<?php
require_once('PracticeSchedulerAPI.php');
require_once('PracticeSchedulerAppointment.php');
class PracticeSchedulerController {

    const PLUGIN_PATH = '/wp-content/plugins/wp-practice-scheduler';

    const SESSION_ID = 'wp-practice-scheduler';

    const OPTION_SLOTSIZE = 'wp-practice-calendar-slotsize';
    const OPTION_DAYSAHEAD = 'wp-practice-calendar-daysahead';
    const OPTION_OPENINGHOURS_OPEN = 'wp-practice-calendar-openinghours-open';
    const OPTION_OPENINGHOURS_CLOSE = 'wp-practice-calendar-openinghours-close';
    const OPTION_CALENDARS = 'wp-practice-calendar-calendars';
    const OPTION_OWNERS = 'wp-practice-calendar-owners';
    const OPTION_COMPLAINTS = 'wp-practice-calendar-complaints';
    const FORM_STEP_VAR = 'psfs';
    const I18N_NS = 'wp-practice-scheduler';

    /**
     * Setup the options, locale, shortcode, session.
     */
    public function __construct() {
        setlocale(LC_TIME, array('nl_NL','nl_NL.utf8'));
        add_option(self::OPTION_SLOTSIZE);
        add_option(self::OPTION_CALENDARS);
        add_shortcode('practice-scheduler', array($this, 'execute'));

        if (!session_id()) {
            session_start();
        }
        if (!$_SESSION[self::SESSION_ID]) $_SESSION[self::SESSION_ID] = array();
        if (isset($_POST[self::FORM_STEP_VAR])) {
            $this->storePostData();
        }
    }

    /**
     * Manages the flow.
     */
    public function execute() {
        $requestedStep = $_REQUEST[self::FORM_STEP_VAR]; // formstep
        $formDir = dirname(__FILE__)."/forms/";

        $step = $this->getActualStep();
        if ($requestedStep && $step < $requestedStep) {
            $errors = "U dient een keuze te maken.";
        } else if ($requestedStep && $requestedStep < $step) {
            $step = $requestedStep; // user has gone back to change something
        }

        switch ($step) {
            case 0:
                $form = "not_allowed.php";
                break;
            case 5:
                try {
                    $this->writeToCalendar();
                    if (!$this->sendConfirmationEmail()) {
                        $errors = "De e-mail kon niet verzonden worden, maar de afspraak is wel vastgelegd.";
                    }
                    $this->clearSession();
                    $form = 'thankyou.php';
                } catch (PracticeSchedulerNoAvailabilityException $e) {
                    $errors = "De gewenste afspraak is niet meer beschikbaar. Selecteer een nieuw tijdstip.";
                    $form = 'select_date.php';
                    $nextStep = 3;
                }
                break;
            case 4:
                $form = 'confirm_appointment.php';
                $nextStep = 5;
                break;
            case 3:
                $form = 'select_complaint.php';
                $nextStep = 4;
                break;
            case 2:
                $form = 'select_date.php';
                $nextStep = 3;
                break;
            case 1:
            default:
                $form = 'select_doctor.php';
                $nextStep = 2;
        }
        // make stored data available in view scope
        $calendars = get_option(self::OPTION_CALENDARS);
        extract($this->getSubmittedData());
        $postUrl = $_SERVER['REQUEST_URI'];

        ob_start();
        require_once($formDir."header.php");
        require_once($formDir.$form);
        require_once($formDir."footer.php");
        return ob_get_clean();
    }

    /**
     * Decide what the current step should be based on the stored data.
     */
    private function getActualStep() {
        $user = wp_get_current_user();
        if (!$user->ID) {
            return 0;
        }
        extract($this->getSubmittedData());
        if (!$selectedDoctorId) {
            $step = 1;
        } else if (!$selectedDate || !$selectedTime) {
            $step = 2;
        } else if (!$selectedComplaint) {
            $step = 3;
        } else if (!$selectedConfirmation) {
            $step = 4;
        } else {
            $step = 5;
        }
        return $step;
    }

    /**
     * Write the appointment to the calendar.
     */
    private function writeToCalendar() {
        $appointment = $this->getAppointment();
        $api = new PracticeSchedulerAPI($appointment->doctor->email, $appointment->doctor->key);

        // store it
        $user = wp_get_current_user();
        $titel = preg_match('/^m/i', $user->geslacht) ? "Dhr." : "Mevr.";

        $result = $api->addAppointmentToCalendar($appointment->startTime, $appointment->endTime,
"$titel $user->last_name ($user->geboortedatum)", "$titel $user->first_name $user->last_name
Klacht: $appointment->complaint

- Ingepland via website",
            "Praktijk");
        return $result;
    }

    /**
     * @return PracticeSchedulerAppointment
     */
    private function getAppointment() {
        extract($this->getSubmittedData());
        $doctors = get_option(self::OPTION_CALENDARS);
        $slotSize = get_option(self::OPTION_SLOTSIZE);
        $doctor = $doctors[$selectedDoctorId];

        $appointment = new PracticeSchedulerAppointment();
        $appointment->doctor = (object)$doctor;
        $appointment->startTime = $selectedTime;
        $appointment->endTime = $selectedTime+$slotSize*60;
        $appointment->complaint = $selectedComplaint;
        return $appointment;
    }

    /**
     * Send a confirmation of the appointment
     * @return boolean
     */
    private function sendConfirmationEmail() {
        $user = wp_get_current_user();
        $appointment = $this->getAppointment();
        $confirmation =
"U heeft via onze website een afspraak gemaakt voor een consult. Hieronder ziet u de details van deze afspraak.

Huisarts: ".$appointment->doctor->owner."
Datum en tijd: ".trim(strftime('%e %B %Y', $appointment->startTime)).", ".strftime('%H:%M', $appointment->startTime)." - ".strftime('%H:%M', $appointment->endTime)." uur
Klacht: $appointment->complaint

Mocht u nog vragen hebben over deze afspraak, of de afspraak willen annuleren, dan wordt u verzocht hierover telefonisch contact op te nemen met de praktijk.
";

        return wp_mail($user->user_email, "Bevestiging consult ".trim(strftime('%e %B', $appointment->startTime)), $confirmation);
    }

    /**
     * Clear session data
     */
    private function clearSession() {
        $_SESSION[self::SESSION_ID] = array();
    }

    /**
     * Store post data in session.
     */
    private function storePostData() {
        foreach ($_POST as $k=>$v) {
            $var = 'selected'.ucfirst($k); // store the selected $var as $selectedVar
            $_SESSION[self::SESSION_ID][$var] = $v;
        }
    }

    /**
     * Unpack session data.
     */
    private function getSubmittedData() {
        return $_SESSION[self::SESSION_ID];
    }

    /**
     * Creates a url to jump to a specific step in the registration process.
     * @param unknown_type $step
     */
    public static function getUrlForStep($step) {
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'], $data);
        $data[self::FORM_STEP_VAR] = $step;

        return $_SERVER['REDIRECT_URL'] . '?' . http_build_query($data);
    }

    /**
     * Utility function to tell if a certain date is in the weekend.
     * @param date $date
     */
    public static function isWeekendDay($date) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = strtotime($date);
        }
        return intval(strftime('%u', $date)) >= 6; // Saturday or Sunday
    }
}