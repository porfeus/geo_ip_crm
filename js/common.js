//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    $(window).bind("load resize", function() {
        var topOffset = 50;
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    });

    var url = window.location;
    // var element = $('ul.nav a').filter(function() {
    //     return this.href == url;
    // }).addClass('active').parent().parent().addClass('in').parent();
    var element = $('ul.nav a').filter(function() {
        return this.href == url;
    }).addClass('active').parent();

    while (true) {
        if (element.is('li')) {
            element = element.parent().addClass('in').parent();
        } else {
            break;
        }
    }
});

function checkAll(o){
  $(o).parents('.dataTables').find('[type="checkbox"]').prop('checked', o.checked);
}


//Если модальное окно закрыто, то сбрасываем таймер ее закрытия
$('#myModal').on('hidden.bs.modal', function (e) {
  $('#myModal .modal-footer').hide();
  clearTimeout(window.modalHideTimeout);
});


function showModalInfo(title, message){
  $('#myModal .modal-title').text(title);
  $('#myModal .modal-body').html(message);
  $('#myModal .modal-footer').hide();
  $('#myModal').modal('show').find('.modal-info').show();

  window.modalHideTimeout = setTimeout(function(){
   $('#myModal').modal('hide');
  }, 180000);
}


function showFormErrors(data){
  var status = false;

  try{
    data = JSON.parse(data);
    if( typeof(data) != 'object' ) return status;
  }catch(e){
    return status;
  }
  $('form')
    .find('.form-group').removeClass('has-error')
    .find('.help-block').text('');

  for(var name in data){
    status = true;
    $('form').find('.'+ name +'-block').each(function(){
      $(this).find('.help-block').text(data[name]);
      $(this).addClass('has-error');
    });
  }
  return status;
}


function disableLink(e){
  e.preventDefault();
}
