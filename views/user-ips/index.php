<?php
$this->registerJsFile('design/copy-to-clipboard/jquery.copy-to-clipboard.js');
$this->registerJsFile('design/timer/jquery.simple.timer.js');

$userInfo = $this->app->user->identity;
?>

<style>
#datatable_2_filter input{
  display: none;
}
</style>

<?php
$email = $userInfo->email;
if(
  $userInfo->need_email &&
  empty($email)
){

  if( empty($email_message) ){
    $email_message = '<span style="color: #e00e18"><i class="fa fa-info-circle"></i> '.App::t('Если Вы хотите получать напоминание об окончании действия аккаунта, или системные уведомления, укажите ваш e-mail').'</span>';
  }

?>
<!-- /.row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-danger">
            <div class="panel-heading">
              <?=App::t('Запрос e-mail')?>
            </div>
            <div class="panel-body">
              <form class="form-inline text-center" method="post">
                <input type="hidden" name="action" value="save-email" />
                <div class="form-group<?=(($email_error)? ' has-error':'')?><?=(($email_success)? ' has-success':'')?>">
                  <input name="email" type="email" class="form-control" placeholder="<?=App::t('Введите e-mail')?>" size="30" value="<?=$email?>">
                  <button type="submit" class="btn btn-success"><?=App::t('Сохранить')?></button>
                  <p class="help-block text-left"><?=(($email_error)? '<i class="fa fa-warning" style="color: #e00e18"></i>':'')?> <?=$email_message?></p>
                </div>
              </form>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-6 -->
</div>
<!-- /.row -->
<?php } ?>

