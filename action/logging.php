<?php

/**
 * DokuWiki Plugin logger (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Jens Pabel <info@geofreak.de>
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
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_before', array());
    }

    /**
     * catch logouts
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
            if ($this->getConf('pc_name') === 1) {
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
            # http://ipinfo.io is enabled.
            # 
            # Example: http://ipinfo.io/91.56.234.56/json
            #
            if ($this->getConf('ipinfo_enabled')) {

                #Request from http://ipinfo.io as json
                $info_json_string = file_get_contents("http://ipinfo.io/" . $_SERVER['REMOTE_ADDR'] . "/json");

                #Request from http://ipinfo.io as array
                $info_array = json_decode($info_json_string, true);

                #Check hostname option
                if ($this->getConf('hostname') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['hostname'] . '"';
                }

                #Check city option
                if ($this->getConf('city') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['city'] . '"';
                }

                #Check region option
                if ($this->getConf('region') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['region'] . '"';
                }

                #Check country option
                if ($this->getConf('country') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['country'] . '"';
                }

                #Check location option
                if ($this->getConf('loc') === 1) {
                    #Create wkt geometry
                    $coordArray = explode(',', trim($info_array['loc']));
                    $coord = 'POINT(' . $coordArray[1] . " " . $coordArray[0] . ')';
                    $log_dataset .= ";" . '"' . $coord . '"';
                }

                #Check organization option
                if ($this->getConf('org') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['org'] . '"';
                }

                #Check postal option
                if ($this->getConf('postal') === 1) {
                    $log_dataset .= ";" . '"' . $info_array['postal'] . '"';
                }
            }
            #-----------------------------------------------------------------------
            #
            #Remove the first delimiter (;)
            $log_dataset = substr($log_dataset, 1);

            #-----------------------------------------------------------------------
            #
            #Save log dataset
            io_saveFile($conf['mediadir'] . '/admin/logs/' . date("Y", $timestamp) . '.csv', "$log_dataset\n", true);

            #-----------------------------------------------------------------------
        }
    }

}
