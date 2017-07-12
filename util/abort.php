<?php
  function abort()
  {
    ob_clean();
    include $_SERVER["DOCUMENT_ROOT"]."/util/404.php";
    http_response_code( 404 );
    exit( 1 );
  }
?>
