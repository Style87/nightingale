<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>
      <?php echo _("Nightingale"); ?> <?= ENVIRONMENT; ?>
    </title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- favicon -->
    <link rel="apple-touch-icon" sizes="57x57" href="<?= BASE_URL; ?>/public/images/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?= BASE_URL; ?>/public/images/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?= BASE_URL; ?>/public/images/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?= BASE_URL; ?>/public/images/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?= BASE_URL; ?>/public/images/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?= BASE_URL; ?>/public/images/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= BASE_URL; ?>/public/images/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= BASE_URL; ?>/public/images/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL; ?>/public/images/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?= BASE_URL; ?>/public/images/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL; ?>/public/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL; ?>/public/images/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL; ?>/public/images/favicon-16x16.png">
    <link rel="manifest" href="<?= BASE_URL; ?>/public/images/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?= BASE_URL; ?>/public/images/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

    <!-- Material Design fonts -->
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">

    <!-- Bootstrap -->
    <link href="<?= BASE_URL; ?>/public/stylesheets/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Material Design -->
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>/public/stylesheets/bootstrap-material-design.min.css">
    <link rel="stylesheet" type="text/css" href="<?= BASE_URL; ?>/public/stylesheets/ripples.min.css">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">

    <!-- Custom Style -->
    <link href="<?= BASE_URL; ?>/public/stylesheets/style.css" rel="stylesheet">

    <script type="text/javascript" src="<?= BASE_URL; ?>/public/scripts/libs/jquery/jquery-2.1.4.min.js"></script>
    <script type="text/javascript" src="<?= BASE_URL; ?>/public/scripts/libs/bootstrap/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?= BASE_URL; ?>/public/scripts/libs/material.min.js"></script>
    <script type="text/javascript" src="<?= BASE_URL; ?>/public/scripts/libs/ripples.min.js"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>

  </head>
  <body>
    <div class="navbar navbar-inverse">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">Nightingale <small><?= strtoupper (ENVIRONMENT); ?></small></a>
        </div>
        <div class="navbar-collapse collapse navbar-inverse-collapse">
          <ul class="nav navbar-nav">
            <li class="<?= $this->router->getControllerString() == 'DashboardController' ? 'active' : ''?>">
              <a href="<?= BASE_URL ?>/dashboard/">Dashboard</a>
            </li>
            <li class="<?= $this->router->getControllerString() == 'RevisionsController' ? 'active' : ''?>">
              <a href="<?= BASE_URL ?>/revisions/">Revisions</a>
            </li>
            <li class="<?= $this->router->getControllerString() == 'MigrationsController' ? 'active' : ''?>">
              <a href="<?= BASE_URL ?>/migrations/">Migrations</a>
            </li>
            <li class="<?= $this->router->getControllerString() == 'SchemaController' ? 'active' : ''?>">
              <a href="<?= BASE_URL ?>/schema/">Schema</a>
            </li>
            <?php if (strtolower(ENVIRONMENT) == 'localhost') : ?>
              <li class="<?= $this->router->getControllerString() == 'SqliteController' ? 'active' : ''?>">
                <a href="<?= BASE_URL ?>/sqlite/">Sqlite</a>
              </li>
            <?php endif; ?>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li>
              <a href="javascript:void(0)" class="btn-font" id="nav-pull-git">
                Pull
                <span id="pull-count" class="badge"></span>
              </a>
            </li>
            <li>
              <a href="javascript:void(0)" class="btn-font" id="nav-push-git">
                Push
                <span id="push-count" class="badge"></span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div id="source-button" class="btn btn-primary btn-xs" style="display: none;">&lt; &gt;</div>

    <div id="content" class="container">
      <div class="row">
        <div class="col-sm-12" id="top">
          <?php if (isset($top) && !empty($top)): ?>
            <?php $this->_view($top, $data); ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12 col-sm-4 col-md-4" id="left">
          <?php if (isset($left) && !empty($left)): ?>
            <?php $this->_view($left, $data); ?>
          <?php endif; ?>
        </div>
        <div class="col-xs-12 col-sm-8 col-md-8" id="right">
          <?php if (isset($right) && !empty($right)): ?>
            <?php $this->_view($right, $data); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <script>
      String.prototype.ucfirst = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
      }

      $(document).ready(function(){
        $.material.init();

        $(function () {
          $('[data-toggle="tooltip"]').tooltip()
        })

        // Set default ajax success and error handlers
        $.ajaxSetup({
          async : false,
          success : function(html) {
            window.location.reload();
          },
          error : function(jqXHR, textStatus, errorThrown) {
            $('body').append(jqXHR.responseText);
          }
        });

        // Get the pull and push counts
        $.ajax({
          url : '<?= BASE_URL ?>/git/readPullCount',
          success : function(data) {
            $('#pull-count').text(data);
          }
        });

        $.ajax({
          url : '<?= BASE_URL ?>/git/readPushCount',
          success : function(data) {
            $('#push-count').text(data);
          }
        });

        $('#nav-pull-git').click(function(){
          $.ajax({
            url : '<?= BASE_URL ?>/git/pull',
            error : function(jqXHR, textStatus, errorThrown) {
              $this.removeClass('disabled').prop('disabled', false);
              $('body').append(jqXHR.responseText);
            }
          });
        });

        $('#nav-push-git').click(function(){
          $.ajax({
            url : '<?= BASE_URL ?>/git/push',
            error : function(jqXHR, textStatus, errorThrown) {
              $this.removeClass('disabled').prop('disabled', false);
              $('body').append(jqXHR.responseText);
            }
          });
        });

        $(window).resize(function(){
          if ($('#left-container').length == 0) return;
          var height = (
            $(window).height() - (
              $("body div.navbar").outerHeight(true) +
              $('#left-header').outerHeight(true) +
              parseInt($('#left-container').css("padding-top")) +
              parseInt($('#left-container').css("padding-bottom")) +
              50
            )
          ) + "px";
          $('#left-body').css({
            "max-height": height
          });
        });
        $(window).resize();

      });

    </script>
  </body>
</html>
