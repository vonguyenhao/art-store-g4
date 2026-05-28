<?php

require __DIR__ . '/../src/bootstrap.php';

$session = app('session');

unset($_SESSION['customer_email']);
unset($_SESSION['customer_name']);

$session->flash('You have been logged out.');

redirect('/');