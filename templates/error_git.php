<div class="error-message alert alert-dismissible alert-danger">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong>Git is out of date!</strong>
  You must <a href="javascript:void(0)" class="alert-link update-git">pull</a> before adding a migration or revision.
  <script>
  $(".error-message").fadeTo(5000, 500).slideUp(500, function(){
      $(".error-message").alert('close');
  });
  </script>
</div>
