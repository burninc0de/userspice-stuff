<script src="<?=$us_url_root?>usersc/widgets/logins/Chart.bundle.js"></script>
  <div class="row">
<!-- This is an example widget file.  It will be included on the statistics page of the Dashboard. -->
<!-- Do any php that needs to happen. You already have access to the db -->
<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$date = date("Y-m-d H:i:s");
$logins = '0,';
$c = $db->query("SELECT id FROM logs WHERE logdate < ? AND logdate > ? AND lognote = ?",array(date("Y-m-d H:i:s", strtotime("-52 weeks", strtotime($date))),date("Y-m-d H:i:s", strtotime("-53 weeks", strtotime($date))), "User logged in."))->count();
$logins .= $c.", ";
$c = $db->query("SELECT id FROM logs WHERE logdate < ? AND logdate > ? AND lognote = ?",array(date("Y-m-d H:i:s", strtotime("-2 weeks", strtotime($date))),date("Y-m-d H:i:s", strtotime("-3 weeks", strtotime($date))), "User logged in."))->count();
$logins .= $c.", ";
$c = $db->query("SELECT id FROM logs WHERE logdate < ? AND logdate > ? AND lognote = ?",array(date("Y-m-d H:i:s", strtotime("-1 week", strtotime($date))),date("Y-m-d H:i:s", strtotime("-2 weeks", strtotime($date))), "User logged in."))->count();
$logins .= $c.", ";
//This week
$c = $db->query("SELECT id FROM logs WHERE logdate > ? AND lognote = ?",array(date("Y-m-d H:i:s", strtotime("-1 week", strtotime($date))), "User logged in."))->count();
$logins .= $c.", ";

?>
<!-- Create a div to hold your widget -->
<div class="col-lg-6">
  <div class="card">
    <div class="card-body">
      <h4 class="mb-3">Logins</h4>
      <canvas id="logins-chart"></canvas>
    </div>
  </div>
</div><!-- /# column -->


  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3">New Users Daily</h4>
        <canvas id="new-users-daily"></canvas>
      </div>
    </div>
  </div><!-- /# column -->
  
  
    <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-3">New Users</h4>
        <canvas id="new-users-chart"></canvas>
      </div>
    </div>
  </div>
  
</div> 


<?php
//php for charts
$date = date("Y-m-d H:i:s");
$newUsers = '';
$c = $db->query("SELECT id FROM users WHERE join_date > ?",array(date("Y-m-d H:i:s", strtotime("-1 week", strtotime($date)))))->count();
$newUsers .= $c.", ";
$c = $db->query("SELECT id FROM users WHERE join_date > ?",array(date("Y-m-d H:i:s", strtotime("-1 month", strtotime($date)))))->count();
$newUsers .= $c.", ";
$c = $db->query("SELECT id FROM users WHERE join_date > ?",array(date("Y-m-d H:i:s", strtotime("-90 days", strtotime($date)))))->count();
$newUsers .= $c.", ";
$c = $db->query("SELECT id FROM users WHERE join_date > ?",array(date("Y-m-d H:i:s", strtotime("-6 months", strtotime($date)))))->count();
$newUsers .= $c.", ";
$c = $db->query("SELECT id FROM users WHERE join_date > ?",array(date("Y-m-d H:i:s", strtotime("-1 year", strtotime($date)))))->count();
$newUsers .= $c;



// Get the current date
$currentDate = date("Y-m-d");

// Query the database to count new users for today
$todayCount = $db->query("SELECT id FROM users WHERE DATE(join_date) = ?", array($currentDate))->count();

// Query the database to count new users for each day of the last week
$newUsersPerDay = array();
for ($i = 0; $i < 7; $i++) {
    $date = date("Y-m-d", strtotime("-{$i} days", strtotime($currentDate)));
    $c = $db->query("SELECT id FROM users WHERE DATE(join_date) = ?", array($date))->count();
    $newUsersPerDay[] = $c; // Push the count directly into the array
}
$newUsersPerDay[] = $todayCount; // Append the count for today to the array

$newUsersPerDay = array_reverse($newUsersPerDay);

?>


