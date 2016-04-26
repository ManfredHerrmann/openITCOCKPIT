<?php
$this->Frontend->init($frontendData);
if(Configure::read('debug') == 0 && file_exists(WWW_ROOT . 'js/app_build.js') && file_exists(WWW_ROOT . 'css/app_build.css')) {
?>
<link rel="stylesheet" type="text/css" href="/js/app_build.css" />
<script type="text/javascript" src="/js/app_build.js"></script>
<?php
} else {
?>
<link rel="stylesheet" type="text/css" href="/js/assets.css" />
<script type="text/javascript" src="/js/assets.js"></script>
<?php
}
?>
<!--[if lt IE 9]>
      <script src="/js/vendor/html5shiv.js"></script>
      <script src="/js/vendor/respond.min.js"></script>
 <![endif]-->
<?php
echo $this->fetch('meta');
?>