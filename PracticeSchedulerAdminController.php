<?php
// revert the magic quotes hell that Wordpress imposes...
array_walk_recursive($_GET, array('PracticeSchedulerAdminController', 'stripMySlashesOff'));
array_walk_recursive($_POST, array('PracticeSchedulerAdminController', 'stripMySlashesOff'));
array_walk_recursive($_COOKIE, array('PracticeSchedulerAdminController', 'stripMySlashesOff'));
array_walk_recursive($_REQUEST, array('PracticeSchedulerAdminController', 'stripMySlashesOff'));

class PracticeSchedulerAdminController {

    const KEYSALT = "Fhdu4#hgruuuesdbbhgrq&^ianwjk&";

    /**
     * This method is called to disable the Wordpress magic quotes at runtime.
     * @param string $value
     */
    public static function stripMySlashesOff(&$value) {
        $value = stripslashes_deep($value);
    }

    public function __construct() {

    }

    /**
     * Display the admin form
     */
    public function execute() {
        if (!current_user_can('manage_options')) {
            wp_die(_("Access denied"));
        }
        if (isset($_POST['submitted'])) {
            try {
                $this->store();
                $message = __("Uw wijzigingen zijn opgeslagen", PracticeSchedulerController::I18N_NS);
            } catch (PracticeSchedulerAdminException $e) {
                $error = $e->getMessage();
            }
        }
        require_once('forms/admin.php');
    }

    /**
     * Store the data
     */
    private function store() {
        if (intval($_POST[PracticeSchedulerController::OPTION_SLOTSIZE]) < 5 || intval($_POST[PracticeSchedulerController::OPTION_SLOTSIZE]) > 60) {
            throw new PracticeSchedulerAdminException(__("De duur van het consult moet liggen tussen de 5 en 60 minuten", PracticeSchedulerController::I18N_NS));
        }
        if (is_array($_POST[PracticeSchedulerController::OPTION_CALENDARS])) {
            $currentCalendars = get_option(PracticeSchedulerController::OPTION_CALENDARS);

            $calendars = array();
            foreach ($_POST[PracticeSchedulerController::OPTION_CALENDARS] as $id=>$data) {
                $owner = $data['owner'];
                $email = $data['email'];
                $key = $data['key'];
                $availableDays = $data['availableDays'];
                $opensAt = $data['opensAt'];
                $closesAt = $data['closesAt'];
                if (trim($key) == '') $key = $currentCalendars[$id]['key']; // no password change
                if ($key != "" || $owner != "" || $email != "") {
                    if ($id == 'NEW') $id = $this->createId($email);
                    $calendars [$id]= array('id'=>$id, 'owner'=>trim($owner), 'email'=>trim($email), 'key'=>trim($key), 'availableDays'=>$availableDays, 'opensAt'=>trim($opensAt), 'closesAt'=>trim($closesAt));
                }
            }
            update_option(PracticeSchedulerController::OPTION_CALENDARS, $calendars);
        }
        update_option(PracticeSchedulerController::OPTION_SLOTSIZE, intval($_POST[PracticeSchedulerController::OPTION_SLOTSIZE]));
        update_option(PracticeSchedulerController::OPTION_OPENINGHOURS_OPEN, trim($_POST[PracticeSchedulerController::OPTION_OPENINGHOURS_OPEN]));
        update_option(PracticeSchedulerController::OPTION_OPENINGHOURS_CLOSE, trim($_POST[PracticeSchedulerController::OPTION_OPENINGHOURS_CLOSE]));
        update_option(PracticeSchedulerController::OPTION_DAYSAHEAD, intval($_POST[PracticeSchedulerController::OPTION_DAYSAHEAD]));
        update_option(PracticeSchedulerController::OPTION_COMPLAINTS, trim($_POST[PracticeSchedulerController::OPTION_COMPLAINTS]));
    }

    /**
     * Hash a key and use it as an id.
     * @param string $key
     * @return string
     */
    private function createId($key) {
        return md5(md5(self::KEYSALT . $key));
    }
}

class PracticeSchedulerAdminException extends Exception {}