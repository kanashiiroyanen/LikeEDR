<?php

// insert <br>
function br() 
{
  echo nl2br("\n");
}

// send post request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST["sub1"])) {
    $kbn = htmlspecialchars($_POST["sub1"], ENT_QUOTES, "UTF-8");
    $command_vmname = preg_split("/[\s,]+/", $kbn);
    $command = $command_vmname[0];
    $vmname = $command_vmname[1];

    switch($command) {
      case "stop": 
          vm_stop($vmname);
          break;
      case "start": 
          vm_start($vmname);
          break;
      case "suspend": 
          vm_suspend($vmname);
          break;
      case "resume": 
          vm_resume($vmname);
          break;
      case "restore": 
          vm_restore($vmname);
          break;
      case "detach": 
          if (isset($_POST["mac"])) {
            $mac = htmlspecialchars($_POST["mac"], ENT_QUOTES, "UTF-8");
            //echo $mac;
            vmnet_detach_edr($mac);
          } else vmnet_detach($vmname);
          break;
      case "attach": 
          vmnet_attach($vmname);
          break;
      case "forensic": 
          vm_forensic($vmname);
          break;
      default:
          echo "kuso";
          break;
    }
  } 
  header('Location:http://192.168.11.7/LikeEDR/forensic.php');
}

// make button
function make_button($vmname)
{
    echo '<td>';
    echo '<form method="POST" action="">';
    echo '<button type="submit" name="sub1" value="stop '.$vmname.'" class="btn btn-default">Stop</button>';
    echo '<button type="submit" name="sub2" value="start '.$vmname.'" class="btn btn-primary">Start</button>';
    echo '<button type="submit" name="sub1" value="suspend '.$vmname.'" class="btn btn-default">Suspend</button>';
    echo '<button type="submit" name="sub1" value="resume '.$vmname.'" class="btn btn-primary">Resume</button>';
    echo '<button type="submit" name="sub1" value="restore '.$vmname.'" class="btn btn-success">Restore</button>';
    echo '<button type="submit" name="sub1" value="detach '.$vmname.'" class="btn btn-info">network detach</button>';
    echo '<button type="submit" name="sub1" value="attach '.$vmname.'" class="btn btn-warning">network attach</button>';
    echo '<button type="submit" name="sub1" value="forensic '.$vmname.'" class="btn btn-danger">Forensic</button>';
    //echo '<button type=\"button\" class=\"btn btn-link\">Link</button>';
    echo '</form>';
    echo '</td>';
}


// command execution
function system_ex($cmd, $stdin = "")
{
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
        );

    $process = proc_open($cmd, $descriptorspec, $pipes);
    $result_message = "";
    $error_message = "";
    $return = null;

    if (is_resource($process))
    {
        fputs($pipes[0], $stdin);
        fclose($pipes[0]);
        
        while ($error = fgets($pipes[2])){
            $error_message .= $error;
        }
        while ($result = fgets($pipes[1])){
            $result_message .= $result;
        }
        foreach ($pipes as $k=>$_rs){
            if (is_resource($_rs)){
                fclose($_rs);
            }
        }
        $return_value = proc_close($process);
    }
    return array(
        'return' => $return_value,
        'stdout' => $result_message,
        'stderr' => $error_message,
        );
}

// get vm status (running only)
function get_vmstatus_run()
{
    //$cmd = 'virsh -c qemu:///system detach-interface centos7.0 --mac 52:54:00:a2:cf:18 --type network';
    $cmd = 'virsh -c qemu:///system list';
    $ret = system_ex($cmd, $input);
    $vm_status = preg_split("/[\s,]+/", $ret['stdout']);
    $vm_status = array_slice($vm_status, 5);

    $array = array();
    $count = 0;
    foreach($vm_status as $val) {
        if ($val == "off") break;
        if ($val == "in") break;
        if ($val == "shut") $val = $val."off";
        if ($count == 0) {
            $count++;
        } elseif ($count == 1) {
            echo '<td>'.$val.'</td>';
            $array[] = $val;
            $count++;
        } else {
            $count = 0;
        }
    }
    return $array;
}

// get vm status (all)
function get_vmstatus()
{
    //$cmd = 'virsh -c qemu:///system detach-interface centos7.0 --mac 52:54:00:a2:cf:18 --type network';
    $cmd = 'virsh -c qemu:///system list --all';
    $ret = system_ex($cmd, $input);
    $vm_status = preg_split("/[\s,]+/", $ret['stdout']);
    $vm_status = array_slice($vm_status, 5);

    $count = 0;
    foreach($vm_status as $val) {
        if ($val == "off") break;
        if ($val == "in") break;
        if ($val == "shut") $val = $val."off";
        if ($count == 0) {
            echo '<tr>';
            echo '<th scope=\"row\">'.$val.'</th>';
            $count++;
        } elseif ($count == 1) {
            echo '<td>'.$val.'</td>';
            $vmname = $val;
            $count++;
        } else {
            echo '<td>'.$val.'</td>';
            make_button($vmname);
            echo '</tr>';
            $count = 0;
        }
    }
}

// stop vm
function vm_stop($vmname)
{
    $cmd = 'virsh -c qemu:///system shutdown '.$vmname;
    $ret = system_ex($cmd, $input);
}

