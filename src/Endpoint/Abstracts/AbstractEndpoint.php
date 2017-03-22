<?php

namespace MRussell\REST\Endpoint\Abstracts;

use MRussell\Http\Request\Curl;
use MRussell\REST\Auth\AuthControllerInterface;
use MRussell\REST\Endpoint\Data\AbstractEndpointData;
use MRussell\REST\Endpoint\Data\DataInterface;
use MRussell\REST\Endpoint\Interfaces\EndpointInterface;
use MRussell\REST\Exception\Endpoint\InvalidRequest;
use MRussell\REST\Exception\Endpoint\InvalidURLEndpointException;
use MRussell\REST\Exception\Endpoint\InvalidData;
use MRussell\REST\Exception\Endpoint\InvalidOptions;
use MRussell\Http\Response\ResponseInterface;
use MRussell\Http\Request\RequestInterface;

/**
 * Class AbstractEndpoint
 * @package MRussell\REST\Endpoint\Abstracts
 */
abstract class AbstractEndpoint implements EndpointInterface
{

    protected static $_DEFAULT_PROPERTIES = array(
        'url' => '',
        'httpMethod' => '',
        'auth' => FALSE
    );

    /**
     * The Variable Identifier to parse Endpoint URL
     * @var string
     */
    protected static $_URL_VAR_CHARACTER = '$';

    /**
     * The Endpoint Relative URL to the API
     * @var string
     */
    protected static $_ENDPOINT_URL = '';

    /**
     * The initial URL passed into the Endpoint
     * @var string
     */
    protected $baseUrl = '';

    /**
     * The passed in Options for the Endpoint, mainly used for parsing URL Variables
     * @var array
     */
    protected $options = array();

    /**
     * Associative array of properties that define an Endpoint
     * @var array
     */
    protected $properties = array();

    /**
     * The data being passed to the API Endpoint.
     * Defaults to Array, but can be mixed based on how you want to use Endpoint.
     * @var mixed
     */
    protected $data;

    /**
     * The Request Object used by the Endpoint to submit the data
     * @var RequestInterface
     */
    protected $Request;

    /**
     * The Response Object used by the Endpoint
     * @var ResponseInterface
     */
    protected $Response;

    /**
     * @var AuthControllerInterface
     */
    protected $Auth;

