<footer id="footer" class="site-width">
  かきくけこ
</footer>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
  $(function(){
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style':'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;'});

    }


    var $favo = $('.js-click-favo') || null;

    //favoMsgId = $favo.data('messageid') || null;

    //if(favoMsgId !== undefined && favoMsgId !== null){
      $favo.on('click',function(){
        var $this = $(this);
        $.ajax({
          type: "POST",
          url: "ajaxFavo.php",
          data: {messageId : $this.data('messageid')}
        }).done(function(data){
          //console.log('ajax success');
          //console.log($this.data('messageid'));
          $this.toggleClass('active');
        }).fail(function(msg){
          //console.log('ajax error');
        });
      });
  //}

    var $side_favo = $('.favo-side-icon');

    $side_favo.on('click',function(){

      var $this = $(this);
      $.ajax({
        url: 'forum.php',
        type: 'POST',
        data: {userId: $this.data('userid')}
      })
      .done(function(data){
        console.log('done!');
      })
      .fail(function(msg){
        console.log('its failed..');
      });

    });

    var $test = $('.test');

    $test.on('click',function(){

      var $this = $(this);
      $.ajax({
        url: 'forum2.php',
        type: 'POST',
        data: {userId: $this.data('userid')}
      })
      .done(function(data){
        console.log('test done!');
      })
      .fail(function(msg){
        console.log('its failed..');
      });

    });



    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+[\s　]+$/g,"").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 3500);
    }


var $dropArea = $('.area-drop');
var $fileInput = $('.input-file');
$dropArea.on('dragover',function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border','3px #ccc dashed');
});

$dropArea.on('dragleave',function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border','none');
});


$fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      console.log(this.files[0]);
      var file = this.files[0],
          $img = $(this).siblings('.prev-img'),
          fileReader = new FileReader();


      fileReader.onload = function(event) {

        $img.attr('src', event.target.result).show();
      };

      fileReader.readAsDataURL(file);

    });



    var $height_wrapper = $('#forum-wrapper');
    var $height_sidebar = $('#forum-sidebar');

    $height_sidebar.height($height_wrapper.height());






















  });
</script>
</body>
</html>