<div class="row">
    <div class="col-lg-12" id="buttons_station" style="display: none">


      <p>
        <a id="load_ips_link" data-class="btn-success" class="btn btn-success" href="ips/import"><i class="fa fa-plus"></i> <?=App::t('Загрузить список IP:PORT')?></a>

        <?php if( $total > 0 ){ ?>
        <a id="check_ips_link" class="btn btn-primary" href="ips/check"><i class="fa fa-search"></i> <?=App::t('Проверить список')?></a>
        <a id="clear_ips_link" data-class="btn-danger" class="btn btn-danger" href="ips/clear" onclick="if(!confirm('<?=App::t('Вы действительно хотите очистить список IP адресов?')?>')) return false;"><i class="fa fa-trash-o"></i> <?=App::t('Очистить список')?></a>
        <?php } ?>
        <?php
        if( App::get()->user->identity->checkIpsLimited() ){ ?>
          <script>
          $('#check_ips_link')
            .on('click', disableLink)
            .removeClass('btn-primary')
            .addClass('btn-default')
            .css('border', '1px solid #c9302c')
            .html('<?=App::t('Проверка невозможна: достигнут лимит проверок')?>');
          $('#load_ips_link, #clear_ips_link').hide();
          </script>
        <?php }else
        if( App::get()->user->identity->checkIpsCountdown() ){ ?>
          <script>
          $('#check_ips_link')
            .on('click', disableLink)
            .removeClass('btn-primary')
            .addClass('btn-default')
            .css('border', '1px solid #c9302c')
            .html('<?=str_replace('_TIME_', '<span id="load_ips_timer" data-seconds-left="'.App::get()->user->identity->checkIpsTimeLeft().'"></span>', App::t('Следующая проверка возможна через: _TIME_'))?>');
          $('#load_ips_timer').startTimer({
            onComplete: function(){
              $('#check_ips_link')
                .off('click', disableLink)
                .addClass('btn-primary')
                .removeClass('btn-default')
                .css('border', 'none')
                .html('<i class="fa fa-search"></i> <?=App::t('Проверить список')?>');
            }
          });
          </script>
        <?php } ?>
        <?php
        if( App::get()->user->identity->addIpsLimited() ){ ?>
          <script>
          $('#load_ips_link')
            .on('click', disableLink)
            .removeClass('btn-success')
            .addClass('btn-default')
            .addClass('tip')
            .attr('title', '<font color=#f0ad4e><?=App::t('Для загрузки нового списка, очистите старый список IP')?></font>')
            .tooltip()
            .css('border', '1px solid #c9302c')
            .html('<i style="color:#c9302c" class="fa fa-question-circle tip"></i> <?=App::t('Загрузка ограничена')?>');
          </script>
        <?php } ?>
      </p><br />
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<div class="row">
  <?php
    $tablesWidth1 = 'col-lg-12 col-md-12 col-sm-12';
    if( App::get()->config['show_info_block'] ){
      $tablesWidth1 = 'col-lg-8 col-md-9 col-sm-12';
      $tablesWidth3 = 'col-lg-4 col-md-3 col-sm-12';
    }
    if( $userInfo->import_admin_list ){
      $tablesWidth1 = 'col-lg-9 col-md-9 col-sm-12';
      $tablesWidth2 = 'col-lg-3 col-md-3 col-sm-12';
    }
    ?>
    <div class="<?=$tablesWidth1?>">
        <div class="panel panel-default" style="min-width: 560px;">
            <div class="panel-heading">
                <span style="font-size: 16px; font-weight: bold"><?=App::t('Всего IP-адресов')?>: <span id="total-items"><?=$total?></span></span>

                <?php if( !empty(App::t('Описание базы пользователя')) ){ ?>
                <p class="text-center alert alert-success" style="font-size: 16px">
                  <?=App::t('Описание базы пользователя')?>
                </p>
                <?php } ?>

                <?php if( Request::get('country') ){ echo '<div style="text-align: center; font-weight: bold; font-size:200%">'.Request::get('country').'</div>'; } ?>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" id="datatable_1" class="dataTables table table-striped table-hover " style="font-size: 16px">
                    <thead>
                        <tr>
                            <th style="text-align: left">
                              <?php if( isset($_GET['country']) ){ ?>
                              <?=App::t('Город')?>
                              <?php }else{ ?>
                              <?=App::t('Страна')?>
                              <?php } ?>
                            </th>
                            <th><?=App::t('Количество')?></th>
                            <th><?=App::t('Копировать')?></th>
                            <th><?=App::t('Экспорт')?></th>
                        </tr>
                    </thead>
                </table>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->

    <?php if( $userInfo->import_admin_list ){ ?>
    <div class="<?=$tablesWidth2?>">
        <div class="panel panel-default">
            <div class="panel-heading">
                <span style="font-size: 16px; font-weight: bold"><?=App::t('Всего IP-адресов')?>: <span id="total-items"><?=$total_admin?></span></span>

                <?php if( !empty(App::t('Описание базы админа')) ){ ?>
                <p class="text-center alert alert-success" style="font-size: 16px">
                  <?=App::t('Описание базы админа')?>
                </p>
                <?php } ?>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <table width="100%" id="datatable_2" class="dataTables table table-striped table-hover " style="font-size: 16px">
                    <thead>
                        <tr>
                            <th style="text-align: left">
                              <?=App::t('Страна')?>
                            </th>
                            <th><?=App::t('Количество')?></th>
                        </tr>
                    </thead>
                </table>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
    <?php } ?>

    <?php if( App::get()->config['show_info_block'] && !$userInfo->import_admin_list ){ ?>
    <div class="<?=$tablesWidth3?>">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?=App::t('Название информационного блока')?>
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <?=App::t('Содержимое информационного блока')?>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
    <?php } ?>
</div>
<!-- /.row -->

