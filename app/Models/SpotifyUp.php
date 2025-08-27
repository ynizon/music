<?php

namespace App\Models;

use Aerni\Spotify\PendingRequest;
use Aerni\Spotify\Spotify;

class SpotifyUp extends Spotify
{
    /**
     * Get Spotify catalog information about an artist’s albums. Optional parameters can be specified in the query string to filter and sort the response.
     *
     * @param string $id
     * @return PendingRequest
     */
    public function artistAlbums(string $id): PendingRequest
    {
        $endpoint = '/artists/'.$id.'/albums/';

        $acceptedParams = [
            'include_groups' => null,
            'country' => $this->defaultConfig['country'],
            'limit' => 50, //INCREASE LIMIT MAX
            'offset' => null,
        ];

        return new PendingRequest($endpoint, $acceptedParams);
    }
}
