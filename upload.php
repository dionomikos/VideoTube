<?php 
require_once("includes/header.php");
require_once("includes/classes/VideoDetailsFormProvider.php");
?>


<div class="column">

    <?php
    $formProvider = new VideoDetailsFormProvider($con);
    echo $formProvider->createUploadForm();
    ?>


</div>

<script>
$("form").submit(function(){
    $("#loadingModal").modal("show");
});
</script>

<!-- We add the data-backdrop = "static" and data-keyboard = "false" to secure that our spinner won't dissapear if we click outside 
     the spinner message box. The data-backdrop prevents the modal from closing when we click on it, and the data-keyboard prevents
     any keyboard pressing closing... -->

<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        Please wait. This might take a while....
        <img src="assets/images/icons/loading-spinner.gif" />
      </div>
    </div>
  </div>
</div>


<?php require_once("includes/footer.php"); ?>
                