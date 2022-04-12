<?php
require '../vendor/autoload.php';

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function indexAction()
    {
    }

    public function spotifyAction()
    {

        $query = ["client_id" => '6ffe73b6125d488287b94a895cec5662', 'redirect_uri' => 'http://localhost:8080/api/spotifyToken', 'scope' => 'user-read-email playlist-modify-public playlist-read-private playlist-modify-private', 'response_type' => 'code', 'show_dialog' => 'true']; {
            $que = http_build_query($query, '', '&');
            $url2 = "https://accounts.spotify.com/authorize?" . $que . "";
            http_build_query($query);
            $this->response->redirect($url2);
        }
    }
    public function spotifyTokenAction()
    {

        $code = $this->request->getQuery('code');
        $clientId = '6ffe73b6125d488287b94a895cec5662';
        $clientSecret = 'fb97c1cabef048a5ac8212430bfe733e';
        $url = "https://accounts.spotify.com";

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($clientId . ":" . $clientSecret)
        ];

        $client = new Client(
            [

                'base_uri' => $url,
                'headers' => $headers
            ]
        );
        $query = ["grant_type" => 'authorization_code', 'code' => $code, 'redirect_uri' => 'http://localhost:8080/api/spotifyToken'];
        $response = $client->request('POST', '/api/token', ['form_params' => $query]);
        $response =  $response->getBody();
        $response = json_decode($response, true);
        // echo "<pre>";
        // print_r($response);
        $this->session->tokens = $response;
        // print_r($this->session->tokens);
        // die;
        $this->response->redirect('/api/search');
    }
    public function searchAction()
    {
        $this->addPlaylistAction();
        $add = $this->getPlaylists();
        // print_r($add);
        // die;

        $url = "https://api.spotify.com/";
        $data = $this->request->getPost();
        if ($this->request->has('tosearch')) {
            $client = new Client(
                [
                    'base_uri' => $url
                ]
            );
            if (isset($data) != 0) {
                $checked = $data['box'];
                $type = "";
                foreach ($checked as $check)

                    $type .=  $check . ",";
                $type = substr($type, 0, strlen($type) - 1);

                // echo $type;
                // die;



                $query = ['q' => $data['tosearch'], 'type' => $type, 'access_token' => $this->session->tokens['access_token']];
                print_r($query);
                $response = $client->request('GET', '/v1/search', ['query' => $query]);
                $response = $response->getBody();
                $response = json_decode($response, true);
                $this->view->response = $response;
                // echo "<pre>";
                // print_r($response['albums']['items']);
                // die;
                $disp = '';

                foreach ($response as $key => $val) {
                    $disp .= "<h1 class='text-center text-warning'>" . $key . "</h1><div class='row '>";
                    foreach ($val['items'] as $k => $v) {
                        $disp .= '<form action="/api/addsong/" method="post">
                        <div class="col-4 border border-3 ">
                                    <img class="" src="' . $v['images'][0]['url'] . '" alt="Card image" width="100px !important" height="100px !important">
                                    <div class="">
                                    <h4 class="">Name : ' . $v['name'] . '</h4>
                                    <p class="">Popularity : ' . $v['popularity'] . '</p>
                                    <p>Add to playlist</p>
                                    <input type="hidden" name="uri" value=' . $v['uri'] . '>

                                   <select name="playlist" onchange="submit()">
                                        <option>select to add in playlist</option>';
                        foreach ($add as $k => $v) {
                            $disp .= '<option value=' . $v['id'] . '>' . $v["name"] . '
                                        </option>';
                        }



                        $disp .= '</select> 
                                    
                                    </div>
                                </div>
                                </form>';
                        $this->session->id = $v['id'];
                    }
                    $disp .= "</div>";
                }
                $this->view->show = $disp;
            }
        }
    }
    public function addsongAction()
    {
        // echo 'hello';
        $uriid = $this->request->get('uri');
        $playlist = $this->request->get('playlist');
        $token = $this->session->tokens['access_token'];
        $url = "https://api.spotify.com/";
        $client = new Client(
            [


                'base_uri' => $url,
                'headers' => ['Authorization' => 'Bearer ' . $token]

            ]

        );
        $response = $client->request('POST', "/v1/playlists/$playlist/tracks?uris=$uriid");
        $response = json_decode($response->getBody(), true);
        $this->response->redirect('api/search/');
        // echo"<pre>";
        // print_r($response);
        // die;

    }
    public function addPlaylistAction()
    {
        if ($this->request->isPost() && $this->request->has('playlist')) {


            $token = $this->session->tokens['access_token'];
            // print_r($token);

            $url = "https://api.spotify.com/";
            $playlist = $this->request->getPost();
            // print_r($playlist['playlist']);
            // die;
            $client = new Client(
                [


                    'base_uri' => $url,
                    'headers' => ['Authorization' => 'Bearer ' . $token]

                ]

            );
            $play = ([
                "name" => $playlist['playlist'],
                "description" => "New Playlist",
                "public" => false

            ]);

            $response = $client->request('POST', '/v1/users/j9e5elonsm41b0adbr77zwtnp/playlists', ['body' => json_encode($play)]);
            $response = json_decode($response->getBody(), true);
            $this->response->redirect('/api/displayplaylist');
        }
    }
    public function displayplaylistAction()
    {

        $arr = $this->getPlaylists();
        // echo "<pre>";
        // print_r($arr);
        // die;
        $display = "";
        $display .= "<h1 style='text-align:center'>All Playlists</h1>";
        $display .= "<table><tr><th>Playlist Name</th>
                    <th>Description</th>
                     </tr>";
        foreach ($arr as $key => $val) {
            $display .= "<tr>
           <td>" . $val['name'] . "</td>
           <td>" . $val['description'] . "</td>
           <td><a class='btn btn-dark text-white' href='/api/viewlist?id=" . $val['id'] . "'>Show</button></a>
           </tr>";
        }
        $display .= "</table>";
        echo $display;
    }
    public function getPlaylists()
    {
        $token = $this->session->tokens['access_token'];
        $url = "https://api.spotify.com/v1/me/playlists";
        $client = new Client(
            [


                'base_uri' => $url,
                'headers' => ['Authorization' => 'Bearer ' . $token]

            ]

        );
        $response = $client->request('GET', $url);
        $response = json_decode($response->getBody(), true);
        echo "<pre>";
        return $response['items'];
    }
    public function viewlistAction()
    {
        $id = $this->request->get('id');
        $token = $this->session->tokens['access_token'];
        $url = "https://api.spotify.com/";
        $client = new Client(
            [


                'base_uri' => $url,
                'headers' => ['Authorization' => 'Bearer ' . $token]

            ]

        );
        $response = $client->request('GET', "v1/playlists/$id");
        $response = json_decode($response->getBody(), true);
        $this->view->response = $response;
    }
    public function deleteAction()
    {
        $del_id=$this->request->get('uid');
        $pid=$_GET['id'];
        echo $pid ;
        // echo $del_id;
        // die;
        $token = $this->session->tokens['access_token'];
        $url = "https://api.spotify.com/";
        $client = new Client(
            [


                'base_uri' => $url,
                'headers' => ['Authorization' => 'Bearer ' . $token]

            ]

        );
        $arg= ["tracks"=>[['uri'=>$del_id]]];
        $response = $client->request('DELETE', "/v1/playlists/$pid/tracks", ['body'=>json_encode($arg)]);
        $response = json_decode($response->getBody(), true);
        $this->response->redirect('/api/displayplaylist/');

    }
}
