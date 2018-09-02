<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <base href="<?=Request::basedir()?>" />

    <title><?=App::t('Вход')?></title>

    <!-- Bootstrap Core CSS -->
    <link href="../design/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../design/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Languages for Bootstrap -->
    <link href="../design/languages/languages.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body style="background: url(../img/<?=$this->app->config['bg_img']?>) center center repeat-x">

    <div class="container">
      <div class="row" style="margin: 20px 0 20px 0; min-height: 100px;">
        <div class="col-md-6 col-md-offset-3 text-center">
          <img src="../img/<?=$this->app->config['logo_img']?>" style="max-height: 200px;" />
        </div>
      </div>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="panel panel-default"><!--login-panel -->
                    <div class="panel-heading">
                        <h3 class="panel-title"><?=App::t('Вход пользователя')?></h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" method="post">
                            <fieldset>
                                <div class="form-group<?=(($login_error)? ' has-error':'')?>">
                                  <label class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-user fa-fw fa-lg"></i></span>
                                    <input class="form-control" placeholder="<?=App::t('Логин')?>" name="login" type="text" value="<?=Request::post('login')?>">
                                  </label>
                                </div>
                                <div class="form-group<?=(($login_error)? ' has-error':'')?>">
                                  <label class="input-group">
                                    <span class="input-group-addon" id="basic-addon1"><i class="fa fa-lock fa-fw fa-lg"></i></span>
                                    <input class="form-control" placeholder="<?=App::t('Пароль')?>" name="password" type="password" value="<?=Request::post('password')?>">
                                  </label>
                                </div>
                                <?php if($need_agree){ ?>
                                <div class="checkbox<?=(($agree_error)? ' has-error':'')?>">
                                  <label>
                                    <input type="hidden" name="agree" value="0" />
                                    <input type="checkbox" name="agree" value="1">
                                    <?=App::t('agree_text')?>
                                  </label>
                                </div>
                                <?php } ?>
                                <?php if( !Request::session('captcha_off') ) { ?>
                                <div class="form-group<?=(($captcha_error)? ' has-error':'')?>">
                                    <div class="input-group" style="width:100%">
                                    <?php if( $this->app->config['show_captcha_user'] ) { ?>
                                    <label>
                                      <?=App::t('Докажите, что Вы не робот:')?><br />
                                      <img src="<?=$_SESSION['captcha']['image_src']?>" style="height: 50px;" />
                                      <input class="form-control" name="captcha" style="width:100px; height: 50px; font-size:20px; margin-right: 5px;" autocomplete="off">
                                    </label>
                                    <?php } ?>
                                    <ul class="nav " style="float: right">
                                      <?=App::get()->language->dropdown()?>
                                    </ul>
                                  </div>
                                </div>
                                  <?php if( $this->app->config['show_captcha_user'] ) { ?>
                                  <div class="form-group">
                                    <div class="input-group">
                                      <i class="fa fa-warning" style="color: #e00e18"></i>
                                      <?=App::t('Внимание! Код чувствителен к регистру')?>
                                    </div>
                                  </div>
                                  <?php } ?>
                                <?php } ?>
                                <?php if($error_message){ ?>
                                <div class="alert alert-danger" role="alert">
                                  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                                  <span class="sr-only"><?=App::t('Ошибка')?>:</span>
                                  <?=$error_message?>
                                </div>
                                <?php } ?>
                                <!-- Change this to a button or input when using this as a form -->
                                <button class="btn btn-lg btn-success btn-block"><?=App::t('Войти')?></button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../design/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../design/bootstrap/js/bootstrap.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../js/common.js"></script>

</body>

</html>
