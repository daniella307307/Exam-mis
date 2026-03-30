<?php
include('session.php');
$SIM  = $_GET['SIM'];
 $select_link = mysqli_query($conn,"SELECT * FROM simulators WHERE sim_id='$SIM'");
 $link_details = mysqli_fetch_array($select_link );
 $sim_link = $link_details['sim_link'];
 
?>
 
<!DOCTYPE html>
<html>
<body>
  <iframe 
    src="<?php echo htmlspecialchars($sim_link); ?>" 
    onerror="fallback()"
    width="100%" 
    height="600px"
  ></iframe>
  <div id="fallback" style="display:none;">
    <p>Embedding blocked. Open the site directly: 
      <a href="<?php echo htmlspecialchars($sim_link); ?>">Click Here</a>
    </p>
  </div>
  <script>
    function fallback() {
      document.getElementById('fallback').style.display = 'block';
    }
  </script>
</body>
</html>