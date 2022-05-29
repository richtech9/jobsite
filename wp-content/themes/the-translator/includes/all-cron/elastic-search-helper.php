<?php
/*
 * NOTES
 *  to see the details of an index from the command line (index is named contest) :
 *          curl -XGET 127.0.0.1:9200/contest/_mapping
 *
 * to get details, including version of the ES :
 *          curl -XGET 127.0.0.1:9200
 *
 * to get an individual record
 *               curl -XGET 127.0.0.1:9200/<index name>/<type>/<_id>
 *   example     curl -XGET 127.0.0.1:9200/content/freelinguist/152047
 *   example     curl -XGET 127.0.0.1:9200/translator/freelinguist/3588

 */
require_once( ABSPATH . '/wp-content/themes/the-translator/vendor-frozen/autoload.php');

class FreelinguistElasticSearchHelper {

    /*
     * current-php-code 2020-Oct-13
     * internal-call
     * input-sanitized :
    */
    
    const ES_OPTION_KEY = 'elasticsearch_option';
    const ES_OPTION_SUBKEY_IP = 'elastic_server_ip';
    const ES_OPTION_SUBKEY_PORT = 'elastic_server_port';

    const DEFAULT_BULK_LIMIT = 1000;
    
    const DEFAULT_ES_IP = '127.0.0.1';
    const DEFAULT_ES_PORT = '9200';
    public  $elsatic_server_ip;
    public  $elsatic_server_port;

    static $last_ip_used;
    static $last_port_used;

    /**
     * @var Elasticsearch\Client
     */
    protected $client;

    //code-notes allow ES to be invalid behind the scenes for modifying indexes, and it not break code
    protected $b_initted = false;

    /**
     * FreelinguistElasticSearchHelper constructor.
     * @param string $option_key
     * @throws Exception
     */
    public function __construct($option_key = '')
    {
        $this->b_initted = false;
        if (!$option_key) {$option_key = static::ES_OPTION_KEY;}
        $options = get_option($option_key);
        if (empty($options)) {
            throw new InvalidArgumentException("No option set for $option_key");
        }
        
        static::$last_ip_used = $this->elsatic_server_ip = static::DEFAULT_ES_IP;
        static::$last_port_used = $this->elsatic_server_port = static::DEFAULT_ES_PORT;
        
        if (is_array($options)) {
            if (isset($options[static::ES_OPTION_SUBKEY_IP])) {
                $this->elsatic_server_ip = $options[static::ES_OPTION_SUBKEY_IP];
            }

            if (isset($options[static::ES_OPTION_SUBKEY_PORT])) {
                $this->elsatic_server_port = $options[static::ES_OPTION_SUBKEY_PORT];
            }
        }

        $hosts = [
            $this->elsatic_server_ip . ':' . $this->elsatic_server_port       // IP + Port
        ];

        try {
            $this->client = Elasticsearch\ClientBuilder::create()->
            setSSLVerification(false)->
            setHosts($hosts)->build();
            $this->b_initted = true;
        } catch (Exception $e) {
            will_send_to_error_log('Issue with construction of ES',will_get_exception_string($e));
        }
    }

    public function get_client() {
        return $this->client;
    }

    /**
     * @param string $index
     * @param string[] $log IN OUT REF
     * @return $this
     * @throws Exception
     */
    public function clear_cache($index,&$log = []) {
        if (empty($index)) {throw new InvalidArgumentException("empty index to delete");}
        try{
            $client = $this->get_client();
            if (empty($client)) {
                $client = static::get_client();
            }
            $params = [
                'index' => $index
            ];
            if (!$client->indices()->exists($params)) {
                $response = $client->indices()->create($params);
                $log[] = 'Creating Index of '. $index . ' for elasticsearch'." \n". print_r($response,true);
                FreelinguistDebugFramework::note( 'Creating Index of '. $index . ' for elasticsearch',
                    $response);
            }else{
                $response = $client->indices()->delete($params);
                $log[] = 'Deleting Index of '. $index . ' for elasticsearch'."\n". print_r($response,true);
                FreelinguistDebugFramework::note( 'Deleting Index of '. $index . ' for elasticsearch',
                    $response);

                $response = $client->indices()->create($params);
                $log[] = 'Recreating Index of '. $index . ' for elasticsearch'.": \n". print_r($response,true);
                FreelinguistDebugFramework::note('Recreating Index of '. $index . ' for elasticsearch',
                    $response);
            }
            return $this;
        }catch(Exception $e){
            will_send_to_error_log_and_array($log,"Error clearing/creating index [$index] for ElasticSearch ",$e->getMessage(),true);
            will_send_rate_limited_admin_notice('There is an issue with elastic search',$e->getMessage());
            //throw $e;
            return $this;
        }
    }

