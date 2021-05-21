<?PHP
$swapfile_script_path = "/plugins/swapfile/scripts/rc.swapfile";
$swapfile_cfg = parse_ini_file("/boot/config/plugins/swapfile/swapfile.cfg");

$swapfile_location = $swapfile_cfg['SWAP_LOCATION'];
$swapfile_filename = $swapfile_cfg['SWAP_FILENAME'];

$swapfile_exists = (file_exists($swapfile_location."/".$swapfile_filename)) ? "Yes" : "No";

shell_exec("swapon -s > /tmp/swapfile_summary.txt 2>&1");
$swapfile_summary = file("/tmp/swapfile_summary.txt", FILE_IGNORE_NEW_LINES);
$swapfile_summary_cnt = count($swapfile_summary);

$swapfile_running = "No";
$swapfile_size = 0;
$swapfile_usage = 0;
for ($i=0; $i<$swapfile_summary_cnt; $i++)
{
  $pos = strpos($swapfile_summary[$i], $swapfile_location."/".$swapfile_filename);
  if (($pos !== false) && ($pos == 0))
  {
    $swapfile_running = "Yes";
    $split_string = preg_split("/\s+/", $swapfile_summary[$i]);
    $swapfile_fullpath = $split_string[0];
    $swapfile_size = $split_string[2];
    $swapfile_usage = $split_string[3];
  }
}
shell_exec("rm --force /tmp/swapfile_summary.txt");

$percentage = 0;
if (((float)$swapfile_size) > 0)
{
  $percentage = round(((float)$swapfile_usage)/((float)$swapfile_size)*100);
}

$control_actions_exist = "false";
$version_actions_exist = "false";

$arr = explode("-", trim(shell_exec("uname -r")), 2);
if ($arr[0] < "4.1.5")
{
  $path_prefix = "/usr/local/emhttp";
}
else
{
  $path_prefix = "";
}

?>

<HTML>
<HEAD></HEAD>
<BODY>

<div style="width: 49%; float:left; border: 0px solid black;">
  <div id="title">
    <span class="left">Status</span>
  </div>

  <div style="border: 0px solid black;">
    Swap file exists:
      <?if ($swapfile_exists == "Yes") :?>
        <span class="green-text"><b> &#10004</b></span>
      <?else:?>
        <span class="orange-text"><b> &#10006</b></span>
      <?endif;?>
    <br></br>
    Swap file in use:
    <?if ($swapfile_running == "Yes"):?>
      <span class="green-text"><b> &#10004</b></span>
      <br></br>
      Swap file location and filename: <b><?=$swapfile_fullpath?></b>
      <br></br>
      <div>
        <div style="width: 35%; float:left; border: 0px solid black;">
          Swap file size: <b><?=printf("%0.1f",$swapfile_size/1024);?> MB</b>
        </div>
        <div style="width: 25%; float:left; border: 0px solid black;">
          used: <b><?=printf("%0.1f",$swapfile_usage/1024);?> MB</b>
        </div>
        <div style="width: 35%; float:left; border: 0px solid black;">
          <?if ($percentage <= 50) :?>
            <div style="background:#CCCCCC; border:1px solid #666666; height:15px; width:100px;">
              <div style="background:#6fa239; height:15px; width:<?=$percentage;?>px;"><center><?=$percentage;?>%</center></div>
            </div>
          <?elseif ($percentage <= 75) :?>
            <div style="background:#CCCCCC; border:1px solid #666666; height:15px; width:100px;">
              <div style="background:#ff9900; height:15px; width:<?=$percentage;?>px;"><center><?=$percentage;?>%</center></div>
            </div>
          <?elseif ($percentage <= 100) :?>
            <div style="background:#CCCCCC; border:1px solid #666666; height:15px; width:100px;">
              <div style="color:#ffffff; background:#cc0000; height:15px; width:<?=$percentage;?>px;"><center><?=$percentage;?>%</center></div>
            </div>
          <?endif;?>
        </div>
      </div>
    <?else:?>
      <span class="orange-text"><b> &#10006</b></span>
    <?endif;?>
    
  </div>

  <div id="title">
    <span class="left">Actions</span>
  </div>

  <div>
    <table>
      <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
        <td colspan="3">Control Actions</td>
      </tr>
      <?if ($swapfile_running == "Yes"):?>
        <tr>
          <td width="30%">
            <form name="stop" method="POST" action="/update.htm" target="progressFrame">
              <input type="hidden" name="cmd" value="<?=$path_prefix;?><?=$swapfile_script_path;?>">
              <input type="hidden" name="arg1" value="stop"/>
              <input type="submit" name="runCmd" value="Stop">
            </form>
          </td>
          <td>Stop swap file usage</td>
        </tr>
        <tr>
          <td width="30%">
            <form name="restart" method="POST" action="/update.htm" target="progressFrame">
              <input type="hidden" name="cmd" value="<?=$path_prefix;?><?=$swapfile_script_path;?>">
              <input type="hidden" name="arg1" value="restart"/>
              <input type="submit" name="runCmd" value="Restart">
            </form>
          </td>
          <td>Restart swap file usage</td>
        </tr>
        <?$control_actions_exist = "true"?>
      <?else:?>
        <tr>
          <td width="30%">
            <form name="start" method="POST" action="/update.htm" target="progressFrame">
              <input type="hidden" name="cmd" value="<?=$path_prefix;?><?=$swapfile_script_path;?>">
              <input type="hidden" name="arg1" value="start"/>
              <input type="submit" name="runCmd" value="Start">
            </form>
          </td>
          <td>Start swap file usage</td>
        </tr>
        <?$control_actions_exist = "true"?>
      <?endif;?>
      <?if ($control_actions_exist=="false"):?>
        <tr>
          <td colspan="3" align="center">No Control Actions available</td>
        </tr>
      <?endif;?>
    </table>
  </div>

  <br></br>
  <br></br>

