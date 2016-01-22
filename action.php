<?php
/**
 * Date: 2015/3/11
 * Time: 4:39
 */
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

class  action_plugin_remoteinf extends DokuWiki_Action_Plugin{
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'AFTER',  $this, 'set_data',array());
    }

    function set_data(){
        $enkey= $this->getConf("authkey");
        $timespan = $this->getConf("refreshtime");
        if($_SERVER['REMOTE_USER']!=null){
            $theuser=$_SERVER['REMOTE_USER'];
            $entime=floor (time()/(60*$timespan));
            $pat_str=$entime."~".$theuser."+".$enkey; // must be same with check_data
            $enmd5=md5($pat_str);
            $finalout= base64_encode( $theuser."|".$enmd5 );
            //    setcookie(DOKU_COOKIE, '', time() - 600000, $cookieDir, '', ($conf['securecookie'] && is_ssl()), true);
            setcookie("DWremoteinf",$finalout,time()+$timespan*60);
        }else{
            setcookie("DWremoteinf","",10);
        }
    }

    function check_data($infcode){
        $enkey= $this->getConf("authkey");    // this should be manual record in the other server which receive inf
        $timespan = $this->getConf("refreshtime");  //time span should be manual set to the same in the other server
        $entime=floor (time()/(60*$timespan));

        $txtstr=base64_decode($infcode);
        $argdata=explode("|",$txtstr,2);
        if(count($argdata)!=2){
            return false;
        }
        $theuser=$argdata[0];
        $enmd5_remote=$argdata[1];

        $pat_str=$entime."~".$theuser."+".$enkey;
        $enmd5=md5($pat_str);

        if($enmd5==$enmd5_remote){
            return $theuser;
        }else{
            return false;
        }

    }

}