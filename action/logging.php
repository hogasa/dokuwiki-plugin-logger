<?php

/**
 * DokuWiki Plugin logger (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Jens Brokfeld <geofreak.de@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class action_plugin_logger_logging extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {
        #Log dokuwiki page requests
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_before', array());

        #Log dokuwiki media files requests
        $controller->register_hook('FETCH_MEDIA_STATUS', 'BEFORE', $this, 'handle_before', array());
    }

    /**
     *
     */
    function handle_before(&$event, $param) {
        $this->_log();
    }

    /**
     * This function logs the current request to dokuwiki.
     * @global type $conf
     */
    function _log() {
        global $conf;

        #Logger is enabled
        if ($this->getConf('enabled') === 1) {
            #-----------------------------------------------------------------------
            #
            #log dataset variable (empty)
            $log_dataset = "";

            #current timestamp
            $timestamp = time();

            #-----------------------------------------------------------------------
            #
            #Check date option
            if ($this->getConf('date') === 1) {
                $log_dataset .= ";" . '"' . date("Y-m-d", $timestamp) . '"';
            }

            #Check time option
            if ($this->getConf('time') === 1) {
                $log_dataset .= ";" . '"' . date("H:i:s", $timestamp) . '"';
            }

            #Check ip option
            if ($this->getConf('ip') === 1) {
                $log_dataset .= ";" . '"' . $_SERVER['REMOTE_ADDR'] . '"';
            }

            #Check user option
            if ($this->getConf('user') === 1) {
                if (isset($_SERVER['REMOTE_USER'])) {
                    $log_dataset .= ";" . '"' . $_SERVER['REMOTE_USER'] . '"';
                } else {
                    $log_dataset .= ";\"\"";
                }
            }

            #Check pc name option gethostbyaddr
            if ($this->getConf('host_name') === 1) {
                $log_dataset .= ";" . '"' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . '"';
            }


            #Check query string option
            if ($this->getConf('query_string') === 1) {
                $log_dataset .= ";" . '"' . $_SERVER['QUERY_STRING'] . '"';
            }

            #Check user agent option
            if ($this->getConf('user_agent') === 1) {
                $log_dataset .= ";" . '"' . $_SERVER['HTTP_USER_AGENT'] . '"';
            }



            #-----------------------------------------------------------------------
            #
            #Remove the first delimiter (;)
            $log_dataset = substr($log_dataset, 1);


            #Check monthly option
            if ($this->getConf('monthly') === 1) {
                $log_filename = date("Y-m", $timestamp) . '.csv';
            } else {
                $log_filename = date("Y", $timestamp) . '.csv';
            }

            $ns_exclude = $this->getConf('ns_exclude');
            if ($ns_exclude == '') {
              #-----------------------------------------------------------------------
              #
              #Save log dataset
              io_saveFile($conf['mediadir'] . '/admin/logs/' . $log_filename, "$log_dataset\n", true);
              #-----------------------------------------------------------------------
            }else{
              $nsArray = explode(';',$ns_exclude);

              #Check, if logging is desired
              $doLogging = true;
              for ($i=0; $i < sizeof($nsArray); $i++) {
                $ns = 'id=' . $nsArray[$i];
                if(strpos($_SERVER['QUERY_STRING'],$ns)===0){
                  $doLogging = false;
                  break;
                }
              }

              #Save log dataset
              if($doLogging){
                io_saveFile($conf['mediadir'] . '/admin/logs/' . $log_filename, "$log_dataset\n", true);
              }

            }


        }
    }

}
