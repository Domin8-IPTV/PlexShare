<?php

use Fuel\Core\Controller_Rest;
use Fuel\Core\View;
use Fuel\Core\FuelException;

class Controller_Rest_Movie extends Controller_Rest
{
    public function get_stream()
    {
        try {
            $movie_id = Input::get('movie_id');

            if (!$movie_id)
                throw new FuelException('No movie id');

            $movie = Model_Movie::find_by_pk($movie_id);

            if (!$movie)
                throw new FuelException('No movie found');

            $user_settings = Model_Settings::find_one_by('user_id', Session::get('user')->id);

            if ($movie->type !== 'movie')
                $episodes = $movie->getSeason()->getEpisodes();
            else
                $episodes = [$movie];

            $view = View::forge('stream/index');

            $view->set('user_settings', $user_settings);
            $view->set('movie', $movie);
            $view->set('episodes', $episodes);

            return $this->response($view->render());
        } catch (Exception $exception) {
            return $this->response($exception->getMessage(), 500);
        }
    }
}