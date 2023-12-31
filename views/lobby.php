<?php 
    require_once( GET_IN_LINE_PATH . "functions/functions.php" );      
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo(get_bloginfo( 'name' ))?> - Lobby</title>    
    <style type="text/css">
      #countdown {
        position: relative;
        margin: auto;
        margin-top: 40px;
        height: 40px;
        width: 40px;
        text-align: center;
      }
      #countdown-number {
        color: white;
        display: inline-block;
        line-height: 40px;
      }
      svg {
        position: absolute;
        top: 0;
        right: 0;
        width: 40px;
        height: 40px;
        transform: rotateY(-180deg) rotateZ(-90deg);
      }
      svg circle {
        stroke-dasharray: 113px;
        stroke-dashoffset: 0px;
        stroke-linecap: round;
        stroke-width: 2px;
        stroke: white;
        fill: none;
        animation: countdown 15s linear infinite forwards;
      }
      @keyframes countdown {
        from {
            stroke-dashoffset: 0px;
      }
        to {
            stroke-dashoffset: 113px;
      }
      }
      .container {
        color: whitesmoke;
        padding: 100px;
        font-family: 'Oxygen', sans-serif;
        text-align: center;
      }
      html {
        height: 100%;
      }
      body {
        min-height: 100%;
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover;
      }
      p{
        padding-top: 50px;
      }

    </style>
    
  </head>
  <body style="background-image: url('<?php echo(GET_IN_LINE_URL . 'assets/images/lobby.jpg') ?>');">
    <div class="position-relative container">
      <h1 class="z-index-1 position-relative">        
        <?php _e('Welcome to ' . get_bloginfo( 'name' ), 'gil')?>
      </h1>
      <div>
        <p>
          <?php _e("We are having a high number of visitors and we limited the access to the site to better serve you.", 'gil') ?>
        </p>
        <p>
          <?php _e("You're the number ", 'gil') ?>  
          <?php echo(get_in_line_position_in_queue()); ?> 
          <?php _e(" in the line; the waiting time is approximately ", 'gil') ?>  
          <?php _e(get_in_line_waiting_time()); ?> 
          <?php _e(" minutes.", 'gil') ?>  
        </p>

        <p>
        <?php 
          _e("This page will reload automatically and you won't lose your position
              in the queue if you leave and come back within the session limit time.", 'gil') 
        ?>            
        </p>
      </div>
      <div id="countdown">
        <div id="countdown-number"></div>
        <svg>
          <circle r="18" cx="20" cy="20"></circle>
        </svg>
      </div>
    </div>    
    <script>
      setTimeout(function () {
        window.location.reload();
      }, 15000);

      var countdownNumberEl = document.getElementById("countdown-number");
      var countdown = 15;

      countdownNumberEl.textContent = countdown;

      setInterval(function () {
        countdown = --countdown <= 0 ? 15 : countdown;

        countdownNumberEl.textContent = countdown;
      }, 1000);

    </script>
  </body>
</html>


<?php exit(); ?>