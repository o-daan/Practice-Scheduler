<h2>Selecteer uw klacht</h2>
<form name="" action="<?php echo $postUrl; ?>" method="POST">
<input type="hidden" name="<?php echo PracticeSchedulerController::FORM_STEP_VAR; ?>" value="<?php echo $nextStep; ?>" />

<p>U kunt maar één klacht aankruisen!</p>
<div class="two-column">
<?php
// parse complaints
$complaints = get_option(PracticeSchedulerController::OPTION_COMPLAINTS);
$complaints = explode("\n", $complaints);

$halfway = round(count($complaints)/2);
$i = $id = 0;
foreach ($complaints as $complaint) {
    $i++;
    $complaint = trim($complaint);
    if (preg_match('/^\+/', $complaint)) { // title
        if ($i >= $halfway) {
            echo '</div><div class="two-column">'; // split up
        }
        echo '<h3>'.preg_replace('/^\+\s*/', '', $complaint).'</h3>';
        continue;
    }
    if ($complaint == '') { // empty line
        echo '<br />';
        continue;
    }
    $id++;
    $selected = ($selectedComplaint && $selectedComplaint == $complaint) ? 'checked="checked"' : "";
    echo '<input name="complaint" type="radio" value="'.$complaint.'" id="'.$id.'" '.$selected.' /><label for="'.$id.'">'.$complaint.'</label><br />';
}
?>
</div>
<p>&nbsp;</p>
<p><input type="submit" value="Verder" /></p>
</form>
