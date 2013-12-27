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
  The tub is turned <span class="js-tub-state <?php echo $account->telldusData->data['tubStateOn'] ? "on" : "off" ?>"><?php echo $account->telldusData->data['tubStateOn'] ? "on" : "off" ?></span> <a href="/post/turn-<?php echo $account->telldusData->data['tubStateOn'] ? "off" : "on" ?>" data-turn="<?php echo $account->telldusData->data['tubStateOn'] ? "off" : "on" ?>" class="change js-change">Turn <?php echo $account->telldusData->data['tubStateOn'] ? "off" : "on" ?></a>
  </div>
<?php endif; ?>
  <div>
  Last checked <span class="js-last-checked <?php if(!$account->telldusData->data['tubLastCheckedRecently']): ?>off<?php endif; ?>"><?php echo date('H:i', $account->telldusData->data['tubLastChecked']); ?></span>
  </div>
</div>
<?php endif; ?>
<h2>TEST CONFIG:</h2>
<form class="tub-time clearfix" method="post" onsubmit="return validateTubTime();">
  <div class="date">
    <label for="js-date">Day: </label> <input type="text" id="js-date" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" />
  </div>
  <div class="time">
    <label for="js-time">Time (HH:MM): </label> <input type="text" id="js-time" name="time" maxlength="5" value="<?php echo isset($_POST['time']) ? $_POST['time'] : ''; ?>" />
  </div>
  <div>
    <label for="js-temp">Temp (&deg;C): </label> <input type="text" id="js-temp" name="temp" maxlength="5" value="<?php echo isset($_POST['temp']) ? $_POST['temp'] : ''; ?>" />
  </div>
  <input type="submit" value="TEST" />
</form>
<?php var_dump($turnOn); ?>
