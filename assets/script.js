jQuery(document).ready(function($) {

  $('.btn-file').click(function(e) {
    e.preventDefault();

    var ay_mediaframe;
    var name = $(this).data('name');
    var target = $(this).data('target');

    if ( undefined !== ay_mediaframe ) {
      ay_mediaframe.open();
      return;
    }

    ay_mediaframe = wp.media.frames.ay_mediaframe = wp.media({
      frame: 'select',
      title: 'Add file ' + name,
      multiple: false,
      button : { text : 'Define as "' + name + '"' }
    });

    ay_mediaframe.on( 'select', function() {

      var media_attachment = ay_mediaframe.state().get('selection').first().toJSON();
      $('#' + target).val(media_attachment.id);
      $('#' + target).next('button').html('Change file');
      
      if($('.term-' + target + '-wrap .del-file').length == 0){
        $('#' + target).after('<button class="button del-file">Remove file</button>');
      }

      $('.term-' + target + '-wrap .file-rep').remove();

      if(typeof media_attachment.sizes != 'undefined') {
        $('#' + target).after('<img src="' + media_attachment.sizes.thumbnail.url + '" alt="" class="file-rep"/><br/>');
      } else {
        $('#' + target).after('<a href="' + media_attachment.url + '" title="" class="file-rep">Download file</a><br/>');
      }

    });

    ay_mediaframe.open();
  });

  $('.form-field').on('click', '.del-file', function(e){
    e.preventDefault();
    $(this).parent().find('.file-rep').remove();
    $(this).parent().find('br').remove();
    $(this).parent().find('input').val('');
    $(this).parent().find('button').html('Add file');
    $(this).remove();
  });

  $(document).ajaxComplete(function( event, xhr, settings ) {
    var params = parseQueryString(settings.data);
    if(params.action == 'add-tag'){
      $('.form-field input[type=checkbox], .form-field input[type=radio]').removeAttr('checked');
      var filefield = $('.form-field .btn-file').parent();
      filefield.find('.file-rep').remove();
      filefield.find('br').remove();
      filefield.find('input').val('');
      filefield.find('button').html('Add file');
      filefield.find('.del-file').remove();
      $('.form-field select').val('');
    }
  });
});

function parseQueryString( queryString ) {
    var params = {}, queries, temp, i, l;
 
    queries = queryString.split("&");
 
    for( i = 0; i < queries.length; i++ ) {
      temp = queries[i].split('=');
      params[temp[0]] = temp[1];
    }
 
    return params;
};