<?php
$this->registerJsFile('design/copy-to-clipboard/jquery.copy-to-clipboard.js');
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=App::t('Определенные IP-адреса')?></h1>
        <p>
          <a class="btn btn-success" href="admin-ips/import"><i class="fa fa-plus"></i> <?=App::t('Загрузить список IP:PORT')?></a>
          <a class="btn btn-primary" href="admin-ips/check"><i class="fa fa-search"></i> <?=App::t('Проверить список')?></a>
          <a class="btn btn-danger" href="admin-ips/clear" onclick="if(!confirm('<?=App::t('Вы действительно хотите очистить список IP адресов?')?>')) return false;"><i class="fa fa-trash-o"></i> <?=App::t('Очистить список')?></a>
          <a class="btn btn-warning" href="admin-ips/update-geobase"><i class="fa fa-download"></i> <?=App::t('Обновить геобазу')?></a>
        </p><br />
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<div class="row">
    <div class="col-md-12" style="min-width:820px;">
        <form method="post" id="delete-form">
          <div class="panel panel-default">
              <div class="panel-heading">
                  <?=App::t('Всего IP-адресов')?>: <span id="total-items"><?=$total?></span>
                  <?php if( Request::get('country') ){ echo '<a style="float: right" href="admin-ips/index"><i class="fa fa-angle-double-left "></i> Вернуться назад</a>'; } ?>
              </div>
              <!-- /.panel-heading -->
              <div class="panel-body">
                  <table width="100%" class="dataTables table table-striped table-hover ">
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
        </form>
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->

<script>
window.id = 0;
$(document).ready(function() {
    window.dataTable = $('.dataTables').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url" :"./admin-ips?action=ajax<?php if( Request::get('country') ){ echo '&country='.Request::get('country'); } ?>",
          "type": "post"
        },
        "columns":[
            { "data": function ( row, type, set ) {
                var lang = row.country_code.toLowerCase();
                if( row.name == 'not_checked' ){
                  return '<div style="margin-left: 20px; text-align: left"><?=App::t('Не проверено')?></div>';
                }else
                if( !row.name ){
                  return '<div style="margin-left: 20px; text-align: left"><i style="color:#c9302c" class="fa fa-question-circle tip" title="<?=((!Request::get('country'))? App::t('Страна не определена'):App::t('Город не определен'))?>"></i> <?=App::t('Неизвестно')?></div>';
                }else
                if( <?php if( !Request::get('country') ){ echo '1'; }else echo '0'; ?> ){
                  return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> <a href="admin-ips/index?country=' + row.name +'">' + row.name +'</a></div>';
                }else{
                  return '<div style="margin-left: 20px; text-align: left">'+ '<i class="flag-icon flag-icon-'+ lang +'" title="'+ lang +'"></i> ' + row.name +'</div>';
                }
            } },
            { "data": "length" },
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
                return '<a href="admin-ips/get-ips-by-country?<?php if( !Request::get('country') ){ echo 'country_name'; }else echo 'country_name='.Request::get('country').'&city_name'; ?>='+ row.name +'&save=txt">[txt]</a>'
                +' '+
                '<a href="admin-ips/get-ips-by-country?<?php if( !Request::get('country') ){ echo 'country_name'; }else echo 'country_name='.Request::get('country').'&city_name'; ?>='+ row.name +'&save=csv">[csv]</a>';
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
          "next": "<?=App::t('Далее')?>",
          "previous": "<?=App::t('Назад')?>"
        },
        "emptyTable": "<?=App::t('Таблица пуста')?>",
        "info": "<?=App::t('Страница _PAGE_ из _PAGES_')?>",
        "infoEmpty": "<?=App::t('Нет записей для отображения')?>",
        "lengthMenu": "",
        "loadingRecords": "<?=App::t('Пожалуйста, ждите...')?>",
        "processing": "<?=App::t('Пожалуйста, ждите...')?>",
        "search": "<?=App::t('Поиск:')?>",
        "zeroRecords": "<?=App::t('Нет записей для отображения')?>"
      }
    });
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
</script>
