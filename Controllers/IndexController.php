<?php

namespace Controllers;

use Jenssegers\Blade\Blade;
use Illuminate\Database\Capsule\Manager as Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Httpful\Request as Client;
use Httpful\Mime;

use GuzzleHttp\Client as GuzzleHttp;

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

        $asd = Client::post('http://merlinface.com:12345/api/')
            ->body([
                'name' => $photoName,
                'photo' => curl_file_create('C:\OpenServer\domains\max-test.loc\storage\photo_2020-07-16_15-13-561.jpg', $photoMime, basename($photoName))
            ], Mime::FORM)
            ->send();

        echo '<pre>';
        var_dump($asd->body);
        echo '</pre>';

        if (!file_exists($path)) {
            if (move_uploaded_file($file, $path)) {
                $taskId = Database::table('tasks')->insertGetId([
                    'status' => 'received',
                    'photo_name' => $photoName,
                ]);


                $asd = Client::post('http://merlinface.com:12345/api/')
                    ->body([
                        'name' => $photoName,
                        'photo' => curl_file_create('C:\OpenServer\domains\max-test.loc\storage\photo_2020-07-16_15-13-561.jpg', $photoMime, basename($photoName))
                    ], Mime::FORM)
                    ->send();

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
                    'asd' => $asd->body
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
