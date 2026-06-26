
<?php
/**
 * Created by PhpStorm.
 * User: blumine
 * Date: 2018. 3. 7.
 * Time: PM 6:42
 */

function aa($b)
{
    return 'hello '.$b;
}
?>

안녕하세요.
<?php
aa('김치');
?>



$logData = array(
'when' => date("Y-m-d H:i:s"),
'where' => $this->input->ip_address(),
'who' => $data['users_id'],
'what' => $this->input->server('PATH_INFO'), //$this->router->fetch_method(),
'how' => $data,
'why' => 'event',
'success' => 'T'.
'memo' => ''
);