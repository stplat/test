<?php

namespace Controllers;

use Jenssegers\Blade\Blade;
use Illuminate\Database\Capsule\Manager as Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Httpful\Request as Client;
use Httpful\Mime;

use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Psr7;

class IndexController
{
    protected $request;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->response->headers->set('Content-Type', 'application/json');
    }

    /**
     * Отображаем главную страницу
     *
     * @return
     */
    public function index()
    {




        $blade = new Blade('views', 'cache');



        echo $blade->make('index', ['name' => 'John Doe'])->render();
    }

    /**
     * Обрабатываем загрузку фотографий
     *
     * @return
     */
    public function photo()
    {
        $file = $this->request->files->get('photo');
        $photoName = $file->getClientOriginalName();
        $photoMime = $file->getMimeType();

        $uploadDir = 'storage/';
        $path = $uploadDir . basename($photoName);

        $client = new GuzzleHttp();

        $client = $client->request('POST', 'http://merlinface.com:12345/api/', [
            'multipart' => [
                [
                    'name' => $photoMime,
                    'photo' => curl_file_create($path, $photoMime, basename($photoName)),
                    'contents' => Psr7\Utils::tryFopen($path, 'r'),
                    'headers' => [
                        // 'Content-Type' => 'multipart/form-data'
                    ],
                ],
            ],
            // 'headers' => [
            //     'Content-Type' => 'multipart/form-data'
            // ],
        ]);

        // $client = Client::post('http://merlinface.com:12345/api/')
        //     ->body([
        //         'name' => $photoName,
        //         'photo' =>  curl_file_create('https://purepng.com/public/uploads/thumbnail/purepng.com-donald-duckdonald-duckdonaldduckcartoon-character1934walt-disneywhite-duck-1701528532083emc6z.png')
        //     ], Mime::FORM)
        //     ->send();

        echo '<pre>';
        var_dump($client->body);
        echo '</pre>';

        if (!file_exists($path)) {
            if (move_uploaded_file($file, $path)) {
                $taskId = Database::table('tasks')->insertGetId([
                    'status' => 'received',
                    'photo_name' => $photoName,
                ]);


                // $asd = Client::post('http://merlinface.com:12345/api/')
                //     ->body([
                //         'name' => $photoName,
                //         'photo' => curl_file_create($path, $photoMime, basename($photoName))
                //     ], Mime::FORM)
                //     ->send();

                // echo '<pre>';
                // var_dump($file);
                // echo '</pre>';
                // $client->request('POST', 'http://merlinface.com:12345/api/', [
                //     'multipart' => [
                //         [
                //             'name' => 'asd',
                //             'contents' => 'abc'
                //         ],
                //     ],
                //     'headers' => [
                //         'Accept' => 'application/json',
                //         'Content-Type' => 'multipart/form-data'
                //     ],
                // ]);

                // $data = mb_convert_encoding($data, 'UTF-8', 'windows-1251');

                $this->response->setContent(json_encode([
                    'task' => $taskId,
                    'status' => 'received',
                    'result' => null,
                    // 'asd' => $asd->body
                ]));

                $this->response->send();
            }
        } else {
            $task = Database::table('tasks')->where('photo_name', $photoName)->first();

            $this->response->setContent(json_encode([
                'status' => 'ready',
                'result' => $task->result,
            ]));

            $this->response->send();
        }
    }
}
