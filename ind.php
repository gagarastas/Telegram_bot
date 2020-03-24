<?php
$start_time=microtime(true);
require_once'/home/users/j/j1026875/incs/database.php';
require_once '/home/users/j/j1026875/incs/classes.php';
require_once '/home/users/j/j1026875/incs/vendor/autoload.php';
$token='mytoken';//i can't tell this token to you(
$proxy="151.253.165.70:8080";
$curent_time=0;
while($curent_time-$start_time<50)
{
    $obr=new handler($token);
    $old_update_id=$obr->get_update_id();
    if(!empty($old_update_id))
    {
        $new_update_id=$old_update_id+1;
        $url="https://api.telegram.org/bot$token/getUpdates?offset=$new_update_id";
        $key=1;
    }
    else
    {
        $url="https://api.telegram.org/bot$token/getUpdates";
        $key=0;
    }
    $curl=curl_init($url);
    curl_setopt($curl, CURLOPT_PROXY, $proxy);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
    $data=curl_exec($curl);
    curl_close($curl);
    $data=json_decode($data,true);
    if(!empty($data))
    {
        if(array_key_exists('result',$data))
        {
            foreach($data['result'] as $info)
            {
                $update_id=$info['update_id'];
                $chat_id=$info['message']['chat']['id'];
                $text=@$info['message']['text'];
                $type=$info['message']['chat']['type'];
                $first_name=$info['message']['chat']['first_name'];
                $obr->set_chat_id($chat_id);
                if($type=='private')
                {
                    if(array_key_exists('entities',$info['message']))
                    {

                        if(array_key_exists('type',$type=$info['message']['entities'][0]))
                        {
                            if ($type['type']=='bot_command')
                            {
                                $text_array = explode(' ',$text);
                                $count=count($text_array);
                                $command=strtolower($text_array[0]);
                                switch($command)
                                {
                                    case '/start':
                                        $answer="Привет $first_name, это бот-хранилище, который позволит тебе сохранять нужные файлы и скачивать их обратно, ниже представлены команды для бота.\nЧтобы можно было сохранять файлы и документы сперва нужно зарегистрироваться(у вас может быть несколько профилей для сохранения файлов, например один для работы, второй для учебы). Чтобы добавить файл(фотографию или документ) просто прикрепите его во вложение и добавьте подпись которая будет использована в качестве названия файла(зная название файла можно будет его запрашивать обратно). Прикреплять файлы нужно по одному.\n Команды:\n/info -информация о боте и список команд \n /register username password - зарегистрироваться\n /login username password-залогинится \n/logout-разлогинится\n /getfile filename-посмотреть конкретный файл. Можно указывать часть имени, если не помните полное. Если есть несколько файлов с данным именем, то высылает последний сохраненный\n/images-список всех ваших изображений\n /docs-список всех ваших документов\n /delete filename -удалить файл. В случае если присутствует несколько файлов с этим именем, то удаляется самый старый, поэтому во избежание удаления не того файла, удастоверьтесь что имя файла правильное\nБот пока не работает и находится на стадии разработки.";
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/info':
                                        $answer="Чтобы можно было сохранять файлы и документы сперва нужно зарегистрироваться(у вас может быть несколько профилей для сохранения файлов, например один для работы, второй для учебы). Чтобы добавить файл(фотографию или документ) просто прикрепите его во вложение и добавьте подпись которая будет использована в качестве названия файла(зная название файла можно будет его запрашивать обратно). Прикреплять файлы нужно по одному.\n Команды:\n/info -информация о боте и список команд \n /register username password - зарегистрироваться\n /login username password-залогинится \n/logout-разлогинится\n /getfile filename-скачать конкретный файл. Можно указывать часть имени, если не помните полное. Если есть несколько файлов с данным именем, то высылает последний сохраненный\n/images-список всеx вашиx изображений\n /docs-список всех ваших документов\n /delete filename -удалить файл. В случае если присутствует несколько файлов с этим именем, то удаляется самый старый, поэтому во избежание удаления не того файла, удастоверьтесь что имя файла правильное\nБот пока не работает и находится на стадии разработки.";
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/logout':
                                        $answer=$obr->logout();
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/images':
                                        $answer= $obr->get_all_files($proxy,'image');
                                        break;
                                    case '/docs':
                                        $answer= $obr->get_all_files($proxy,'docs');
                                        break;
                                    case '/register':
                                        if(isset($text_array[1])and isset($text_array[2]))
                                        {
                                            $answer=$obr->register($text_array[1],$text_array[2]);
                                        }
                                        else
                                            $answer='Чтобы зарегистрироваться в боте введите команду, логин и пароль в формате /register username password';
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/login':
                                        if(isset($text_array[1])and isset($text_array[2]))
                                        {
                                            $answer=$obr->login($text_array[1],$text_array[2]);
                                        }
                                        else
                                            $answer='Чтобы войти в свой аккаунт в боте введите команду, логин и пароль в формате /login username password';
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/deleteuser':
                                        if(isset($text_array[1])and isset($text_array[2]))
                                        {
                                            $answer=$obr->delete_user($text_array[1],$text_array[2]);
                                        }
                                        else
                                            $answer='Чтобы удалить пользователя (а вместе с ним и все сохраненные файлы) введите команду, логин и пароль в формате /deleteuser username password';
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    case '/getfile':
                                        if(isset($text_array[1]))
                                            $answer=$obr->get_file($text_array[1],$proxy);
                                        else
                                        {
                                            $answer='чтобы скачать файл введите команду и название файла(полностью или частично) в формате /getfile filename';
                                            $ans=$obr->text_response($answer,$proxy);
                                        }
                                        break;
                                    case '/delete':
                                        if(isset($text_array[1]))
                                            $answer=$obr->delete_file($text_array[1]);
                                        else
                                            $answer='чтобы удалить файл введите команду и название файла(полностью или частично) в формате /deletefile filename\n Если есть несколько файлов с таким названием, то удаляется самый старый. Рекомендуется писать полное название файла, чтобы избежать ситуации когда удаляется не тот файл';
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                    default:
                                        $answer='Неизвестная команда, используйте команду /info чтобы получить справку и список команд';
                                        $ans=$obr->text_response($answer,$proxy);
                                        break;
                                }

                            }
                        }
                    }
                    elseif(array_key_exists('photo',$info['message']))
                    {
                        if(array_key_exists('caption',$info['message']))
                        {
                            $caption=str_replace(' ','_',$info['message']['caption']);
                            $caption=str_replace('.','_',$caption);
                            $photo_id=$info['message']['photo'][2]['file_id'];
                            //$answer=$obr->get_file_from_server_and_save($proxy,$photo_id,$caption);
                            //$ans=$obr->text_response($answer.": $caption",$proxy);
                            $answer=$obr->save_file($proxy,$photo_id,$caption);
                            $ans=$obr->text_response($answer,$proxy);

                        }
                        else
                            $ans=$obr->text_response("Вы забыли указать подпись к фотографии, которая будет использоваться в качестве названия фотографии. Повторите отправку и не забудьте указать подпись",$proxy);

                    }
                    elseif(array_key_exists('document',$info['message']))
                    {
                        if(array_key_exists('caption',$info['message']))
                        {
                            $caption=str_replace(' ','_',$info['message']['caption']);
                            $caption=str_replace('.','_',$caption);
                            $file_id=$info['message']['document']['file_id'];
                            $answer=$obr->save_file($proxy,$file_id,$caption);
                            $ans=$obr->text_response($answer,$proxy);
                        }
                        else
                            $ans=$obr->text_response("Вы забыли указать подпись к файлу, которая будет использоваться в качестве названия файла. Повторите отправку и не забудьте указать подпись",$proxy);
                    }
                    else
                    {
                        $answer='Неизвестная команда, используйте команду /info чтобы получить справку и список команд';
                        $ans=$obr->text_response($answer,$proxy);
                    }
                }

            }
            if($key==1)
                $obr->set_update_id($update_id,1);
            if($key==0)
                $obr->set_update_id($update_id,0);
        }
    }
    unset($obr);
    $curent_time=microtime(true);
}
$time=$curent_time-$start_time;
?>


