<form name="" action="<?php echo $postUrl; ?>" method="POST">
<input type="hidden" name="<?php echo PracticeSchedulerController::FORM_STEP_VAR; ?>" value="<?php echo $nextStep; ?>" />
<input type="hidden" name="confirmation" value="1" />
<?php
$calendar = $calendars[$selectedDoctorId];
$user = wp_get_current_user(); // get the logged in user
?>
<h3>Controleer uw gegevens</h3>
<p>
<strong>Arts:</strong> <?php echo $calendar['owner']?>
 <a class="wp-practice-scheduler-update" href="<?php echo PracticeSchedulerController::getUrlForStep(1); ?>">wijzig</a><br />
<strong>Datum en tijd:</strong> <?php echo strftime('%e %B %Y', strtotime($selectedDate))?>, <?php echo strftime("%H:%M uur", $selectedTime)?>
 <a class="wp-practice-scheduler-update" href="<?php echo PracticeSchedulerController::getUrlForStep(2); ?>">wijzig</a><br />
<strong>Klacht:</strong> <?php echo $selectedComplaint?>
 <a class="wp-practice-scheduler-update" href="<?php echo PracticeSchedulerController::getUrlForStep(3); ?>">wijzig</a><br /><br />
<strong>Naam pati&euml;nt:</strong> <?php echo $user->first_name . " " . $user->last_name ?><br />
</p>
<p>
    <input type="submit" value="Afspraak bevestigen" />
</p>
</form>