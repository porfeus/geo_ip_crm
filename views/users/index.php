<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=App::t('Аккаунты')?></h1>
        <div class="form-inline" style="float: right">
          <div class="form-group">
            <label for="activated-filter"><?=App::t('Активация')?>:</label>
            <select class="form-control" id="activated-filter">
              <option value="yes"><?=App::t('Активированные')?></option>
              <option value="no"><?=App::t('Неактивированные')?></option>
              <option value="all"><?=App::t('Любые')?></option>
            </select>
          </div>
        </div>
        <p>
          <a class="btn btn-success" href="users/create"><?=App::t('Создать аккаунт')?></a>
          <a class="btn btn-primary" href="users/generate"><?=App::t('Сгенерировать')?></a>
        </p><br />
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->
<div class="row">
    <div class="col-md-12">
        <form method="post" id="delete-form">
          <div class="panel panel-default">
              <div class="panel-heading">
                  <?=App::t('Всего аккаунтов')?>: <span id="total-items"><?=$total?></span>,
                  <?=App::t('активированных')?>: <span id="activated-items"><?=$activated?></span>
              </div>
              <!-- /.panel-heading -->
              <div class="panel-body">
                  <table width="100%" class="dataTables table table-striped table-hover ">
                      <thead>
                          <tr>
                              <th>
                                  <input type="checkbox" onclick="checkAll(this)" />
                              </th>
                              <th><?=$model->getLabel('login')?></th>
                              <th><?=$model->getLabel('password')?></th>
                              <th><?=$model->getLabel('email')?></th>
                              <th><?=$model->getLabel('ip_old')?></th>
                              <th><?=$model->getLabel('ip_new')?></th>
                              <th><?=$model->getLabel('tariff_time')?></th>
                              <th><?=$model->getLabel('activated_time')?></th>
                              <th><?=$model->getLabel('activated_add_time')?></th>
                              <th><?=$model->getLabel('last_enter_time')?></th>
                              <th class="text-right"><?=App::t('Действие')?></th>
                          </tr>
                      </thead>
                  </table>
                  <!-- /.table-responsive -->
              </div>
              <!-- /.panel-body -->
          </div>
          <!-- /.panel -->
        </form>
        <p>
          <a type="submit" class="btn btn-danger" id="delete-button"><?=App::t('Удалить отмеченное')?></a>
        </p>
    </div>
    <!-- /.col-lg-12 -->
</div>
<!-- /.row -->