    /**
     * @param array[] $params
     * @param string[] $log IN OUT REF
     * @throws Exception
     * @return array
     */
    public function bulk_add($params,&$log = []) {
        try {
            if (!is_array($params)) {
                throw new InvalidArgumentException("Param to bulk send is not an array");
            }

            if (!isset($params['body'])) {
                throw new InvalidArgumentException("Param to bulk send does not have a body");
            }
            $count_me = count( $params['body'])/2;
            $response = $this->get_client()->bulk($params);
            if (is_array($response)) {
                if (isset($response['items'])) {
                    $count_items =  count($response['items']);
                    $copy = $response;
                    unset($copy['items']);
                    $copy['item_count'] = $count_items;
                } else {
                    $copy = ['issue'=>"Response was not an array",'response'=>$response];
                }

            } else {
                $copy = ['issue'=>"items key missing in response array",'response'=>$response];
            }

            $log[] = "Bulk adding $count_me items to elasticsearch".": \n". print_r($copy,true);
            FreelinguistDebugFramework::note("Bulk adding $count_me items to elasticsearch",
                $copy);
            return $response;

        } catch (Exception $e) {
            will_send_to_error_log_and_array($log,"Error sending bulk params to ElasticSearch ",$e->getMessage(),true);
            will_send_rate_limited_admin_notice('There is an issue with elastic search',$e->getMessage());
            //throw $e;
            return [];

        }
    }

    /**
     * @param array[] $params
     * @param string[] $log IN OUT REF
     * @throws Exception
     * @return array //ES response to index
     */
    public function add_index($params,&$log = []) {
        try {
            if (!is_array($params)) {
                throw new InvalidArgumentException("Param to add index is not an array");
            }

            if (!isset($params['body'])) {
                throw new InvalidArgumentException("Param to add index does not have a body");
            }
            $response = $this->get_client()->index($params);
            $log[] = "Index adding item to eleastic search: \n". print_r($response,true);
            FreelinguistDebugFramework::note("Index adding item to elasticsearch",
                $response);

            return $response;

        } catch (Exception $e) {
            will_send_to_error_log_and_array($log,"Error sending add index to ElasticSearch ",$e->getMessage(),true);
            will_send_rate_limited_admin_notice('There is an issue with elastic search',$e->getMessage());
            //throw $e;
            return [];

        }
    }

    /**
     * @param string $type
     * @param string $index
     * @param string $id
     * @param string[] $log IN OUT REF
     * @throws Exception
     * @return array
     */
    public function delete_id_inside_index($type,$index,$id,&$log = []) {
        try {
            if (empty($index) || empty($id)) {
                throw new InvalidArgumentException("Need both index and id to delete an item");
            }


            $response = $this->get_client()->delete([
                'index' => $index,
                'type' => $type,
                'id' => $id
            ]);

            $log[] = "Deleted ID of type [$type], index[$index],id[$id] elasticsearch"." \n". print_r($response,true);
            FreelinguistDebugFramework::note("Deleted ID of type [$type], index[$index],id[$id] elasticsearch",
                $response);

            return $response;

        } catch (Exception $e) {
            //will_send_to_error_log_and_array($log,"Error deleting ElasticSearch ",$e->getMessage(),true);
            //task-future-work re-enable log about not finding deleted index index from ES after test things deleted on laptop
            //will_send_rate_limited_admin_notice('There is an issue with elastic search',$e->getMessage());
            //code-notes do not notify as emergency if something is missing from the ES when deleting, just put it in logs
            //throw $e;
            return [];
        }
    }
}