<script>
window.id = 0;
$(document).ready(function() {

  $('#datatable_1').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url" :"./ips?action=ajax"+
        "<?php if( Request::get('country') ){ echo '&country='.Request::get('country'); } ?>",
        "type": "post"
      },
      "columns":[
          { "data": function ( row, type, set ) {
              var lang = row.country_code.toLowerCase();
              if( row.name == 'not_checked' ){
                return '<div style="margin-left: 20px; text-align: left"><?=App::t('Не проверено - у юзера')?></div>';
              }else
              if( !row.name ){
                return '<div style="margin-left: 20px; text-align: left"><i style="color:#c9302c" class="fa fa-question-circle tip" title="<?=((!Request::get('country'))? App::t('Страна не определена'):App::t('Город не определен'))?>"></i> <?=App::t('Неизвестно')?></div>';
              }else
              if( <?php if( !Request::get('country') ){ echo '1'; }else echo '0'; ?> ){
                return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> <a href="ips?country=' + row.name +'" title="<?=App::t('Посмотреть города этой страны')?>" class="tip">' + row.name +'</a></div>';
              }else{
                return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> ' + row.name +'</div>';
              }
          } },
          { "data": function ( row, type, set ) {
              return '<span style="font-weight: bold">'+ row.length +'</span>';
          } },
          { "data": function ( row, type, set ) {
              if( row.name == 'not_checked' ){
                return '-';
              }
              window.id ++;
              return '<a data-clipboard-target="#clipboard_content_'+ window.id +'" id="copy_link_'+ window.id +'" class="btn btn-primary" onclick="copyIp(event, this, \''+ row.name +'\')"><?=App::t('Копировать как IP:PORT')?></a>'+
              '<textarea id="clipboard_content_'+ window.id +'" style="display: none">'+ row.ips +'</textarea>';
          } },
          { "data": function ( row, type, set ) {
              if( row.name == 'not_checked' ){
                return '-';
              }
              return '<a style="font-size: 14px;" href="ips/get-ips-by-country?<?php if( !Request::get('country') ){ echo 'country_name'; }else echo 'country_name='.Request::get('country').'&city_name'; ?>='+ row.name +'&save=txt">[txt]</a>'
              +' '+
              '<a style="font-size: 14px;" href="ips/get-ips-by-country?<?php if( !Request::get('country') ){ echo 'country_name'; }else echo 'country_name='.Request::get('country').'&city_name'; ?>='+ row.name +'&save=csv">[csv]</a>';
          } }
      ],
      "responsive": true,
      "sort": false,
      "pageLength": <?=App::get()->config['ips_show_num']?>,
      "language": {
        "infoFiltered": "",
        "paginate": {
        "first": "<?=App::t('Первая')?>",
        "last": "<?=App::t('Последняя')?>",
        "next": "<?=App::t('&raquo;')?>",
        "previous": "<?=App::t('&laquo;')?>"
      },
      "emptyTable": "<?=App::t('Таблица пуста')?>",
      "info": "<?=App::t('Страница _PAGE_ из _PAGES_')?>",
      "infoEmpty": "<?=App::t('Нет записей для отображения')?>",
      "lengthMenu": "",
      "loadingRecords": "<?=App::t('Пожалуйста, ждите...')?>",
      "processing": "<?=App::t('Пожалуйста, ждите...')?>",
      "search": "<b><?=App::t('Поиск:')?></b>",
      "zeroRecords": "<?=App::t('Нет записей для отображения')?>"
    },
    "initComplete": function(settings, json) {
      <?php if( Request::get('country') ){ ?>
        $('<a href="ips" class="btn btn-info" style="background-color: #124F61;"><i class="fa fa-angle-double-left "></i> <?=App::t('Вернуться назад')?></a>').appendTo($('#datatable_1_length label'));
      <?php }else{ ?>
        $('#buttons_station > p').appendTo($('#datatable_1_length label'));
      <?php } ?>
      $('#datatable_1_filter')
        .css('text-align', 'left')
        .parent().removeClass('col-sm-6').addClass('col-sm-12');
      $('#datatable_1_length').parent().removeClass('col-sm-6').addClass('col-sm-12');
    }
  });

  <?php if( $userInfo->import_admin_list ): ?>
  $('#datatable_2').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url" :"./ips?action=ajax&admin_table=1",
        "type": "post"
      },
      "columns":[
          { "data": function ( row, type, set ) {
              var lang = row.country_code.toLowerCase();
              if( row.name == 'not_checked' ){
                return '<div style="margin-left: 20px; text-align: left"><?=App::t('Не проверено - у юзера')?></div>';
              }else
              if( !row.name ){
                return '<div style="margin-left: 20px; text-align: left"><i style="color:#c9302c" class="fa fa-question-circle tip" title="<?=((!Request::get('country') || true)? App::t('Страна не определена'):App::t('Город не определен'))?>"></i> <?=App::t('Неизвестно')?></div>';
              }else
              if( <?php if( !Request::get('country') && 0 ){ echo '1'; }else echo '0'; ?> ){
                return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> <a href="ips?country=' + row.name +'">' + row.name +'</a></div>';
              }else{
                return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> ' + row.name +'</div>';
              }
          } },
          { "data": function ( row, type, set ) {
              return '<span style="font-weight: bold">'+ row.length +'</span>';
          } }
      ],
      "responsive": true,
      "sort": false,
      "pageLength": <?=App::get()->config['ips_show_num']?>,
      "language": {
        "infoFiltered": "",
        "paginate": {
        "first": "<?=App::t('Первая')?>",
        "last": "<?=App::t('Последняя')?>",
        "next": "<?=App::t('&raquo;')?>",
        "previous": "<?=App::t('&laquo;')?>"
      },
      "emptyTable": "<?=App::t('Таблица пуста')?>",
      "info": "<?=App::t('Страница _PAGE_ из _PAGES_')?>",
      "infoEmpty": "<?=App::t('Нет записей для отображения')?>",
      "lengthMenu": "",
      "loadingRecords": "<?=App::t('Пожалуйста, ждите...')?>",
      "processing": "<?=App::t('Пожалуйста, ждите...')?>",
      "search": "<b><?=App::t('Поиск:')?></b>",
      "zeroRecords": "<?=App::t('Нет записей для отображения')?>"
    },
    "initComplete": function(settings, json) {
      $('#datatable_2_filter')
        .css('text-align', 'left')
        .parent().removeClass('col-sm-6').addClass('col-sm-12');
      $('#datatable_2_length').parent().removeClass('col-sm-6').addClass('col-sm-12');
      $('#datatable_2_filter input').show();
    }
  });
  <?php endif; ?>
});


