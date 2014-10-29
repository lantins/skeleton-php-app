<?php

Flight::route('/', function() {
  Flight::view()->display('index.twig');
});
