<?php
    // read existing caches and test for lifetime
    class cacheEntry {
        
        public $timestamp;
        public $hash;
        public $result;
        
        function __construct ($xtimestamp, $xhash, $xresult) {
            $this->timestamp = $xtimestamp;
            $this->hash = $xhash;
            $this->result = $xresult;
        }
    }
    
    class cache {
        
        public $cachePath = 'cache.txt';
        public $cachedSearches = array();
        
        function __construct () {
            if (file_exists($this->cachePath)) {
                // load cache to php
                $caches = file($this->cachePath);
                foreach ($caches as $cache) {                                        
                    $cacheEntryParts = explode('@@@|||@@@', trim($cache));
                    $cacheEntry = new cacheEntry($cacheEntryParts[0], $cacheEntryParts[1], $cacheEntryParts[2]);
                    // lifetime ok?
                    if (time() < ($cacheEntryParts[0] + 24 * 60 * 60)) {
                        array_push($this->cachedSearches, (array)$cacheEntry);
                    }
                }
                // check size of cache and eventually delete old cache-entries
                $file = $this->cachePath;
                $linecount = 0;
                $handle = fopen($file, "r");
                while(!feof($handle)){
                  $line = fgets($handle);
                  $linecount++;
                }
                fclose($handle);
                
                if ($linecount > 500) {
                    $output = '';
                    foreach ($this->cachedSearches as $cache) { 
                        $output .= $cache['timestamp'] . '@@@|||@@@' . $cache['hash'] . '@@@|||@@@' . $cache['result'] . '
';
                    }
                    file_put_contents($this->cachePath, $output);
                }                
            }        
        }
    }

    // process caching-mechanisms
    class cacher {
    
        private $caches;
        private $hash;
        private $isInCache = 0;

    	function __construct ($hash) {
            $this->caches = new cache();
            $this->hash = $hash;
	}
	
        // take from cache, if available
	public function getFromCache () {
            foreach ($this->caches->cachedSearches as $entry) {
                if ($entry['hash'] == $this->hash) {
                    $this->isInCache = 1;
                    return $entry['result'];                    
                }
            }
            return 0;
        }
        
        // if not in cache, add to cache
        public function putToCache ($result) {    
            if ( ! $this->isInCache ) {
                $newEntry = '
' . time() . '@@@|||@@@' . $this->hash . '@@@|||@@@' . $result . '';
                file_put_contents($this->caches->cachePath, $newEntry, FILE_APPEND);
            }
	}
    }
?>