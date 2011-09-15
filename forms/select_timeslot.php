<?php
error_reporting(E_ALL & ~ (E_NOTICE | E_DEPRECATED));

require_once(dirname(dirname(__FILE__)) .'/PracticeSchedulerAPI.php');

require_once("../../../../wp-config.php"); // this is an ajax request
$slotSize = get_option(PracticeSchedulerController::OPTION_SLOTSIZE, 10)*60; // seconds
$openingHours = get_option(PracticeSchedulerController::OPTION_OPENINGHOURS_OPEN, "8:00");
$closingHours = get_option(PracticeSchedulerController::OPTION_OPENINGHOURS_CLOSE, "16:30");
$calendars = get_option(PracticeSchedulerController::OPTION_CALENDARS);
$doctorId = $_REQUEST['doctorId'];
$selectedDoctor = $calendars[$doctorId];
$api = new PracticeSchedulerAPI($selectedDoctor['email'], $selectedDoctor['key']);

try {
    $appointments = $api->getAppointmentsForDate($_REQUEST['date']);
} catch (PracticeSchedulerRequestFailedException $e) {
    echo "Er is iets misgegaan. Probeer het opnieuw."; die;
}
$slots = array();
$startTime = explode(':', $openingHours);
$endTime = explode(':', $closingHours);

$date = strtotime($_REQUEST['date']);
$startTs = mktime($startTime[0], $startTime[1], 0, strftime('%m', $date), strftime('%d', $date), strftime('%Y', $date));
$endTs = mktime($endTime[0], $endTime[1], 0, strftime('%m', $date), strftime('%d', $date), strftime('%Y', $date));

$availableSlots = array();
for ($time = $startTs; $time <= $endTs; $time += $slotSize) {
    if ($time + $slotSize > $endTs) break; // no more space
    if ($api->isSlotAvailable($time,$time+$slotSize,$appointments)) {
        $selected = ($time == $selectedTime) ? "checked" : "";
        $availableSlots []= '<div class="select-time"><input type="radio" name="time" value="'.$time.'" id="time_'.$time.'" '.$selected.' />
            <label for="time_'.$time.'">'.strftime("%H:%M", $time) . " - " . strftime("%H:%M", $time+$slotSize).'</label>
            </div>';
    }
}

echo '<h3>Kies een tijdstip</h3>';
if (count($availableSlots) == 0) {
    echo "Deze dag is vol.";
} else {
    echo implode('',$availableSlots);
    ?>
    <script>
    $(document).ready(function() {
        $(".select-time input").bind("click", selectTimeSlot);
    });
   </script>
    <?php
}

