<?php 

class Client
{
    public $ch;
    public $status = 200;
    public $errors = null;
    public $refresh_data = null;
    public $host = null;
    public $method;
    public $headers = [];
    public $body = null;

    /**
     * Create client
     *
     * @param string $host
     * @param int $port
     * @param string $protocol
     */
    // public function __construct($host,$access_token,$method='POST')
    public function __construct($host, $method='POST')
    {
        $this->host = $host;
        $this->method = $method;
        $this->ch = curl_init();
        $this->setHeader();
    }
   
    /**
     * Perform API request
     *
     * @param string $request
     * @return string
     */
    public function request($request)
    {
        if(strtoupper($this->method) == 'POST') {
            curl_setopt($this->ch, CURLOPT_URL, $this->host);
            curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request);
        } else {
            $query = http_build_query($request);
            curl_setopt($this->ch, CURLOPT_URL, "$this->_host?$query");
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($this->ch, CURLOPT_VERBOSE, false);
        curl_setopt( $this->ch, CURLOPT_HEADER, true );
        
        curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $this->headers );
        $result = curl_exec($this->ch);
        $info   = curl_getinfo( $this->ch );
        $this->status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if (curl_error($this->ch)) {
            $this->errors = curl_error($this->ch);
        }
        if( !$result ) {
            throw new \Exception( "failed curl_exec." );
        }

        $this->extractResponse( $result, $info );
        return $this;
    }

    public function setHeader($headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * \brief Curlインスタンス削除
     */
    public function __destruct()
    {
        if( $this->ch != null ) {
            curl_close( $this->ch );
            $this->ch = null;
        }
    }

    /**
     * \brief 全レスポンスヘッダ取得メソッド
     */
    public function getResponseHeaders()
    {
        if( $this->headers != null ) {
            return $this->headers;
        } else {
            return false;
        }
    }

    /**
     * \brief レスポンスヘッダ取得メソッド
     * @param   $header_name    ヘッダフィールド
     */
    public function getResponseHeader($header_name)
    {
        if( array_key_exists( $header_name, $this->headers ) ) {
            return $this->headers[$header_name];
        } else {
            return null;
        }
    }

    /**
     * \brief レスポンスボディ取得メソッド
     */
    public function getResponseBody()
    {
        if( $this->body != null ) {
            return $this->body;
        } else {
            return null;
        }
    }

    /**
     * \brief レスポンス抽出メソッド
     *
     * レスポンスをヘッダとボディ別に抽出
     *
     * @param   $raw_response   レスポンス文字列
     */
    private function extractResponse($raw_response, $info)
    {
        // ヘッダとボディを分割
        $headers_raw = substr( $raw_response, 0, $info['header_size'] );
        $headers_raw = preg_replace( "/(\r\n\r\n)$/", "", $headers_raw );
        $body_raw    = substr( $raw_response, $info['header_size'] );

        // ヘッダを連想配列形式に変換
        $headers_raw_array = preg_split( "/\r\n/", $headers_raw );
        $headers_raw_array = array_map( "trim", $headers_raw_array );

        foreach( $headers_raw_array as $header_raw ) {

            if( preg_match( "/HTTP/", $header_raw ) ) {
                $headers_asoc_array[0] = $header_raw;
            } elseif( !empty( $header_raw ) ) {
                $tmp = preg_split( "/: /", $header_raw );
                $field = $tmp[0];
                $value = $tmp[1];
                $headers_asoc_array[$field] = $value;
            }

        }

        $this->headers = $headers_asoc_array;
        $this->body    = $body_raw;
    }
}
