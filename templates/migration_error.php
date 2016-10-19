<div class="error-message alert alert-dismissible alert-danger">
  <button type="button" class="close" data-dismiss="alert">×</button>
  Error running migration <strong><?= $migration->getName(); ?></strong>.
  <script>
  $(".error-message").fadeTo(5000, 500).slideUp(500, function(){
      $(".error-message").alert('close');
  });
  </script>
</div>
