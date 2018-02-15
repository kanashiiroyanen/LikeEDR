<?php

// insert <br>
function br() 
{
    echo nl2br("\n");
}


// push button operation
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
          vmnet_detach($vmname);
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
  header('Location:http://192.168.11.5/forensic.php');
}

// make button
function make_button($vmname)
{
    echo '<td>';
    echo '<form method="POST" action="">';
    echo '<button type="submit" name="sub1" value="stop '.$vmname.'" class="btn btn-default">Stop</button>';
    echo '<button type="submit" name="sub1" value="start '.$vmname.'" class="btn btn-primary">Start</button>';
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

// get vm status
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
    
    $cmd = 'virsh -c qemu:///system detach-interface '.$vmname.' --mac '.$mac[1].' --type network';
    $ret = system_ex($cmd, $input);
}

// attach vmnetwork
function vmnet_attach($vmname)
{
    $vmnet = 'oa_network';
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LikeEDR?</title>
    <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="./bootstrap/js/bootstrap.min.js"></script>
  </head>
  <body>
  <h1>LikeEDR? for KVM</h1>
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
    </div>
    <p>
      <button type="button" class="btn btn-primary btn-lg">Large button</button>
        <button type="button" class="btn btn-default btn-lg">Large button</button>
        </p>
    </body>
</html>
