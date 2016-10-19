<div class="success-message alert alert-dismissible alert-success">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong><?= $data['message'] ?></strong>
  <script>
  $(".success-message").fadeTo(5000, 500).slideUp(500, function(){
      $(".success-message").alert('close');
  });
  </script>
</div>
