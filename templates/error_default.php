<div class="error-message alert alert-dismissible alert-danger">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong><?= $message ?></strong>
  <script>
  $(".error-message").fadeTo(5000, 500).slideUp(500, function(){
      $(".error-message").alert('close');
  });
  </script>
</div>
