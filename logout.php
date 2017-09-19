<?php

require_once 'php/core.php';

logout();

header( 'location: ' . SITE_URL );
exit;