function copyIp(e, o, name){
  $(o).removeClass('btn-primary').addClass('btn-success').text('<?=App::t('Скопировано в буфер обмена')?>');

  $($(o).attr('data-clipboard-target')).CopyToClipboard();

  if( !copyIp.timeouts ) copyIp.timeouts = {};
  clearTimeout(copyIp.timeouts[o.id]);
  copyIp.timeouts[o.id] = setTimeout(function(){
    $(o).removeClass('btn-success').addClass('btn-primary').text('<?=App::t('Копировать как IP:PORT')?>');
  }, 5000);
}

$(function(){
  $('#clipboard_content').CopyToClipboard(); //off bug scroll top after first copy
});

<?php
if( App::get()->user->identity->issetUncheckedIps() ){ ?>
$('#check_ips_link').jrumble({
  speed: 100
}).hover(function(){
  $(this).trigger('stopRumble');
});

setInterval(function(){
  if( !$('#check_ips_link').hasClass('btn-primary') ) return;

  $('#check_ips_link').trigger('startRumble');
  setTimeout(function(){
    $('#check_ips_link').trigger('stopRumble');
  }, 2000);
}, 6000);
<?php }else{
?>
$('#check_ips_link').attr('title', '<font color=#f0ad4e><?=App::t('Этот список IP уже проверен')?></font>');
$('#check_ips_link').tooltip();
<?php
} ?>

</script>
