<?php require_once('utils.php'); ?>
<link type="text/css" rel="stylesheet" href="<?php echo get_bloginfo('wpurl') ?>/wp-content/plugins/wp-practice-scheduler/css/wp-practice-scheduler.css" />
<div id="wp-practice-scheduler">
    <div>
        <h3><?php echo __("Practice Scheduler Configuration", PracticeSchedulerController::I18N_NS)?></h3>
        <i><?php echo __("Beheer uw instellingen en agenda's", PracticeSchedulerController::I18N_NS)?></i>
    </div>
<?php
if ($error) {
    echo '<div class="error">'.$error.'</div>';
}
if ($message) {
    echo '<div class="updated"><p>'.$message .'</p></div>';
}
?>

<form name="wp-practice-scheduler-configuration" class="wp-practice-scheduler" action="<? echo $_SERVER['REQUEST_URI']?>" method="POST">
<input type="hidden" name="submitted" value="1" />
<fieldset>
<legend><?php echo __("Algemene instellingen", PracticeSchedulerController::I18N_NS)?></legend>
<label><?php echo __('Tijdsduur consult', PracticeSchedulerController::I18N_NS)?></label>
<input type="text" class="short" name="<?php echo PracticeSchedulerController::OPTION_SLOTSIZE?>" value="<?php echo get_option(PracticeSchedulerController::OPTION_SLOTSIZE, 10) ?>" />
min<br />
<label><?php echo __('Maximaal ', PracticeSchedulerController::I18N_NS)?></label>
<input type="text" class="short" name="<?php echo PracticeSchedulerController::OPTION_DAYSAHEAD?>" value="<?php echo get_option(PracticeSchedulerController::OPTION_DAYSAHEAD, 7) ?>" />
dagen vooruit plannen<br />
<!--
<label><?php echo __('Openingstijden', PracticeSchedulerController::I18N_NS)?></label>
<input type="text" class="short" name="<?php echo PracticeSchedulerController::OPTION_OPENINGHOURS_OPEN?>" value="<?php echo get_option(PracticeSchedulerController::OPTION_OPENINGHOURS_OPEN, "8:30") ?>" />
- <input type="text" class="short" name="<?php echo PracticeSchedulerController::OPTION_OPENINGHOURS_CLOSE?>" value="<?php echo get_option(PracticeSchedulerController::OPTION_OPENINGHOURS_CLOSE, "16:30") ?>" />
uur
-->
</fieldset>
<fieldset>
<legend><?php echo __("Agenda's", PracticeSchedulerController::I18N_NS)?></legend>
<?php
$calendars = get_option(PracticeSchedulerController::OPTION_CALENDARS);
//$owners = get_option(PracticeSchedulerController::OPTION_OWNERS);
if (is_array($calendars)) {
    $i=1;
    foreach ($calendars as $id=>$calendar) {
        $optVar = PracticeSchedulerController::OPTION_CALENDARS."[$id]";
        $i++;
        ?>
        <label>Naam</label> <input type="text" name="<?php echo $optVar ?>[owner]" value="<?php echo $calendar['owner']; ?>" /><br />
        <label>E-mail</label> <input type="text" name="<?php echo $optVar ?>[email]" value="<?php echo $calendar['email']; ?>" /><br />
        <label>Wachtwoord</label> <input type="password" name="<?php echo $optVar ?>[key]" value="" /><br />
        <label>Beschikbaar op</label> <?php psCreateOptionList('checkbox', $optVar ."[availableDays][]", array(1=>'maandag',2=>'dinsdag',3=>'woensdag',4=>'donderdag',5=>'vrijdag',6=>'zaterdag',7=>'zondag'), $calendar['availableDays'], false); ?><br />
        <label>Openingstijden</label> <input type="text" class="short" name="<?php echo $optVar ?>[opensAt]" value="<?php echo $calendar['opensAt']; ?>" /> -
                                <input type="text" class="short" name="<?php echo $optVar ?>[closesAt]" value="<?php echo $calendar['closesAt']; ?>" /><br />
        <hr size="1" />
        <?php
    }
}
$optVar = PracticeSchedulerController::OPTION_CALENDARS."[NEW]";
echo '<br />';
echo '<h3>'.__("Nieuwe agenda", PracticeSchedulerController::I18N_NS).'</h3>';
echo '<label>Naam</label><input type="text" name="'.$optVar.'[owner]" value="" /><br />
        <label>E-mail</label><input type="text" name="'.$optVar.'[email]" value="" /><br />
        <label>Wachtwoord</label><input type="password" name="'.$optVar.'[key]" value="" /><br />';
?>      <label>Beschikbaar op</label> <?php psCreateOptionList('checkbox', $optVar ."[availableDays][]", array(1=>'maandag',2=>'dinsdag',3=>'woensdag',4=>'donderdag',5=>'vrijdag',6=>'zaterdag',7=>'zondag'), array(1,2,3,4,5), false); ?><br />
        <label>Openingstijden</label> <input type="text" class="short" name="<?php echo $optVar ."[opensAt]"; ?>" value="" /> -
                                <input type="text" class="short" name="<?php echo $optVar ."[closesAt]"; ?>" value="" /><br />
</fieldset>
<fieldset>
<legend><?php echo __("Klachtenlijst", PracticeSchedulerController::I18N_NS)?></legend>
<p>Voer per regel 1 klacht in. Geef categorie&euml;n aan door een plusje (+) aan het begin van de regel te plaatsen.</p>
<textarea name="<?php echo PracticeSchedulerController::OPTION_COMPLAINTS?>" style="width: 500px; height: 600px;">
<?php echo get_option(PracticeSchedulerController::OPTION_COMPLAINTS) ?>
</textarea>
</fieldset>
<p class="submit"><input type="submit" name="Submit"
    class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>
</div>
