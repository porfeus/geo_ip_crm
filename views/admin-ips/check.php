<?php
$this->registerJsFile('design/form/jquery.form.js');

//Стираем данные о незакачанном файле (при обновлении страницы)
Request::session('next_step', '');

//Стираем данные статистики об обработанных адресах
Request::session('import_result', '');
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=App::t('Проверка IP-адресов')?></h1>
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
<li class="active"><?=App::t('Проверка IP-адресов')?></li>
</ul>

<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
            </div>
            <div class="panel-body">
              <form id="check_form" role="form" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="start" value="1" />
              </form>
              <div id="import_progress" class="progress">
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

$('#check_form').ajaxForm(function(data) {
    if( !showFormErrors(data) ){
      if(data == 'ok'){
        location = '<?=App::get()->controllerName?>/index';
      }else
      if( data.match(/^next_step:[0-9]+$/) ){
        var percent = data.split(":")[1];
        $('#import_progress > .progress-bar')
          .attr('aria-valuenow', percent)
          .css('width', percent + '%')
          .text('<?=App::t('Загрузка _PERCENT_')?>'.replace('_PERCENT_', percent + '%'));
        setTimeout(function(){
          $('#check_form').submit();
        }, 100);
      }else{
        //alert(data);
        location = '<?=App::get()->controllerName?>/index';
      }
    }
});
$(function(){
  $('#check_form').submit();
})
</script>
