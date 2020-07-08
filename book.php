<?php
$mysqli = new mysqli('localhost', 'root', '', 'bookingcalendar');

if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare("select * from bookings where date=?");
    $stmt->bind_param('s',$date);
    $bookings=array();
    if($stmt->execute()){
        $result=$stmt->get_result();
        if($result->num_rows>0){
            while($row=$result->fetch_assoc()){
                $bookings[]=$row['timeslot'];
            }
            $stmt->close();
        }
    }
}

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $timeslot = $_POST['timeslot'];
    $stmt = $mysqli->prepare("select * from bookings where date=? AND timeslot=?");
    $stmt->bind_param('ss',$date,$timeslot);
    if($stmt->execute()){
        $result=$stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Already Booked</div>"; 
        }else{
            $stmt = $mysqli->prepare("INSERT INTO bookings (name, timeslot, email, date) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $name, $timeslot, $email, $date);
            $stmt->execute();
            $msg = "<div class='alert alert-info'>Booking Successfull</div>";
            $bookings[]=$timeslot;
            $stmt->close();
            $mysqli->close();
        }
    }
    
}

$duration=60;
$cleanup= 0;
$start= "00:00";
$end= "24:00";


function timeslots($duration, $cleanup, $start, $end){
    $start= new DateTime($start);
    $end= new DateTime($end);
    $interval= new DateInterval("PT".$duration."M");
    $cleanupInterval= new DateInterval("PT".$cleanup."M");
    $slots= array();

    for($intStart= $start; $intStart<$end; $intStart->add($interval)->add($cleanupInterval)){
        $endPeriod= clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end){
        break;
        }

        $slots[]= $intStart->format("H:iA")."-".$endPeriod->format("H:iA");

    }

    return $slots;

}


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title></title>

    <link
      rel="stylesheet"
      href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="/css/main.css" />

    <style>
      body {
        background-image: linear-gradient(-225deg, #e3fdf5 0%, #ffe6fa 100%);
        background-image: linear-gradient(to top, #6be7e1 0%, #fed6e3 100%);
        background-attachment: fixed;
        background-repeat: no-repeat;

        font-family: "Vibur", cursive;
        /*   the main font */
        font-family: "Abel", sans-serif;
        opacity: 0.95;
        /* background-image: linear-gradient(to top, #d9afd9 0%, #97d9e1 100%); */
      }

      .headervolunteer {
        font-size: 2em;
        text-align: center;
      }

      
      .headerbooking{
        font-size: 2em;
        text-align: center;
        margin:1rem;
      }


      form {
        box-shadow: 0 9px 50px hsla(20, 67%, 75%, 0.31);
        padding: 1rem;
        background-image: linear-gradient(-225deg, #e3fdf5 50%, #ffe6fa 50%);
      }

      .submits {
        width: 48%;
        display: inline-block;
        float: center;
        text-align: center;
        display: inline-block;
        color: #252537;

        width: 280px;
        height: 50px;

        background: #fff;
        border-radius: 5px;

        outline: none;
        border: none;

        cursor: pointer;
        text-align: center;
        transition: all 0.2s linear;

        margin: 7% auto;
        letter-spacing: 0.05em;

        background: #b8f2e6;
      }
      input[type="text"] {
        font-family: "Abel", sans-serif;
        color: white;

        outline: none;
        border: none;
        background-color: rgba(0, 0, 0, 0.644);
        border-radius: 5px 5px 5px 5px;
        transition: 0.2s linear;
      }

      input[type="email"] {
        background-color: rgba(0, 0, 0, 0.644);

        font-family: "Abel", sans-serif;
        color: white;

        outline: none;
        border: none;

        border-radius: 5px 5px 5px 5px;
        transition: 0.2s linear;
      }

      ::placeholder {
        color: white;
      }

      .container {
        background-color: azure;
        opacity: 0.5;
      }
    </style>
  </head>

  <body>
    <div class="container">
      <h1 class="text-center headervolunteer">
        Book for Date:
        <?php echo date('m/d/Y', strtotime($date)); ?>
      </h1>
      <hr />
      <div class="row">
        <div class="col-md-12">
          <?php echo isset($msg)?$msg:""; ?>
        </div>
        <?php $timeslots= timeslots($duration, $cleanup, $start, $end); 
             foreach($timeslots as $ts){
            ?>
        <div class="col-md-2">
          <div class="form-group">
            <?php if(in_array($ts,$bookings)){ ?>
            <button class="btn btn-danger"><?php echo $ts; ?></button>
            <?php }else{ ?>
            <button
              class="btn btn-info book"
              data-timeslot="<?php echo $ts; ?>"
            >
              <?php echo $ts; ?>
            </button>
            <?php } ?>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
    <div id="myModal" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
            <h4 class="modal-title headerbooking">
              Booking<span id="slot"></span>
            </h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <form action="" method="post">
                  <div class="form-group" class="headervolunteer">
                    <label for="">Timeslot</label>
                    <input
                      required
                      type="text"
                      readonly
                      name="timeslot"
                      id="timeslot"
                      class="form-control"
                    />
                  </div>
                  <div class="form-group">
                    <label for="">Name</label>
                    <input
                      required
                      type="text"
                      name="name"
                      class="form-control"
                    />
                  </div>
                  <div class="form-group" class="headervolunteer">
                    <label for="">Email</label>
                    <input
                      required
                      type="email"
                      name="email"
                      class="form-control"
                    />
                  </div>
                  <div style="text-align: center;">
                    <div class="form group">
                      <button
                        class="btn btn-primary submits"
                        type="submit"
                        name="submit"
                      >
                        Submit
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script
      src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
      integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
      crossorigin="anonymous"
    ></script>
    <script>
      $(".book").click(function () {
        var timeslot = $(this).attr("data-timeslot");
        $("#slot").html(timeslot);
        $("#timeslot").val(timeslot);
        $("#myModal").modal("show");
      });
    </script>
  </body>
</html>