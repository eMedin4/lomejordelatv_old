<?php
namespace App\Http\Controllers\Administration;

use Illuminate\Http\Request;
use App\Repositories\MovieRepository;
use App\Library\NetflixRepository;

	

class ManageMovies
{
    private $request;
    private $movieRepository;
    private $netflixRepository;

    public function __Construct(Request $request, MovieRepository $movieRepository, NetflixRepository $netflixRepository)
	{
        $this->request = $request;
        $this->netflixRepository = $netflixRepository;
        $this->movieRepository = $movieRepository;
    }

    public function index($provider)
    {     
        $sort = ($this->request->has('sort')) ? $this->request->input('sort') : 'destacadas';
        if ($provider == "netflix") {
            $movies = $this->movieRepository->getNetflix('peliculas', 'todas', $sort, 1000);
            $shows = $this->movieRepository->getNetflix('series', 'todas', $sort, 1000);
            $provider = "netflix";
        } elseif ($provider == "amazon") {
            $movies = $this->movieRepository->getAmazon('peliculas', $sort, 1000);
            $shows = $this->movieRepository->getAmazon('series', $sort, 1000);
            $provider = "amazon";
        } elseif ($provider == "hbo") {
            $movies = $this->movieRepository->getHbo('peliculas', $sort, 1000);
            $shows = $this->movieRepository->getHbo('series', $sort, 1000);
            $provider = "hbo";
        }
        
        return view('administration.manageMovies', compact('movies', 'shows', 'provider'));
    }

    public function store()
    {
        $this->netflixRepository->setTrending($this->request->input('id'), $this->request->input('value'));

    }

}