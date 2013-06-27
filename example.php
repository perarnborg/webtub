<?php

require_once 'common.php';

if (isset($_GET['clear'])) {
        session_destroy();
        header('location:example.php');
        exit();
}

if (!isset($_SESSION['accessToken'])) {
        ?>We have no access token, <a href="getRequestToken.php">connect us</a><?php
        exit();
}

?>
        <p>We have access!</p>
        <p>
                In your system, store these values to do requests for this user:<br>
                Token: <?php echo $_SESSION['accessToken']; ?><br>
                Secret: <?php echo $_SESSION['accessTokenSecret']; ?>
        </p>
        <p><a href="example.php?clear">Clear the token and restart</a></p>
        <p><a href="example.php?listDevices">List users devices</a></p>
<?php

if (isset($_GET['listDevices'])) {
        $consumer = new HTTP_OAuth_Consumer(constant('PUBLIC_KEY'), constant('PRIVATE_KEY'), $_SESSION['accessToken'], $_SESSION['accessTokenSecret']);
        $params = array(
                'supportedMethods' => constant('TELLSTICK_TURNON') | constant('TELLSTICK_TURNOFF'),
        );
        $response = $consumer->sendRequest(constant('REQUEST_URI').'/devices/list', $params, 'GET');
        echo '<pre>';
        echo( htmlentities($response->getBody()));
}

?><p><a href="example.php?listClients">List users clients</a></p><?php

if (isset($_GET['listClients'])) {
        $consumer = new HTTP_OAuth_Consumer(constant('PUBLIC_KEY'), constant('PRIVATE_KEY'), $_SESSION['accessToken'], $_SESSION['accessTokenSecret']);
        echo constant('PUBLIC_KEY') . '<br/>';
        echo constant('PRIVATE_KEY') . '<br/>';
        echo $_SESSION['accessToken'] . '<br/>';
        echo $_SESSION['accessTokenSecret'] . '<br/>';
        echo constant('REQUEST_URI').'/clients/list' . '<br/>';
        $params = array();
        $response = $consumer->sendRequest(constant('REQUEST_URI').'/clients/list', $params, 'GET');
        echo '<pre>';
        echo( htmlentities($response->getBody()));
}