// start vm
function vm_start($vmname)
{
    $cmd = 'virsh -c qemu:///system start '.$vmname;
    $ret = system_ex($cmd, $input);
}

// suspend vm
function vm_suspend($vmname)
{
    $cmd = 'virsh -c qemu:///system suspend '.$vmname;
    $ret = system_ex($cmd, $input);
}

// resume vm
function vm_resume($vmname)
{
    $cmd = 'virsh -c qemu:///system resume '.$vmname;
    $ret = system_ex($cmd, $input);
}

// restore vm
function vm_restore($vmname)
{
    $cmd = 'virsh -c qemu:///system resume '.$vmname;
    $ret = system_ex($cmd, $input);
}

// detach vmnetwork
function vmnet_detach($vmname)
{
    $cmd = 'virsh -c qemu:///system dumpxml '.$vmname.' | grep "mac address"';
    $ret = system_ex($cmd);
    preg_match('/mac address=\'(.*)\'/', $ret['stdout'], $mac);
    
    //$cmd = 'virsh -c qemu:///system detach-interface '.$vmname.' --mac '.$mac[1].' --type network';
    $cmd = 'virsh -c qemu:///system detach-interface '.$vmname.' --type network';
    $ret = system_ex($cmd, $input);
}

// detach vmnetwork by mal-detection
function vmnet_detach_edr($mac)
{
    $mac = strtolower($mac);
    $vmnames = get_vmstatus_run();
    foreach($vmnames as $val) {
      $cmd = 'virsh -c qemu:///system dumpxml '.$val.' | grep '.$mac;
      $ret = system_ex($cmd, $input);
      if ($ret['return'] == 0) {
        $vmname = $val;
      }
    }
    $cmd = 'virsh -c qemu:///system detach-interface '.$vmname.' --type network';
    $ret = system_ex($cmd);

    $vmnet = 'forensic';
    $cmd = 'virsh -c qemu:///system attach-interface '.$vmname. ' --type network '.$vmnet;
    $ret = system_ex($cmd);
    echo '<script src="./mal_alert.js"></script>';
    echo '<script>mal_alert();</script>';

    //echo '<script>swal({title: "E・D・R!! E・D・R!!",text: "Malware was detected from Windows10",icon: "warning",button: "OK",});</script>';
}

// attach vmnetwork
function vmnet_attach($vmname)
{
    $vmnet = 'oa-network';
    $cmd = 'virsh -c qemu:///system attach-interface '.$vmname. ' --type network '.$vmnet;
    $ret = system_ex($cmd, $input);
}


// forensic of vm
function vm_forensic()
{
}
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
    <title>LikeEDR?</title>
  <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

  <!-- Tell the browser to be responsive to screen width -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect. -->
    <link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  </head>

<body class="hold-transition skin-blue sidebar-mini">
<!--<body class="skin-blue">-->
<div class="wrapper">

<!-- Main Header -->
<header class="main-header">
<!-- Logo -->
<a href="index2.html" class="logo">
<!-- mini logo for sidebar mini 50x50 pixels -->
<!--
<span class="logo-mini"><b>A</b>LT</span>
-->
<!-- logo for regular state and mobile devices -->
<span class="logo-lg"><b>Like</b>EDR?</span>
</a>
<!-- トップメニュー -->
<nav class="navbar navbar-static-top" role="navigation">
<ul class="nav navbar-nav">
<li><a href="">管理情報</a></li>
</ul>
</nav>
</header>

<!-- サイドバー -->
<aside class="main-sidebar">
<section class="sidebar">
<ul class="sidebar-menu">

<!-- メニューヘッダ -->
<li class="header">機能一覧</li>

<!-- メニュー項目 -->
<li><a href="">新規登録</a></li>

</ul>
</section>
</aside><!-- end sidebar -->

<div class="content-wrapper">
<!-- コンテンツヘッダ -->
<section class="content-header">
<h1>VM status & Information</h1>
</section>

<section class="content">
<div class="box">
 <div class="box-header with-border">
  <!--<h3 class="box-title">LikeEDR? for KVM</h3>-->
 </div>
    <div class="container">
    <div class="table-responsive">
    <table class="table table-bordered">
    <thead>
    <tr>
    <th>id</th>
    <th>vmname</th>
    <th>status</th>
    <th>operation</th>
    </tr>
    </thead>
    <tbody>
    <?php get_vmstatus(); ?>
    </tbody>
    </table>
    </div>
    <iframe width="100%" height="100%" src=./honda/report.html></iframe>
    <script>
    $('iframe')
    .on('load', function(){
    try {
    $(this).height(this.contentWindow.document.documentElement.scrollHeight);
    } catch (e) {
    }
    })
    .trigger('load');
    </script>
    </div>
    </div>
    </section>
    </div>
    <!--
    <script src="./mal_alert.js"></script>
    <script>mal_alert();</script>
    -->
    <!--
    <script>swal({title: "E・D・R!!  E・D・R!!",text: "Malware was detected from Windows10",icon: "warning",button: "OK",});</script>
    -->
    <!--
    <p>
      <button type="button" class="btn btn-primary btn-lg">Large button</button>
        <button type="button" class="btn btn-default btn-lg">Large button</button>
        </p>
        -->
    </body>
</html>