<script>
$(document).ready(function() {
    window.dataTable = $('.dataTables').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
          "url" :"./users?action=ajax",
          "type": "post"
        },
        "columns":[
            { "data": function ( row, type, set ) {
                return '<input type="checkbox" name="delete[]" value="'+ row.id +'" />' +
                (row.online? '<span class="online"></span>':'');
            } },
            { "data": function ( row, type, set ) {
                return '<div class="text-left">'+ ((row.status == 3)? '<a><i style="color:#c9302c" class="fa fa-circle fa-fw" title="<?=App::t('Заблокирован')?>"></i></a>':(row.status == 2)? '<a><i style="color:#286090" class="fa fa-circle fa-fw" title="<?=App::t('Приостановлен')?>"></i></a>':'<a><i style="color:#5cb85c" class="fa fa-circle fa-fw" title="<?=App::t('Активен')?>"></i></a>') +' '+ row.login +'</div>';
            } },
            { "data": "password" },
            { "data": function ( row, type, set ) {
                return ((row.email)? '<a href="mailto:'+ row.email +'">'+ row.email +'</a>':'<?=App::t('Нет')?>');
            } },
            { "data": "ip_old" },
            { "data": "ip_new" },
            { "data": "tariff_time" },
            { "data": "activated_from" },
            { "data": "activated_add_time" },
            { "data": "last_enter_time" },
            { "data": function ( row, type, set ) {
                return '<div class="text-right">' + [
                  ((row.activated_time == '0')? '<a href="#" onclick="activateAccount(event, '+ row.id +')"><i style="color:#449d44" class="glyphicon glyphicon-ok " title="<?=App::t('Активировать')?>"></i></a>':''),

                  '<a href="#" id="note-link-'+ row.id +'" onclick="loadNote(event, this, '+ row.id +')"><i style="color:'+ ((row.note)? '#c9302c':'#337ab7') +'" class="fa fa-comment fa-fw" title="<?=App::t('Примечание')?>"></i></a>',

                  ((Number(row.banned))? '<a href="#" onclick="ban(event, '+ row.id +', 0)"><i style="color:#c9302c" class="fa fa-play fa-fw" title="<?=App::t('Разблокировать')?>"></i></a>':'<a href="#" onclick="ban(event, '+ row.id +', 1)"><i class="fa fa-pause fa-fw" title="<?=App::t('Заблокировать')?>"></i></a>'),

                  '<a href="users/edit?id='+ row.id +'"><i class="glyphicon glyphicon-pencil" title="<?=App::t('Редактировать')?>"></i></a>',

                  '<a href="#" data-id="'+ row.id +'" onclick="deleteLink(event, this)"><i class="glyphicon glyphicon-trash" title="<?=App::t('Удалить')?>"></i></a>'
                ].join(' ') + '</div>';
            } }
        ],
        "responsive": true,
        "sort": false,
        "pageLength": <?=App::get()->config['users_show_num']?>,
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

    $('#activated-filter').on('change', function(){
      window.dataTable
        .column( 1 )
        .search( this.value )
        .draw();
    });

    $('#delete-button').on('click', function(){
      if( !confirm("<?=App::t('Удалить выбранное')?>?") ) return;
      $.post('users/delete', $( "#delete-form" ).serialize(), function(data){
        data = JSON.parse(data);
        $('#total-items').text( data.total );
        $('#activated-items').text( data.activated );

        window.dataTable.draw();
        $('.dataTables').find('[type="checkbox"]').prop('checked', false);
      });
    });

    $('.dataTables').on( 'draw.dt', function () {
        $(this).find('.online').each(function(){
          $(this).parents('tr').css('background-color', '#d6e9c6');
        });
    });
});

function deleteLink(e, o){
  e.preventDefault();
  if( !confirm("<?=App::t('Удалить выбранное')?>?") ) return;
  $.post('users/delete', 'delete[0]='+ $(o).data('id') +'', function(data){
    data = JSON.parse(data);
    $('#total-items').text( data.total );
    $('#activated-items').text( data.activated );

    window.dataTable.draw();
  });
}

function loadNote(e, o, id){
  e.preventDefault();

  var note = '';
  $.ajax({
    url: "users/get-note?id="+ id,
    cache: false,
    async: false
  }).done(function(data) {
    note = data;
  });

  if( $(o).parents('tr').next().hasClass('note') ){
    $(o).parents('tr').next().remove();
  }else{
    var html = '';
    html += '<div class="text-center"><b><?=App::t('Примечание')?>:</b></div>';
    $(o).parents('tr').after('<tr class="note"><td colspan="20" style="text-align:left">'+ html +'<textarea style="width:100%" rows="5" id="note-'+ id +'">'+ note +'</textarea><a class="btn btn-success" href="#" onclick="saveNote(event, '+ id +')"><?=App::t('Сохранить примечание')?></a></td></tr>');
  }
}

function saveNote(e, id){
  e.preventDefault();

  var note = $('textarea#note-'+id).val();
  if( note ){
    $('#note-link-'+id).find('i').css('color', '#c9302c');
  }else{
    $('#note-link-'+id).find('i').css('color', '#337ab7');
  }

  $.post('users/save-note', {id: id, note: note}, function(){
    $('textarea#note-'+id).parents('tr').remove();
  });
}

function activateAccount(e, id){
  e.preventDefault();

  if( !confirm('<?=App::t('Активировать?')?>') ) return;

  $.post('users/activate', {id: id}, function(){
    window.dataTable.draw();
  });
}

function ban(e, id, status){
  e.preventDefault();

  if( status && !confirm('<?=App::t('Заблокировать?')?>') ) return;
  if( !status && !confirm('<?=App::t('Разблокировать?')?>') ) return;

  $.post('users/ban', {id: id, status: status}, function(){
    window.dataTable.draw();
  });
}
</script>
