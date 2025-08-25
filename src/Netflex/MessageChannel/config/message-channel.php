<?php

return [

  /*
   |--------------------------------------------------------------------------
   | Base URI
   |--------------------------------------------------------------------------
   |
   | The backend endpoint that handles the websocket backend
   |
   */
  'baseURI' => 'https://broadcast.netflexapp.com',
  'prefixChannel' => true,

  /*
   |--------------------------------------------------------------------------
   | Handlers
   |--------------------------------------------------------------------------
   |
   | Your application's incoming message handler
   |
   */
  'handler' => [
    'endpoint' => env('APP_URL') . '/incoming-message-handler',
    'method' => 'post',
  ],

];
