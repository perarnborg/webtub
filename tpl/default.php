<?php if(isset($account->telldusData->data['tubTemp'])): ?>
<div class="current">
  <div>
  Current tub temp: <span class="js-tub-temp"><?php echo $account->telldusData->data['tubTemp']; ?></span>&deg;C
  </div>
<?php if(isset($account->telldusData->data['airTemp'])): ?>
  <div>
  Current air temp: <span class="js-air-temp"><?php echo $account->telldusData->data['airTemp']; ?></span>&deg;C
  </div>
<?php endif; ?>
<?php if(!is_null($account->telldusData->data['tubStateOn'])): ?>
  <div>
  The tub is turned <span class="js-tub-state <?php echo $account->telldusData->data['tubStateOn'] ? "on" : "off" ?>"><?php echo $account->telldusData->data['tubStateOn'] ? "on" : "off" ?></span>
  </div>
<?php endif; ?>
  <div>
  Last checked <span class="js-last-checked <?php if($account->telldusData->data['tubLastCheckedRecently']): ?>off<?php endif; ?>"><?php echo date('H:i', $account->telldusData->data['tubLastChecked']); ?></span>
  </div>
</div>
<?php endif; ?>

<?php if(!$account->hasMissingRequiredSettings): ?>
<form class="tub-time" action="/post/tubtime" method="post" onsubmit="return validateTubTime();">
  <?php if($account->tubTime) { ?>
    <h2>The tub will be warm at:</h2>
  <?php } else { ?>
    <h2>When do you want to take a bath?</h2>
  <?php } ?>
  <div>
    <label for="js-date">Day: </label> <input type="text" id="js-date" name="date" value="<?php echo $account->tubTime ? date('Y-m-d', $account->tubTime['time']) : '' ?>" />
  </div>
  <div>
    <label for="js-time">Time (HH:MM): </label> <input type="text" id="js-time" name="time" maxlength="5" value="<?php echo $account->tubTime ? date('H:i', $account->tubTime['time']) : '' ?>" />
  </div>
  <div>
    <label for="js-temp">Temp (&deg;C): </label> <input type="text" id="js-temp" name="temp" maxlength="5" value="<?php echo $account->tubTime ? $account->tubTime['temp'] : $account->settings['defaultTemp']['value']; ?>" />
  </div>
  <input type="submit" value="OK" />
</form>
<?php endif; ?>