<!-- Put any javascript here -->
<script type="text/javascript">
$(document).ready(function() {
var ctx = document.getElementById( "new-users-chart" );
ctx.height = 150;
var myChart = new Chart( ctx, {
  type: 'line',
  data: {
    labels: [ "Last 7 Days", "Last 30 Days", "Last 90 Days", "Last 180 Days", "Last 365 Days" ],
    type: 'line',
    defaultFontFamily: 'Montserrat',
    datasets: [ {
      label: "New Users",
      data: [ <?=$newUsers?> ],
      backgroundColor: 'transparent',
      borderColor: 'rgba(220,53,69,0.75)',
      borderWidth: 3,
      pointStyle: 'circle',
      pointRadius: 5,
      pointBorderColor: 'transparent',
      pointBackgroundColor: 'rgba(220,53,69,0.75)',
    }]
  },
  options: {
    responsive: true,

    tooltips: {
      mode: 'index',
      titleFontSize: 12,
      titleFontColor: '#000',
      bodyFontColor: '#000',
      backgroundColor: '#fff',
      titleFontFamily: 'Montserrat',
      bodyFontFamily: 'Montserrat',
      cornerRadius: 3,
      intersect: false,
    },
    legend: {
      display: false,
      labels: {
        usePointStyle: true,
        fontFamily: 'Montserrat',
      },
    },
    scales: {
      xAxes: [ {
        display: true,
        gridLines: {
          display: false,
          drawBorder: false
        },
        scaleLabel: {
          display: false,
          labelString: 'Time'
        }
      } ],
      yAxes: [ {
        display: true,
        gridLines: {
          display: false,
          drawBorder: false
        },
        scaleLabel: {
          display: true,
          labelString: 'Users'
        }
      } ]
    },
    title: {
      display: false,
      text: 'Normal Legend'
    }
  }

});

var ctx = document.getElementById( "logins-chart" );
ctx.height = 150;
var myChart = new Chart( ctx, {
  type: 'line',
  data: {
    labels: [" ","This week Last Year", "2 Weeks Ago", "1 Week Ago", "Last 7 days" ],
    type: 'line',
    defaultFontFamily: 'Montserrat',
    datasets: [ {
      label: "Logins",
      data: [ <?=$logins?> ],
      backgroundColor: 'transparent',
      borderColor: 'rgba(40,167,69,0.75)',
      borderWidth: 3,
      pointStyle: 'circle',
      pointRadius: 5,
      pointBorderColor: 'transparent',
      pointBackgroundColor: 'rgba(40,167,69,0.75)',
    }]
  },
  options: {
    responsive: true,

    tooltips: {
      mode: 'index',
      titleFontSize: 12,
      titleFontColor: '#000',
      bodyFontColor: '#000',
      backgroundColor: '#fff',
      titleFontFamily: 'Montserrat',
      bodyFontFamily: 'Montserrat',
      cornerRadius: 3,
      intersect: false,
    },
    legend: {
      display: false,
      labels: {
        usePointStyle: true,
        fontFamily: 'Montserrat',
      },
    },
    scales: {
      xAxes: [ {
        display: true,
        gridLines: {
          display: false,
          drawBorder: false
        },
        scaleLabel: {
          display: false,
          labelString: 'Time'
        }
      } ],
      yAxes: [ {
        display: true,
        gridLines: {
          display: false,
          drawBorder: false
        },
        scaleLabel: {
          display: true,
          labelString: 'Users'
        }
      } ]
    },
    title: {
      display: false,
      text: 'Normal Legend'
    }
  }
} );

var ctx = document.getElementById("new-users-daily");
ctx.height = 150;

// Adjust labels array to include today
var labels = ["7 days ago", "6 days ago", "5 days ago", "4 days ago", "3 days ago", "2 days ago", "Yesterday", "Today"];

var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        type: 'line',
        defaultFontFamily: 'Montserrat',
        datasets: [{
            label: "New Users",
            data: [<?php echo implode(", ", $newUsersPerDay); ?>],
            backgroundColor: 'transparent',
            borderColor: 'rgb(106, 90, 205)',
            borderWidth: 3,
            pointStyle: 'circle',
            pointRadius: 5,
            pointBorderColor: 'transparent',
            pointBackgroundColor: 'rgb(106, 90, 205)',
        }]
    },
    options: {
        responsive: true,
        tooltips: {
            mode: 'index',
            titleFontSize: 12,
            titleFontColor: '#000',
            bodyFontColor: '#000',
            backgroundColor: '#fff',
            titleFontFamily: 'Montserrat',
            bodyFontFamily: 'Montserrat',
            cornerRadius: 3,
            intersect: false,
        },
        legend: {
            display: false,
            labels: {
                usePointStyle: true,
                fontFamily: 'Montserrat',
            },
        },
        scales: {
            xAxes: [{
                display: true,
                gridLines: {
                    display: false,
                    drawBorder: false
                },
                scaleLabel: {
                    display: false,
                    labelString: 'Time'
                }
            }],
            yAxes: [{
                display: true,
                gridLines: {
                    display: false,
                    drawBorder: false
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Users'
                }
            }]
        },
        title: {
            display: false,
            text: 'Normal Legend'
        }
    }
});

}); //End DocReady
</script>
