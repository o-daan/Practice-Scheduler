<img src="http://huisartsendeburgt.nl/wp-content/themes/deburgt/images/Scheduler-Timeline-1.gif" />
<h2>Selecteer uw arts</h2>
<form name="" action="<?php echo $postUrl; ?>" method="POST">
<input type="hidden" name="<?php echo PracticeSchedulerController::FORM_STEP_VAR; ?>" value="<?php echo $nextStep; ?>" />

<?php
foreach ($calendars as $calendar) {
    $selected = $calendar['id'] == $selectedDoctorId ? 'checked' : '';
    echo '<input type="radio" name="doctorId" value="'.$calendar['id'].'" id="doc'.$calendar['id'].'" '.$selected.' /><label for="doc'.$calendar['id'].'">'.$calendar['owner'].'</label><br />';
}

?>
<p><input type="submit" value="Verder" /></p>
</form>
