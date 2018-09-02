<?php
$this->registerJsFile('design/multiselect/jquery.multiselect.js');
$this->registerCssFile('design/multiselect/jquery.multiselect.css');
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=App::t('Создание аккаунта')?></h1>
    </div>
    <!-- /.col-lg-12 -->
</div>

<ul class="breadcrumb">
<li><a href="<?=App::get()->controllerName?>"><?=App::t('Аккаунты')?></a></li>
<li class="active"><?=App::t('Создание аккаунта')?></li>
</ul>

<?php
if($result){
?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
              Результат
            </div>
            <div class="panel-body">
              <div class="form-group">
                  <label>Данные аккаунта</label>
                  <textarea class="form-control" rows="10"><?=$result?></textarea>
              </div>

              <a href="main/download?file=generate.csv" class="btn btn-primary">
                <i class="fa fa-save fa-fw"></i>Скачать
              </a>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-6 -->
</div>
<!-- /.row -->
<?php
}
?>

<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">

            </div>
            <div class="panel-body">
              <form role="form" method="post">
                  <?=Form::textInput($model, 'login')?>
                  <?=Form::textInput($model, 'password')?>
                  <input type="hidden" name="need_email" value="0" />
                  <?=Form::dropDownList($model, 'need_email', [
                    App::t('Да') => 1,
                    App::t('Нет') => 0,
                  ])?>
                  <?=Form::dropDownList($model, 'activated_type', $model->getActivatedTypes())?>
                  <?=Form::textInput($model, 'activated_num')?>
                  <input type="hidden" name="users_limit" value="1" />
                  <?=Form::dropDownList($model, 'users_limit', [1,2,3,4,5,6,7,8,9,10])?>
                  <?=Form::textInput($model, 'import_load_length')?>
                  <?=Form::textInput($model, 'check_interval_time')?>
                  <?=Form::dropDownList($model, 'check_limit_on', [
                    App::t('Да') => 1,
                    App::t('Нет') => 0,
                  ])?>
                  <?=Form::textInput($model, 'check_limit_num')?>
                  <?=Form::dropDownList($model, 'import_admin_list', [
                    App::t('Да') => 1,
                    App::t('Нет') => 0,
                  ])?>
                  <?=Form::dropDownList($model, 'language', $languages)?>
                  <?php
                  foreach($messages as $name=>$message){
                    $model->{$name} = Request::post($name, $message);
                    echo Form::textArea($model, $name, ['rows' => 5, 'label' => 'Сообщение']);
                  }
                  ?>
                  <button type="submit" class="btn btn-success"><?=App::t('Сохранить')?></button>
              </form>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-6 -->
</div>
<!-- /.row -->

<script>
var messagesSelector = ['.message_<?=implode("-block', '.message_", array_values($languages))?>-block'].join(', ');
$(messagesSelector).hide();
if( $('[name="language"]').val() ){
  $('.message_'+ $('[name="language"]').val() +'-block').show();
}

$('[name="language"]').on('change', function(){
  $(messagesSelector).hide();
  $('.message_'+ this.value +'-block').show();
});

$('[name="check_limit_on"]').on('change', function(){
  if( $(this).val() == '1' ){
    $('.check_limit_num-block').show();
  }else{
    $('.check_limit_num-block').hide();
    $('[name="check_limit_num"]').val('0');
  }
}).triggerHandler('change');
</script>
