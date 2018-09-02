<?php
$this->registerJsFile('design/form/jquery.form.js');

//Стираем данные о незакачанном файле (при обновлении страницы)
Request::session('next_step', '');

//Стираем данные статистики об обработанных адресах
Request::session('import_result', '');

//Ограничение на количество загружаемых ип, если установлено
if( $this->app->user->role == 'user' ){
  if( $this->app->user->identity->addIpsLimited() ){
    $this->redirect('ips/index');
  }
}
//Конец. Ограничение на количество загружаемых ип, если установлено
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=App::t('Импорт IP-адресов')?></h1>
    </div>
    <!-- /.col-lg-12 -->
</div>

<ul class="breadcrumb">
<li><a href="<?=App::get()->controllerName?>">
  <?php
  if(App::get()->user->role == 'user'){ ?>
    <?=App::t('Панель управления')?>
  <?php }else{ ?>
    <?=App::t('Определенные IP-адреса')?>
  <?php } ?>
</a></li>
<li class="active"><?=App::t('Импорт IP-адресов')?></li>
</ul>

<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
              <?=App::t('Загружать как IP:PORT')?>
            </div>
            <div class="panel-body">
              <form id="import_form" role="form" method="post" enctype="multipart/form-data">
                  <?=Form::dropDownList($model, 'variant', [
                    App::t('С файла на компьютере') => 'file',
                    App::t('С поля ввода') => 'field',
                    ])?>
                  <?=Form::fileInput($model, 'file')?>
                  <?=Form::textArea($model, 'field', ['rows' => 10])?>
                  <button id="import_button" type="submit" class="btn btn-success"
                  style="background-color: #2F2EA5"><?=App::t('Импортировать')?></button>
              </form>
              <div id="import_progress" class="progress" style="display: none">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                  <?=str_replace('_PERCENT_', '0', App::t('Загрузка _PERCENT_'))?>%
                </div>
              </div>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-6 -->
</div>
<!-- /.row -->

<script>
$('.file-block, .field-block').hide();
if( $('[name="variant"]').val() ){
  $('.'+ $('[name="variant"]').val() +'-block').show();
}

$('[name="variant"]').on('change', function(){
  $('.file-block, .field-block').hide();
  $('.'+ this.value +'-block').show();
});

$('#import_button').on('click', function(){
    $(this)
    .css('background-color', '#DA0710')
    .text('<?=App::t('Идёт загрузка списка. Ждите...')?>');
});

$('#import_form').ajaxForm(function(data) {
    if( !showFormErrors(data) ){
      if(data == 'ok'){
        $('#import_form').hide();
        location = '<?=App::get()->controllerName?>/index';
      }else
      if(data.indexOf('next_step') > -1){
        $('#import_form').hide();
        $('#import_progress').show();
        var percent = data.split(":")[1];
        $('#import_progress > .progress-bar')
          .attr('aria-valuenow', percent)
          .css('width', percent + '%')
          .text('<?=App::t('Загрузка _PERCENT_')?>'.replace('_PERCENT_', percent + '%'));
        setTimeout(function(){
          $('#import_form').submit();
        }, 100);
      }else{
        //alert(data);
        location = '<?=App::get()->controllerName?>/index';
      }
    }else{
      $('#import_button')
      .css('background-color', '#2F2EA5')
      .text('<?=App::t('Импортировать')?>');
    }
});
</script>
