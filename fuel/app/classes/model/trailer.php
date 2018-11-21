<?php

class Model_Trailer
{
    private $_title;

    private $_year;

    private $_type;

    private $_url;

    public $trailer;

    public function __construct($title, $year, $type)
    {
        $this->_title   = $title;
        $this->_year   = $year;
        $this->_type    = $type;

        if($this->_type === 'movie') {
            $this->getUrl();

            if($this->_url === null)
                return;

            $this->getMovieTrailer();

            if(!$this->trailer)
                $this->getMovieTeaser();

            return $this->trailer;
        }
    }

    private function getUrl()
    {
        $html = Request::forge('https://www.themoviedb.org/search/movie?query=' . urlencode($this->_title) . '+y%3A' . $this->_year . '&language=us', 'curl');
        $html->execute();

        if ($html->response()->status !== 200)
            return false;

        $media = $html->response()->body;

        preg_match('/<a id="[a-z0-9_]*" data-id="[a-z0-9]*" data-media-type="movie" data-media-adult="[a-z]*" class="[a-z]*" href="(\/movie\/[\d]*\?language\=us)" title=".*" alt=".*">/i', $media, $urls);

        if (!isset($urls[1]))
            return false;

        $this->_url = explode('?', $urls[1])[0];
    }

    private function getMovieTrailer()
    {
        $html = Request::forge('https://www.themoviedb.org' . $this->_url . '/videos?active_nav_item=Trailers&video_language=en-US&language=en-US', 'curl');
        $html->set_options(array(
                CURLOPT_FOLLOWLOCATION => true,
            )
        );
        $html->execute();

        if ($html->response()->status !== 200)
            return false;

        $media = $html->response()->body;

        preg_match('/<iframe type="text\/html" src="(\/\/www.youtube.com\/embed\/[a-zA-Z0-9\_\-]*\?enablejsapi\=1&autoplay\=0\&origin\=https%3A%2F%2Fwww\.themoviedb\.org\&hl\=en-US\&modestbranding\=1\&fs\=1)" frameborder\="0" allowfullscreen><\/iframe>/', $media, $youtube);

        if (!isset($youtube[1]))
            return false;

        $this->trailer = $youtube[1];
    }

    private function getMovieTeaser()
    {
        $html = Request::forge('https://www.themoviedb.org' . $this->_url . '/videos?active_nav_item=Teasers&video_language=en-US&language=en-US', 'curl');
        $html->set_options(array(
                CURLOPT_FOLLOWLOCATION => true,
            )
        );
        $html->execute();

        if ($html->response()->status !== 200)
            return false;

        $media = $html->response()->body;

        preg_match('/<iframe type="text\/html" src="(\/\/www.youtube.com\/embed\/[a-zA-Z0-9\_]*\?enablejsapi\=1&autoplay\=0\&origin\=https%3A%2F%2Fwww\.themoviedb\.org\&hl\=en-US\&modestbranding\=1\&fs\=1)" frameborder\="0" allowfullscreen><\/iframe>/', $media, $youtube);

        if (!isset($youtube[1]))
            return false;

        $youtube = preg_replace('/\&origin\=https%3A%2F%2Fwww\.themoviedb\.org/i', '', $youtube[1]);

        $this->trailer = $youtube;
    }
}