    public function __construct(array $options = array(),array $properties = array()) {
        $this->setProperties(static::$_DEFAULT_PROPERTIES);
        if (!empty($options)){
            $this->setOptions($options);
        }
        if (!empty($properties)){
            foreach($properties as $key => $value){
                $this->setProperty($key,$value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProperty($name, $value) {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @inheritdoc
     */
    public function setBaseUrl($url) {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function getEndPointUrl($full = FALSE) {
        $url = static::$_ENDPOINT_URL;
        if (isset($this->properties['url'])&&$this->properties['url']!==''){
            $url = $this->properties['url'];
        }
        if ($full){
            $url = $this->getBaseUrl().$url;
        }
        return $url;
    }

    /**
     * @inheritdoc
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(RequestInterface $Request)
    {
        $this->Request = $Request;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRequest()
    {
        return $this->Request;
    }

    /**
     * @param ResponseInterface $Response
     * @return $this
     */
    public function setResponse(ResponseInterface $Response)
    {
        $this->Response = $Response;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResponse()
    {
        return $this->Response;
    }

    /**
     * @inheritdoc
     * @param null $data - short form data for Endpoint, which is configure by configureData method
     * @return $this
     * @throws InvalidRequest
     * @throws InvalidURLEndpointException
     */
    public function execute()
    {
        if (is_object($this->Request)) {
            $this->configureRequest($this->Request)->send();
            if (is_object($this->Response)){
                $this->configureResponse($this->Response);
            }
        } else {
            throw new InvalidRequest(get_called_class(), "Request property not configured");
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authRequired() {
        $required = FALSE;
        if (isset($this->properties['auth'])){
            $required = $this->properties['auth'];
        }
        return $required;
    }


    /**
     *
     * @inheritdoc
     */
    public function setAuth(AuthControllerInterface $Auth)
    {
        $this->Auth = $Auth;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAuth()
    {
        return $this->Auth;
    }

    /**
     * Verifies URL and Data are setup, then sets them on the Request Object
     * @param RequestInterface $Request
     * @return RequestInterface
     * @throws InvalidURLEndpointException
     * @throws InvalidData
     */
    protected function configureRequest(RequestInterface $Request)
    {
        if ($Request->getStatus() > Curl::STATUS_SENT){
            $Request->reset();
        }
        if (isset($this->properties['httpMethod'])){
            $Request->setMethod($this->properties['httpMethod']);
        }
        $url = $this->configureURL($this->getOptions());
        if ($this->verifyUrl($url)) {
            $url = rtrim($this->getBaseUrl(),"/")."/".$url;
            $Request->setURL($url);
        }
        $data = $this->configureData($this->data);
        $Request->setBody($data);
        if ($this->authRequired()){
            if (isset($this->Auth)){
                $this->Auth->configureRequest($Request);
            }
        }
        return $Request;
    }

    /**
     * Configure the Response Object after sending of the Request
     * @param ResponseInterface $Response
     * @return ResponseInterface
     */
    protected function configureResponse(ResponseInterface $Response){
        $Response->setRequest($this->Request);
        $Response->extract();
        return $Response;
    }

    /**
     * Configures Data on the Endpoint to be set on the Request.
     * @var mixed $data
     * @return array $data
     */
    protected function configureData($data)
    {
        return $data;
    }

    /**
     * Configures the URL, by updating any variable placeholders in the URL property on the Endpoint
     * - Replaces $module with $this->Module
     * - Replaces all other variables starting with $, with options in the order they were given
     * @param array $options
     * @return string
     */
    protected function configureURL(array $options)
    {
        $url = $this->getEndPointUrl();
        if ($this->requiresOptions()) {
            $urlArr = explode("/",$url);
            $optional = FALSE;
            $optionNum = 0;
            foreach($urlArr as $key => $urlPart){
                $replace = NULL;
                if (strpos($urlPart,static::$_URL_VAR_CHARACTER) !== FALSE){
                    if (strpos($urlPart,':') !== FALSE){
                        $optional = TRUE;
                        $replace = '';
                    }
                    $opt = str_replace(array(static::$_URL_VAR_CHARACTER,':'),'',$urlPart);
                    if (isset($options[$opt])){
                        $replace = $options[$opt];
                    }
                    if (isset($options[$optionNum]) && ($replace == '' || $replace == NULL)){
                        $replace = $options[$optionNum];
                        $optionNum = $key+1;
                    }
                    if ($optional && $replace == ''){
                        $urlArr = array_slice($urlArr,0,$key);
                        break;
                    }
                    $urlArr[$key] = $replace;
                }
            }
            $url = implode("/",$urlArr);
            rtrim($url,"/");
        }

        return $url;
    }

    /**
     * Verify if URL is configured properly
     * @param string $url
     * @return bool
     * @throws InvalidURLEndpointException
     */
    private function verifyUrl($url)
    {
        if (strpos($url, static::$_URL_VAR_CHARACTER) !== false) {
            throw new InvalidURLEndpointException(get_called_class(), "Configured URL is ".$url);
        }
        return true;
    }


    /**
     * Checks if Endpoint URL requires Options
     * @return bool|array
     */
    protected function requiresOptions()
    {
        $url = $this->getEndPointUrl();
        $variables = $this->extractUrlVariables($url);
        return !empty($variables);
    }

    /**
     * Helper method for extracting variables via Regex from a passed in URL
     * @param $url
     * @return array
     */
    protected function extractUrlVariables($url){
        $matches = array();
        $pattern = "/(\\".static::$_URL_VAR_CHARACTER.".*?[^\\/]*)/";
        preg_match($pattern,$url,$matches);
        if (!empty($matches)){
            $variables = array();
            foreach($matches as $match){
                $variables = $match[0];
            }
            return $variables;
        }
        return FALSE;
    }

}
