<form name="" action="<?php echo $postUrl; ?>" method="POST">
<div class="three-column">
<h3>Kies een datum</h3>
<input type="hidden" name="<?php echo PracticeSchedulerController::FORM_STEP_VAR; ?>" value="<?php echo $nextStep; ?>" />
<?php
$daysAhead = get_option(PracticeSchedulerController::OPTION_DAYSAHEAD, 7);
$ajaxUrl = PracticeSchedulerController::PLUGIN_PATH . '/forms/select_timeslot.php?';
for ($i=1; $i<=$daysAhead; $i++) {
    $date = mktime(0,0,0,date('m'),date('d')+$i,date('Y'));
    if (PracticeSchedulerController::isWeekendDay($date)) continue;
    $value = strftime('%Y-%m-%d', $date);
    $selected = ($value == $selectedDate) ? "checked" : "";
    echo '<div class="select-date"><input type="radio" name="date" value="'.$value.'" id="date_'.$value.'" '.$selected.' /> <label for="date_'.$value.'">'.ucfirst(strftime('%A %e %B', $date)).'</label></div>';
}

?>
</div>
<div id="wp-practice-scheduler-available-timeslots" class="three-column">

</div>
<div id="wp-practice-scheduler-select-appointment" class="three-column">

</div>
</form>

<script>
function getAvailableTimeSlots(event) {
    $('#wp-practice-scheduler-available-timeslots').html('<p class="ajax-progress">Bezig met ophalen beschikbaarheid...<p>');
    $("#wp-practice-scheduler-select-appointment").html('');

    var $tgt = $(event.target);
    $(".select-date").removeClass('selected');
    $tgt.parents('div:eq(0)').addClass('selected');
    var date = $tgt.attr('value');
    var requestUri = '<?php echo $ajaxUrl?>date=' + date + '&doctorId=<?php echo $selectedDoctorId ?>';
    $('#wp-practice-scheduler-available-timeslots').load(requestUri);
}
function selectTimeSlot(event) {
    var $tgt = $(event.target);
    $(".select-time").removeClass('selected');
    $tgt.parents('div:eq(0)').addClass('selected');

    $("#wp-practice-scheduler-select-appointment").html('<p><input type="submit" value="Afspraak inplannen" /></p>');
}
$(document).ready(function() {
    $(".select-date input").bind("click", getAvailableTimeSlots);
    if ($(".select-date input:checked").val()) {
        $(".select-date input:checked").trigger('click');
    }
});
</script>