</div>
    
<div style="width: 49%; float:right; border: 0px solid black;">

  <div id="title">
    <span class="left">Configuration</span>
  </div>

  <div>
    <form name="swapfile_settings" method="POST" action="/update.htm" target="progressFrame" onsubmit="">
      <table>
        <tr>
          <td colspan="2" align="center">
            <input type="hidden" name="cmd" value="<?=$path_prefix;?><?=$swapfile_script_path;?>">
            <input type="hidden" name="arg1" value="updatecfg"/>
            <input type="submit" name="runCmd" value="Save Below Configuration & Implement Immediately">
            <button type="button" onClick="done();">Return to unRAID Settings Page</button>
          </td>
        </tr>
        <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
          <td colspan="2">Boot and Startup options</td>
        </tr>
        <tr>
          <td>Start Swap file during array mount:</td>
          <td>
            <select name="arg3" id="arg3" size="1">
              <?=mk_option($swapfile_cfg['SWAP_ENABLE_ON_BOOT'], "true", "Yes");?>
              <?=mk_option($swapfile_cfg['SWAP_ENABLE_ON_BOOT'], "false", "No");?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Delete Swap file upon Stop (will be recreated during Start):</td>
          <td>
            <select name="arg4" id="arg4" size="1">
              <?=mk_option($swapfile_cfg['SWAP_DELETE'], "true", "Yes");?>
              <?=mk_option($swapfile_cfg['SWAP_DELETE'], "false", "No");?>
            </select>
          </td>
        </tr>
        <tr style="font-weight:bold; color:#333333; background:#F0F0F0; text-shadow:0 1px 1px #FFFFFF;">
          <td colspan="2">Swapfile Settings (Any change will cause the swap file to be recreated if running)</td>
        </tr>
        <tr>
          <td>Swap file location (Choose DISK share and not USER share):</td>
          <td><input type="text" name="arg5" id="arg5" style="width: 17em;" maxlength="255" value="<?=$swapfile_cfg['SWAP_LOCATION'];?>"></td>
        </tr>
        <tr>
          <td>Swap file file name:</td>
          <td><input type="text" name="arg6" id="arg6" style="width: 17em;" maxlength="25" value="<?=$swapfile_cfg['SWAP_FILENAME'];?>"></td>
        </tr>
        <tr>
          <td>Swap file swap name:</td>
          <td><input type="text" name="arg7" id="arg7" style="width: 17em;" maxlength="25" value="<?=$swapfile_cfg['SWAP_NAME'];?>"></td>
        </tr>
        <tr>
          <td>Swap file size in MBs (example: for 2GB enter 2048):</td>
          <td><input type="text" name="arg8" id="arg8" style="width: 3em;" maxlength="10" value="<?=$swapfile_cfg['SWAP_SIZE_MB'];?>"> MB</td>
        </tr>
      </table>
    </form>
  </div>

  <br></br>
  <br></br>

</div>

</BODY>
</HTML>
