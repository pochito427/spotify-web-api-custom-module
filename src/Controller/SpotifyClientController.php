<?php
/**
 * @file
 * Contains \Drupal\spotify_client\Controller\SpotifyClientController.
 */

namespace Drupal\spotify_client\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Controlador que retorna el contenido de las páginas definidas
 */
class SpotifyClientController extends ControllerBase
{

    protected $client;

    public function __construct()
    {
        $this->client = \Drupal::httpClient();
    }

    /**
     * Autenticación
     * @return mixed|void
     */
    private function autorization()
    {

        try {
            $autorization = $this->client->request('POST', 'https://accounts.spotify.com/api/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => '2edd06339a844ce1a95d978f1cf61e04',
                    'client_secret' => 'bb38a872461c43a5b5b8dced0d75d322'
                ]
            ]);

            return $response = json_decode($autorization->getBody());
        } catch (GuzzleException $e) {
            return \Drupal::logger('spotify_cliernt')->error($e);
        }

    }

    /**
     * Página de canciones detacadas
     */
    public function newReleases()
    {

        $auth = $this->autorization();

        try {
            $request = $this->client->request('GET', 'https://api.spotify.com/v1/browse/new-releases', [
                'headers' => [
                    'Authorization' => $auth->token_type . ' ' . $auth->access_token
                ]
            ]);

            $releases = json_decode($request->getBody());
        } catch (GuzzleException $e) {
            return \Drupal::logger('spotify_cliernt')->error($e);
        }

        $build['releases_page'] = [
            '#theme' => 'new_releases_page',
            '#releases' => $releases,
        ];

        return $build;

    }

    /**
     * Página de artista con top de canciones
     * @param $id
     */
    public function artist($id)
    {

        $auth = $this->autorization();

        try {
            $requestArtist = $this->client->request('GET', 'https://api.spotify.com/v1/artists/' . $id, [
                'headers' => [
                    'Authorization' => $auth->token_type . ' ' . $auth->access_token
                ]
            ]);

            $responseArtist = json_decode($requestArtist->getBody());
        } catch (GuzzleException $e) {
            return \Drupal::logger('spotify_cliernt')->error($e);
        }

        try {
            $requestTracks = $this->client->request('GET', 'https://api.spotify.com/v1/artists/' . $id . '/top-tracks?country=ES', [
                'headers' => [
                    'Authorization' => $auth->token_type . ' ' . $auth->access_token
                ]
            ]);

            $responseTracks = json_decode($requestTracks->getBody());
        } catch (GuzzleException $e) {
            return \Drupal::logger('spotify_cliernt')->error($e);
        }

        $build['artist_page'] = [
            '#theme' => 'artist_page',
            '#artist' => $responseArtist,
            '#tracks' => $responseTracks,
        ];
        return $build;

    }

}