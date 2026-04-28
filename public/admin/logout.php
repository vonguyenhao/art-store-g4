<?php

require __DIR__ . '/../../src/bootstrap.php';

app('auth')->logout();
app('session')->flash('Logged out.');
redirect('/');
