<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--meta charset="UTF-8" /-->
<title><?php echo (isset($pageTitle) ? $pageTitle : webtub::pageTitle); ?></title>
<meta name="description" content="<?php echo (isset($pageDescription) ? $pageDescription : webtub::pageDescription); ?>" />
<meta property="og:title" content="<?php echo (isset($pageTitle) ? $pageTitle : webtub::pageTitle); ?>"/>
<meta property="og:description" content="<?php echo (isset($pageDescription) ? $pageDescription : webtub::pageDescription); ?>"/>
<meta property="og:image" content="<?php echo (isset($images) && count($images) > 0 ? $images[0]['url'] : 'http://' . $_SERVER['HTTP_HOST'] . '/res/img/logo.png'); ?>" />
<link rel="image_src" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/res/img/logo.png" />
<meta property="og:site_name" content="webtub"/>
<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST']; ?><?php echo $_SERVER['REQUEST_URI']; ?>">
<meta property="og:type" content="website" />
<meta property="og:locale" content="en_US" />
<meta name="viewport" content="width=1050">
<!--meta property="fb:app_id" content="193334507468525" /-->
<link rel="shortcut icon" href="/favicon.ico">  
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" href="/res/style/style.css" type="text/css" media="all" />
<!--[if lt IE 10]>
<link type="text/css" rel="stylesheet" href="/res/style/ie.css" media="all" />
<![endif]-->
<script src="/res/script/jquery.min.js"></script>
<script src="/res/script/modernizr.js"></script>
<script src="/res/script/jquery.ui.touch-punch.min.js"></script>
<script src="/res/script/common.js"></script>
<!--script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36127368-1']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script-->
</head>
<body<?php echo (isset($bodyClasses) ? ' class="' . implode(' ', $bodyClasses) . '"' : ''); ?>>
  <header class="global clearfix">
    <div class="limiter">
      <nav>
        <ul>
          <li class="link-about"><a href="/about">About</a></li>
          <li class="link-settings"><a href="/settings">Settings</li>
        </ul>
      </nav>
      <h1 id="logo">
        <a href="/">WebTub</a>
      </h1>
    </div>
  </header>
  <div class="settings<?php echo $account->hasMissingRequiredSettings ? " expanded" : ""; ?>">
    <div class="limiter">
    <?php foreach($account->settings as $setting) { ?>
      <label for="<?php echo $setting['key']; ?>" class="block"><?php echo $setting['name']; ?></label>
      <?php if($setting['sourceEndpoint']) {
        foreach($account->telldusData->data[$setting['sourceEndpoint']] as $item) {
          echo '<div><input type="radio" name="' . $setting['key'] . '" id="' . $setting['key'] . '-' . $item['id'] . '"' . ($item['id'] == $setting['value'] ? ' selected="selected"' : '') . '><label for="' . $setting['key'] . '-' . $item['id'] . '">' . $item['name'] . '</label></div>';
        }      
      } else {
        echo '<input type="text" maxlength="252" name="' . $setting['key'] . '" value="' . $setting['value'] . '" />';
      } ?>
    <?php } ?>
    </div>
  </div>
  <div id="main" class="limiter clearfix">